-- Tabela de produtos relacionados (seleção manual no admin)
-- Execute uma vez no phpMyAdmin

CREATE TABLE IF NOT EXISTS `product_related` (
  `product_id` INT UNSIGNED NOT NULL,
  `related_id` INT UNSIGNED NOT NULL,
  `sort_order` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`product_id`, `related_id`),
  KEY `idx_related_id` (`related_id`),
  CONSTRAINT `fk_pr_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pr_related` FOREIGN KEY (`related_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
