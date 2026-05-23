-- =============================================================
--  Luluat Almisbah — Children's Clothing & Baby Products eCommerce
--  Database schema (MySQL 5.7+ / MariaDB 10.3+)
-- =============================================================

CREATE DATABASE IF NOT EXISTS `kids_store`
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE `kids_store`;

-- -------------------------------------------------------------
--  Drop existing tables (safe re-import)
-- -------------------------------------------------------------
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `wishlist`;
DROP TABLE IF EXISTS `cart`;
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `offers`;
DROP TABLE IF EXISTS `product_images`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `sections`;
DROP TABLE IF EXISTS `contact_messages`;
DROP TABLE IF EXISTS `settings`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `admins`;
SET FOREIGN_KEY_CHECKS = 1;

-- -------------------------------------------------------------
--  admins
-- -------------------------------------------------------------
CREATE TABLE `admins` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username`      VARCHAR(60)   NOT NULL,
  `email`         VARCHAR(120)  NOT NULL,
  `password_hash` VARCHAR(255)  NOT NULL,
  `full_name`     VARCHAR(120)  NOT NULL,
  `avatar`        VARCHAR(255)  DEFAULT NULL,
  `is_active`     TINYINT(1)    NOT NULL DEFAULT 1,
  `last_login`    DATETIME      DEFAULT NULL,
  `created_at`    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_admins_username` (`username`),
  UNIQUE KEY `uk_admins_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
--  users (storefront customers)
-- -------------------------------------------------------------
CREATE TABLE `users` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `full_name`     VARCHAR(120)  NOT NULL,
  `email`         VARCHAR(120)  NOT NULL,
  `phone`         VARCHAR(40)   DEFAULT NULL,
  `password_hash` VARCHAR(255)  NOT NULL,
  `address`       VARCHAR(255)  DEFAULT NULL,
  `city`          VARCHAR(80)   DEFAULT NULL,
  `is_active`     TINYINT(1)    NOT NULL DEFAULT 1,
  `created_at`    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
--  sections (top-level groupings, e.g. "Baby Clothing")
-- -------------------------------------------------------------
CREATE TABLE `sections` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name_ar`    VARCHAR(120)  NOT NULL,
  `name_en`    VARCHAR(120)  NOT NULL,
  `slug`       VARCHAR(140)  NOT NULL,
  `icon`       VARCHAR(80)   DEFAULT NULL,
  `image`      VARCHAR(255)  DEFAULT NULL,
  `sort_order` INT           NOT NULL DEFAULT 0,
  `is_active`  TINYINT(1)    NOT NULL DEFAULT 1,
  `created_at` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_sections_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
