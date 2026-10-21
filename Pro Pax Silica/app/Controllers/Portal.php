<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;

class Portal extends BaseController
{
    private const SERVICES = ['Consultation', 'Designing', 'Permitting', 'Installation', 'Maintenance', 'Repair', 'Monitoring'];
    private const DEPARTMENTS = ['Administration', 'IT', 'Dispatch', 'Accounting', 'HR', 'Marketing', 'Sales', 'Customer Service'];

    public function index()
    {
        $user = $this->currentUser();
        if (! $user) return redirect()->to(site_url('account?mode=signin'))->with('status', 'Please sign in to continue.');
        if ((int) ($user['must_change_password'] ?? 0)) return view('password_reset', ['user' => $user, 'status' => session()->getFlashdata('status'), 'errors' => session()->getFlashdata('errors') ?? []]);
        $db = db_connect();
        $data = ['user' => $user, 'status' => session()->getFlashdata('status'), 'errors' => session()->getFlashdata('errors') ?? []];

        if ($user['role'] === 'admin') {
            $data['jobs'] = $db->table('schedules s')->select('s.*, c.first_name AS customer_first, c.last_name AS customer_last, e.user_id AS tech_user_id, t.first_name AS tech_first, t.last_name AS tech_last')
                ->join('users c', 'c.id = s.customer_id')->join('employees e', 'e.id = s.assigned_employee_id', 'left')->join('users t', 't.id = e.user_id', 'left')->orderBy('s.scheduled_start', 'DESC')->get()->getResultArray();
            $data['employees'] = $db->table('employees e')->select('e.id, e.department, u.first_name, u.last_name')->join('users u', 'u.id = e.user_id')->where('u.is_active', 1)->orderBy('u.last_name')->get()->getResultArray();
            $data['customers'] = (new UserModel())->where('role', 'customer')->orderBy('created_at', 'DESC')->findAll();
            $data['allUsers'] = (new UserModel())->orderBy('role')->orderBy('created_at', 'DESC')->findAll();
            $data['tickets'] = $db->table('support_tickets t')->select('t.*, u.first_name, u.last_name')->join('users u', 'u.id = t.user_id')->orderBy('t.created_at', 'DESC')->get()->getResultArray();
            $data['incidents'] = $db->table('incidents i')->select('i.*, u.first_name, u.last_name, s.service_type')->join('employees e', 'e.id = i.employee_id')->join('users u', 'u.id = e.user_id')->join('schedules s', 's.id = i.schedule_id', 'left')->orderBy('i.created_at', 'DESC')->get()->getResultArray();
            $data['catalog'] = $db->table('catalog_items')->orderBy('kind')->orderBy('name')->get()->getResultArray();
            $data['stats'] = [
                'customers' => $db->table('users')->where('role', 'customer')->countAllResults(),
                'requests' => $db->table('schedules')->whereIn('status', ['requested', 'approved'])->countAllResults(),
                'completed' => $db->table('schedules')->where('status', 'completed')->countAllResults(),
                'revenue' => $db->table('payments')->selectSum('amount')->where('status', 'paid')->get()->getRowArray()['amount'] ?? 0,
            ];
            $data['usage'] = $db->table('schedules')->select('service_type, COUNT(*) AS total')->groupBy('service_type')->orderBy('total', 'DESC')->get()->getResultArray();
        } elseif ($user['role'] === 'employee') {
            $employee = $db->table('employees')->where('user_id', $user['id'])->get()->getRowArray();
            $data['employee'] = $employee;
            $data['jobs'] = $employee ? $db->table('schedules s')->select('s.*, u.first_name AS customer_first, u.last_name AS customer_last')
                ->join('users u', 'u.id = s.customer_id')->where('s.assigned_employee_id', $employee['id'])->orderBy('s.scheduled_start')->get()->getResultArray() : [];
        } else {
            $data['jobs'] = $db->table('schedules')->where('customer_id', $user['id'])->orderBy('scheduled_start', 'DESC')->get()->getResultArray();
            $data['services'] = $db->table('catalog_items')->where('kind', 'service')->where('is_active', 1)->orderBy('name')->get()->getResultArray();
            $data['payments'] = $db->table('payments')->where('user_id', $user['id'])->orderBy('paid_at', 'DESC')->get()->getResultArray();
            $data['tickets'] = $db->table('support_tickets')->where('user_id', $user['id'])->orderBy('created_at', 'DESC')->get()->getResultArray();
        }

        $data['notifications'] = $db->table('notifications')->groupStart()->where('user_id', $user['id'])->orWhere('user_id', null)->groupEnd()->orderBy('created_at', 'DESC')->limit(8)->get()->getResultArray();
        $data['announcements'] = $db->table('announcements')->groupStart()->where('target_role', $user['role'])->orWhere('target_role', null)->groupEnd()->orderBy('created_at', 'DESC')->limit(8)->get()->getResultArray();
        return view('portal', $data);
    }

