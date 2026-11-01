-- Skema dasar Warkop Bahagia tanpa baris data atau kredensial.
-- Impor ke database kosong setelah membuat database.

--
-- ------------------------------------------------------

/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;

--
-- Table structure for table `data_products`
--

DROP TABLE IF EXISTS `data_products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `data_products` (
  `id_data` varchar(11) NOT NULL,
  `aktivitas` varchar(50) NOT NULL,
  `id_produk` varchar(11) NOT NULL,
  `nama_produk` varchar(200) NOT NULL,
  `nama_kasir` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_data`),
  KEY `idx_data_products_id_produk` (`id_produk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id_notif` int(11) NOT NULL AUTO_INCREMENT,
  `id_order` varchar(11) NOT NULL,
  `id_meja` varchar(11) NOT NULL,
  `nama_pelanggan` varchar(100) NOT NULL,
  `total` decimal(12,2) DEFAULT 0.00,
  `status` enum('unread','read') DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_notif`),
  KEY `fk_notif_order` (`id_order`),
  KEY `fk_notif_meja` (`id_meja`),
  CONSTRAINT `fk_notif_meja` FOREIGN KEY (`id_meja`) REFERENCES `tables_meja` (`id_meja`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_notif_order` FOREIGN KEY (`id_order`) REFERENCES `orders` (`id_order`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_items` (
  `id_items` varchar(11) NOT NULL,
  `id_order` varchar(11) NOT NULL,
  `id_produk` varchar(11) NOT NULL,
  `nama` varchar(200) NOT NULL,
  `kategori` varchar(100) NOT NULL,
  `opsi` varchar(100) DEFAULT NULL,
  `catatan` varchar(500) DEFAULT NULL,
  `suhu` varchar(10) DEFAULT NULL,
  `harga` decimal(12,2) DEFAULT 0.00,
  `qty` int(11) DEFAULT 0,
  `subtotal` decimal(12,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_items`),
  KEY `fk_orderitems_order` (`id_order`),
  KEY `fk_orderitems_produk` (`id_produk`),
  CONSTRAINT `fk_orderitems_order` FOREIGN KEY (`id_order`) REFERENCES `orders` (`id_order`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_orderitems_produk` FOREIGN KEY (`id_produk`) REFERENCES `products` (`id_produk`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `id_order` varchar(11) NOT NULL,
  `id_meja` varchar(11) NOT NULL,
  `nama_pelanggan` varchar(100) NOT NULL,
  `total` decimal(12,2) DEFAULT 0.00,
  `status` enum('menunggu_pembayaran','lunas','dibatalkan') DEFAULT 'menunggu_pembayaran',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `metode_pembayaran` enum('cash','qris') DEFAULT 'cash',
  `token` char(16) NOT NULL,
  PRIMARY KEY (`id_order`),
  UNIQUE KEY `uq_orders_token` (`token`),
  KEY `fk_orders_meja` (`id_meja`),
  KEY `idx_orders_status_created` (`status`, `created_at`),
  CONSTRAINT `fk_orders_meja` FOREIGN KEY (`id_meja`) REFERENCES `tables_meja` (`id_meja`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments` (
  `id_pay` varchar(11) NOT NULL,
  `id_order` varchar(11) NOT NULL,
  `id_user` varchar(11) NOT NULL,
  `nama_kasir` varchar(200) NOT NULL,
  `metode_pembayaran` enum('cash','qris') DEFAULT 'cash',
  `total_order` decimal(12,2) DEFAULT 0.00,
  `uang_dibayar` decimal(12,2) DEFAULT 0.00,
  `kembalian` decimal(12,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_pay`),
  UNIQUE KEY `uniq_payments_id_order` (`id_order`),
  KEY `fk_payment_user` (`id_user`),
  CONSTRAINT `fk_payment_order` FOREIGN KEY (`id_order`) REFERENCES `orders` (`id_order`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_payment_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id_produk` varchar(11) NOT NULL,
  `nama` varchar(200) NOT NULL,
  `kategori` varchar(100) NOT NULL,
  `harga` decimal(12,2) DEFAULT 0.00,
  `stok` int(11) DEFAULT 0,
  `varian_suhu` tinyint(1) NOT NULL DEFAULT 0,
  `tambahan_harga_es` decimal(12,2) NOT NULL DEFAULT 0.00,
  `gambar` varchar(255) NOT NULL,
  `suhu_tetap` varchar(10) DEFAULT NULL,
  `punya_opsi` tinyint(1) NOT NULL DEFAULT 0,
  `label_opsi` varchar(40) DEFAULT NULL,
  `deskripsi` varchar(255) DEFAULT NULL,
  `aktif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_produk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `product_options`
--

DROP TABLE IF EXISTS `product_options`;
CREATE TABLE `product_options` (
  `id_opsi` varchar(11) NOT NULL,
  `id_produk` varchar(11) NOT NULL,
  `nama_opsi` varchar(100) NOT NULL,
  `tambahan_harga` decimal(12,2) NOT NULL DEFAULT 0.00,
  `gambar` varchar(255) DEFAULT NULL,
  `urutan` int(11) NOT NULL DEFAULT 0,
  `aktif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_opsi`),
  KEY `product_options_product` (`id_produk`),
  CONSTRAINT `fk_product_options_product` FOREIGN KEY (`id_produk`) REFERENCES `products` (`id_produk`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `id_setting` varchar(10) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `value` text DEFAULT NULL,
  PRIMARY KEY (`id_setting`),
  UNIQUE KEY `uniq_settings_nama` (`nama`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tables_meja`
--

DROP TABLE IF EXISTS `tables_meja`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tables_meja` (
  `id_meja` varchar(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  PRIMARY KEY (`id_meja`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id_user` varchar(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_kasir` varchar(200) NOT NULL,
  `role` enum('admin','kasir') DEFAULT 'kasir',
  `aktif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `uniq_users_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
