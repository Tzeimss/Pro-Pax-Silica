# Sun Son Solar — Professor Presentation Guide

This guide gives you a complete walkthrough of the current Sun Son Solar website and its customer, admin, and employee workspaces. The recommended demonstration is a single appointment moving from customer request, to admin dispatch, to employee check-in, and back to the customer's service history.

> **This computer is now set up.** To open the site in a later session, start the **MySQL** service in XAMPP if it is stopped, open a terminal in `Pro Pax Silica`, run `php spark serve`, and visit **http://localhost:8080**. Leave the server terminal open while presenting.

## 1. Presentation plan

Allow about 10–15 minutes for the walkthrough:

1. Introduce the purpose and main user roles.
2. Show the public website and customer registration.
3. Submit a service request as a customer.
4. Approve and assign it as an administrator.
5. Check in and report an issue as the field employee.
6. Return to the admin and customer views to show the updates.
7. Close with the security design and current system scope.

**Suggested opening:** “Sun Son Solar is a solar products and services website with separate workspaces for customers, administrators, and field employees. I’ll demonstrate how one service appointment moves through the system.”

## 2. Start the application

The application is in the `Pro Pax Silica` folder. It uses CodeIgniter 4, PHP, and MySQL/MariaDB.

### First-time setup

1. Install PHP 8.1 or later, Composer, and MySQL or MariaDB. Enable the PHP extensions listed in `README.md` (`intl`, `mysqli`, `mbstring`, `json`, and `curl`).
2. Open a terminal in `Pro Pax Silica` and install the PHP dependencies:

   ```powershell
   composer install
   ```

3. Copy the environment template and edit the database settings:

   ```powershell
   Copy-Item .env.example .env
   ```

   Set the database host, database name, username, and password in `.env`. The default database name is `sunsonsolar`.

