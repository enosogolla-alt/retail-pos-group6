-- Retail POS database schema
-- Owner: Kyle Tarus (M2) · Reviewed by: David Muturi (M6)
-- Task: 2.1
--
-- Engine InnoDB (transactions and foreign keys), charset utf8mb4.
-- Re-running this file drops and recreates every table, so all local data is reset.

CREATE DATABASE IF NOT EXISTS retail_pos
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE retail_pos;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS audit_log;
DROP TABLE IF EXISTS stock_movements;
DROP TABLE IF EXISTS suppliers;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS sale_items;
DROP TABLE IF EXISTS sales;
DROP TABLE IF EXISTS price_history;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS settings;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    id              INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    username        VARCHAR(50)      NOT NULL,
    full_name       VARCHAR(100)     NOT NULL,
    password_hash   VARCHAR(255)     NOT NULL,
    role            ENUM('Admin', 'Cashier') NOT NULL DEFAULT 'Cashier',
    is_active       TINYINT(1)       NOT NULL DEFAULT 1,
    failed_attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
    locked_until    DATETIME         NULL,
    must_change_pw  TINYINT(1)       NOT NULL DEFAULT 0,
    created_at      DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME         NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_username (username)
) ENGINE = InnoDB;

CREATE TABLE categories (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name       VARCHAR(60)  NOT NULL,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_categories_name (name)
) ENGINE = InnoDB;

CREATE TABLE settings (
    setting_key   VARCHAR(50)  NOT NULL,
    setting_value VARCHAR(255) NOT NULL,
    PRIMARY KEY (setting_key)
) ENGINE = InnoDB;

CREATE TABLE products (
    id            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    barcode       VARCHAR(20)   NOT NULL,
    name          VARCHAR(120)  NOT NULL,
    category_id   INT UNSIGNED  NOT NULL,
    selling_price DECIMAL(10,2) NOT NULL,
    cost_price    DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    stock_qty     INT           NOT NULL DEFAULT 0,
    reorder_level INT           NOT NULL DEFAULT 5,
    is_active     TINYINT(1)    NOT NULL DEFAULT 1,
    created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME      NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_products_barcode (barcode),
    KEY idx_products_name (name),
    CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories (id),
    CONSTRAINT chk_products_price CHECK (selling_price > 0),
    CONSTRAINT chk_products_cost CHECK (cost_price >= 0),
    CONSTRAINT chk_products_stock CHECK (stock_qty >= 0),
    CONSTRAINT chk_products_reorder CHECK (reorder_level >= 0)
) ENGINE = InnoDB;

CREATE TABLE price_history (
    id         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    product_id INT UNSIGNED  NOT NULL,
    old_price  DECIMAL(10,2) NOT NULL,
    new_price  DECIMAL(10,2) NOT NULL,
    changed_by INT UNSIGNED  NOT NULL,
    changed_at DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_price_history_product (product_id),
    CONSTRAINT fk_price_history_product FOREIGN KEY (product_id) REFERENCES products (id),
    CONSTRAINT fk_price_history_user FOREIGN KEY (changed_by) REFERENCES users (id)
) ENGINE = InnoDB;

CREATE TABLE sales (
    id               INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    receipt_no       VARCHAR(20)   NOT NULL,
    user_id          INT UNSIGNED  NOT NULL,
    subtotal         DECIMAL(10,2) NOT NULL,
    discount_percent DECIMAL(5,2)  NOT NULL DEFAULT 0.00,
    discount         DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total            DECIMAL(10,2) NOT NULL,
    vat              DECIMAL(10,2) NOT NULL,
    status           ENUM('COMPLETED', 'VOID') NOT NULL DEFAULT 'COMPLETED',
    void_reason      VARCHAR(255)  NULL,
    voided_by        INT UNSIGNED  NULL,
    voided_at        DATETIME      NULL,
    created_at       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_sales_receipt (receipt_no),
    KEY idx_sales_created (created_at),
    CONSTRAINT fk_sales_user FOREIGN KEY (user_id) REFERENCES users (id),
    CONSTRAINT fk_sales_voided_by FOREIGN KEY (voided_by) REFERENCES users (id),
    CONSTRAINT chk_sales_total CHECK (total >= 0),
    CONSTRAINT chk_sales_discount CHECK (discount_percent BETWEEN 0 AND 100)
) ENGINE = InnoDB;

