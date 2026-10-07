# Sun Son Solar (CodeIgniter 4)

The website is now a CodeIgniter 4 application. The homepage is served from a view, static files are under `public/assets`, and account registration/sign-in use CodeIgniter controllers, validation, sessions, and the database.

## Requirements

- PHP 8.1 or newer with `intl`, `mysqli`, `mbstring`, `json`, and `curl` extensions
- Composer
- MySQL or MariaDB

## Setup

1. Clone or pull this project, then install the CodeIgniter dependencies:

   ```sh
   composer install
   ```

2. Copy `.env.example` to `.env` and set your database host, database name, username, password, and `app.baseURL`.

3. Create the configured database (default name: `sunsonsolar`). Apply the account table migration:

   ```sh
   php spark migrate
   ```

4. Start the local server:

   ```sh
   php spark serve
   ```

5. Open `http://localhost:8080`.

Point Apache/Nginx document root at the `public/` directory in deployment. Keep `.env`, `app/`, and `writable/` outside the web root.

## Routes

- `GET /` — solar landing page
- `GET /account?mode=signup` — registration form
- `GET /account?mode=signin` — sign-in form
- `POST /register` — validates and creates an account
- `POST /login` — verifies credentials and starts a session
- `POST /logout` — ends the session

Registration stores password hashes; plain text passwords are never saved. The old `sunsonsolar.sql` dump is not used because it stores passwords as integers and has a mismatched schema. The CodeIgniter migration creates the corrected `users` table.
