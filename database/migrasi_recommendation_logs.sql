CREATE TABLE IF NOT EXISTS `recommendation_logs` (
  `id_log` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `jam` TINYINT UNSIGNED NOT NULL,
  `source` VARCHAR(64) NOT NULL,
  `menu_ditampilkan` TEXT NOT NULL,
  `id_produk_dipilih` VARCHAR(11) DEFAULT NULL,
  PRIMARY KEY (`id_log`),
  KEY `idx_recommendation_logs_created_at` (`created_at`),
  KEY `idx_recommendation_logs_jam` (`jam`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
