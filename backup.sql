-- MySQL dump 10.13  Distrib 8.4.3, for Win64 (x86_64)
--
-- Host: localhost    Database: marketplace_locale
-- ------------------------------------------------------
-- Server version	8.4.3

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cart_items`
--

DROP TABLE IF EXISTS `cart_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cart_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `qty` int NOT NULL DEFAULT '1',
  `option_label` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unit_price` decimal(12,2) NOT NULL DEFAULT '0.00',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cart_items_user_id_product_id_option_label_unique` (`user_id`,`product_id`,`option_label`),
  KEY `cart_items_product_id_foreign` (`product_id`),
  CONSTRAINT `cart_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cart_items_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cart_items`
--

LOCK TABLES `cart_items` WRITE;
/*!40000 ALTER TABLE `cart_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `cart_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Package',
  `accent` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'teal',
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `count` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `parent_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_slug_unique` (`slug`),
  KEY `categories_parent_id_foreign` (`parent_id`),
  CONSTRAINT `categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Produits frais','frais','Carrot','teal','Légumes, céréales et denrées alimentaires fraîches de Parakou.',NULL,0,'2026-06-29 15:33:20','2026-06-29 15:33:20',NULL),(2,'Fruits & Légumes','fruits','Apple','amber','Fruits tropicaux et légumes locaux.',NULL,0,'2026-06-29 15:33:20','2026-06-29 15:33:20',NULL),(3,'Épices & Condiments','epices','Flame','plum','Épices, piments, gingembre et aromates du Bénin.',NULL,0,'2026-06-29 15:33:20','2026-06-29 15:33:20',NULL),(4,'Boissons','boissons','CupSoda','teal','Jus naturels, infusions et boissons artisanales.',NULL,0,'2026-06-29 15:33:20','2026-06-29 15:33:20',NULL),(5,'Mode & Tissus','tissus','Shirt','plum','Wax, bazin, pagnes et accessoires de mode africaine.',NULL,0,'2026-06-29 15:33:20','2026-06-29 15:33:20',NULL),(6,'Cosmétiques naturels','cosmetiques','Sparkles','plum','Karité, savons et soins naturels du terroir béninois.',NULL,0,'2026-06-29 15:33:20','2026-06-29 15:33:20',NULL),(7,'Tomates','tomates','Carrot','teal','Tomates fraîches de Parakou, du marché au panier.',NULL,0,'2026-06-29 15:33:20','2026-06-29 15:33:20',1),(8,'Légumes verts','legumes-verts','Leaf','teal','Gombo, aubergines, feuilles vertes fraîches.',NULL,0,'2026-06-29 15:33:20','2026-06-29 15:33:20',1),(9,'Céréales & Tubercules','cereales','Wheat','amber','Riz, maïs, igname, manioc et autres féculents locaux.',NULL,0,'2026-06-29 15:33:20','2026-06-29 15:33:20',1),(10,'Oignons & Ail','oignons','Carrot','teal','Oignons violets, ail et condiments frais.',NULL,0,'2026-06-29 15:33:20','2026-06-29 15:33:20',1),(11,'Bananes & Plantain','bananes','Apple','amber',NULL,NULL,0,'2026-06-29 15:33:20','2026-06-29 15:33:20',2),(12,'Mangues','mangues','Apple','amber',NULL,NULL,0,'2026-06-29 15:33:20','2026-06-29 15:33:20',2),(13,'Piment','piment','Flame','plum',NULL,NULL,0,'2026-06-29 15:33:20','2026-06-29 15:33:20',3),(14,'Gingembre','gingembre','Flame','plum',NULL,NULL,0,'2026-06-29 15:33:20','2026-06-29 15:33:20',3),(15,'Karité & Huiles','karite','Sparkles','plum',NULL,NULL,0,'2026-06-29 15:33:20','2026-06-29 15:33:20',6),(16,'Savons naturels','savons','Sparkles','plum',NULL,NULL,0,'2026-06-29 15:33:20','2026-06-29 15:33:20',6);
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `deliveries`
--

DROP TABLE IF EXISTS `deliveries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `deliveries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `courier_id` bigint unsigned DEFAULT NULL,
  `status` enum('pending','assigned','in_progress','otp_requested','delivered','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `distance_km` decimal(8,2) DEFAULT NULL,
  `accepted_at` timestamp NULL DEFAULT NULL,
  `otp_requested_at` timestamp NULL DEFAULT NULL,
  `otp_verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `deliveries_order_id_foreign` (`order_id`),
  KEY `deliveries_courier_id_foreign` (`courier_id`),
  CONSTRAINT `deliveries_courier_id_foreign` FOREIGN KEY (`courier_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `deliveries_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `deliveries`
--

LOCK TABLES `deliveries` WRITE;
/*!40000 ALTER TABLE `deliveries` DISABLE KEYS */;
/*!40000 ALTER TABLE `deliveries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dispute_evidences`
--

DROP TABLE IF EXISTS `dispute_evidences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dispute_evidences` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `dispute_id` bigint unsigned NOT NULL,
  `uploader_id` bigint unsigned NOT NULL,
  `uploader_role` enum('client','vendor','admin') COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` bigint unsigned DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `dispute_evidences_dispute_id_foreign` (`dispute_id`),
  KEY `dispute_evidences_uploader_id_foreign` (`uploader_id`),
  CONSTRAINT `dispute_evidences_dispute_id_foreign` FOREIGN KEY (`dispute_id`) REFERENCES `disputes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `dispute_evidences_uploader_id_foreign` FOREIGN KEY (`uploader_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dispute_evidences`
--

LOCK TABLES `dispute_evidences` WRITE;
/*!40000 ALTER TABLE `dispute_evidences` DISABLE KEYS */;
/*!40000 ALTER TABLE `dispute_evidences` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dispute_messages`
--

DROP TABLE IF EXISTS `dispute_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dispute_messages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `dispute_id` bigint unsigned NOT NULL,
  `sender_id` bigint unsigned NOT NULL,
  `sender_role` enum('client','vendor','admin') COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_internal` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `dispute_messages_dispute_id_foreign` (`dispute_id`),
  KEY `dispute_messages_sender_id_foreign` (`sender_id`),
  CONSTRAINT `dispute_messages_dispute_id_foreign` FOREIGN KEY (`dispute_id`) REFERENCES `disputes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `dispute_messages_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dispute_messages`
--

LOCK TABLES `dispute_messages` WRITE;
/*!40000 ALTER TABLE `dispute_messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `dispute_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `disputes`
--

DROP TABLE IF EXISTS `disputes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `disputes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `client_id` bigint unsigned NOT NULL,
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `priority` enum('Haute','Moyenne','Basse') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Moyenne',
  `status` enum('open','in_review','resolved','closed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `resolution_type` enum('full_refund','partial_refund','rejected','pending') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `refund_amount` decimal(12,2) DEFAULT NULL,
  `admin_note` text COLLATE utf8mb4_unicode_ci,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `last_activity_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `vendor_id` bigint unsigned DEFAULT NULL,
  `opened_by_id` bigint unsigned DEFAULT NULL,
  `opened_by_role` enum('client','vendor','courier') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'client',
  `courier_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `disputes_order_id_foreign` (`order_id`),
  KEY `disputes_client_id_foreign` (`client_id`),
  KEY `disputes_vendor_id_foreign` (`vendor_id`),
  KEY `disputes_opened_by_id_foreign` (`opened_by_id`),
  KEY `disputes_courier_id_foreign` (`courier_id`),
  CONSTRAINT `disputes_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `disputes_courier_id_foreign` FOREIGN KEY (`courier_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `disputes_opened_by_id_foreign` FOREIGN KEY (`opened_by_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `disputes_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `disputes_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `disputes`
--

LOCK TABLES `disputes` WRITE;
/*!40000 ALTER TABLE `disputes` DISABLE KEYS */;
/*!40000 ALTER TABLE `disputes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2024_01_01_100000_create_shops_table',1),(5,'2024_01_01_100001_create_categories_table',1),(6,'2024_01_01_100002_create_products_table',1),(7,'2024_01_01_100003_create_cart_items_table',1),(8,'2024_01_01_100004_create_orders_table',1),(9,'2024_01_01_100005_create_order_items_table',1),(10,'2024_01_01_100006_create_deliveries_table',1),(11,'2024_01_01_100007_create_payments_table',1),(12,'2024_01_01_100008_create_notifications_table',1),(13,'2024_01_01_100009_create_disputes_table',1),(14,'2026_06_11_125140_create_personal_access_tokens_table',1),(15,'2026_06_12_000001_add_security_fields_to_users_table',1),(16,'2026_06_12_000002_add_escrow_and_delivery_to_orders_table',1),(17,'2026_06_12_000003_add_parent_id_to_categories_table',1),(18,'2026_06_12_000004_create_dispute_messages_table',1),(19,'2026_06_12_000005_create_dispute_evidences_table',1),(20,'2026_06_12_000006_add_resolution_type_to_disputes_table',1),(21,'2026_06_12_000007_add_opened_by_and_courier_to_disputes_table',1),(22,'2026_06_19_000001_create_wallet_transactions_table',1),(23,'2026_06_19_000002_add_opening_hours_to_shops_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `data` json DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_user_id_foreign` (`user_id`),
  CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES (1,2,'welcome','Bienvenue sur BeniMarket !','Votre compte client est actif. Explorez les catégories pour trouver vos produits.',NULL,NULL,'2026-06-29 15:33:20','2026-06-29 15:33:20');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned DEFAULT NULL,
  `shop_id` bigint unsigned DEFAULT NULL,
  `product_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `option_label` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `qty` int NOT NULL,
  `total` decimal(12,2) NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_items_order_id_foreign` (`order_id`),
  KEY `order_items_product_id_foreign` (`product_id`),
  KEY `order_items_shop_id_foreign` (`shop_id`),
  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  CONSTRAINT `order_items_shop_id_foreign` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `reference` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `client_id` bigint unsigned NOT NULL,
  `status` enum('pending','confirmed','preparing','shipping','delivered','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `subtotal` decimal(12,2) NOT NULL DEFAULT '0.00',
  `service_fee` decimal(12,2) NOT NULL DEFAULT '0.00',
  `delivery_fee` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `delivery_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `delivery_phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `delivery_neighborhood` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `delivery_instructions` text COLLATE utf8mb4_unicode_ci,
  `delivery_coordinates` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `delivery_zone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `delivery_distance_km` decimal(8,2) DEFAULT NULL,
  `delivery_weight_kg` decimal(8,2) DEFAULT NULL,
  `items_count` int DEFAULT NULL,
  `delivery_fee_breakdown` json DEFAULT NULL,
  `otp` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_method` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'mobile_money',
  `payment_operator` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_status` enum('pending','paid','failed','refunded') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `escrow_status` enum('held','released','refunded','disputed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'held',
  `funds_released_at` timestamp NULL DEFAULT NULL,
  `received_at` timestamp NULL DEFAULT NULL,
  `payment_reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orders_reference_unique` (`reference`),
  KEY `orders_client_id_foreign` (`client_id`),
  CONSTRAINT `orders_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `operator` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `currency` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'XOF',
  `fedapay_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','approved','declined','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payments_reference_unique` (`reference`),
  KEY `payments_order_id_foreign` (`order_id`),
  CONSTRAINT `payments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `shop_id` bigint unsigned NOT NULL,
  `category_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(12,2) NOT NULL DEFAULT '0.00',
  `stock` int NOT NULL DEFAULT '0',
  `images` json DEFAULT NULL,
  `tags` json DEFAULT NULL,
  `options` json DEFAULT NULL,
  `status` enum('active','inactive','out_of_stock') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `rating` decimal(3,2) NOT NULL DEFAULT '0.00',
  `reviews_count` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `products_shop_id_foreign` (`shop_id`),
  KEY `products_category_id_foreign` (`category_id`),
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `products_shop_id_foreign` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,1,7,'Tomates fraîches (tas)','Tomates mûries au soleil, cueillies le matin. Parfaites pour vos sauces et salades.',500.00,30,'[\"/tomates.webp\"]','[\"Bio\", \"Récolte du jour\"]',NULL,'active',4.80,47,'2026-06-29 15:33:20','2026-06-29 15:33:20'),(2,1,7,'Tomates cerise','Tomates cerises sucrées, idéales pour les salades et apéritifs.',750.00,12,'[\"/tomates.webp\"]','[\"Premium\"]',NULL,'active',4.50,18,'2026-06-29 15:33:20','2026-06-29 15:33:20'),(3,1,10,'Oignon Violet','Oignons violets de Parakou, très parfumés pour vos assaisonnements.',400.00,60,'[\"/oignon violet.jpg\"]','[\"Légumes\"]',NULL,'active',4.60,35,'2026-06-29 15:33:20','2026-06-29 15:33:20'),(4,1,8,'Gombo frais','Gombo frais récolté ce matin. Idéal pour la sauce gombo.',350.00,25,'[]','[\"Local\", \"Bio\"]',NULL,'active',4.30,22,'2026-06-29 15:33:20','2026-06-29 15:33:20'),(5,1,9,'Riz local (bol)','Riz local produit au Bénin, savoureux et parfait pour tous vos plats.',600.00,120,'[\"/riz.jpg\"]','[\"Céréales\", \"Local\"]',NULL,'active',4.40,63,'2026-06-29 15:33:20','2026-06-29 15:33:20'),(6,1,4,'Jus de Bissap','Jus de bissap fait maison, rafraîchissant et riche en antioxydants.',500.00,20,'[\"/photo_jus_bissap_cuisinovores-500x375.webp\"]','[\"Artisanal\", \"Naturel\"]',NULL,'active',4.70,31,'2026-06-29 15:33:20','2026-06-29 15:33:20'),(7,2,15,'Beurre de karité (100g)','Beurre de karité pur et naturel du Bénin. Hydratant pour la peau et les cheveux.',1000.00,30,'[\"/beurre de karité.jpg\"]','[\"Bio\", \"Naturel\"]',NULL,'active',4.90,84,'2026-06-29 15:33:20','2026-06-29 15:33:20'),(8,2,15,'Beurre de karité (250g)','Grand format pour usage quotidien. Karité 100% naturel et non raffiné.',2500.00,15,'[\"/beurre de karité.jpg\"]','[\"Bio\", \"Premium\"]',NULL,'active',4.90,52,'2026-06-29 15:33:20','2026-06-29 15:33:20'),(9,2,16,'Savon noir artisanal','Savon noir africain traditionnel pour nettoyage en profondeur de la peau.',800.00,40,'[\"/savoir noir.webp\"]','[\"Naturel\", \"Traditionnel\"]',NULL,'active',4.60,29,'2026-06-29 15:33:20','2026-06-29 15:33:20'),(10,2,5,'Tissu Wax 6 yards','Wax 100% coton, motifs africains authentiques. Lavable en machine.',9500.00,8,'[\"/tissus-wax.jpg\"]','[\"Premium\"]',NULL,'active',4.70,14,'2026-06-29 15:33:20','2026-06-29 15:33:20'),(11,2,7,'Tomates confites au piment','Tomates confites maison relevées au piment. Parfaites pour accompagner vos plats.',1200.00,10,'[\"/tomates.webp\"]','[\"Artisanal\", \"Épicé\"]',NULL,'active',4.80,19,'2026-06-29 15:33:20','2026-06-29 15:33:20'),(12,3,13,'Piment rouge séché','Piment rouge séché, très fort. Indispensable pour relever vos sauces.',250.00,80,'[\"/epicette-piment-rouge-seche-1.jpg\"]','[\"Épices\", \"Séché\"]',NULL,'active',4.50,66,'2026-06-29 15:33:20','2026-06-29 15:33:20'),(13,3,13,'Piment oiseau frais','Petits piments oiseaux très puissants, cultivés à Parakou.',300.00,45,'[]','[\"Épices\", \"Frais\"]',NULL,'active',4.40,38,'2026-06-29 15:33:20','2026-06-29 15:33:20'),(14,3,14,'Gingembre frais','Gingembre frais de Parakou, idéal pour tisanes, marinades et plats épicés.',300.00,50,'[\"/gingembre.jpg\"]','[\"Bio\", \"Épices\"]',NULL,'active',4.70,44,'2026-06-29 15:33:20','2026-06-29 15:33:20'),(15,3,14,'Poudre de gingembre','Gingembre séché et moulu finement. Pratique pour vos recettes.',450.00,35,'[\"/gingembre.jpg\"]','[\"Épices\", \"Séché\"]',NULL,'active',4.60,27,'2026-06-29 15:33:20','2026-06-29 15:33:20'),(16,3,7,'Tomates fraîches (panier)','Panier de tomates fraîches, direct du jardin. Environ 2 kg.',1800.00,15,'[\"/tomates.webp\"]','[\"Local\"]',NULL,'active',4.60,22,'2026-06-29 15:33:20','2026-06-29 15:33:20'),(17,3,10,'Ail local','Ail local du Bénin, petites gousses très parfumées.',350.00,40,'[]','[\"Local\", \"Épices\"]',NULL,'active',4.30,17,'2026-06-29 15:33:20','2026-06-29 15:33:20'),(18,4,7,'Tomates bio (tas)','Tomates certifiées sans pesticides, cultivées dans notre jardin bio à Albarika.',600.00,20,'[\"/tomates.webp\"]','[\"Bio\", \"Sans pesticides\"]',NULL,'active',4.90,56,'2026-06-29 15:33:20','2026-06-29 15:33:20'),(19,4,7,'Tomates bio (sac 5 kg)','Sac de 5 kg de tomates bio. Idéal pour familles et restaurateurs.',2800.00,8,'[\"/tomates.webp\"]','[\"Bio\", \"Gros volume\"]',NULL,'active',4.80,21,'2026-06-29 15:33:20','2026-06-29 15:33:20'),(20,4,8,'Aubergine locale','Aubergines locales bio, parfaites pour les sauces et ragoûts.',300.00,35,'[]','[\"Bio\", \"Local\"]',NULL,'active',4.40,19,'2026-06-29 15:33:20','2026-06-29 15:33:20'),(21,4,8,'Feuilles de moringa','Feuilles de moringa fraîches, riches en nutriments et antioxydants.',200.00,50,'[]','[\"Bio\", \"Santé\"]',NULL,'active',4.70,33,'2026-06-29 15:33:20','2026-06-29 15:33:20'),(22,4,9,'Igname blanche','Igname blanche de bonne qualité. Idéale pour le pilé et les ragoûts.',800.00,25,'[]','[\"Local\", \"Tubercule\"]',NULL,'active',4.50,28,'2026-06-29 15:33:20','2026-06-29 15:33:20'),(23,4,11,'Bananes plantain mûres','Bananes plantain bien mûres, idéales pour alloco et accompagnements.',450.00,18,'[\"photo-1603833665858-e61d17a86224\"]','[\"Local\"]',NULL,'active',4.60,41,'2026-06-29 15:33:20','2026-06-29 15:33:20'),(24,4,12,'Mangues kent','Mangues kent juteuses et sucrées. En saison de mai à juillet.',350.00,30,'[]','[\"Local\", \"Saison\"]',NULL,'active',4.80,24,'2026-06-29 15:33:20','2026-06-29 15:33:20'),(25,4,4,'Jus de gingembre citron','Jus frais de gingembre et citron, tonique et rafraîchissant.',600.00,15,'[]','[\"Artisanal\", \"Santé\"]',NULL,'active',4.50,16,'2026-06-29 15:33:20','2026-06-29 15:33:20');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shops`
--

DROP TABLE IF EXISTS `shops`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `shops` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Parakou',
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `opening_hours` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cover` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','active','rejected','suspended') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `rejection_reason` text COLLATE utf8mb4_unicode_ci,
  `documents_submitted` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `shops_slug_unique` (`slug`),
  KEY `shops_vendor_id_foreign` (`vendor_id`),
  CONSTRAINT `shops_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shops`
--

LOCK TABLES `shops` WRITE;
/*!40000 ALTER TABLE `shops` DISABLE KEYS */;
INSERT INTO `shops` VALUES (1,3,'Saveurs de Mama Chantal','saveurs-mama-chantal','Produits frais du marché de Parakou, livrés chaque matin. Légumes, tomates, céréales.','Parakou','Stand N°12, Allée A, Marché Central',NULL,'+22997111111',NULL,NULL,'active',NULL,1,'2026-06-29 15:33:20','2026-06-29 15:33:20'),(2,4,'Karité Nature','karite-nature','Cosmétiques naturels, beurre de karité, savons et produits du terroir béninois.','Parakou','Stand N°05, Zone Artisanale, Parakou',NULL,'+22997222222',NULL,NULL,'active',NULL,1,'2026-06-29 15:33:20','2026-06-29 15:33:20'),(3,5,'Chez Alhaji Koudjo','chez-alhaji-koudjo','Épices rares, piments forts, gingembre et aromates directement des producteurs.','Parakou','Marché Arzèkè, Allée des Épices',NULL,'+22997333333',NULL,NULL,'active',NULL,1,'2026-06-29 15:33:20','2026-06-29 15:33:20'),(4,6,'Jardin Bio de Mariam','jardin-bio-mariam','Légumes bio cultivés sans pesticides à 5 km de Parakou. Tomates, gombo, aubergines.','Parakou','Quartier Albarika, Rue des Maraîchers',NULL,'+22997444444',NULL,NULL,'active',NULL,1,'2026-06-29 15:33:20','2026-06-29 15:33:20'),(5,7,'Délices du Nord','delices-du-nord','Spécialités culinaires et produits de saison du nord Bénin.','Parakou',NULL,NULL,NULL,NULL,NULL,'pending',NULL,1,'2026-06-29 15:33:20','2026-06-29 15:33:20');
/*!40000 ALTER TABLE `shops` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('client','vendor','courier','admin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'client',
  `status` enum('pending','actif','banned') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `must_change_password` tinyint(1) NOT NULL DEFAULT '0',
  `reset_token` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reset_token_expires_at` timestamp NULL DEFAULT NULL,
  `zone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vehicle_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `plate_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_phone_unique` (`phone`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Admin BeniMarket','admin@guema.bj',NULL,NULL,'$2y$12$kdIjFG5hSwG.rnprczyw1uw1qUDfJs5P6ZkRqfi4sGcQs2w2lEKFu','admin','actif',NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-29 15:33:16','2026-06-29 15:33:16'),(2,'Aïcha Dossou','client@guema.bj',NULL,NULL,'$2y$12$wnS6oJcO1Z.K0lEljNyI9.2p532.S4nkRUHplZasp4Rk781AGhAgK','client','actif',NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-29 15:33:16','2026-06-29 15:33:16'),(3,'Mama Chantal',NULL,'+22997111111',NULL,'$2y$12$2RHdgQE5yWdEaaOY6C359OnEKaEhGP79Pd3FHNgBkQc3K.eFVqYXK','vendor','actif',NULL,0,NULL,NULL,'Parakou',NULL,NULL,NULL,'2026-06-29 15:33:17','2026-06-29 15:33:17'),(4,'Tantie Reine',NULL,'+22997222222',NULL,'$2y$12$6adyFJJKN8uDqmBbS6vWu.b8uPOkw.XtAgekOpqLSR1h66aovJEVe','vendor','actif',NULL,0,NULL,NULL,'Parakou',NULL,NULL,NULL,'2026-06-29 15:33:17','2026-06-29 15:33:17'),(5,'Alhaji Koudjo',NULL,'+22997333333',NULL,'$2y$12$6bjReuYTy3NER4wyxWivU.AnH6f7iCclhlc/AoaTjtxL/jWNgx0xq','vendor','actif',NULL,0,NULL,NULL,'Parakou',NULL,NULL,NULL,'2026-06-29 15:33:18','2026-06-29 15:33:18'),(6,'Mariam Bio',NULL,'+22997444444',NULL,'$2y$12$VCEcHwRvm7qxI/jQx6IcHucxBU688qz4BNt2EcZpsJPJiekdqW8Z.','vendor','actif',NULL,0,NULL,NULL,'Parakou',NULL,NULL,NULL,'2026-06-29 15:33:19','2026-06-29 15:33:19'),(7,'Délices du Nord SARL',NULL,'+22997555555',NULL,'$2y$12$pZ1BKXL3PAu9bHCs0jAUBe/ex.7l.vMdQc9tTzSEJarRfQB2oBf9G','vendor','pending',NULL,0,NULL,NULL,'Parakou',NULL,NULL,NULL,'2026-06-29 15:33:19','2026-06-29 15:33:19'),(8,'Moussa Tchabi',NULL,'+22997666666',NULL,'$2y$12$igFG0e2VmGYneX6KtK4PpuYsKVk6CGOousmKcG3oCZJxpMxr53Gxe','courier','actif',NULL,0,NULL,NULL,'Parakou','Moto Yamaha','RB 1234 X',NULL,'2026-06-29 15:33:20','2026-06-29 15:33:20');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wallet_transactions`
--

DROP TABLE IF EXISTS `wallet_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wallet_transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `order_id` bigint unsigned DEFAULT NULL,
  `type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'credit',
  `reason` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'order_payout',
  `amount` decimal(12,2) NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `wallet_transactions_order_id_user_id_reason_unique` (`order_id`,`user_id`,`reason`),
  KEY `wallet_transactions_user_id_created_at_index` (`user_id`,`created_at`),
  CONSTRAINT `wallet_transactions_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `wallet_transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wallet_transactions`
--

LOCK TABLES `wallet_transactions` WRITE;
/*!40000 ALTER TABLE `wallet_transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `wallet_transactions` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-01 14:37:55
