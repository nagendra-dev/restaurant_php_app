-- Dummy entries for login
TRUNCATE TABLE login;
INSERT INTO login (username, password, role) VALUES
('admin', 'admin123', 'admin'),
('manager', 'manager@2024', 'manager'),
('waiter', 'waiterpass', 'waiter');

-- Dummy entries for table_availability
TRUNCATE TABLE table_availability;
INSERT INTO table_availability (table_id, capacity, is_available) VALUES
(1, 4, 1),
(2, 6, 1),
(3, 2, 0),
(4, 8, 1),
(5, 4, 1);

-- Dummy entries for restaurant_tables
TRUNCATE TABLE restaurant_tables;
INSERT INTO restaurant_tables (capacity, is_available) VALUES
(4, 1),
(6, 1),
(2, 0),
(8, 1),
(4, 1);

-- Dummy entries for Menu
TRUNCATE TABLE Menu;
INSERT INTO Menu (item_id, item_name, item_type, item_price) VALUES
('I1', 'Vanilla', 'Dessert', 105.00),
('I2', 'Margherita Pizza', 'Main Course', 250.00),
('I3', 'Coke', 'Beverage', 45.00);

-- Dummy entries for reservations
TRUNCATE TABLE reservations;
INSERT INTO reservations (customer_name, table_id, reservation_time, reservation_date, head_count, special_request) VALUES
('John Doe', 1, '19:00:00', '2025-04-23', 4, 'Window seat'),
('Jane Smith', 2, '20:30:00', '2025-04-23', 2, 'Birthday celebration');

-- Dummy entries for bills
TRUNCATE TABLE bills;
INSERT INTO bills (reservation_id, table_id, card_id, payment_method, payment_time) VALUES
(1, 1, NULL, 'cash', '2025-04-23 19:30:00'),
(2, 2, NULL, 'credit_card', '2025-04-23 21:00:00');

-- Dummy entries for bill_items
TRUNCATE TABLE bill_items;
INSERT INTO bill_items (bill_id, item_id, quantity) VALUES
(1, 'I1', 2),
(1, 'I2', 1),
(2, 'I3', 3);


