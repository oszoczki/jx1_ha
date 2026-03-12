-- =============================================================================
-- Events & Tickets schema (MySQL)
-- Tables: events, tickets, transactions, transaction_items, transaction_payment_methods
-- Payment method types: bank card, cash, SZEP card, gift voucher
-- One purchase can be paid with multiple payment methods (split payment).
-- =============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------------------------------
-- Payment method lookup (optional; can use enum in transaction_payment_methods)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS payment_method_types (
    id TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    code VARCHAR(32) NOT NULL COMMENT 'bank_card, cash, szep_card, gift_voucher',
    name VARCHAR(64) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uk_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Events
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS events (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    event_date DATETIME NOT NULL,
    capacity INT UNSIGNED NOT NULL COMMENT 'Total number of seats/tickets',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_event_date (event_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Event ticket types
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS event_ticket_types (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    event_id INT UNSIGNED NOT NULL,
    ticket_type_name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT UNSIGNED NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_event_id (event_id),
    INDEX idx_ticket_type_name (ticket_type_name),
    FOREIGN KEY (event_id) REFERENCES events (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Tickets: one row per seat; as many rows as event capacity. Each has code and status.
-- Status: sold, free, reserved, not_for_sale
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS tickets (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    event_id INT UNSIGNED NOT NULL,
    ticket_type_id INT UNSIGNED NOT NULL,
    code VARCHAR(64) NOT NULL COMMENT 'Unique ticket code',
    ticket_status ENUM('sold', 'free', 'reserved', 'not_for_sale') NOT NULL DEFAULT 'free',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_code (code),
    INDEX idx_event_id (event_id),
    INDEX idx_ticket_type_id (ticket_type_id),
    INDEX idx_status (ticket_status),
    FOREIGN KEY (ticket_type_id) REFERENCES event_ticket_types (id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Transactions (purchase / sale)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS transactions (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    transaction_number VARCHAR(64) NOT NULL COMMENT 'Human-readable transaction ref',
    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    transaction_status ENUM('pending', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_transaction_number (transaction_number),
    INDEX idx_created_at (created_at),
    INDEX idx_status (transaction_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Transaction items (tickets sold in this transaction)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS transaction_items (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    transaction_id INT UNSIGNED NOT NULL,
    ticket_id INT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_transaction_id (transaction_id),
    INDEX idx_ticket_id (ticket_id),
    UNIQUE KEY uk_ticket_sale (ticket_id) COMMENT 'One ticket can only be sold once',
    FOREIGN KEY (transaction_id) REFERENCES transactions (id) ON DELETE CASCADE,
    FOREIGN KEY (ticket_id) REFERENCES tickets (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Transaction payment methods: one purchase can be paid with multiple methods
-- (e.g. part card, part cash)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS transaction_payment_methods (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    transaction_id INT UNSIGNED NOT NULL,
    payment_type ENUM('bank_card', 'cash', 'szep_card', 'gift_voucher') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_transaction_id (transaction_id),
    KEY idx_payment_type (payment_type),
    CONSTRAINT fk_payment_transaction FOREIGN KEY (transaction_id) REFERENCES transactions (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================================
-- Test data
-- =============================================================================

INSERT INTO payment_method_types (code, name) VALUES
('bank_card', 'Bank card'),
('cash', 'Cash'),
('szep_card', 'SZÉP card'),
('gift_voucher', 'Gift voucher');

INSERT INTO events (id, name, event_date, capacity) VALUES
(1, 'Concert A', '2026-02-15 19:00:00', 100),
(2, 'Theatre B', '2026-02-20 18:00:00', 50),
(3, 'Festival C', '2026-03-01 14:00:00', 200),
(4, 'Concert D', '2026-01-10 20:00:00', 80);

-- Event ticket types (event_id, name, price)
INSERT INTO event_ticket_types (id, event_id, ticket_type_name, price, quantity) VALUES
(1, 1, 'Standard', 30.00, 80),
(2, 1, 'VIP', 50.00, 20),
(3, 2, 'Balcony', 25.00, 25),
(4, 2, 'Ground floor', 40.00, 25),
(5, 3, 'Day ticket', 50.00, 200),
(6, 4, 'Standard', 45.00, 80);

-- Tickets: for each event_ticket_type create as many tickets as its quantity (code EV{event_id}-{ticket_type_id}-{seq})
-- Requires MySQL 8+ (recursive CTE). Max quantity in event_ticket_types is 200.
INSERT INTO tickets (event_id, ticket_type_id, code, ticket_status)
SELECT
    ett.event_id,
    ett.id,
    CONCAT('EV', ett.event_id, '-', ett.id, '-', LPAD(n.num, 3, '0')),
    'free'
FROM event_ticket_types ett
CROSS JOIN (
    WITH RECURSIVE n AS (
        SELECT 1 AS num
        UNION ALL
        SELECT num + 1 FROM n WHERE num < 200
    )
    SELECT num FROM n
) n
WHERE n.num <= ett.quantity;

-- Transactions (completed / pending / cancelled)
INSERT INTO transactions (id, transaction_number, total_amount, transaction_status, created_at) VALUES
(1, 'T2026-001', 60.00, 'completed', '2026-01-05 10:00:00'),
(2, 'T2026-002', 120.00, 'completed', '2026-01-12 14:30:00'),
(3, 'T2026-003', 45.00, 'completed', '2026-01-20 09:15:00'),
(4, 'T2026-004', 90.00, 'completed', '2026-02-01 11:00:00'),
(5, 'T2026-005', 150.00, 'completed', '2026-02-10 16:45:00'),
(6, 'T2026-006', 75.00, 'completed', '2026-02-15 12:00:00'),
(7, 'T2026-007', 200.00, 'completed', '2026-02-20 18:00:00'),
(8, 'T2026-008', 80.00, 'pending', '2026-03-10 09:00:00'),
(9, 'T2026-009', 50.00, 'cancelled', '2026-02-25 14:00:00');

-- Transaction items: which ticket belongs to which transaction (ticket_id by code)
INSERT INTO transaction_items (transaction_id, ticket_id)
SELECT 1, id FROM tickets WHERE code = 'EV1-001'
UNION ALL SELECT 1, id FROM tickets WHERE code = 'EV1-002';
INSERT INTO transaction_items (transaction_id, ticket_id)
SELECT 2, id FROM tickets WHERE code IN ('EV2-006', 'EV2-007', 'EV2-008');
INSERT INTO transaction_items (transaction_id, ticket_id)
SELECT 3, id FROM tickets WHERE code = 'EV4-001';
INSERT INTO transaction_items (transaction_id, ticket_id)
SELECT 4, id FROM tickets WHERE code IN ('EV1-004', 'EV1-006', 'EV1-007');
INSERT INTO transaction_items (transaction_id, ticket_id)
SELECT 5, id FROM tickets WHERE code IN ('EV3-001', 'EV3-002', 'EV3-003');
INSERT INTO transaction_items (transaction_id, ticket_id)
SELECT 6, id FROM tickets WHERE code IN ('EV1-008', 'EV1-009', 'EV1-013');
INSERT INTO transaction_items (transaction_id, ticket_id)
SELECT 7, id FROM tickets WHERE code IN ('EV2-001', 'EV2-002', 'EV2-003', 'EV2-004', 'EV2-005');

-- Mark as sold all tickets that appear in transaction_items
UPDATE tickets SET ticket_status = 'sold' WHERE id IN (SELECT ticket_id FROM transaction_items);

-- Payment methods per transaction (amounts match total_amount / split)
INSERT INTO transaction_payment_methods (transaction_id, payment_type, amount) VALUES
(1, 'bank_card', 60.00),
(2, 'cash', 80.00), (2, 'szep_card', 40.00),
(3, 'bank_card', 45.00),
(4, 'gift_voucher', 50.00), (4, 'cash', 40.00),
(5, 'bank_card', 150.00),
(6, 'cash', 75.00),
(7, 'bank_card', 120.00), (7, 'szep_card', 80.00),
(8, 'bank_card', 80.00),
(9, 'cash', 50.00);