--  categories (under each section)
-- -------------------------------------------------------------
CREATE TABLE `categories` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `section_id` INT UNSIGNED NOT NULL,
  `name_ar`    VARCHAR(120)  NOT NULL,
  `name_en`    VARCHAR(120)  NOT NULL,
  `slug`       VARCHAR(140)  NOT NULL,
  `image`      VARCHAR(255)  DEFAULT NULL,
  `sort_order` INT           NOT NULL DEFAULT 0,
  `is_active`  TINYINT(1)    NOT NULL DEFAULT 1,
  `created_at` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_categories_section_slug` (`section_id`, `slug`),
  KEY `idx_categories_section` (`section_id`),
  CONSTRAINT `fk_categories_section`
    FOREIGN KEY (`section_id`) REFERENCES `sections`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
--  products
-- -------------------------------------------------------------
CREATE TABLE `products` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `section_id`     INT UNSIGNED NOT NULL,
  `category_id`    INT UNSIGNED NOT NULL,
  `name_ar`        VARCHAR(180)   NOT NULL,
  `name_en`        VARCHAR(180)   NOT NULL,
  `slug`           VARCHAR(200)   NOT NULL,
  `sku`            VARCHAR(60)    DEFAULT NULL,
  `description_ar` TEXT           NOT NULL,
  `description_en` TEXT           NOT NULL,
  `price`          DECIMAL(10,2)  NOT NULL,
  `discount_price` DECIMAL(10,2)  DEFAULT NULL,
  `stock`          INT            NOT NULL DEFAULT 0,
  `sizes`          VARCHAR(255)   DEFAULT NULL COMMENT 'Comma separated',
  `colors`         VARCHAR(255)   DEFAULT NULL COMMENT 'Comma separated',
  `is_featured`    TINYINT(1)     NOT NULL DEFAULT 0,
  `is_active`      TINYINT(1)     NOT NULL DEFAULT 1,
  `views`          INT UNSIGNED   NOT NULL DEFAULT 0,
  `created_at`     DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP
                                 ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_products_slug` (`slug`),
  KEY `idx_products_section`  (`section_id`),
  KEY `idx_products_category` (`category_id`),
  KEY `idx_products_featured` (`is_featured`),
  KEY `idx_products_active`   (`is_active`),
  CONSTRAINT `fk_products_section`
    FOREIGN KEY (`section_id`)  REFERENCES `sections`(`id`)   ON DELETE CASCADE,
  CONSTRAINT `fk_products_category`
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
--  product_images (4 per product)
-- -------------------------------------------------------------
CREATE TABLE `product_images` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT UNSIGNED NOT NULL,
  `image_path` VARCHAR(255)  NOT NULL,
  `is_primary` TINYINT(1)    NOT NULL DEFAULT 0,
  `sort_order` INT           NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_product_images_product` (`product_id`),
  CONSTRAINT `fk_product_images_product`
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
--  offers
-- -------------------------------------------------------------
CREATE TABLE `offers` (
  `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title_ar`         VARCHAR(180)   NOT NULL,
  `title_en`         VARCHAR(180)   NOT NULL,
  `description_ar`   TEXT           DEFAULT NULL,
  `description_en`   TEXT           DEFAULT NULL,
  `image`            VARCHAR(255)   DEFAULT NULL,
  `discount_percent` DECIMAL(5,2)   NOT NULL DEFAULT 0,
  `link_url`         VARCHAR(255)   DEFAULT NULL,
  `start_date`       DATE           DEFAULT NULL,
  `end_date`         DATE           DEFAULT NULL,
  `is_active`        TINYINT(1)     NOT NULL DEFAULT 1,
  `created_at`       DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
--  orders
-- -------------------------------------------------------------
CREATE TABLE `orders` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`        INT UNSIGNED DEFAULT NULL,
  `full_name`      VARCHAR(120)   NOT NULL,
  `email`          VARCHAR(120)   NOT NULL,
  `phone`          VARCHAR(40)    NOT NULL,
  `address`        VARCHAR(255)   NOT NULL,
  `city`           VARCHAR(80)    NOT NULL,
  `notes`          TEXT           DEFAULT NULL,
  `subtotal`       DECIMAL(10,2)  NOT NULL DEFAULT 0,
  `shipping`       DECIMAL(10,2)  NOT NULL DEFAULT 0,
  `total`          DECIMAL(10,2)  NOT NULL DEFAULT 0,
  `payment_method` ENUM('cod','card','wallet') NOT NULL DEFAULT 'cod',
  `status`         ENUM('pending','processing','shipped','delivered','cancelled')
                   NOT NULL DEFAULT 'pending',
  `created_at`     DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_orders_user`   (`user_id`),
  KEY `idx_orders_status` (`status`),
  CONSTRAINT `fk_orders_user`
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
--  order_items
-- -------------------------------------------------------------
CREATE TABLE `order_items` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id`      INT UNSIGNED NOT NULL,
  `product_id`    INT UNSIGNED DEFAULT NULL,
  `product_name`  VARCHAR(180)   NOT NULL,
  `unit_price`    DECIMAL(10,2)  NOT NULL,
  `quantity`      INT            NOT NULL DEFAULT 1,
  `size`          VARCHAR(40)    DEFAULT NULL,
  `color`         VARCHAR(40)    DEFAULT NULL,
  `line_total`    DECIMAL(10,2)  NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_order_items_order`   (`order_id`),
  KEY `idx_order_items_product` (`product_id`),
  CONSTRAINT `fk_order_items_order`
    FOREIGN KEY (`order_id`)   REFERENCES `orders`(`id`)   ON DELETE CASCADE,
  CONSTRAINT `fk_order_items_product`
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
--  cart  (supports both logged-in users and guest sessions)
-- -------------------------------------------------------------
CREATE TABLE `cart` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    INT UNSIGNED DEFAULT NULL,
  `session_id` VARCHAR(120)   DEFAULT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `quantity`   INT            NOT NULL DEFAULT 1,
  `size`       VARCHAR(40)    DEFAULT NULL,
  `color`      VARCHAR(40)    DEFAULT NULL,
  `created_at` DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cart_user`    (`user_id`),
  KEY `idx_cart_session` (`session_id`),
  KEY `idx_cart_product` (`product_id`),
  CONSTRAINT `fk_cart_user`
    FOREIGN KEY (`user_id`)    REFERENCES `users`(`id`)    ON DELETE CASCADE,
  CONSTRAINT `fk_cart_product`
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
--  wishlist
-- -------------------------------------------------------------
CREATE TABLE `wishlist` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    INT UNSIGNED DEFAULT NULL,
  `session_id` VARCHAR(120)   DEFAULT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `created_at` DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_wishlist_user_product`    (`user_id`, `product_id`),
  UNIQUE KEY `uk_wishlist_session_product` (`session_id`, `product_id`),
  KEY `idx_wishlist_product` (`product_id`),
  CONSTRAINT `fk_wishlist_user`
    FOREIGN KEY (`user_id`)    REFERENCES `users`(`id`)    ON DELETE CASCADE,
  CONSTRAINT `fk_wishlist_product`
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
--  contact_messages
-- -------------------------------------------------------------
CREATE TABLE `contact_messages` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(120) NOT NULL,
  `email`      VARCHAR(120) NOT NULL,
  `phone`      VARCHAR(40)  DEFAULT NULL,
  `subject`    VARCHAR(200) NOT NULL,
  `message`    TEXT         NOT NULL,
  `is_read`    TINYINT(1)   NOT NULL DEFAULT 0,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
--  settings (key/value site config)
-- -------------------------------------------------------------
CREATE TABLE `settings` (
  `key_name`   VARCHAR(80)  NOT NULL,
  `value`      TEXT         DEFAULT NULL,
  `updated_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
                            ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`key_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
