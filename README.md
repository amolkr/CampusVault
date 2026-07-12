# CampusVault - Academic Resource Sharing Platform

CampusVault is a PHP and MySQL based academic resource sharing platform for students and faculty. It lets registered users upload, browse, bookmark, and download academic files such as notes, previous papers, textbooks, lab manuals, presentations, assignments, and research material.

The application is designed for a local XAMPP environment and keeps resources private: uploaded files and resource listings are available only after a user logs in.


## Features

- User registration and login
- Student and faculty account roles
- Admin seed account in the database script
- Dashboard with upload, download, bookmark, and rating statistics
- Resource upload with category, subject, semester, and tags
- Browse and search resources after login
- Bookmark resources through an AJAX API
- Authenticated file downloads through `download.php`
- Direct upload-folder access blocked with `uploads/.htaccess`
- Responsive UI using the existing CampusVault theme


## Technology Stack

- HTML
- CSS
- JavaScript
- PHP
- MySQL
- XAMPP


## Project Structure

```text
academic_platform/
├── api.php                 # AJAX endpoints, currently bookmark toggle
├── config.php              # App constants, database connection, helpers
├── database.sql            # Database schema and seed data
├── download.php            # Authenticated resource download endpoint
├── index.php               # Main layout and page router
├── logout.php              # User logout handler
├── css/
│   └── style.css           # Main theme and responsive styles
├── js/
│   └── main.js             # UI behavior and AJAX helpers
├── pages/
│   ├── browse.php          # Browse/search/my resources/bookmarks view
│   ├── dashboard.php       # Logged-in user dashboard
│   ├── home.php            # Landing/home page
│   ├── login.php           # Login page
│   ├── register.php        # Registration page
│   ├── resource-card.php   # Shared resource card component
│   └── upload.php          # Resource upload page
└── uploads/
    └── .htaccess           # Blocks direct access to uploaded files
```


## Setup Instructions

1. Place the project folder inside your XAMPP `htdocs` directory:

```text
C:\xampp\htdocs\academic_platform
```

2. Start Apache and MySQL from the XAMPP Control Panel.

3. Open phpMyAdmin:

```text
http://localhost/phpmyadmin
```

4. Import `database.sql`.

The SQL file creates the `academic_platform` database, all required tables, default categories, and a default admin account.

5. Open the application:

```text
http://localhost/academic_platform
```


## Default Admin Login

```text
Email: admin@academic.com
Password: Admin@123
```


## Resource Access Rules

Resources are protected in two layers:

- Guests cannot view browse/search resource listings. These pages redirect to login.
- Files are not served directly from `uploads/`. Download links use `download.php?id=...`, which verifies that the user is logged in before serving the file.

The `uploads/.htaccess` file also blocks direct Apache access to uploaded files. For this to work fully, Apache must allow `.htaccess` overrides. The PHP login check in `download.php` works independently of that Apache setting.


## Important Configuration

Main configuration values are in `config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'academic_platform');
define('SITE_URL', 'http://localhost/academic_platform');
```

If your folder name, host, database username, or password changes, update these values.


## Upload Rules

Allowed file types:

```text
pdf, doc, docx, ppt, pptx, xls, xlsx, txt, zip, png, jpg, jpeg
```

Maximum file size:

```text
20 MB
```

Uploaded files are stored in:

```text
uploads/
```


## Verification

PHP syntax was checked with:

```powershell
C:\xampp\php\php.exe -l path\to\file.php
```

All PHP files passed syntax linting after the latest changes.


## Notes

- Empty placeholder files such as `index.html` and `test.php` are not required for the main application flow.
- This project is intended for local XAMPP usage. Before hosting publicly, add stronger production security settings, CSRF protection, stricter upload scanning, HTTPS, and environment-based configuration.
