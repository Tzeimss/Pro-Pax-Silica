# Sun Son Solar

**Live website:** https://tzeimss.github.io/Pro-Pax-Silica/

Sun Son Solar introduces residential solar products and services. The static homepage at the repository root preserves the website design and is served by GitHub Pages.

## CodeIgniter 4 app

The CodeIgniter project is in [`Pro Pax Silica/`](Pro%20Pax%20Silica/README.md). To run it locally, open that folder and follow its README:

```sh
cd "Pro Pax Silica"
composer install
php spark migrate
php spark serve
```

Configure the database in `Pro Pax Silica/.env` first. GitHub Pages only serves static files, so account registration and sign-in require running the CodeIgniter app on a PHP-capable server; those backend actions do not run on the Pages preview.

## Team

Pro Pax Silica
