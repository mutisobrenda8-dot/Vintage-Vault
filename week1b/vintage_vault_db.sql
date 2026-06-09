-- ============================================================
-- VINTAGE VAULT — Complete Database Setup
-- Run this entire file in phpMyAdmin SQL tab
-- It drops and recreates everything cleanly
-- ============================================================

-- Drop and recreate database
DROP DATABASE IF EXISTS vintage_vault_db;
CREATE DATABASE vintage_vault_db
    CHARACTER SET utf8
    COLLATE utf8_general_ci;

USE vintage_vault_db;

-- ============================================================
-- TABLE: categories
-- ============================================================
CREATE TABLE categories (
    id    INT AUTO_INCREMENT PRIMARY KEY,
    name  VARCHAR(100) NOT NULL,
    slug  VARCHAR(100) NOT NULL UNIQUE
);

INSERT INTO categories (name, slug) VALUES
    ('Books',        'books'),
    ('Vinyl',        'vinyl'),
    ('Cameras',      'cameras'),
    ('Jewellery',    'jewellery'),
    ('Ceramics',     'ceramics'),
    ('Home Decor',   'home-decor'),
    ('Toys',         'toys'),
    ('Furniture',    'furniture'),
    ('Collectibles', 'collectibles');

-- ============================================================
-- TABLE: users
-- ============================================================
CREATE TABLE users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(150) NOT NULL,
    email      VARCHAR(150) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    role       ENUM('customer','admin') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- Admin user
-- Email:    admin@vintagevault.com
-- Password: password
-- ============================================================
INSERT INTO users (name, email, password, role) VALUES (
    'Admin',
    'admin@vintagevault.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin'
);

-- ============================================================
-- Sample customer account
-- Email:    customer@vintagevault.com
-- Password: password
-- ============================================================
INSERT INTO users (name, email, password, role) VALUES (
    'Jane Smith',
    'customer@vintagevault.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'customer'
);

