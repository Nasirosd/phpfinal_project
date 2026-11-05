# LuxStay Hotel Management

A PHP 8 and MySQL hotel booking demo with customer accounts, room reservations, a demo checkout, guest feedback, and an administrator portal.

## Requirements

- PHP 8.0 or later with PDO MySQL and mbstring enabled
- MySQL 5.7+ or MariaDB 10.4+
- Apache (for example, XAMPP)

## Run locally with XAMPP

1. Copy the project folder into `xampp/htdocs`.
2. Start Apache and MySQL from the XAMPP Control Panel.
3. Import `database/luxstay_hotel_db.sql` using phpMyAdmin. The script creates the `luxstay_hotel_db` database, sample hotels/rooms, and an initial administrator account.
4. If needed, update the database credentials in `config/database.php`, or set `DB_HOST`, `DB_NAME`, `DB_USER`, and `DB_PASSWORD` environment variables.
5. Open `http://localhost/Hotel-managment-system/` (use the actual folder name if you renamed it).

### Initial administrator

- Username: `admin`
- Password: `admin123`

Sign in at `/admin/login.php`. Replace the initial password hash in the `admins` table before exposing the app publicly. Generate a replacement hash with PHP's `password_hash('your-new-password', PASSWORD_DEFAULT)`.

The checkout is intentionally a demo: it stores a successful test payment and does not connect to a payment processor or collect card information. Configure HTTPS and production-grade operational controls before using this application with real guests or payments.

## Main areas

- Public hotel directory and room search: `/` and `/booking/rooms.php`
- Customer registration, sign-in, profile, and bookings: `/user/`
- Reservations, payment demo, and feedback: `/booking/`, `/payment/`, `/feedback/`
- Hotel, room, booking, payment, and feedback management: `/admin/`
