# Company Signature Portal

بوابة التحقق من التوقيعات المعتمدة

A secure web application that allows external companies to view and download official signatures of authorized signatories.

## Features

- **Admin Panel** — Manage signatories (add, edit, delete, activate/deactivate)
- **Company Access Management** — Generate secure time-limited access links for external companies
- **Token-Based Access** — Secure token links with 24–72 hour expiration
- **Signature Display** — Clean, professional display of authorized signatures
- **Multi-Language** — Full Arabic (RTL) and English support
- **Security** — Password hashing, CSRF protection, rate limiting, access logging
- **Responsive Design** — Mobile-friendly corporate UI

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache with mod_rewrite enabled

## Installation

1. **Clone the repository:**

```bash
git clone https://github.com/YOUR_USERNAME/company-signature-portal.git
```

2. **Create the database:**

```bash
mysql -u root -p < database/schema.sql
```

3. **Configure database connection:**

```bash
cp config/database.example.php config/database.php
```

Edit `config/database.php` with your database credentials.

4. **Set file permissions:**

```bash
chmod 755 uploads/signatures/
```

5. **Update site URL** in `config/app.php`:

```php
define('SITE_URL', 'https://your-domain.com/company-signature-portal');
```

6. **Access the admin panel:**

Navigate to `/admin/login.php`

Default credentials:
- Username: `admin`
- Password: `password`

> **Important:** Change the default password immediately after first login.

## Project Structure

```
company-signature-portal/
├── admin/                  # Admin panel
│   ├── login.php          # Admin login
│   ├── index.php          # Dashboard
│   ├── signatories.php    # Manage signatories
│   ├── companies.php      # Manage company access
│   ├── logs.php           # Access logs
│   └── logout.php         # Admin logout
├── assets/
│   ├── css/style.css      # Main stylesheet
│   └── js/app.js          # Main JavaScript
├── config/
│   ├── app.php            # Application config
│   └── database.example.php  # Database config template
├── database/
│   └── schema.sql         # Database schema
├── includes/
│   ├── admin_footer.php   # Admin footer template
│   ├── admin_header.php   # Admin nav template
│   ├── db.php             # Database connection
│   ├── footer.php         # Footer template
│   ├── header.php         # Header template
│   ├── helpers.php        # Helper functions
│   ├── lang.php           # Language handler
│   └── session.php        # Session management
├── lang/
│   ├── ar.php             # Arabic translations
│   └── en.php             # English translations
├── uploads/
│   └── signatures/        # Signature images (protected)
├── .htaccess              # Security & URL rules
├── index.php              # Entry point
├── signatures.php         # Company login & signature view
├── serve_signature.php    # Secure image server
└── company_logout.php     # Company logout
```

## Security Features

- Password hashing with bcrypt
- CSRF token protection on all forms
- Session regeneration
- Login attempt rate limiting with lockout
- Direct image access prevention (served via PHP)
- Security headers (X-Frame-Options, CSP, etc.)
- Input sanitization and validation
- Access logging (company, IP, timestamp)

## Usage

### Admin Workflow

1. Login to admin panel
2. Add signatories with their signature images (PNG)
3. Generate access credentials for external companies
4. Share the secure link with the company
5. Monitor access logs

### Company Workflow

1. Receive secure access link from admin
2. Open the link and login with provided credentials
3. View authorized signatures
4. Download signatures as needed

## License

MIT License
