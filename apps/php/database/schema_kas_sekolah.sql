-- MySQL dump 10.16  Distrib 10.1.34-MariaDB, for Win32 (AMD64)
--
-- Host: localhost    Database: kas-sekolah
-- ------------------------------------------------------
-- Server version	11.4.2-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `academic_years`
--

DROP TABLE IF EXISTS `academic_years`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `academic_years` (
  `id` varchar(191) NOT NULL,
  `school_id` varchar(191) NOT NULL,
  `year` varchar(191) NOT NULL,
  `is_current` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime(3) NOT NULL DEFAULT current_timestamp(3),
  `updated_at` datetime(3) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `academic_years_school_id_fkey` (`school_id`),
  CONSTRAINT `academic_years_school_id_fkey` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `academic_years`
--

LOCK TABLES `academic_years` WRITE;
/*!40000 ALTER TABLE `academic_years` DISABLE KEYS */;
INSERT INTO `academic_years` VALUES ('a2afedb8-ca14-4405-b09d-234abff708b2','a2afedb8-b5cb-4e99-9db8-4d0fb0aad961','2026/2027',1,'2026-09-07 07:08:32.000','2026-09-07 07:08:32.000');
/*!40000 ALTER TABLE `academic_years` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit_logs` (
  `id` varchar(191) NOT NULL,
  `user_id` varchar(191) NOT NULL,
  `action` varchar(191) NOT NULL,
  `resource` varchar(191) NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `ip_address` varchar(191) DEFAULT NULL,
  `created_at` datetime(3) NOT NULL DEFAULT current_timestamp(3),
  PRIMARY KEY (`id`),
  KEY `audit_logs_user_id_fkey` (`user_id`),
  CONSTRAINT `audit_logs_user_id_fkey` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cash_accounts`
--

DROP TABLE IF EXISTS `cash_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cash_accounts` (
  `id` varchar(191) NOT NULL,
  `class_id` varchar(191) NOT NULL,
  `name` varchar(191) NOT NULL,
  `current_balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(191) NOT NULL DEFAULT 'IDR',
  `created_at` datetime(3) NOT NULL DEFAULT current_timestamp(3),
  `updated_at` datetime(3) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `cash_accounts_class_id_fkey` (`class_id`),
  CONSTRAINT `cash_accounts_class_id_fkey` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cash_accounts`
--

LOCK TABLES `cash_accounts` WRITE;
/*!40000 ALTER TABLE `cash_accounts` DISABLE KEYS */;
INSERT INTO `cash_accounts` VALUES ('a2afedb8-cf56-4e3d-b3d6-2f391ed28cb1','a2afedb8-cd3b-4753-8526-c54b9da759b4','Kas Utama Kelas 5-A',500000.00,'IDR','2026-09-07 07:08:32.000','2026-09-07 07:08:32.000');
/*!40000 ALTER TABLE `cash_accounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `class_enrollments`
--

DROP TABLE IF EXISTS `class_enrollments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `class_enrollments` (
  `id` varchar(191) NOT NULL,
  `class_id` varchar(191) NOT NULL,
  `student_id` varchar(191) NOT NULL,
  `created_at` datetime(3) NOT NULL DEFAULT current_timestamp(3),
  PRIMARY KEY (`id`),
  UNIQUE KEY `class_enrollments_class_id_student_id_key` (`class_id`,`student_id`),
  KEY `class_enrollments_student_id_fkey` (`student_id`),
  CONSTRAINT `class_enrollments_class_id_fkey` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `class_enrollments_student_id_fkey` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `class_enrollments`
--

LOCK TABLES `class_enrollments` WRITE;
/*!40000 ALTER TABLE `class_enrollments` DISABLE KEYS */;
INSERT INTO `class_enrollments` VALUES ('a2afedb8-dfff-4eaa-8fc7-70269a71b24f','a2afedb8-cd3b-4753-8526-c54b9da759b4','a2afedb8-de9b-44ec-b2b1-0b43912b140a','2026-09-07 14:08:32.927'),('a2afedb8-e214-4a40-a8e1-a6a718f44bd7','a2afedb8-cd3b-4753-8526-c54b9da759b4','a2afedb8-e0da-42b6-a5e0-b2944e2ea22a','2026-09-07 14:08:32.932'),('a2afedb8-e43f-46b9-be42-4ff4361fa571','a2afedb8-cd3b-4753-8526-c54b9da759b4','a2afedb8-e2ef-4547-ae28-05b3c8072064','2026-09-07 14:08:32.938');
/*!40000 ALTER TABLE `class_enrollments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `classes`
--

DROP TABLE IF EXISTS `classes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `classes` (
  `id` varchar(191) NOT NULL,
  `academic_year_id` varchar(191) NOT NULL,
  `name` varchar(191) NOT NULL,
  `created_at` datetime(3) NOT NULL DEFAULT current_timestamp(3),
  `updated_at` datetime(3) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `classes_academic_year_id_fkey` (`academic_year_id`),
  CONSTRAINT `classes_academic_year_id_fkey` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `classes`
--

LOCK TABLES `classes` WRITE;
/*!40000 ALTER TABLE `classes` DISABLE KEYS */;
INSERT INTO `classes` VALUES ('a2afedb8-cd3b-4753-8526-c54b9da759b4','a2afedb8-ca14-4405-b09d-234abff708b2','Kelas 5-A','2026-09-07 07:08:32.000','2026-09-07 07:08:32.000');
/*!40000 ALTER TABLE `classes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dues_schemes`
--

DROP TABLE IF EXISTS `dues_schemes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dues_schemes` (
  `id` varchar(191) NOT NULL,
  `cash_account_id` varchar(191) NOT NULL,
  `title` varchar(191) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `due_date` datetime(3) NOT NULL,
  `created_at` datetime(3) NOT NULL DEFAULT current_timestamp(3),
  `updated_at` datetime(3) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `dues_schemes_cash_account_id_fkey` (`cash_account_id`),
  CONSTRAINT `dues_schemes_cash_account_id_fkey` FOREIGN KEY (`cash_account_id`) REFERENCES `cash_accounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dues_schemes`
--

LOCK TABLES `dues_schemes` WRITE;
/*!40000 ALTER TABLE `dues_schemes` DISABLE KEYS */;
INSERT INTO `dues_schemes` VALUES ('a2afedb8-e767-470c-8b52-c58dc07c5870','a2afedb8-cf56-4e3d-b3d6-2f391ed28cb1','Iuran Kas Bulanan September 2026',20000.00,'2026-09-27 07:08:32.000','2026-09-07 07:08:32.000','2026-09-07 07:08:32.000');
/*!40000 ALTER TABLE `dues_schemes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `expenses`
--

DROP TABLE IF EXISTS `expenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `expenses` (
  `id` varchar(191) NOT NULL,
  `cash_account_id` varchar(191) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `title` varchar(191) NOT NULL,
  `description` varchar(191) NOT NULL,
  `receipt_url` varchar(191) DEFAULT NULL,
  `approval_status` enum('PENDING','APPROVED','REJECTED') NOT NULL DEFAULT 'PENDING',
  `requested_by` varchar(191) NOT NULL,
  `approved_by` varchar(191) DEFAULT NULL,
  `created_at` datetime(3) NOT NULL DEFAULT current_timestamp(3),
  `updated_at` datetime(3) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `expenses_cash_account_id_fkey` (`cash_account_id`),
  KEY `expenses_requested_by_fkey` (`requested_by`),
  KEY `expenses_approved_by_fkey` (`approved_by`),
  CONSTRAINT `expenses_approved_by_fkey` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `expenses_cash_account_id_fkey` FOREIGN KEY (`cash_account_id`) REFERENCES `cash_accounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `expenses_requested_by_fkey` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `expenses`
--

LOCK TABLES `expenses` WRITE;
/*!40000 ALTER TABLE `expenses` DISABLE KEYS */;
INSERT INTO `expenses` VALUES ('a2afedb8-eeab-463d-ba1c-938118f98cee','a2afedb8-cf56-4e3d-b3d6-2f391ed28cb1',45000.00,'Spidol & Penghapus Whiteboard','Pembelian 3 pcs spidol snowman dan 1 penghapus papan tulis untuk ruang kelas 5-A',NULL,'PENDING','a2afedb8-dbac-4c18-85d7-3409b1a08c65',NULL,'2026-09-07 07:08:32.000','2026-09-07 07:08:32.000');
/*!40000 ALTER TABLE `expenses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2019_12_14_000001_create_personal_access_tokens_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
INSERT INTO `personal_access_tokens` VALUES (1,'App\\Models\\User','48d33925-0c94-4e1d-9023-b4f585cd093a','auth-token','ab3b1d97daa1efda98cef1051701108b0745dcdfe33a4d6c22d2239aae7514ea','[\"*\"]',NULL,NULL,'2026-09-07 00:10:49','2026-09-07 00:10:49');
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `schools`
--

DROP TABLE IF EXISTS `schools`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `schools` (
  `id` varchar(191) NOT NULL,
  `name` varchar(191) NOT NULL,
  `code` varchar(191) NOT NULL,
  `address` varchar(191) DEFAULT NULL,
  `phone` varchar(191) DEFAULT NULL,
  `created_at` datetime(3) NOT NULL DEFAULT current_timestamp(3),
  `updated_at` datetime(3) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `schools_code_key` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schools`
--

LOCK TABLES `schools` WRITE;
/*!40000 ALTER TABLE `schools` DISABLE KEYS */;
INSERT INTO `schools` VALUES ('4ec47d9d-38df-46c0-9ca6-db67817c1c91','Pusat Sistem Kas','sistemkas',NULL,NULL,'2026-09-07 03:04:41.057','2026-09-07 03:04:41.057'),('a2afedb8-b5cb-4e99-9db8-4d0fb0aad961','SD Negeri 08 PAGI','SDN08PAGI','Jl. Merdeka No. 8, Jakarta','021-5550808','2026-09-07 07:08:32.000','2026-09-07 07:08:32.000');
/*!40000 ALTER TABLE `schools` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_billings`
--

DROP TABLE IF EXISTS `student_billings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_billings` (
  `id` varchar(191) NOT NULL,
  `dues_scheme_id` varchar(191) NOT NULL,
  `student_id` varchar(191) NOT NULL,
  `amount_due` decimal(15,2) NOT NULL,
  `amount_paid` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` enum('PENDING','PARTIAL','PAID','OVERDUE') NOT NULL DEFAULT 'PENDING',
  `due_date` datetime(3) NOT NULL,
  `created_at` datetime(3) NOT NULL DEFAULT current_timestamp(3),
  `updated_at` datetime(3) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `student_billings_dues_scheme_id_fkey` (`dues_scheme_id`),
  KEY `student_billings_student_id_fkey` (`student_id`),
  CONSTRAINT `student_billings_dues_scheme_id_fkey` FOREIGN KEY (`dues_scheme_id`) REFERENCES `dues_schemes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `student_billings_student_id_fkey` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_billings`
--

LOCK TABLES `student_billings` WRITE;
/*!40000 ALTER TABLE `student_billings` DISABLE KEYS */;
INSERT INTO `student_billings` VALUES ('a2afedb8-e8d3-4a6b-a584-2aa9eb35238a','a2afedb8-e767-470c-8b52-c58dc07c5870','a2afedb8-de9b-44ec-b2b1-0b43912b140a',20000.00,20000.00,'PAID','2026-09-27 07:08:32.000','2026-09-07 07:08:32.000','2026-09-07 07:08:32.000'),('a2afedb8-ebc7-435e-b22b-857643410efd','a2afedb8-e767-470c-8b52-c58dc07c5870','a2afedb8-e0da-42b6-a5e0-b2944e2ea22a',20000.00,0.00,'PENDING','2026-09-27 07:08:32.000','2026-09-07 07:08:32.000','2026-09-07 07:08:32.000'),('a2afedb8-ed4b-461a-9651-f7be0bdaf20a','a2afedb8-e767-470c-8b52-c58dc07c5870','a2afedb8-e2ef-4547-ae28-05b3c8072064',20000.00,0.00,'PENDING','2026-09-27 07:08:32.000','2026-09-07 07:08:32.000','2026-09-07 07:08:32.000');
/*!40000 ALTER TABLE `student_billings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transactions` (
  `id` varchar(191) NOT NULL,
  `cash_account_id` varchar(191) NOT NULL,
  `billing_id` varchar(191) DEFAULT NULL,
  `type` enum('INCOME','EXPENSE') NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `category` varchar(191) NOT NULL,
  `description` varchar(191) NOT NULL,
  `receipt_url` varchar(191) DEFAULT NULL,
  `created_by` varchar(191) NOT NULL,
  `created_at` datetime(3) NOT NULL DEFAULT current_timestamp(3),
  PRIMARY KEY (`id`),
  KEY `transactions_cash_account_id_fkey` (`cash_account_id`),
  KEY `transactions_billing_id_fkey` (`billing_id`),
  KEY `transactions_created_by_fkey` (`created_by`),
  CONSTRAINT `transactions_billing_id_fkey` FOREIGN KEY (`billing_id`) REFERENCES `student_billings` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `transactions_cash_account_id_fkey` FOREIGN KEY (`cash_account_id`) REFERENCES `cash_accounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `transactions_created_by_fkey` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transactions`
--

LOCK TABLES `transactions` WRITE;
/*!40000 ALTER TABLE `transactions` DISABLE KEYS */;
INSERT INTO `transactions` VALUES ('a2afedb8-ea51-4290-97e2-cd83e91037e2','a2afedb8-cf56-4e3d-b3d6-2f391ed28cb1','a2afedb8-e8d3-4a6b-a584-2aa9eb35238a','INCOME',20000.00,'Iuran Kas','Pembayaran Iuran Kas September 2026 - Andi Wijaya',NULL,'a2afedb8-dbac-4c18-85d7-3409b1a08c65','2026-09-07 07:08:32.000');
/*!40000 ALTER TABLE `transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` varchar(191) NOT NULL,
  `school_id` varchar(191) NOT NULL,
  `nis` varchar(191) DEFAULT NULL,
  `nisn` varchar(191) DEFAULT NULL,
  `name` varchar(191) NOT NULL,
  `email` varchar(191) NOT NULL,
  `phone` varchar(191) DEFAULT NULL,
  `gender` enum('MALE','FEMALE') DEFAULT NULL,
  `religion` enum('ISLAM','CHRISTIAN','CATHOLIC','HINDU','BUDDHA','CONFUCIAN','OTHER') DEFAULT NULL,
  `password_hash` varchar(191) NOT NULL,
  `role` enum('SUPER_ADMIN','ADMIN','TREASURER','KORLAS','STUDENT','PARENT') NOT NULL DEFAULT 'STUDENT',
  `student_id` varchar(191) DEFAULT NULL,
  `managed_class` varchar(191) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime(3) NOT NULL DEFAULT current_timestamp(3),
  `updated_at` datetime(3) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_key` (`email`),
  UNIQUE KEY `users_nis_key` (`nis`),
  UNIQUE KEY `users_nisn_key` (`nisn`),
  KEY `users_school_id_fkey` (`school_id`),
  KEY `users_student_id_fkey` (`student_id`),
  CONSTRAINT `users_school_id_fkey` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `users_student_id_fkey` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES ('48d33925-0c94-4e1d-9023-b4f585cd093a','a2afedb8-b5cb-4e99-9db8-4d0fb0aad961',NULL,NULL,'Budi Santoso','admin@sdn08pagi.sch.id',NULL,NULL,NULL,'$2y$12$5Ud6km7jaRr5PuTNvr1AyuaCb0p/h2oZbrJueytn4pHQlqrdbwNMy','ADMIN',NULL,NULL,1,'2026-09-07 03:04:41.079','2026-09-07 07:08:32.000'),('88aadfcf-913b-4a47-b0bf-46818d919eca','4ec47d9d-38df-46c0-9ca6-db67817c1c91',NULL,NULL,'System Admin','sysadmin@sistemkas.com',NULL,NULL,NULL,'$2y$12$5Ud6km7jaRr5PuTNvr1AyuaCb0p/h2oZbrJueytn4pHQlqrdbwNMy','SUPER_ADMIN',NULL,NULL,1,'2026-09-07 03:04:41.067','2026-09-07 07:08:32.000'),('a2afedb8-dbac-4c18-85d7-3409b1a08c65','a2afedb8-b5cb-4e99-9db8-4d0fb0aad961',NULL,NULL,'Siti Rahma','bendahara@sdn08pagi.sch.id',NULL,NULL,NULL,'$2y$12$5Ud6km7jaRr5PuTNvr1AyuaCb0p/h2oZbrJueytn4pHQlqrdbwNMy','TREASURER',NULL,NULL,1,'2026-09-07 07:08:32.000','2026-09-07 07:08:32.000'),('a2afedb8-dcbb-453c-ba3f-7e7dccdb0b55','a2afedb8-b5cb-4e99-9db8-4d0fb0aad961',NULL,NULL,'Korlas 5-A','korlas5a@sdn08pagi.sch.id',NULL,NULL,NULL,'$2y$12$5Ud6km7jaRr5PuTNvr1AyuaCb0p/h2oZbrJueytn4pHQlqrdbwNMy','KORLAS',NULL,'a2afedb8-cd3b-4753-8526-c54b9da759b4',1,'2026-09-07 07:08:32.000','2026-09-07 07:08:32.000'),('a2afedb8-de9b-44ec-b2b1-0b43912b140a','a2afedb8-b5cb-4e99-9db8-4d0fb0aad961','20260501','0051234567','Andi Wijaya','andi@sdn08pagi.sch.id',NULL,'MALE','ISLAM','$2y$12$5Ud6km7jaRr5PuTNvr1AyuaCb0p/h2oZbrJueytn4pHQlqrdbwNMy','STUDENT',NULL,NULL,1,'2026-09-07 07:08:32.000','2026-09-07 07:08:32.000'),('a2afedb8-e0da-42b6-a5e0-b2944e2ea22a','a2afedb8-b5cb-4e99-9db8-4d0fb0aad961','20260502','0051234568','Dewi Lestari','dewi@sdn08pagi.sch.id',NULL,'FEMALE','CHRISTIAN','$2y$12$5Ud6km7jaRr5PuTNvr1AyuaCb0p/h2oZbrJueytn4pHQlqrdbwNMy','STUDENT',NULL,NULL,1,'2026-09-07 07:08:32.000','2026-09-07 07:08:32.000'),('a2afedb8-e2ef-4547-ae28-05b3c8072064','a2afedb8-b5cb-4e99-9db8-4d0fb0aad961','20260503','0051234569','Rian Pratama','rian@sdn08pagi.sch.id',NULL,'MALE','HINDU','$2y$12$5Ud6km7jaRr5PuTNvr1AyuaCb0p/h2oZbrJueytn4pHQlqrdbwNMy','STUDENT',NULL,NULL,1,'2026-09-07 07:08:32.000','2026-09-07 07:08:32.000'),('a2afedb8-e551-49ce-adbe-b4780cb1ea11','a2afedb8-b5cb-4e99-9db8-4d0fb0aad961',NULL,NULL,'Wali Murid Andi Wijaya','ortu.andi@sdn08pagi.sch.id',NULL,NULL,NULL,'$2y$12$5Ud6km7jaRr5PuTNvr1AyuaCb0p/h2oZbrJueytn4pHQlqrdbwNMy','PARENT','a2afedb8-de9b-44ec-b2b1-0b43912b140a',NULL,1,'2026-09-07 07:08:32.000','2026-09-07 07:08:32.000');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-07 14:12:52
