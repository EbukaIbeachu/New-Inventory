# PHP Inventory & Receipt Management System

A comprehensive web-based system for managing inventory, receipts, and users with automation capabilities.

## Features

- **User Authentication**: Login, Register, Password Reset, Admin Approval system.
- **Inventory Management**: Add, Edit, Delete, Import/Export (CSV), Barcode support, Low stock alerts.
- **Receipt Management**: Inbound/Outbound receipts, Print view with Barcodes, Payment Status tracking.
- **Automation**: Scheduled tasks for email alerts, reports, and backups (requires Cron).
- **Admin Dashboard**: Analytics, User Management, System Settings.
- **Responsive UI**: Built with Bootstrap 5.

## Installation

1. **Configure Web Server**: Point your web server (Apache/Nginx/XAMPP) to the project directory.
2. **Database Setup**:
   - Access `install.php` via your browser (e.g., `http://localhost/inventory/install.php`).
   - Click "Run Installation" to create the database and tables.
   - Default Admin Credentials:
     - Username: `admin`
     - Password: `admin123`
3. **Cron Job Setup (for Automation)**:
   - Add the following entry to your system's crontab to run every minute:
     ```
     * * * * * php /path/to/inventory/cron.php
     ```

## Configuration

- **Database**: Edit `config/config.php` if you need to change DB credentials manually.
- **Email**: Edit `includes/mailer.php` to configure SMTP settings (currently logs to `uploads/email_log.txt`).

## Usage

- **Login**: Use the default admin credentials to log in.
- **Users**: New registrations require Admin approval from the "Users" page.
- **Inventory**: Add items, upload images, and manage stock levels.
- **Receipts**: Create receipts for sales (outbound) or purchases (inbound).
- **Automation**: Go to "Automation" in the sidebar to enable/disable tasks like Low Stock Alerts.

## Tech Stack

- PHP (PDO for Database)
- MySQL / MariaDB
- Bootstrap 5 (Frontend)
- jQuery & DataTables
- JsBarcode (Barcode generation)

## License

MIT
