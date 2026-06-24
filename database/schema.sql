-- =============================================================================
-- Maia Suplementos — Schema SQL Completo
-- MySQL 8.0+ | InnoDB | utf8mb4
-- PRD v1.0
-- =============================================================================

SET NAMES utf8mb4;
SET time_zone = '-03:00';
SET foreign_key_checks = 0;

-- =============================================================================
-- 1. users — Clientes cadastrados
-- =============================================================================
CREATE TABLE IF NOT EXISTS `users` (
    `id`               INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `name`             VARCHAR(150)    NOT NULL,
    `email`            VARCHAR(255)    NOT NULL,
    `password_hash`    VARCHAR(255)    NOT NULL,
    `phone`            VARCHAR(20)     DEFAULT NULL,
    `city`             VARCHAR(100)    DEFAULT NULL,
    `state`            CHAR(2)         DEFAULT NULL,
    `email_opt_in`     TINYINT(1)      NOT NULL DEFAULT 1,
    `tag`              ENUM('','vip','atacado','bloqueado') NOT NULL DEFAULT '',
    `total_orders`     INT UNSIGNED    NOT NULL DEFAULT 0,
    `total_spent`      DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    `last_purchase_at` DATETIME        DEFAULT NULL,
    `created_at`       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_users_email` (`email`),
    KEY `idx_users_tag` (`tag`),
    KEY `idx_users_created_at` (`created_at`),
    KEY `idx_users_last_purchase_at` (`last_purchase_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 2. admin_users — Administradores do painel
-- =============================================================================
CREATE TABLE IF NOT EXISTS `admin_users` (
    `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `name`          VARCHAR(150)  NOT NULL,
    `email`         VARCHAR(255)  NOT NULL,
    `password_hash` VARCHAR(255)  NOT NULL,
    `role`          ENUM('admin','operator') NOT NULL DEFAULT 'operator',
    `last_login`    DATETIME      DEFAULT NULL,
    `active`        TINYINT(1)    NOT NULL DEFAULT 1,
    `created_at`    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_admin_users_email` (`email`),
    KEY `idx_admin_users_role` (`role`),
    KEY `idx_admin_users_active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 3. categories — Categorias de produtos
-- =============================================================================
CREATE TABLE IF NOT EXISTS `categories` (
    `id`              INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `name`            VARCHAR(150)  NOT NULL,
    `slug`            VARCHAR(150)  NOT NULL,
    `seo_title`       VARCHAR(255)  DEFAULT NULL,
    `seo_description` VARCHAR(500)  DEFAULT NULL,
    `image`           VARCHAR(255)  DEFAULT NULL,
    `active`          TINYINT(1)    NOT NULL DEFAULT 1,
    `sort_order`      INT UNSIGNED  NOT NULL DEFAULT 0,
    `created_at`      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_categories_slug` (`slug`),
    KEY `idx_categories_active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 4. brands — Marcas dos produtos
-- =============================================================================
CREATE TABLE IF NOT EXISTS `brands` (
    `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(150)  NOT NULL,
    `slug`        VARCHAR(150)  NOT NULL,
    `logo`        VARCHAR(255)  DEFAULT NULL,
    `description` TEXT          DEFAULT NULL,
    `active`      TINYINT(1)    NOT NULL DEFAULT 1,
    `created_at`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_brands_slug` (`slug`),
    KEY `idx_brands_active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 5. products — Catálogo de produtos
-- =============================================================================
CREATE TABLE IF NOT EXISTS `products` (
    `id`                   INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `name`                 VARCHAR(255)   NOT NULL,
    `slug`                 VARCHAR(255)   NOT NULL,
    `sku`                  VARCHAR(100)   DEFAULT NULL,
    `category_id`          INT UNSIGNED   DEFAULT NULL,
    `brand_id`             INT UNSIGNED   DEFAULT NULL,
    `price`                DECIMAL(10,2)  NOT NULL,
    `price_sale`           DECIMAL(10,2)  DEFAULT NULL,
    `stock`                INT            NOT NULL DEFAULT 0,
    `stock_alert_threshold` INT UNSIGNED  NOT NULL DEFAULT 5,
    `description`          TEXT           DEFAULT NULL,
    `benefits`             TEXT           DEFAULT NULL,
    `nutrition_table`      JSON           DEFAULT NULL,
    `seo_title`            VARCHAR(255)   DEFAULT NULL,
    `seo_description`      VARCHAR(500)   DEFAULT NULL,
    `og_image`             VARCHAR(255)   DEFAULT NULL,
    `active`               TINYINT(1)     NOT NULL DEFAULT 1,
    `featured`             TINYINT(1)     NOT NULL DEFAULT 0,
    `bestseller`           TINYINT(1)     NOT NULL DEFAULT 0,
    `total_sold`           INT UNSIGNED   NOT NULL DEFAULT 0,
    `created_at`           DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`           DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_products_slug` (`slug`),
    UNIQUE KEY `uq_products_sku` (`sku`),
    KEY `idx_products_category_id` (`category_id`),
    KEY `idx_products_brand_id` (`brand_id`),
    KEY `idx_products_active` (`active`),
    KEY `idx_products_featured` (`featured`),
    KEY `idx_products_bestseller` (`bestseller`),
    KEY `idx_products_stock` (`stock`),
    CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON UPDATE CASCADE,
    CONSTRAINT `fk_products_brand`    FOREIGN KEY (`brand_id`)    REFERENCES `brands` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 6. product_images — Galeria de imagens
-- =============================================================================
CREATE TABLE IF NOT EXISTS `product_images` (
    `id`             INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `product_id`     INT UNSIGNED  NOT NULL,
    `filename`       VARCHAR(255)  NOT NULL,
    `filename_webp`  VARCHAR(255)  NOT NULL,
    `is_main`        TINYINT(1)    NOT NULL DEFAULT 0,
    `sort_order`     INT UNSIGNED  NOT NULL DEFAULT 0,
    `created_at`     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_product_images_product_id` (`product_id`),
    KEY `idx_product_images_is_main` (`is_main`),
    CONSTRAINT `fk_product_images_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 7. combos — Combos promocionais
-- =============================================================================
CREATE TABLE IF NOT EXISTS `combos` (
    `id`          INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(255)   NOT NULL,
    `slug`        VARCHAR(255)   NOT NULL,
    `description` TEXT           DEFAULT NULL,
    `price`       DECIMAL(10,2)  NOT NULL,
    `image`       VARCHAR(255)   DEFAULT NULL,
    `active`      TINYINT(1)     NOT NULL DEFAULT 1,
    `created_at`  DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_combos_slug` (`slug`),
    KEY `idx_combos_active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 8. combo_items — Produtos de cada combo
-- =============================================================================
CREATE TABLE IF NOT EXISTS `combo_items` (
    `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `combo_id`   INT UNSIGNED  NOT NULL,
    `product_id` INT UNSIGNED  NOT NULL,
    `quantity`   INT UNSIGNED  NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_combo_items_combo_product` (`combo_id`, `product_id`),
    KEY `idx_combo_items_product_id` (`product_id`),
    CONSTRAINT `fk_combo_items_combo`   FOREIGN KEY (`combo_id`)   REFERENCES `combos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_combo_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 9. coupons — Cupons de desconto
-- =============================================================================
CREATE TABLE IF NOT EXISTS `coupons` (
    `id`                 INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `code`               VARCHAR(50)    NOT NULL,
    `type`               ENUM('percent','fixed') NOT NULL,
    `value`              DECIMAL(10,2)  NOT NULL,
    `min_order`          DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    `max_uses`           INT UNSIGNED   DEFAULT NULL,
    `max_uses_per_user`  INT UNSIGNED   NOT NULL DEFAULT 1,
    `used_count`         INT UNSIGNED   NOT NULL DEFAULT 0,
    `expires_at`         DATETIME       DEFAULT NULL,
    `active`             TINYINT(1)     NOT NULL DEFAULT 1,
    `created_at`         DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`         DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_coupons_code` (`code`),
    KEY `idx_coupons_active` (`active`),
    KEY `idx_coupons_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 10. orders — Pedidos realizados
-- =============================================================================
CREATE TABLE IF NOT EXISTS `orders` (
    `id`               INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `user_id`          INT UNSIGNED   DEFAULT NULL,
    `customer_name`    VARCHAR(150)   NOT NULL,
    `customer_email`   VARCHAR(255)   NOT NULL,
    `customer_phone`   VARCHAR(20)    DEFAULT NULL,
    `status`           ENUM('aguardando_pagamento','pago','em_preparacao','enviado','entregue','cancelado') NOT NULL DEFAULT 'aguardando_pagamento',
    `subtotal`         DECIMAL(10,2)  NOT NULL,
    `discount`         DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    `total`            DECIMAL(10,2)  NOT NULL,
    `coupon_id`        INT UNSIGNED   DEFAULT NULL,
    `coupon_code`      VARCHAR(50)    DEFAULT NULL,
    `payment_method`   ENUM('pix','cartao','boleto') DEFAULT NULL,
    `payment_id`       VARCHAR(255)   DEFAULT NULL,
    `payment_status`   VARCHAR(50)    DEFAULT NULL,
    `tracking_code`    VARCHAR(100)   DEFAULT NULL,
    `notes`            TEXT           DEFAULT NULL,
    `created_at`       DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_orders_payment_id` (`payment_id`),
    KEY `idx_orders_user_id` (`user_id`),
    KEY `idx_orders_status` (`status`),
    KEY `idx_orders_created_at` (`created_at`),
    KEY `idx_orders_customer_email` (`customer_email`),
    KEY `idx_orders_coupon_id` (`coupon_id`),
    CONSTRAINT `fk_orders_user`   FOREIGN KEY (`user_id`)   REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_orders_coupon` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 11. order_items — Itens de cada pedido
-- =============================================================================
CREATE TABLE IF NOT EXISTS `order_items` (
    `id`           INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `order_id`     INT UNSIGNED   NOT NULL,
    `product_id`   INT UNSIGNED   DEFAULT NULL,
    `combo_id`     INT UNSIGNED   DEFAULT NULL,
    `product_name` VARCHAR(255)   NOT NULL,
    `price`        DECIMAL(10,2)  NOT NULL,
    `quantity`     INT UNSIGNED   NOT NULL,
    `subtotal`     DECIMAL(10,2)  NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_order_items_order_id` (`order_id`),
    KEY `idx_order_items_product_id` (`product_id`),
    CONSTRAINT `fk_order_items_order`   FOREIGN KEY (`order_id`)   REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_order_items_combo`   FOREIGN KEY (`combo_id`)   REFERENCES `combos` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 12. order_status_history — Histórico de status
-- =============================================================================
CREATE TABLE IF NOT EXISTS `order_status_history` (
    `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `order_id`   INT UNSIGNED  NOT NULL,
    `status`     VARCHAR(50)   NOT NULL,
    `note`       TEXT          DEFAULT NULL,
    `created_by` INT UNSIGNED  DEFAULT NULL,
    `created_at` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_order_status_history_order_id` (`order_id`),
    KEY `idx_order_status_history_created_by` (`created_by`),
    CONSTRAINT `fk_order_status_history_order`      FOREIGN KEY (`order_id`)   REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_order_status_history_admin_user` FOREIGN KEY (`created_by`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 13. cart_sessions — Carrinhos abandonados
-- =============================================================================
CREATE TABLE IF NOT EXISTS `cart_sessions` (
    `id`           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `session_id`   VARCHAR(128)  NOT NULL,
    `user_id`      INT UNSIGNED  DEFAULT NULL,
    `user_email`   VARCHAR(255)  DEFAULT NULL,
    `items`        JSON          NOT NULL,
    `coupon_code`  VARCHAR(50)   DEFAULT NULL,
    `created_at`   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `notified_at`  DATETIME      DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_cart_sessions_session_id` (`session_id`),
    KEY `idx_cart_sessions_user_id` (`user_id`),
    KEY `idx_cart_sessions_user_email` (`user_email`),
    KEY `idx_cart_sessions_updated_at` (`updated_at`),
    KEY `idx_cart_sessions_notified_at` (`notified_at`),
    CONSTRAINT `fk_cart_sessions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 14. reviews — Avaliações de produtos
-- =============================================================================
CREATE TABLE IF NOT EXISTS `reviews` (
    `id`               INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `product_id`       INT UNSIGNED  NOT NULL,
    `user_id`          INT UNSIGNED  DEFAULT NULL,
    `order_id`         INT UNSIGNED  DEFAULT NULL,
    `rating`           TINYINT UNSIGNED NOT NULL,
    `comment`          TEXT          DEFAULT NULL,
    `photo`            VARCHAR(255)  DEFAULT NULL,
    `status`           ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    `rejection_reason` TEXT          DEFAULT NULL,
    `review_token`     VARCHAR(64)   DEFAULT NULL,
    `created_at`       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_reviews_token` (`review_token`),
    KEY `idx_reviews_product_id` (`product_id`),
    KEY `idx_reviews_user_id` (`user_id`),
    KEY `idx_reviews_order_id` (`order_id`),
    KEY `idx_reviews_status` (`status`),
    CONSTRAINT `fk_reviews_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON UPDATE CASCADE,
    CONSTRAINT `fk_reviews_user`    FOREIGN KEY (`user_id`)    REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_reviews_order`   FOREIGN KEY (`order_id`)   REFERENCES `orders` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `chk_reviews_rating` CHECK (`rating` BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 15. notifications — Central de notificações admin
-- =============================================================================
CREATE TABLE IF NOT EXISTS `notifications` (
    `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `type`       VARCHAR(50)   NOT NULL,
    `title`      VARCHAR(255)  NOT NULL,
    `message`    TEXT          DEFAULT NULL,
    `read`       TINYINT(1)    NOT NULL DEFAULT 0,
    `read_at`    DATETIME      DEFAULT NULL,
    `created_at` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_notifications_read` (`read`),
    KEY `idx_notifications_type` (`type`),
    KEY `idx_notifications_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 16. scripts_config — Scripts de marketing
-- =============================================================================
CREATE TABLE IF NOT EXISTS `scripts_config` (
    `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `key`        VARCHAR(50)   NOT NULL,
    `value`      TEXT          DEFAULT NULL,
    `active`     TINYINT(1)    NOT NULL DEFAULT 1,
    `updated_at` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_scripts_config_key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 17. settings — Configurações gerais
-- =============================================================================
CREATE TABLE IF NOT EXISTS `settings` (
    `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `key`        VARCHAR(100)  NOT NULL,
    `value`      TEXT          DEFAULT NULL,
    `updated_at` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_settings_key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 18. audit_logs — Logs de auditoria
-- =============================================================================
CREATE TABLE IF NOT EXISTS `audit_logs` (
    `id`            BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `admin_user_id` INT UNSIGNED     DEFAULT NULL,
    `action`        VARCHAR(100)     NOT NULL,
    `entity`        VARCHAR(100)     NOT NULL,
    `entity_id`     INT UNSIGNED     DEFAULT NULL,
    `old_value`     JSON             DEFAULT NULL,
    `new_value`     JSON             DEFAULT NULL,
    `ip`            VARCHAR(45)      DEFAULT NULL,
    `created_at`    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_audit_logs_admin_user_id` (`admin_user_id`),
    KEY `idx_audit_logs_entity` (`entity`),
    KEY `idx_audit_logs_entity_id` (`entity_id`),
    KEY `idx_audit_logs_action` (`action`),
    KEY `idx_audit_logs_created_at` (`created_at`),
    CONSTRAINT `fk_audit_logs_admin_user` FOREIGN KEY (`admin_user_id`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 19. stock_movements — Movimentações de estoque
-- =============================================================================
CREATE TABLE IF NOT EXISTS `stock_movements` (
    `id`           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `product_id`   INT UNSIGNED  NOT NULL,
    `type`         ENUM('in','out','adjust') NOT NULL,
    `quantity`     INT           NOT NULL,
    `reason`       VARCHAR(255)  DEFAULT NULL,
    `reference_id` INT UNSIGNED  DEFAULT NULL,
    `created_by`   INT UNSIGNED  DEFAULT NULL,
    `created_at`   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_stock_movements_product_id` (`product_id`),
    KEY `idx_stock_movements_type` (`type`),
    KEY `idx_stock_movements_created_at` (`created_at`),
    KEY `idx_stock_movements_created_by` (`created_by`),
    CONSTRAINT `fk_stock_movements_product`    FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON UPDATE CASCADE,
    CONSTRAINT `fk_stock_movements_admin_user` FOREIGN KEY (`created_by`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 20. email_queue — Fila de e-mails
-- =============================================================================
CREATE TABLE IF NOT EXISTS `email_queue` (
    `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `to_email`      VARCHAR(255)  NOT NULL,
    `to_name`       VARCHAR(150)  DEFAULT NULL,
    `template`      VARCHAR(100)  NOT NULL,
    `payload`       JSON          DEFAULT NULL,
    `status`        ENUM('pending','sent','failed') NOT NULL DEFAULT 'pending',
    `attempts`      TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `error_message` TEXT          DEFAULT NULL,
    `scheduled_at`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `sent_at`       DATETIME      DEFAULT NULL,
    `created_at`    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_email_queue_status` (`status`),
    KEY `idx_email_queue_scheduled_at` (`scheduled_at`),
    KEY `idx_email_queue_template` (`template`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 21. utm_links — Links UTM gerados
-- =============================================================================
CREATE TABLE IF NOT EXISTS `utm_links` (
    `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `base_url`   VARCHAR(500)  NOT NULL,
    `source`     VARCHAR(100)  DEFAULT NULL,
    `medium`     VARCHAR(100)  DEFAULT NULL,
    `campaign`   VARCHAR(100)  DEFAULT NULL,
    `content`    VARCHAR(100)  DEFAULT NULL,
    `full_url`   VARCHAR(1000) NOT NULL,
    `created_by` INT UNSIGNED  DEFAULT NULL,
    `created_at` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_utm_links_created_by` (`created_by`),
    KEY `idx_utm_links_created_at` (`created_at`),
    CONSTRAINT `fk_utm_links_admin_user` FOREIGN KEY (`created_by`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 22. funnel_events — Eventos do funil de conversão (seção 4.1 PRD)
-- =============================================================================
CREATE TABLE IF NOT EXISTS `funnel_events` (
    `id`         BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `session_id` VARCHAR(128)     DEFAULT NULL,
    `user_id`    INT UNSIGNED     DEFAULT NULL,
    `step`       ENUM('visit','product_view','add_to_cart','checkout_start','purchase') NOT NULL,
    `product_id` INT UNSIGNED     DEFAULT NULL,
    `order_id`   INT UNSIGNED     DEFAULT NULL,
    `ip`         VARCHAR(45)      DEFAULT NULL,
    `user_agent` VARCHAR(500)     DEFAULT NULL,
    `created_at` DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_funnel_events_session_id` (`session_id`),
    KEY `idx_funnel_events_user_id` (`user_id`),
    KEY `idx_funnel_events_step` (`step`),
    KEY `idx_funnel_events_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 23. login_attempts — Controle de tentativas de login (seção 5.1 PRD)
-- =============================================================================
CREATE TABLE IF NOT EXISTS `login_attempts` (
    `id`           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `email`        VARCHAR(255)  NOT NULL,
    `ip`           VARCHAR(45)   NOT NULL,
    `attempted_at` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_login_attempts_email` (`email`),
    KEY `idx_login_attempts_ip` (`ip`),
    KEY `idx_login_attempts_attempted_at` (`attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- Dados iniciais obrigatórios
-- =============================================================================

-- Configurações de scripts de marketing (seção 4.9 PRD)
INSERT IGNORE INTO `scripts_config` (`key`, `value`, `active`) VALUES
    ('ga_id',         NULL, 0),
    ('gtm_id',        NULL, 0),
    ('pixel_id',      NULL, 0),
    ('custom_head',   NULL, 0),
    ('custom_body',   NULL, 0);

-- Configurações gerais da loja (seção 4.11 PRD)
INSERT IGNORE INTO `settings` (`key`, `value`) VALUES
    ('store_name',            'Maia Suplementos'),
    ('store_logo',            NULL),
    ('store_favicon',         NULL),
    ('store_color_primary',   '#e60000'),
    ('store_whatsapp',        NULL),
    ('store_email',           NULL),
    ('store_address',         NULL),
    ('free_shipping_above',   '0.00'),
    ('stock_alert_min',       '5'),
    ('privacy_policy',        NULL),
    ('terms_of_use',          NULL);

-- =============================================================================
-- 24. pages — Páginas institucionais
-- =============================================================================
CREATE TABLE IF NOT EXISTS `pages` (
    `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `slug`       VARCHAR(120)  NOT NULL,
    `title`      VARCHAR(255)  NOT NULL,
    `content`    LONGTEXT      DEFAULT NULL,
    `active`     TINYINT(1)    NOT NULL DEFAULT 1,
    `created_at` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_pages_slug` (`slug`),
    KEY `idx_pages_active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `pages` (`slug`, `title`, `content`, `active`) VALUES
('politica-de-privacidade', 'Política de Privacidade', '<p>Preencha a política de privacidade.</p>', 1),
('termos-de-uso',           'Termos de Uso',           '<p>Preencha os termos de uso.</p>',           1);

-- =============================================================================
-- 25. stock_notifications — Avisos de reposição de estoque para clientes
-- =============================================================================
CREATE TABLE IF NOT EXISTS `stock_notifications` (
    `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `product_id`  INT UNSIGNED  NOT NULL,
    `user_id`     INT UNSIGNED  DEFAULT NULL,
    `email`       VARCHAR(255)  NOT NULL,
    `notified_at` DATETIME      DEFAULT NULL,
    `created_at`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_stock_notifications_product_id` (`product_id`),
    KEY `idx_stock_notifications_notified_at` (`notified_at`),
    CONSTRAINT `fk_stock_notifications_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_stock_notifications_user`    FOREIGN KEY (`user_id`)    REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- Admin inicial deve ser criado fora do schema com senha forte.

SET foreign_key_checks = 1;
