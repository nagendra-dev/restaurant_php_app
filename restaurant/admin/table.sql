CREATE TABLE bills (
    bill_id INT AUTO_INCREMENT PRIMARY KEY,
    reservation_id INT NOT NULL,
    table_id INT NOT NULL,
    card_id INT,
    payment_method VARCHAR(50),
    bill_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    payment_time DATETIME
);

CREATE TABLE bill_items (
    bill_item_id INT AUTO_INCREMENT PRIMARY KEY,
    bill_id INT NOT NULL,
    item_id INT NOT NULL,
    quantity INT NOT NULL
);

CREATE TABLE Menu (
    item_id VARCHAR(6) PRIMARY KEY,
    item_name VARCHAR(255),
    item_type VARCHAR(255),
    item_price DECIMAL(10,2)
);

CREATE TABLE reservations (
    reservation_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(255),
    table_id INT,
    reservation_time TIME,
    reservation_date DATE,
    head_count INT,
    special_request VARCHAR(255)
);

CREATE TABLE restaurant_tables (
    table_id INT AUTO_INCREMENT PRIMARY KEY,
    capacity INT,
    is_available TINYINT(1)
);

CREATE TABLE table_availability (
    table_id INT PRIMARY KEY,
    capacity INT NOT NULL,
    is_available TINYINT(1) DEFAULT 1
);

CREATE TABLE login (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'user'
);

CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    items_ordered TEXT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    order_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_paid BOOLEAN DEFAULT FALSE
);