    public function book(): RedirectResponse
    {
        if ($response = $this->authorize(['customer'])) return $response;
        $user = $this->currentUser();
        $rules = ['service_type' => 'required|max_length[32]', 'scheduled_start' => 'required|valid_date[Y-m-d\\TH:i]', 'service_address' => 'required|max_length[255]', 'customer_notes' => 'permit_empty|max_length[4000]'];
        $data = $this->request->getPost();
        if (! $this->validateData($data, $rules)) return $this->backWithErrors();
        $service = db_connect()->table('catalog_items')->where('kind', 'service')->where('name', $data['service_type'])->where('is_active', 1)->countAllResults();
        if (! $service || strtotime($data['scheduled_start']) < time()) return redirect()->to(site_url('portal'))->with('status', 'Choose an available service and a future appointment time.');
        db_connect()->table('schedules')->insert(['customer_id' => $user['id'], 'service_type' => $data['service_type'], 'status' => 'requested', 'scheduled_start' => date('Y-m-d H:i:s', strtotime($data['scheduled_start'])), 'service_address' => trim($data['service_address']), 'customer_notes' => trim($data['customer_notes'] ?? ''), 'created_at' => date('Y-m-d H:i:s')]);
        $this->notifyAdmins('New service request', $user['first_name'] . ' ' . $user['last_name'] . ' requested ' . $data['service_type'] . '.', 'schedule');
        return redirect()->to(site_url('portal'))->with('status', 'Your service request has been sent for review.');
    }

    public function cancelBooking(int $id): RedirectResponse
    {
        if ($response = $this->authorize(['customer'])) return $response;
        $user = $this->currentUser();
        $db = db_connect();
        $job = $db->table('schedules')->where('id', $id)->where('customer_id', $user['id'])->whereIn('status', ['requested', 'approved'])->get()->getRowArray();
        if (! $job || strtotime($job['scheduled_start']) <= time()) return redirect()->to(site_url('portal'))->with('status', 'This service can no longer be cancelled online. Please contact support.');
        $db->table('schedules')->where('id', $id)->update(['status' => 'cancelled']);
        $this->notifyAdmins('Service request cancelled', $user['first_name'] . ' cancelled a ' . $job['service_type'] . ' appointment.', 'schedule');
        return redirect()->to(site_url('portal'))->with('status', 'Your appointment request has been cancelled.');
    }

    public function updateProfile(): RedirectResponse
    {
        if ($response = $this->authorize()) return $response;
        $user = $this->currentUser();
        $data = $this->request->getPost();
        $rules = ['first_name' => 'required|max_length[100]', 'last_name' => 'required|max_length[100]', 'middle_name' => 'permit_empty|max_length[100]', 'phone' => 'required|regex_match[/^[0-9+()\\-\\s]{7,}$/]|max_length[32]', 'address' => 'required|max_length[255]'];
        if (! $this->validateData($data, $rules)) return $this->backWithErrors();
        (new UserModel())->update($user['id'], ['first_name' => trim($data['first_name']), 'last_name' => trim($data['last_name']), 'middle_name' => trim($data['middle_name'] ?? ''), 'phone' => trim($data['phone']), 'address' => trim($data['address'])]);
        session()->set('user', ['id' => $user['id'], 'name' => trim($data['first_name']), 'role' => $user['role']]);
        return redirect()->to(site_url('portal'))->with('status', 'Profile updated.');
    }

