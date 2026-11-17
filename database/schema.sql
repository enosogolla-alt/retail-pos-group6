-- Retail POS database schema
-- Owner: Kyle Tarus (M2) · Reviewed by: David Muturi (M6)
-- Task: 2.1
--
-- Engine InnoDB, charset utf8mb4, collation utf8mb4_unicode_ci.
-- Create tables in this order so every foreign key points to an existing table:
--   1. users
--   2. categories
--   3. settings
--   4. products          (category_id -> categories)
--   5. price_history     (product_id -> products, changed_by -> users)
--   6. sales             (user_id -> users, voided_by -> users)
--   7. sale_items        (sale_id -> sales, product_id -> products)
--   8. payments          (sale_id -> sales)
--   9. stock_movements   (product_id -> products, user_id -> users)
--  10. audit_log         (user_id -> users)

CREATE DATABASE IF NOT EXISTS retail_pos
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE retail_pos;
