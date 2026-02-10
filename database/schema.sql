-- ============================================================
-- EGOIRE E-COMMERCE DATABASE SCHEMA
-- Luxury Hair Care Platform
-- Engine: InnoDB | Charset: utf8mb4 | Collation: utf8mb4_unicode_ci
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `egoire` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `egoire`;

-- ============================================================
-- 1. USERS & AUTHENTICATION (MVP)
-- ============================================================

CREATE TABLE `users` (
    `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `first_name`      VARCHAR(100) NOT NULL,
    `last_name`       VARCHAR(100) NOT NULL,
    `email`           VARCHAR(255) NOT NULL,
    `password_hash`   VARCHAR(255) NOT NULL,
    `phone`           VARCHAR(30) DEFAULT NULL,
    `status`          ENUM('active','blocked') NOT NULL DEFAULT 'active',
    `email_verified`  TINYINT(1) NOT NULL DEFAULT 0,
    `verify_token`    VARCHAR(64) DEFAULT NULL,
    `reset_token`     VARCHAR(64) DEFAULT NULL,
    `reset_expires`   DATETIME DEFAULT NULL,
    `marketing_optin` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY `uk_users_email` (`email`),
    INDEX `idx_users_status` (`status`),
    INDEX `idx_users_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `user_addresses` (
    `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id`      INT UNSIGNED NOT NULL,
    `label`        VARCHAR(50) DEFAULT 'default',  -- 'home', 'work', 'billing', etc.
    `first_name`   VARCHAR(100) NOT NULL,
    `last_name`    VARCHAR(100) NOT NULL,
    `company`      VARCHAR(150) DEFAULT NULL,
    `address`      VARCHAR(255) NOT NULL,
    `address2`     VARCHAR(255) DEFAULT NULL,
    `city`         VARCHAR(100) NOT NULL,
    `postal_code`  VARCHAR(20) NOT NULL,
    `country`      VARCHAR(100) NOT NULL DEFAULT 'Srbija',
    `phone`        VARCHAR(30) DEFAULT NULL,
    `is_default`   TINYINT(1) NOT NULL DEFAULT 0,
    `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX `idx_user_addr_user` (`user_id`),
    CONSTRAINT `fk_user_addr_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `user_loyalty` (
    `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id`         INT UNSIGNED NOT NULL,
    `points_balance`  INT NOT NULL DEFAULT 0,
    `total_earned`    INT NOT NULL DEFAULT 0,
    `total_spent`     INT NOT NULL DEFAULT 0,
    `updated_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY `uk_loyalty_user` (`user_id`),
    CONSTRAINT `fk_loyalty_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `loyalty_transactions` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id`     INT UNSIGNED NOT NULL,
    `order_id`    INT UNSIGNED DEFAULT NULL,
    `type`        ENUM('earn','spend','admin_add','admin_remove','expire') NOT NULL,
    `points`      INT NOT NULL,  -- positive for earn/admin_add, negative for spend/admin_remove/expire
    `description` VARCHAR(255) DEFAULT NULL,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX `idx_lt_user` (`user_id`),
    INDEX `idx_lt_order` (`order_id`),
    INDEX `idx_lt_created` (`created_at`),
    CONSTRAINT `fk_lt_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `loyalty_settings` (
    `id`                   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `points_per_rsd`       DECIMAL(10,4) NOT NULL DEFAULT 0.01,   -- points earned per 1 RSD spent
    `rsd_per_point`        DECIMAL(10,4) NOT NULL DEFAULT 1.00,   -- RSD discount value of 1 point
    `min_points_redeem`    INT NOT NULL DEFAULT 100,               -- minimum points to redeem
    `expiry_days`          INT DEFAULT NULL,                       -- NULL = no expiry
    `is_active`            TINYINT(1) NOT NULL DEFAULT 1,
    `updated_at`           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `loyalty_settings` (`points_per_rsd`, `rsd_per_point`, `min_points_redeem`, `is_active`)
VALUES (0.01, 1.00, 100, 1);

-- ============================================================
-- 2. BRANDS (MVP)
-- ============================================================

CREATE TABLE `brands` (
    `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`         VARCHAR(150) NOT NULL,
    `slug`         VARCHAR(170) NOT NULL,
    `description`  TEXT DEFAULT NULL,
    `logo`         VARCHAR(500) DEFAULT NULL,
    `is_active`    TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order`   INT NOT NULL DEFAULT 0,
    `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY `uk_brands_slug` (`slug`),
    INDEX `idx_brands_active` (`is_active`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 3. CATEGORIES (MVP) – hierarchical
-- ============================================================

CREATE TABLE `categories` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `parent_id`   INT UNSIGNED DEFAULT NULL,
    `name`        VARCHAR(150) NOT NULL,
    `slug`        VARCHAR(170) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `image`       VARCHAR(500) DEFAULT NULL,
    `status`      ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `sort_order`  INT NOT NULL DEFAULT 0,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY `uk_categories_slug` (`slug`),
    INDEX `idx_cat_parent` (`parent_id`),
    INDEX `idx_cat_status` (`status`, `sort_order`),
    CONSTRAINT `fk_cat_parent` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 4. PRODUCTS (MVP)
-- ============================================================

CREATE TABLE `products` (
    `id`               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `brand_id`         INT UNSIGNED DEFAULT NULL,
    `name`             VARCHAR(255) NOT NULL,
    `slug`             VARCHAR(280) NOT NULL,
    `short_description` TEXT DEFAULT NULL,
    `description`      TEXT DEFAULT NULL,
    `how_to_use`       TEXT DEFAULT NULL,
    `sku`              VARCHAR(50) DEFAULT NULL,
    `price`            DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `sale_price`       DECIMAL(12,2) DEFAULT NULL,
    `on_sale`          TINYINT(1) NOT NULL DEFAULT 0,
    `is_active`        TINYINT(1) NOT NULL DEFAULT 1,
    `main_image`       VARCHAR(500) DEFAULT NULL,
    `created_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY `uk_products_slug` (`slug`),
    UNIQUE KEY `uk_products_sku` (`sku`),
    INDEX `idx_prod_brand` (`brand_id`),
    INDEX `idx_prod_active` (`is_active`),
    INDEX `idx_prod_sale` (`on_sale`, `is_active`),
    INDEX `idx_prod_price` (`price`),
    INDEX `idx_prod_created` (`created_at`),
    CONSTRAINT `fk_prod_brand` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `product_images` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `product_id`  INT UNSIGNED NOT NULL,
    `image_path`  VARCHAR(500) NOT NULL,
    `alt_text`    VARCHAR(255) DEFAULT NULL,
    `sort_order`  INT NOT NULL DEFAULT 0,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX `idx_pimg_product` (`product_id`, `sort_order`),
    CONSTRAINT `fk_pimg_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Many-to-many: products ↔ categories
CREATE TABLE `product_categories` (
    `product_id`   INT UNSIGNED NOT NULL,
    `category_id`  INT UNSIGNED NOT NULL,

    PRIMARY KEY (`product_id`, `category_id`),
    INDEX `idx_pc_category` (`category_id`),
    CONSTRAINT `fk_pc_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_pc_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 5. PRODUCT STOCK (MVP)
-- ============================================================

CREATE TABLE `product_stock` (
    `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `product_id`      INT UNSIGNED NOT NULL,
    `quantity`         INT NOT NULL DEFAULT 0,
    `low_stock_threshold` INT NOT NULL DEFAULT 5,
    `track_stock`      TINYINT(1) NOT NULL DEFAULT 1,
    `updated_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY `uk_pstock_product` (`product_id`),
    INDEX `idx_pstock_qty` (`quantity`),
    CONSTRAINT `fk_pstock_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 6. PRODUCT FLAGS (MVP)
-- ============================================================

CREATE TABLE `product_flags` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `product_id`  INT UNSIGNED NOT NULL,
    `flag`        ENUM('new','on_sale','best_seller') NOT NULL,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY `uk_pflags` (`product_id`, `flag`),
    INDEX `idx_pflags_flag` (`flag`),
    CONSTRAINT `fk_pflags_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 7. CART (MVP) – session-based, supports guests
-- ============================================================

CREATE TABLE `cart` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `session_id`  VARCHAR(128) NOT NULL,
    `user_id`     INT UNSIGNED DEFAULT NULL,
    `product_id`  INT UNSIGNED NOT NULL,
    `quantity`    INT NOT NULL DEFAULT 1,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX `idx_cart_session` (`session_id`),
    INDEX `idx_cart_user` (`user_id`),
    CONSTRAINT `fk_cart_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_cart_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 8. ORDERS & CHECKOUT (MVP)
-- ============================================================

CREATE TABLE `orders` (
    `id`               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_number`     VARCHAR(30) NOT NULL,
    `user_id`          INT UNSIGNED DEFAULT NULL,  -- NULL for guest orders
    `session_id`       VARCHAR(128) DEFAULT NULL,
    `status`           ENUM('new','processing','shipped','delivered','canceled') NOT NULL DEFAULT 'new',
    `payment_method`   VARCHAR(50) NOT NULL DEFAULT 'cod',  -- cod, card, etc.
    `subtotal`         DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `discount_amount`  DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `loyalty_discount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `gift_card_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `shipping_cost`    DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `total_price`      DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `loyalty_earned`   INT NOT NULL DEFAULT 0,
    `loyalty_spent`    INT NOT NULL DEFAULT 0,
    `customer_name`    VARCHAR(200) NOT NULL,
    `email`            VARCHAR(255) NOT NULL,
    `phone`            VARCHAR(30) NOT NULL,
    `customer_note`    TEXT DEFAULT NULL,
    `is_gift_bag`      TINYINT(1) NOT NULL DEFAULT 0,
    `created_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY `uk_orders_number` (`order_number`),
    INDEX `idx_orders_user` (`user_id`),
    INDEX `idx_orders_status` (`status`),
    INDEX `idx_orders_created` (`created_at`),
    INDEX `idx_orders_email` (`email`),
    INDEX `idx_orders_payment` (`payment_method`),
    CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `order_items` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_id`      INT UNSIGNED NOT NULL,
    `product_id`    INT UNSIGNED DEFAULT NULL,
    `product_name`  VARCHAR(255) NOT NULL,   -- snapshot
    `product_sku`   VARCHAR(50) DEFAULT NULL, -- snapshot
    `quantity`      INT NOT NULL DEFAULT 1,
    `unit_price`    DECIMAL(12,2) NOT NULL,   -- price at time of purchase
    `subtotal`      DECIMAL(12,2) NOT NULL,
    `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX `idx_oi_order` (`order_id`),
    INDEX `idx_oi_product` (`product_id`),
    CONSTRAINT `fk_oi_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_oi_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Snapshot of shipping address at time of order
CREATE TABLE `order_addresses` (
    `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_id`     INT UNSIGNED NOT NULL,
    `type`         ENUM('shipping','billing') NOT NULL DEFAULT 'shipping',
    `first_name`   VARCHAR(100) NOT NULL,
    `last_name`    VARCHAR(100) NOT NULL,
    `company`      VARCHAR(150) DEFAULT NULL,
    `address`      VARCHAR(255) NOT NULL,
    `address2`     VARCHAR(255) DEFAULT NULL,
    `city`         VARCHAR(100) NOT NULL,
    `postal_code`  VARCHAR(20) NOT NULL,
    `country`      VARCHAR(100) NOT NULL DEFAULT 'Srbija',
    `phone`        VARCHAR(30) DEFAULT NULL,

    UNIQUE KEY `uk_oaddr` (`order_id`, `type`),
    CONSTRAINT `fk_oaddr_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `order_notes` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_id`   INT UNSIGNED NOT NULL,
    `author`     VARCHAR(100) NOT NULL DEFAULT 'admin',
    `note`       TEXT NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX `idx_onotes_order` (`order_id`),
    CONSTRAINT `fk_onotes_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 9. GIFT BAG SYSTEM (Phase 2)
-- ============================================================

CREATE TABLE `gift_bag_rules` (
    `id`                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`              VARCHAR(150) NOT NULL,
    `min_products`      INT DEFAULT NULL,
    `min_order_value`   DECIMAL(12,2) DEFAULT NULL,
    `allowed_categories` JSON DEFAULT NULL,   -- array of category IDs, NULL = all
    `allowed_brands`    JSON DEFAULT NULL,     -- array of brand IDs, NULL = all
    `is_active`         TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX `idx_gbr_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `gift_bag_discounts` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `rule_id`       INT UNSIGNED NOT NULL,
    `threshold_type` ENUM('product_count','order_value') NOT NULL,
    `threshold_value` DECIMAL(12,2) NOT NULL,
    `discount_type`  ENUM('percentage','fixed') NOT NULL DEFAULT 'percentage',
    `discount_value` DECIMAL(12,2) NOT NULL,
    `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX `idx_gbd_rule` (`rule_id`),
    CONSTRAINT `fk_gbd_rule` FOREIGN KEY (`rule_id`) REFERENCES `gift_bag_rules` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `order_gift_bag` (
    `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_id`        INT UNSIGNED NOT NULL,
    `rule_id`         INT UNSIGNED DEFAULT NULL,
    `discount_id`     INT UNSIGNED DEFAULT NULL,
    `discount_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY `uk_ogb_order` (`order_id`),
    CONSTRAINT `fk_ogb_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ogb_rule` FOREIGN KEY (`rule_id`) REFERENCES `gift_bag_rules` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_ogb_discount` FOREIGN KEY (`discount_id`) REFERENCES `gift_bag_discounts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 10. GIFT CARD SYSTEM (Phase 2)
-- ============================================================

CREATE TABLE `gift_cards` (
    `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `code`            VARCHAR(32) NOT NULL,
    `initial_amount`  DECIMAL(12,2) NOT NULL,
    `balance`         DECIMAL(12,2) NOT NULL,
    `status`          ENUM('active','partially_used','used','expired') NOT NULL DEFAULT 'active',
    `purchaser_email` VARCHAR(255) DEFAULT NULL,
    `recipient_email` VARCHAR(255) DEFAULT NULL,
    `recipient_name`  VARCHAR(200) DEFAULT NULL,
    `message`         TEXT DEFAULT NULL,
    `expires_at`      DATE DEFAULT NULL,
    `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY `uk_gc_code` (`code`),
    INDEX `idx_gc_status` (`status`),
    INDEX `idx_gc_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `gift_card_amounts` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `amount`     DECIMAL(12,2) NOT NULL,
    `is_active`  TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order` INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `gift_card_amounts` (`amount`, `sort_order`) VALUES
(1000.00, 1), (2000.00, 2), (3000.00, 3), (5000.00, 4), (10000.00, 5);

CREATE TABLE `gift_card_settings` (
    `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `is_active`       TINYINT(1) NOT NULL DEFAULT 1,
    `allow_custom`    TINYINT(1) NOT NULL DEFAULT 1,
    `custom_min`      DECIMAL(12,2) NOT NULL DEFAULT 500.00,
    `custom_max`      DECIMAL(12,2) NOT NULL DEFAULT 50000.00,
    `default_expiry_days` INT DEFAULT 365,
    `updated_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `gift_card_settings` (`is_active`, `allow_custom`, `custom_min`, `custom_max`, `default_expiry_days`)
VALUES (1, 1, 500.00, 50000.00, 365);

CREATE TABLE `gift_card_usage` (
    `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `gift_card_id` INT UNSIGNED NOT NULL,
    `order_id`     INT UNSIGNED NOT NULL,
    `amount_used`  DECIMAL(12,2) NOT NULL,
    `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX `idx_gcu_card` (`gift_card_id`),
    INDEX `idx_gcu_order` (`order_id`),
    CONSTRAINT `fk_gcu_card` FOREIGN KEY (`gift_card_id`) REFERENCES `gift_cards` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_gcu_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 11. MARKETING & COMMUNICATION (MVP)
-- ============================================================

CREATE TABLE `email_subscribers` (
    `id`                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id`            INT UNSIGNED DEFAULT NULL,
    `name`               VARCHAR(200) DEFAULT NULL,
    `email`              VARCHAR(255) NOT NULL,
    `source`             ENUM('registration','checkout','manual','footer') NOT NULL DEFAULT 'manual',
    `is_active`          TINYINT(1) NOT NULL DEFAULT 1,
    `unsubscribe_token`  VARCHAR(64) NOT NULL,
    `created_at`         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY `uk_esub_email` (`email`),
    INDEX `idx_esub_active` (`is_active`),
    CONSTRAINT `fk_esub_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `email_campaigns` (
    `id`               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `subject`          VARCHAR(255) NOT NULL,
    `body`             TEXT NOT NULL,
    `target`           ENUM('all','registered','guests') NOT NULL DEFAULT 'all',
    `status`           ENUM('draft','sending','sent','failed') NOT NULL DEFAULT 'draft',
    `total_recipients` INT NOT NULL DEFAULT 0,
    `sent_count`       INT NOT NULL DEFAULT 0,
    `failed_count`     INT NOT NULL DEFAULT 0,
    `sent_at`          DATETIME DEFAULT NULL,
    `created_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX `idx_ec_status` (`status`),
    INDEX `idx_ec_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `email_campaign_logs` (
    `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `campaign_id`  INT UNSIGNED NOT NULL,
    `subscriber_id` INT UNSIGNED NOT NULL,
    `email`        VARCHAR(255) NOT NULL,
    `status`       ENUM('sent','failed') NOT NULL DEFAULT 'sent',
    `error`        VARCHAR(500) DEFAULT NULL,
    `sent_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX `idx_ecl_campaign` (`campaign_id`),
    CONSTRAINT `fk_ecl_campaign` FOREIGN KEY (`campaign_id`) REFERENCES `email_campaigns` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ecl_subscriber` FOREIGN KEY (`subscriber_id`) REFERENCES `email_subscribers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 12. CONTACT & CONSULTATIONS
-- ============================================================

CREATE TABLE `contact_messages` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`       VARCHAR(200) NOT NULL,
    `email`      VARCHAR(255) NOT NULL,
    `phone`      VARCHAR(30) DEFAULT NULL,
    `subject`    VARCHAR(255) DEFAULT NULL,
    `message`    TEXT NOT NULL,
    `status`     ENUM('new','read','replied','archived') NOT NULL DEFAULT 'new',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX `idx_cm_status` (`status`),
    INDEX `idx_cm_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 13. BLOG
-- ============================================================

CREATE TABLE `blog_posts` (
    `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title`          VARCHAR(255) NOT NULL,
    `slug`           VARCHAR(280) NOT NULL,
    `excerpt`        TEXT DEFAULT NULL,
    `body`           TEXT NOT NULL,
    `featured_image` VARCHAR(500) DEFAULT NULL,
    `status`         ENUM('draft','published') NOT NULL DEFAULT 'draft',
    `published_at`   DATETIME DEFAULT NULL,
    `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY `uk_blog_slug` (`slug`),
    INDEX `idx_blog_status` (`status`, `published_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 14. ANALYTICS (aggregated daily data)
-- ============================================================

CREATE TABLE `analytics_daily` (
    `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `date`            DATE NOT NULL,
    `total_orders`    INT NOT NULL DEFAULT 0,
    `total_revenue`   DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    `new_users`       INT NOT NULL DEFAULT 0,
    `new_subscribers` INT NOT NULL DEFAULT 0,
    `guest_orders`    INT NOT NULL DEFAULT 0,
    `registered_orders` INT NOT NULL DEFAULT 0,
    `avg_order_value` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY `uk_analytics_date` (`date`),
    INDEX `idx_analytics_date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 15. ADMIN USERS
-- ============================================================

CREATE TABLE `admin_users` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `username`      VARCHAR(100) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `email`         VARCHAR(255) DEFAULT NULL,
    `role`          ENUM('superadmin','admin','editor') NOT NULL DEFAULT 'admin',
    `is_active`     TINYINT(1) NOT NULL DEFAULT 1,
    `last_login`    DATETIME DEFAULT NULL,
    `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY `uk_admin_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default admin: password = Victory2024!
INSERT INTO `admin_users` (`username`, `password_hash`, `role`)
VALUES ('victoryadmin', '$2y$10$/F8YqwEIyDVUmRNhSC1W4Omo3jlNSyb4LIceQew7nR6sfkVTG6/Ju', 'superadmin');

-- ============================================================
-- 16. STATIC PAGES (FAQ, About, Terms, etc.)
-- ============================================================

CREATE TABLE `pages` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `slug`       VARCHAR(100) NOT NULL,
    `title`      VARCHAR(255) NOT NULL,
    `body`       TEXT NOT NULL,
    `status`     ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY `uk_pages_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `pages` (`slug`, `title`, `body`) VALUES
('about', 'O nama', '<p>Sadržaj o nama stranice.</p>'),
('terms', 'Uslovi korišćenja', '<p>Sadržaj uslova korišćenja.</p>'),
('privacy', 'Politika privatnosti', '<p>Sadržaj politike privatnosti.</p>'),
('shipping', 'Politika isporuke i povraćaja', '<p>Sadržaj politike isporuke.</p>');

-- ============================================================
-- 17. FAQ
-- ============================================================

CREATE TABLE `faq` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `question`   VARCHAR(500) NOT NULL,
    `answer`     TEXT NOT NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `is_active`  TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX `idx_faq_active` (`is_active`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
