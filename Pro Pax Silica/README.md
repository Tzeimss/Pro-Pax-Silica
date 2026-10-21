# Sun Son Solar (CodeIgniter 4)

The CodeIgniter 4 application serves the public website and authenticated customer, admin, and employee workspaces. Homepage views are under `app/Views`, public assets are under `public/assets`, and account registration/sign-in use CodeIgniter validation, sessions, and the database.

## Requirements

- PHP 8.1 or newer with `intl`, `mysqli`, `mbstring`, `json`, and `curl` extensions
- Composer
- MySQL or MariaDB

## Setup

1. Open this folder in a terminal and install dependencies:

   ```sh
   composer install
   ```

2. Copy `.env.example` to `.env`. Configure `app.baseURL` and the database host, database name, username, and password.
3. Create the configured database (default: `sunsonsolar`) and apply the migrations:

   ```sh
   php spark migrate
   ```

4. On a new installation, create the initial admin accounts:

   ```sh
   php spark db:seed InitialAdmins
   ```

   Before seeding, set distinct random values of at least 12 characters for `SUNSON_FOUNDER_ADMIN_PASSWORD` and `SUNSON_IT_ADMIN_PASSWORD` in `.env`. These values are hashed before storage; administrators must change them at first sign-in. Keep `.env` private. The seeder skips an account if its username or email already exists. Replace the `Company Phone` placeholder in those records with the real company number.

5. Start the development server and open `http://localhost:8080`:

   ```sh
   php spark serve
   ```

For deployment, point Apache/Nginx's document root at `public/`. Keep `.env`, `app/`, and `writable/` outside the web root.

## Workspaces

- Customers can register, request or cancel eligible appointments, update their profile and password, review service and payment history, receive in-app notices, and submit support tickets.
- Admins manage accounts and staff, dispatch schedules, update product/service catalog pricing and availability, publish announcements, manage tickets, record payments, review field incidents, and view basic activity and revenue summaries.
- Employees see only assigned jobs with necessary customer details. They can check in with browser GPS, report field incidents, and view announcements.

Protected actions check the account's active state and role on the server. Geolocation requires HTTPS or localhost. Payments are records of amounts already collected; the app does not charge cards. Customer support is a ticket system, not live chat. Email/SMS delivery and automated timed reminders are not configured.

The old `sunsonsolar.sql` dump is not used because it stores passwords as integers and has a mismatched schema. Use CodeIgniter migrations for the current schema.