CREATE TABLE sale_items (
    id         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    sale_id    INT UNSIGNED  NOT NULL,
    product_id INT UNSIGNED  NOT NULL,
    qty        INT           NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    line_total DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (id),
    KEY idx_sale_items_sale (sale_id),
    CONSTRAINT fk_sale_items_sale FOREIGN KEY (sale_id) REFERENCES sales (id),
    CONSTRAINT fk_sale_items_product FOREIGN KEY (product_id) REFERENCES products (id),
    CONSTRAINT chk_sale_items_qty CHECK (qty > 0)
) ENGINE = InnoDB;

CREATE TABLE payments (
    id              INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    sale_id         INT UNSIGNED  NOT NULL,
    method          ENUM('CASH', 'MPESA') NOT NULL,
    amount_tendered DECIMAL(10,2) NOT NULL,
    change_given    DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    mpesa_code      VARCHAR(10)   NULL,
    phone           VARCHAR(15)   NULL,
    paid_at         DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_payments_sale (sale_id),
    UNIQUE KEY uq_payments_mpesa_code (mpesa_code),
    CONSTRAINT fk_payments_sale FOREIGN KEY (sale_id) REFERENCES sales (id)
) ENGINE = InnoDB;

-- Suppliers are deactivated, never deleted, so past deliveries stay linked to them (BR-4).
CREATE TABLE suppliers (
    id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name           VARCHAR(120) NOT NULL,
    contact_person VARCHAR(100) NOT NULL DEFAULT '',
    phone          VARCHAR(20)  NOT NULL DEFAULT '',
    email          VARCHAR(120) NOT NULL DEFAULT '',
    address        VARCHAR(255) NOT NULL DEFAULT '',
    is_active      TINYINT(1)   NOT NULL DEFAULT 1,
    created_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_suppliers_name (name)
) ENGINE = InnoDB;

CREATE TABLE stock_movements (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    product_id  INT UNSIGNED NOT NULL,
    type        ENUM('SALE', 'RESTOCK', 'DAMAGE', 'CORRECTION', 'VOID') NOT NULL,
    quantity    INT          NOT NULL,
    stock_after INT          NOT NULL,
    reason      VARCHAR(255) NOT NULL,
    user_id     INT UNSIGNED NOT NULL,
    sale_id     INT UNSIGNED NULL,
    supplier_id INT UNSIGNED NULL,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_stock_movements_product (product_id, created_at),
    KEY idx_stock_movements_supplier (supplier_id, created_at),
    CONSTRAINT fk_stock_movements_product FOREIGN KEY (product_id) REFERENCES products (id),
    CONSTRAINT fk_stock_movements_user FOREIGN KEY (user_id) REFERENCES users (id),
    CONSTRAINT fk_stock_movements_sale FOREIGN KEY (sale_id) REFERENCES sales (id),
    CONSTRAINT fk_stock_movements_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers (id)
) ENGINE = InnoDB;

CREATE TABLE audit_log (
    id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id    INT UNSIGNED    NULL,
    action     VARCHAR(40)     NOT NULL,
    details    VARCHAR(500)    NOT NULL DEFAULT '',
    ip_address VARCHAR(45)     NOT NULL,
    created_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_audit_log_action (action),
    KEY idx_audit_log_created (created_at),
    CONSTRAINT fk_audit_log_user FOREIGN KEY (user_id) REFERENCES users (id)
) ENGINE = InnoDB;