    public function changePassword(): RedirectResponse
    {
        if ($response = $this->authorize([], true)) return $response;
        $user = $this->currentUser();
        $data = $this->request->getPost();
        if (! $this->validateData($data, ['current_password' => 'required', 'new_password' => 'required|min_length[10]|max_length[72]', 'confirm_password' => 'required|matches[new_password]'])) return $this->backWithErrors();
        if (! password_verify($data['current_password'], $user['password_hash'])) return redirect()->to(site_url('portal'))->with('status', 'Current password is incorrect.');
        (new UserModel())->update($user['id'], ['password_hash' => password_hash($data['new_password'], PASSWORD_DEFAULT), 'must_change_password' => 0]);
        session()->regenerate();
        return redirect()->to(site_url('portal'))->with('status', 'Password changed.');
    }

    public function createEmployee(): RedirectResponse
    {
        if ($response = $this->authorize(['admin'])) return $response;
        $data = $this->request->getPost();
        $rules = ['first_name' => 'required|max_length[100]', 'last_name' => 'required|max_length[100]', 'birthday' => 'required|valid_date[Y-m-d]', 'gender' => 'required|in_list[female,male,non-binary,prefer-not-to-say]', 'email' => 'required|valid_email|max_length[190]|is_unique[users.email]', 'phone' => 'required|max_length[32]', 'address' => 'required|max_length[255]', 'username' => 'required|min_length[3]|max_length[50]|alpha_numeric_punct|is_unique[users.username]', 'department' => 'required|in_list[' . implode(',', self::DEPARTMENTS) . ']', 'password' => 'required|min_length[10]|max_length[72]'];
        if (! $this->validateData($data, $rules)) return $this->backWithErrors();
        $db = db_connect();
        $db->transStart();
        $userModel = new UserModel();
        $userModel->insert(['first_name' => trim($data['first_name']), 'last_name' => trim($data['last_name']), 'birthday' => $data['birthday'], 'gender' => $data['gender'], 'email' => strtolower(trim($data['email'])), 'phone' => trim($data['phone']), 'address' => trim($data['address']), 'username' => trim($data['username']), 'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT), 'role' => 'employee', 'is_active' => 1]);
        $newId = $userModel->getInsertID();
        $db->table('employees')->insert(['user_id' => $newId, 'department' => $data['department'], 'created_at' => date('Y-m-d H:i:s')]);
        $db->transComplete();
        return redirect()->to(site_url('portal'))->with('status', 'Staff account created. Share its temporary password securely and ask the user to change it.');
    }

    public function toggleUser(int $id): RedirectResponse
    {
        if ($response = $this->authorize(['admin'])) return $response;
        $actor = $this->currentUser();
        if ($id === (int) $actor['id']) return redirect()->to(site_url('portal'))->with('status', 'You cannot deactivate your own account.');
        $target = (new UserModel())->find($id);
        if (! $target) return redirect()->to(site_url('portal'))->with('status', 'Account was not found.');
        (new UserModel())->update($id, ['is_active' => $target['is_active'] ? 0 : 1]);
        return redirect()->to(site_url('portal'))->with('status', 'Account access updated.');
    }

    public function updateUser(int $id): RedirectResponse
    {
        if ($response = $this->authorize(['admin'])) return $response;
        $target = (new UserModel())->find($id);
        $data = $this->request->getPost();
        if (! $target || ! $this->validateData($data, ['first_name' => 'required|max_length[100]', 'last_name' => 'required|max_length[100]', 'phone' => 'required|max_length[32]', 'address' => 'required|max_length[255]'])) return $this->backWithErrors();
        (new UserModel())->update($id, ['first_name' => trim($data['first_name']), 'last_name' => trim($data['last_name']), 'phone' => trim($data['phone']), 'address' => trim($data['address'])]);
        return redirect()->to(site_url('portal'))->with('status', 'Account details updated.');
    }

    public function updateSchedule(int $id): RedirectResponse
    {
        if ($response = $this->authorize(['admin'])) return $response;
        $data = $this->request->getPost();
        $allowed = ['requested', 'approved', 'declined', 'in_progress', 'completed', 'cancelled'];
        if (! in_array($data['status'] ?? '', $allowed, true)) return redirect()->to(site_url('portal'))->with('status', 'Invalid schedule status.');
        $employeeId = $data['assigned_employee_id'] ?? '';
        if ($employeeId !== '' && ! db_connect()->table('employees')->join('users', 'users.id = employees.user_id')->where('employees.id', (int) $employeeId)->where('users.is_active', 1)->countAllResults()) return redirect()->to(site_url('portal'))->with('status', 'Select an active technician.');
        $start = $data['scheduled_start'] ?? '';
        $update = ['status' => $data['status'], 'assigned_employee_id' => $employeeId === '' ? null : (int) $employeeId];
        if ($start && strtotime($start)) $update['scheduled_start'] = date('Y-m-d H:i:s', strtotime($start));
        $db = db_connect();
        $db->table('schedules')->where('id', $id)->update($update);
        $schedule = $db->table('schedules')->where('id', $id)->get()->getRowArray();
        if ($schedule) $this->notify($schedule['customer_id'], 'Service schedule updated', 'Your ' . $schedule['service_type'] . ' request is now ' . str_replace('_', ' ', $schedule['status']) . '.', 'schedule');
        return redirect()->to(site_url('portal'))->with('status', 'Schedule updated and customer notified.');
    }

    public function createAnnouncement(): RedirectResponse
    {
        if ($response = $this->authorize(['admin'])) return $response;
        $data = $this->request->getPost();
        $target = $data['target_role'] ?? '';
        if (! in_array($target, ['', 'customer', 'employee'], true) || empty($data['title']) || empty($data['body'])) return redirect()->to(site_url('portal'))->with('status', 'Enter an announcement title and message.');
        $db = db_connect();
        $db->table('announcements')->insert(['target_role' => $target ?: null, 'title' => trim($data['title']), 'body' => trim($data['body']), 'created_by' => $this->currentUser()['id'], 'created_at' => date('Y-m-d H:i:s')]);
        if ($target === 'customer') {
            $customers = (new UserModel())->where('role', 'customer')->where('is_active', 1)->findAll();
            foreach ($customers as $customer) $this->notify((int) $customer['id'], trim($data['title']), trim($data['body']), 'promotion');
        } elseif ($target === '') {
            $this->notify(null, trim($data['title']), trim($data['body']), 'promotion');
        }
        return redirect()->to(site_url('portal'))->with('status', 'Announcement published.');
    }

    public function createTicket(): RedirectResponse
    {
        if ($response = $this->authorize()) return $response;
        $data = $this->request->getPost();
        if (! $this->validateData($data, ['subject' => 'required|max_length[160]', 'message' => 'required|max_length[5000]'])) return $this->backWithErrors();
        db_connect()->table('support_tickets')->insert(['user_id' => $this->currentUser()['id'], 'subject' => trim($data['subject']), 'message' => trim($data['message']), 'status' => 'open', 'created_at' => date('Y-m-d H:i:s')]);
        $this->notifyAdmins('New customer support request', trim($data['subject']), 'support');
        return redirect()->to(site_url('portal'))->with('status', 'Support ticket submitted.');
    }

    public function updateTicket(int $id): RedirectResponse
    {
        if ($response = $this->authorize(['admin'])) return $response;
        $status = $this->request->getPost('status');
        if (! in_array($status, ['open', 'in_progress', 'resolved'], true)) return redirect()->to(site_url('portal'))->with('status', 'Invalid ticket status.');
        $db = db_connect();
        $ticket = $db->table('support_tickets')->where('id', $id)->get()->getRowArray();
        if ($ticket) {
            $db->table('support_tickets')->where('id', $id)->update(['status' => $status]);
            $this->notify($ticket['user_id'], 'Support ticket updated', 'Your ticket “' . $ticket['subject'] . '” is now ' . str_replace('_', ' ', $status) . '.', 'support');
        }
        return redirect()->to(site_url('portal'))->with('status', 'Ticket updated.');
    }

    public function updateCatalog(int $id): RedirectResponse
    {
        if ($response = $this->authorize(['admin'])) return $response;
        $data = $this->request->getPost();
        if (! is_numeric($data['price'] ?? null) || (float) $data['price'] < 0 || ! in_array($data['is_active'] ?? '', ['0', '1'], true)) return redirect()->to(site_url('portal'))->with('status', 'Enter a valid non-negative price and availability.');
        db_connect()->table('catalog_items')->where('id', $id)->update(['price' => number_format((float) $data['price'], 2, '.', ''), 'is_active' => (int) $data['is_active'], 'updated_at' => date('Y-m-d H:i:s')]);
        return redirect()->to(site_url('portal'))->with('status', 'Catalog setting saved.');
    }

    public function createCatalogItem(): RedirectResponse
    {
        if ($response = $this->authorize(['admin'])) return $response;
        $data = $this->request->getPost();
        if (! in_array($data['kind'] ?? '', ['service', 'product'], true) || empty(trim($data['name'] ?? '')) || mb_strlen(trim($data['name'])) > 120 || ! is_numeric($data['price'] ?? null) || (float) $data['price'] < 0) return redirect()->to(site_url('portal'))->with('status', 'Enter a valid catalog type, name, and non-negative price.');
        db_connect()->table('catalog_items')->insert(['kind' => $data['kind'], 'name' => trim($data['name']), 'description' => trim($data['description'] ?? '') ?: null, 'price' => number_format((float) $data['price'], 2, '.', ''), 'is_active' => 1, 'updated_at' => date('Y-m-d H:i:s')]);
        return redirect()->to(site_url('portal'))->with('status', 'Catalog item added.');
    }

    public function recordPayment(): RedirectResponse
    {
        if ($response = $this->authorize(['admin'])) return $response;
        $data = $this->request->getPost();
        if (empty($data['customer_id']) || ! is_numeric($data['amount'] ?? null) || (float) $data['amount'] <= 0) return redirect()->to(site_url('portal'))->with('status', 'Choose a customer and enter a valid payment amount.');
        $customer = (new UserModel())->where('id', (int) $data['customer_id'])->where('role', 'customer')->first();
        if (! $customer) return redirect()->to(site_url('portal'))->with('status', 'Customer not found.');
        $scheduleId = $data['schedule_id'] ?? '';
        if ($scheduleId && ! db_connect()->table('schedules')->where('id', (int) $scheduleId)->where('customer_id', $customer['id'])->countAllResults()) return redirect()->to(site_url('portal'))->with('status', 'The selected service does not belong to this customer.');
        db_connect()->table('payments')->insert(['schedule_id' => $scheduleId ?: null, 'user_id' => $customer['id'], 'amount' => number_format((float) $data['amount'], 2, '.', ''), 'currency' => 'PHP', 'status' => 'paid', 'reference' => substr(trim($data['reference'] ?? ''), 0, 100) ?: null, 'paid_at' => date('Y-m-d H:i:s'), 'created_at' => date('Y-m-d H:i:s')]);
        $this->notify($customer['id'], 'Payment recorded', 'A payment of PHP ' . number_format((float) $data['amount'], 2) . ' has been recorded. Reference: ' . ($data['reference'] ?: 'not provided') . '.', 'payment');
        return redirect()->to(site_url('portal'))->with('status', 'Payment recorded in the customer history.');
    }

    public function checkIn(): RedirectResponse
    {
        if ($response = $this->authorize(['employee'])) return $response;
        $user = $this->currentUser();
        $coords = $this->request->getPost();
        if (! is_numeric($coords['latitude'] ?? null) || ! is_numeric($coords['longitude'] ?? null) || abs((float) $coords['latitude']) > 90 || abs((float) $coords['longitude']) > 180) return redirect()->to(site_url('portal'))->with('status', 'Location could not be verified. Allow location access and try again.');
        $employee = db_connect()->table('employees')->where('user_id', $user['id'])->get()->getRowArray();
        $schedule = $employee ? db_connect()->table('schedules')->where('id', (int) ($coords['schedule_id'] ?? 0))->where('assigned_employee_id', $employee['id'])->whereIn('status', ['approved', 'in_progress'])->get()->getRowArray() : null;
        if (! $schedule) return redirect()->to(site_url('portal'))->with('status', 'This job is not assigned to your account or is not ready for check-in.');
        db_connect()->table('schedules')->where('id', $schedule['id'])->update(['check_in_at' => date('Y-m-d H:i:s'), 'check_in_latitude' => (float) $coords['latitude'], 'check_in_longitude' => (float) $coords['longitude'], 'status' => 'in_progress']);
        $this->notify($schedule['customer_id'], 'Technician checked in', 'Your technician has checked in at the service location.', 'schedule');
        return redirect()->to(site_url('portal'))->with('status', 'Check-in recorded with your current location.');
    }

    public function reportIncident(): RedirectResponse
    {
        if ($response = $this->authorize(['employee'])) return $response;
        $user = $this->currentUser();
        $data = $this->request->getPost();
        if (! $this->validateData($data, ['schedule_id' => 'required|is_natural_no_zero', 'severity' => 'required|in_list[normal,urgent,safety]', 'description' => 'required|max_length[4000]'])) return $this->backWithErrors();
        $employee = db_connect()->table('employees')->where('user_id', $user['id'])->get()->getRowArray();
        $schedule = $employee ? db_connect()->table('schedules')->where('id', (int) $data['schedule_id'])->where('assigned_employee_id', $employee['id'])->get()->getRowArray() : null;
        if (! $schedule) return redirect()->to(site_url('portal'))->with('status', 'Select one of your assigned jobs.');
        db_connect()->table('incidents')->insert(['schedule_id' => $schedule['id'], 'employee_id' => $employee['id'], 'severity' => $data['severity'], 'description' => trim($data['description']), 'status' => 'open', 'created_at' => date('Y-m-d H:i:s')]);
        $this->notifyAdmins('Field incident reported', $data['severity'] . ': ' . mb_substr(trim($data['description']), 0, 180), 'incident');
        return redirect()->to(site_url('portal'))->with('status', 'Incident report sent to the operations team.');
    }

    public function markNotificationsRead(): RedirectResponse
    {
        if ($response = $this->authorize()) return $response;
        db_connect()->table('notifications')->where('user_id', $this->currentUser()['id'])->where('read_at', null)->update(['read_at' => date('Y-m-d H:i:s')]);
        return redirect()->to(site_url('portal'))->with('status', 'Your notifications are marked as read.');
    }

    private function backWithErrors(): RedirectResponse
    {
        return redirect()->to(site_url('portal'))->withInput()->with('errors', $this->validator->getErrors());
    }

    private function notify(?int $userId, string $title, string $body, string $category): void
    {
        db_connect()->table('notifications')->insert(['user_id' => $userId, 'title' => $title, 'body' => $body, 'category' => $category, 'created_at' => date('Y-m-d H:i:s')]);
    }

    private function notifyAdmins(string $title, string $body, string $category): void
    {
        $admins = (new UserModel())->where('role', 'admin')->where('is_active', 1)->findAll();
        foreach ($admins as $admin) $this->notify((int) $admin['id'], $title, $body, $category);
    }
}
