-- Week5/database/week5db.sql
-- Week 5 — Database CRUD Operations

-- Database: vintage-vault_db
-- This file documents the SQL queries used in Week 5

-- CREATE TABLE example
CREATE TABLE IF NOT EXISTS products (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name        VARCHAR(200) NOT NULL,
    description TEXT,
    price       DECIMAL(10,2) NOT NULL,
    stock       INT DEFAULT 0,
    image       VARCHAR(255) DEFAULT 'placeholder.jpg',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- CREATE — Insert a product
INSERT INTO products (category_id, name, description, price, stock)
VALUES (1, 'Sample Book', 'A vintage book.', 25.00, 5);

-- READ — Select all products
SELECT p.*, c.name AS category_name
FROM products p
JOIN categories c ON p.category_id = c.id
ORDER BY p.created_at DESC;

-- READ — Select single product
SELECT * FROM products WHERE id = 1;

-- UPDATE — Edit a product
UPDATE products
SET name = 'Updated Book', price = 30.00
WHERE id = 1;

-- DELETE — Remove a product
DELETE FROM products WHERE id = 1;