-- ============================================================
-- TABLE: products
-- ============================================================
CREATE TABLE products (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT            NOT NULL,
    name        VARCHAR(200)   NOT NULL,
    description TEXT,
    price       DECIMAL(10,2)  NOT NULL,
    stock       INT            DEFAULT 0,
    image       VARCHAR(255)   DEFAULT 'placeholder.jpg',
    created_at  TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

INSERT INTO products (category_id, name, description, price, stock, image) VALUES
-- Cameras
(3, 'Olympus OM-1 (1973)',
 '35mm SLR camera in excellent condition. Includes original strap and lens cap. A true classic for film photography lovers.',
 148.00, 2, 'placeholder.jpg'),

(3, 'Canon AE-1 Program (1981)',
 'Iconic 35mm camera. Fully functional with 50mm f/1.8 lens. Minor cosmetic wear consistent with age.',
 120.00, 1, 'placeholder.jpg'),

-- Vinyl
(2, 'Miles Davis — Kind of Blue (1959)',
 'Original pressing. Light wear on sleeve, plays perfectly. One of the best-selling jazz albums of all time.',
 65.00, 2, 'placeholder.jpg'),

(2, 'The Beatles — Abbey Road (1969)',
 'UK pressing. Sleeve has minor ring wear. Vinyl plays with minimal surface noise. A must-have for any collection.',
 85.00, 1, 'placeholder.jpg'),

(2, 'Fleetwood Mac — Rumours (1977)',
 'Classic rock masterpiece. Vinyl in excellent condition, sleeve shows light shelf wear.',
 55.00, 3, 'placeholder.jpg'),

-- Books
(1, 'Hemingway — The Old Man and the Sea (1st Ed.)',
 'First edition hardcover, 1952. Minor foxing on page edges. Spine intact. A rare find for collectors.',
 220.00, 1, 'placeholder.jpg'),

(1, 'F. Scott Fitzgerald — The Great Gatsby (1925)',
 'Early edition. Some tanning to pages as expected. Cover has light wear. Well preserved for its age.',
 180.00, 1, 'placeholder.jpg'),

-- Jewellery
(4, 'Art Deco Brooch, 1930s',
 'Gold-plated with marcasite stones. Pin mechanism works perfectly. Stamped on reverse.',
 95.00, 3, 'placeholder.jpg'),

(4, 'Victorian Locket Necklace, 1880s',
 'Silver locket with floral engraving. Opens to reveal two photo compartments. Chain included.',
 145.00, 1, 'placeholder.jpg'),

-- Ceramics
(5, 'Wedgwood Jasperware Vase, 1960s',
 'Classic blue and white Wedgwood. No chips or cracks. Wedgwood mark on base.',
 75.00, 2, 'placeholder.jpg'),

(5, 'Royal Doulton Figurine, 1950s',
 'Lady figurine in pink and white. Excellent condition with original coloring intact.',
 110.00, 1, 'placeholder.jpg'),

-- Home Decor
(6, 'Brass Candlestick Holders (Pair), 1940s',
 'Heavy brass. Some patina which adds to the charm. Both holders are level and stable.',
 45.00, 2, 'placeholder.jpg'),

(6, 'Tiffany-Style Table Lamp, 1970s',
 'Stained glass shade in amber and green tones. Works perfectly. Base is solid brass.',
 195.00, 1, 'placeholder.jpg'),

-- Toys
(7, 'Tin Toy Robot, 1960s Japan',
 'Wind-up mechanism still works. Minor paint wear on edges. Original box not included.',
 88.00, 1, 'placeholder.jpg'),

(7, 'Matchbox Cars Collection (Set of 12), 1970s',
 'All 12 in good playing condition. Some paint chips. Stored in original carry case.',
 65.00, 1, 'placeholder.jpg'),

-- Furniture
(8, 'Mid-Century Teak Side Table, 1960s',
 'Danish-style side table. Some light scratches on surface. Legs are solid and stable.',
 175.00, 1, 'placeholder.jpg'),

-- Collectibles
(9, 'Vintage Coca-Cola Tin Sign, 1950s',
 'Embossed tin advertising sign. Some edge rust adding to authenticity. 18x12 inches.',
 55.00, 2, 'placeholder.jpg'),

(9, 'Antique Pocket Watch, 1910s',
 'Gold-filled case with white enamel dial. Movement runs but may need servicing. Chain included.',
 225.00, 1, 'placeholder.jpg');

-- ============================================================
-- TABLE: cart
-- ============================================================
CREATE TABLE cart (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    product_id INT NOT NULL,
    quantity   INT NOT NULL DEFAULT 1,
    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- ============================================================
-- TABLE: orders
-- ============================================================
CREATE TABLE orders (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    user_id        INT            NOT NULL,
    total          DECIMAL(10,2)  NOT NULL,
    status         ENUM('pending','processing','shipped',
                        'completed','cancelled') DEFAULT 'pending',
    full_name      VARCHAR(150),
    address        TEXT,
    city           VARCHAR(100),
    postal_code    VARCHAR(20),
    country        VARCHAR(100),
    notes          TEXT,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Sample orders for the customer account (user id 2)
INSERT INTO orders (user_id, total, status, full_name, address, city, postal_code, country) VALUES
(2, 148.00, 'completed', 'Jane Smith', '123 Vintage Lane', 'Nairobi', '00100', 'Kenya'),
(2,  65.00, 'shipped',   'Jane Smith', '123 Vintage Lane', 'Nairobi', '00100', 'Kenya'),
(2, 220.00, 'pending',   'Jane Smith', '123 Vintage Lane', 'Nairobi', '00100', 'Kenya');

-- ============================================================
-- TABLE: order_items
-- ============================================================
CREATE TABLE order_items (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    order_id   INT           NOT NULL,
    product_id INT           NOT NULL,
    quantity   INT           NOT NULL,
    price      DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id)   REFERENCES orders(id)   ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Sample order items matching the orders above
INSERT INTO order_items (order_id, product_id, quantity, price) VALUES
(1, 1, 1, 148.00),
(2, 3, 1,  65.00),
(3, 6, 1, 220.00);

-- ============================================================
-- TABLE: reviews
-- ============================================================
CREATE TABLE reviews (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    product_id INT NOT NULL,
    rating     INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment    TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Sample reviews
INSERT INTO reviews (user_id, product_id, rating, comment) VALUES
(2, 1, 5, 'Absolutely beautiful camera. Arrived well packaged and works perfectly. Very happy!'),
(2, 3, 4, 'Great record, plays with minimal surface noise. Sleeve was as described.'),
(2, 6, 5, 'A true treasure. First edition in better condition than I expected. Highly recommend this seller.');