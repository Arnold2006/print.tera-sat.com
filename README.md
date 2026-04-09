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

## Security Notes

- All forms are protected with CSRF tokens
- File uploads are validated by MIME type (via `finfo`) and extension
- Uploaded images are served through PHP (never directly accessible) with strict filename validation
- Session cookies use `HttpOnly`, `SameSite=Lax`, and `Secure` (when HTTPS)
- Admin sessions use `session_regenerate_id()` on login
- Directory listing is disabled via `Options -Indexes`