4. Create the database in MySQL/MariaDB if it does not exist:

   ```sql
   CREATE DATABASE sunsonsolar CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

5. Apply all database migrations:

   ```powershell
   php spark migrate
   ```

6. On a new installation, create the initial administrator accounts:

   ```powershell
   php spark db:seed InitialAdmins
   ```

   Before running the seeder, add distinct random passwords of at least 12 characters to `.env` as `SUNSON_FOUNDER_ADMIN_PASSWORD` and `SUNSON_IT_ADMIN_PASSWORD`. Do not commit or share `.env`. The seeder is `app/Database/Seeds/InitialAdmins.php`; these passwords are hashed and must be changed at first sign-in. The seeder skips a username or email that already exists.

7. Start the local web server:

   ```powershell
   php spark serve
   ```

8. Open **http://localhost:8080** in a browser. Keep the terminal running during the presentation.

### Demo account preparation

- **Administrator:** sign in with the username `KittyKat16` or `admin` and the matching password you configured privately in `.env`. On first sign-in, set a new password. The founder email is `katherine.sinagaraw@sunsonsolar.com`; the IT administrator email is `sol.solis@sunsonsolar.com`.
- **Customer:** register a separate demo customer through the website. Use an email inbox you can access if you expect to use any email functionality; the current workflows use in-app notifications and do not send email.
- **Employee:** while signed in as admin, create a staff account. Employee accounts cannot self-register. Save the username and temporary password for the demo.

The bootstrap passwords are private temporary credentials. Do not show them on a projector or reuse them outside a private demo database. Change them before presenting. The seeder uses `Company Phone` as a placeholder; the seeded account does not need that value for the demo.

For the GPS demonstration, use `localhost` as above and allow location access in the browser. Browser geolocation generally requires HTTPS or localhost. If you do not want to share your actual location, skip the live check-in and explain the workflow instead.

## 3. Walk through the public website

1. Start at the homepage. Point out the **“Power Your Future with Solar”** hero headline and **Get a free quote** button.
2. Open **Products** and show its links to the panel, inverter, and battery sections.
3. Open **Services** and show the consultation, design, permitting, installation, maintenance, repair, and monitoring sections.
4. Select a section or scroll down to show the solar product and service cards.
5. Show **About us** and the company introduction near the bottom of the page.
6. Explain that **Support** opens sign-in because submitting and following support tickets is part of the authenticated customer workspace.
7. Select **Sign in** or **Get started** to reach the account page.

The quote button begins customer account creation. A customer signs in before requesting a service appointment.

## 4. Create a customer account and request a service

1. Select **Get started** or open **/account?mode=signup**.
2. Complete the registration form. Include first name, last name, birthdate, gender, email, phone, address, username, and a password of at least eight characters.
3. Submit the form. Customer registration creates a customer account; the form does not allow a visitor to choose an employee or admin role.
4. Sign in using the customer email (or username) and password. You should arrive at **Your solar journey**.
5. In **Book a visit**, select a service, choose a future date and time, confirm the service address, and optionally add a note.
6. Select **Request appointment**. The appointment appears under **Appointments** with a requested status.
7. Optionally submit a question under **Get support** to create a customer support ticket.
8. Point out the profile editor, password change form, notification panel, company updates, service history, and payment history further down the page.

To keep the presentation concise, create the demo customer before presenting and use this section only to show the sign-in and booking flow.

## 5. Demonstrate the admin and dispatch workspace

1. Sign out of the customer account using the top-right **Sign out** button.
2. Sign in using the seeded administrator account. On its first sign-in, set a new password; you will then land on **Operations overview**.
3. Explain the summary cards: customer count, open requests, completed services, and payments recorded in the system.
4. In **Service activity**, show the booking counts grouped by service type.
5. In **Master schedule**, find the request submitted by the demo customer.
6. Choose an active employee in the technician selector, set the status to **Approved**, and adjust the appointment time if needed.
7. Select **Save**. The schedule is assigned and the customer receives an in-app notification about the status update.
8. In **Create staff account**, explain that administrators create employee accounts and choose one of the available departments. For a live demo, create the employee before submitting the customer's appointment so you can assign it here.
9. In **Manage users**, show that an administrator can update basic contact details and deactivate/reactivate accounts. The active administrator cannot deactivate their own account.
10. In **Prices and availability**, update a service price or temporarily disable a service. Active services are offered in the customer booking form. You can also add a service or product entry.
11. In **Send an update**, publish a short announcement to everyone, customers, or employees.
12. In **Support tickets**, change the demo ticket from Open to In progress or Resolved. The customer will see its updated status.
13. In **Record a payment**, select the demo customer, enter an amount and optional receipt reference, and submit. This records a payment that has already been collected; it does not charge a card.
14. In **Field reports**, show incidents submitted by employees.

## 6. Demonstrate the employee / field workspace

1. Sign out of the administrator account.
2. Sign in using the employee username and password created by the administrator.
3. The employee sees **Assigned jobs**. Point out that the job view shows the customer name, service, and address without exposing customer email, phone, or account details.
4. For an approved job, select **Check in at site** and allow browser location access. The app submits the current coordinates; the server records the check-in time and marks the job in progress.
5. Use **Report a field issue** to choose an issue severity, enter a short description, and submit an incident. The report appears in the admin's **Field reports** section.
6. Scroll to **Announcements** and **Notifications** to show employee updates.

If no approved job is assigned, return to the admin workspace, assign one, and set it to Approved before continuing.

## 7. Close the end-to-end workflow

1. Sign out of the employee account and sign in as the administrator again.
2. Show the updated job status and check-in timestamp/coordinates in **Master schedule**, plus the employee incident in **Field reports**.
3. Sign out and return to the demo customer account.
4. Show the appointment's updated status, the schedule notification, the support ticket status, and the recorded payment/receipt reference if you entered one.
5. Explain that the appointment lifecycle is: **customer request → admin approval and assignment → employee check-in and field update → customer history and notifications**.

## 8. Explain access control and data handling

- New public registrations are assigned the customer role by the server.
- Only an administrator can create employee accounts or use dispatch, account management, announcements, pricing, payment-recording, and incident-review tools.
- Employees see only jobs assigned to their employee account and cannot browse the customer directory.
- The server checks the signed-in account's current role and active state for protected actions.
- Passwords are stored as password hashes. The seeded admin accounts must change their temporary passwords at first sign-in.
- Forms use CSRF protection, and portal output escapes user-provided text.

You can show the role separation by signing in to each account and comparing its workspace. Do not rely only on hiding a button; role access is checked on the server too.

## 9. Current demo scope to describe accurately

- Customer support is implemented as tickets with status updates, not live chat.
- Payment history is an administrative record of payments collected elsewhere; the app does not process payment cards.
- Announcements and schedule updates appear in the app. Email/SMS delivery and automatic timed reminders are not configured.
- Product and service catalog entries and prices can be managed in the admin workspace. The customer booking form uses active service entries; the public product showcase remains informational.
- The employee check-in uses browser GPS and needs permission from the browser.

## 10. Troubleshooting before the presentation

- **Database connection error:** confirm MySQL/MariaDB is running and `.env` matches the database name and credentials.
- **Missing table error:** run `php spark migrate` from `Pro Pax Silica`.
- **Admin cannot sign in:** run `php spark db:seed InitialAdmins`; check the terminal output for a skipped username/email conflict. If the account already exists, use its existing credentials instead of repeatedly running the seeder.
- **No job appears for employee:** verify the admin assigned the job to that exact employee and set the schedule to Approved.
- **GPS does not open:** use `http://localhost:8080`, enable browser location access, and reload the employee workspace.
- **Changes do not appear:** refresh the workspace after the form reports that it saved; switch back to the customer account to see customer-facing notifications and status.

## 11. Suggested closing

“The system connects public solar information with appointment booking and field operations. Customers submit and track work, administrators coordinate staff and schedules, and employees receive only the job details needed in the field. Each role is enforced by server-side access checks.”
