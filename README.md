# PrintService – Online Photo Printing

A production-ready PHP web application for an online image printing service. Customers can upload photos, choose print sizes, place orders, and admins can manage orders through a secure dashboard.

## Features

- **Photo Upload** – Drag & drop or click-to-browse with live preview, client/server-side validation (JPG, PNG, WebP, max 10 MB)
- **Order Flow** – Multi-step wizard: Upload → Options → Summary → Confirm
- **Print Sizes** – 10×15 cm, 13×18 cm, 20×30 cm with live price calculation
- **Admin Panel** – Secure login, orders dashboard with stats, order detail view, status management
- **Security** – CSRF protection on all forms, XSS sanitization, safe file serving, session hardening
- **Responsive UI** – Tailwind CSS, mobile-friendly, no build step required

## Requirements

- PHP 8.1+
- MySQL 5.7+ / MariaDB 10.3+
- Apache with `mod_rewrite` enabled

### Required PHP Extensions

| Extension | Purpose | Debian/Ubuntu | RHEL/AlmaLinux |
|-----------|---------|---------------|----------------|
| `fileinfo` | MIME-type validation for uploaded images | `apt install php-fileinfo` | `dnf install php-fileinfo` |
| `pdo_mysql` | MySQL/MariaDB database access | `apt install php-mysql` | `dnf install php-mysqlnd` |
| `session` | User/admin session management | bundled with PHP | bundled with PHP |
| `json` | Upload AJAX responses | bundled with PHP | bundled with PHP |

> **Note:** `session` and `json` are compiled into PHP by default. Only `fileinfo` and `pdo_mysql` typically require a separate package install.

After installing extensions, reload your web server:
```bash
# Apache + PHP-FPM (replace 8.x with your PHP version, e.g. 8.1, 8.2, 8.3)
systemctl restart php8.x-fpm && systemctl reload apache2
# Apache mod_php
systemctl reload apache2
```

### PHP ini Settings

The app allows uploads up to **10 MB**. Ensure your `php.ini` (or PHP-FPM pool `.conf`) has at least:

```ini
upload_max_filesize = 10M
post_max_size       = 12M
```

Default values (`upload_max_filesize = 2M`) will silently reject larger files before the application code even runs.

## Quick Start

### 1. Clone & Configure

```bash
git clone https://github.com/your-org/print.tera-sat.com.git
cd print.tera-sat.com
cp .env.example .env
```

Edit `.env` with your settings:

```
APP_URL=https://print.tera-sat.com
DB_HOST=localhost
DB_NAME=print_service
DB_USER=your_db_user
DB_PASS=your_db_password
ADMIN_USERNAME=admin
ADMIN_PASSWORD_HASH=<generated hash>
```

Generate a secure admin password hash:

```bash
php -r "echo password_hash('your_password', PASSWORD_BCRYPT);"
```

### 2. Database Setup

```bash
mysql -u root -p < database/schema.sql
```

### 3. Storage Permissions

```bash
chmod 755 storage/uploads storage/permanent
```

### 4. Apache Virtual Host

Point your document root to the `public/` directory:

```apache
<VirtualHost *:80>
    ServerName print.tera-sat.com
    DocumentRoot /var/www/print.tera-sat.com/public

    <Directory /var/www/print.tera-sat.com/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Enable rewrite module: `a2enmod rewrite && systemctl reload apache2`

## Project Structure

```
public/           # Web root (point Apache here)
  index.php       # Entry point / router
  .htaccess       # URL rewriting & security headers
  assets/js/      # Frontend JavaScript
src/
  Controllers/    # HTTP request handlers
  Models/         # Database models (PDO)
  Views/          # PHP HTML templates
    layout/       # Shared header/footer
    home/         # Landing page
    upload/       # Upload wizard step 1
    order/        # Order wizard steps 2–4
    admin/        # Admin panel views
storage/
  uploads/        # Temporary uploaded files
  permanent/      # Confirmed order images
config/
  config.php      # Application constants
  database.php    # DB connection helper
database/
  schema.sql      # MySQL schema
```

## Admin Access

Navigate to `/public/?page=admin&action=login` (or `/admin/login` with clean URLs).

Default credentials are set via `.env`:
- Username: `admin`
- Password: set via `ADMIN_PASSWORD_HASH`

## PayPal Smart Buttons

The checkout page uses [PayPal Smart Buttons](https://developer.paypal.com/docs/checkout/) to collect payment. The integration is fully server-side: the PayPal client secret is **never** sent to the browser, and all amounts are validated against the server-side session before capture.

### Setup

1. [Create a PayPal developer account](https://developer.paypal.com/) and create an app in the dashboard to obtain a **Client ID** and **Secret**.

2. Add the following variables to your `.env` file:

   ```
   PAYPAL_CLIENT_ID=<your-client-id>
   PAYPAL_CLIENT_SECRET=<your-client-secret>
   PAYPAL_ENV=sandbox     # use 'live' for production
   ```

3. Ensure PHP's `curl` extension is enabled (required for server-to-server PayPal API calls):

   ```bash
   # Debian/Ubuntu
   apt install php-curl
   systemctl reload apache2
   ```

### Sandbox Testing

1. Set `PAYPAL_ENV=sandbox` and use your sandbox Client ID / Secret.
2. Log in with a [PayPal sandbox buyer account](https://developer.paypal.com/tools/sandbox/) when the PayPal modal appears.
3. Complete the test payment; you will be redirected to the order success page.

### How It Works

| Step | What happens |
|------|-------------|
| Customer clicks **Pay with PayPal** | JS calls `POST ?page=paypal&action=create-order` |
| Server creates a PayPal order | PHP reads the grand total from the session, calls the PayPal Orders API, returns `{ id }` |
| Customer approves payment | PayPal calls `onApprove` in the browser |
| JS calls `POST ?page=paypal&action=capture-order` | PHP captures the payment, verifies the amount, saves the order to the DB |
| Redirect to success page | Order number displayed to the customer |

If `PAYPAL_CLIENT_ID` is not set, the page falls back to a plain form-submit button.

### Optional: Webhooks

For production, configure a [PayPal Webhook](https://developer.paypal.com/docs/api/webhooks/) to listen for `PAYMENT.CAPTURE.COMPLETED` events. This lets you reconcile orders server-to-server without relying solely on the browser redirect (e.g., if the customer closes the tab after payment but before the redirect). A webhook handler is not included in this integration but can be added as a future improvement.



- All forms are protected with CSRF tokens
- File uploads are validated by MIME type (via `finfo`) and extension
- Uploaded images are served through PHP (never directly accessible) with strict filename validation
- Session cookies use `HttpOnly`, `SameSite=Lax`, and `Secure` (when HTTPS)
- Admin sessions use `session_regenerate_id()` on login
- Directory listing is disabled via `Options -Indexes`
