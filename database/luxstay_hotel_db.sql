CREATE DATABASE IF NOT EXISTS luxstay_hotel_db
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE luxstay_hotel_db;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS admins (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(80) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS hotels (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    city VARCHAR(100) NOT NULL,
    country VARCHAR(100) NOT NULL DEFAULT '',
    address VARCHAR(255) NOT NULL DEFAULT '',
    description TEXT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_hotels_active (is_active)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS rooms (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    hotel_id INT UNSIGNED NOT NULL,
    name VARCHAR(120) NOT NULL,
    description TEXT NULL,
    capacity TINYINT UNSIGNED NOT NULL DEFAULT 2,
    price_per_night DECIMAL(10,2) NOT NULL,
    is_available TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_rooms_hotel FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE RESTRICT,
    INDEX idx_rooms_available (hotel_id, is_available)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS bookings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    room_id INT UNSIGNED NOT NULL,
    check_in DATE NOT NULL,
    check_out DATE NOT NULL,
    guests TINYINT UNSIGNED NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending','confirmed','cancelled') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_bookings_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
    CONSTRAINT fk_bookings_room FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE RESTRICT,
    INDEX idx_bookings_dates (room_id, status, check_in, check_out),
    INDEX idx_bookings_user (user_id, created_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS payments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id INT UNSIGNED NOT NULL UNIQUE,
    amount DECIMAL(10,2) NOT NULL,
    method VARCHAR(40) NOT NULL DEFAULT 'demo',
    status ENUM('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
    transaction_reference VARCHAR(80) NULL,
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_payments_booking FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS feedback (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    booking_id INT UNSIGNED NOT NULL UNIQUE,
    rating TINYINT UNSIGNED NOT NULL,
    comment TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_feedback_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
    CONSTRAINT fk_feedback_booking FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE RESTRICT,
    INDEX idx_feedback_created (created_at)
) ENGINE=InnoDB;

INSERT IGNORE INTO admins (username, password_hash)
VALUES ('admin', '$2y$10$2SUmMf79CpKxeQmf4Ou3Te4Z59TRZCp6UiVx8TBh4vto2XOdNGvpG');

INSERT INTO hotels (name, city, country, address, description)
SELECT 'The Willow House', 'Portland', 'United States', '18 Garden Lane',
       'A calm, design-led retreat with warm service and thoughtful details.'
WHERE NOT EXISTS (SELECT 1 FROM hotels WHERE name = 'The Willow House');
INSERT INTO hotels (name, city, country, address, description)
SELECT 'Harbor & Pine', 'Seattle', 'United States', '42 Waterfront Avenue',
       'A relaxed city hideaway close to the water, neighborhood cafes, and local shops.'
WHERE NOT EXISTS (SELECT 1 FROM hotels WHERE name = 'Harbor & Pine');

INSERT INTO rooms (hotel_id, name, description, capacity, price_per_night)
SELECT h.id, 'Garden King', 'A bright king room overlooking a quiet garden.', 2, 145.00
FROM hotels h WHERE h.name='The Willow House'
AND NOT EXISTS (SELECT 1 FROM rooms r WHERE r.hotel_id=h.id AND r.name='Garden King');
INSERT INTO rooms (hotel_id, name, description, capacity, price_per_night)
SELECT h.id, 'Willow Family Suite', 'A generous suite with a separate sitting area.', 4, 225.00
FROM hotels h WHERE h.name='The Willow House'
AND NOT EXISTS (SELECT 1 FROM rooms r WHERE r.hotel_id=h.id AND r.name='Willow Family Suite');
INSERT INTO rooms (hotel_id, name, description, capacity, price_per_night)
SELECT h.id, 'Harbor View Queen', 'A comfortable queen room with glimpses of the waterfront.', 2, 175.00
FROM hotels h WHERE h.name='Harbor & Pine'
AND NOT EXISTS (SELECT 1 FROM rooms r WHERE r.hotel_id=h.id AND r.name='Harbor View Queen');
