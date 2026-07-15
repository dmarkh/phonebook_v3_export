-- MySQL dump 10.13  Distrib 8.0.41, for Linux (x86_64)
--
-- Host: 127.0.0.1    Database: phonebook
-- ------------------------------------------------------
-- Server version	8.0.41

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
-- Table structure for table `documents`
--

DROP TABLE IF EXISTS `documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `documents` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `status` enum('active','onhold','inactive') NOT NULL DEFAULT 'onhold',
  `status_change_date` timestamp NULL DEFAULT NULL,
  `status_change_reason` varchar(255) NOT NULL DEFAULT '',
  `last_update_date` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `status` (`status`)
) ENGINE=MyISAM AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `documents_authors`
--

DROP TABLE IF EXISTS `documents_authors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `documents_authors` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `doc_id` int unsigned NOT NULL,
  `mem_id` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `doc_id` (`doc_id`,`mem_id`),
  KEY `mem_id` (`mem_id`)
) ENGINE=MyISAM AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `documents_comments`
--

DROP TABLE IF EXISTS `documents_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `documents_comments` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `created` datetime DEFAULT CURRENT_TIMESTAMP,
  `document_id` int unsigned NOT NULL,
  `member_id` int unsigned NOT NULL,
  `member_name` varchar(255) NOT NULL DEFAULT '',
  `comment` mediumtext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `document` (`document_id`),
  KEY `member` (`member_id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `documents_data_dates`
--

DROP TABLE IF EXISTS `documents_data_dates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `documents_data_dates` (
  `documents_id` int unsigned NOT NULL DEFAULT '0',
  `documents_fields_id` int unsigned NOT NULL DEFAULT '0',
  `value` datetime DEFAULT NULL,
  PRIMARY KEY (`documents_id`,`documents_fields_id`),
  KEY `documents_id` (`documents_id`),
  KEY `documents_fields_id` (`documents_fields_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `documents_data_ints`
--

DROP TABLE IF EXISTS `documents_data_ints`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `documents_data_ints` (
  `documents_id` int unsigned NOT NULL DEFAULT '0',
  `documents_fields_id` int unsigned NOT NULL DEFAULT '0',
  `value` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`documents_id`,`documents_fields_id`),
  KEY `documents_id` (`documents_id`),
  KEY `documents_fields_id` (`documents_fields_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `documents_data_strings`
--

DROP TABLE IF EXISTS `documents_data_strings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `documents_data_strings` (
  `documents_id` int unsigned NOT NULL DEFAULT '0',
  `documents_fields_id` int unsigned NOT NULL DEFAULT '0',
  `value` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`documents_id`,`documents_fields_id`),
  KEY `documents_id` (`documents_id`),
  KEY `documents_fields_id` (`documents_fields_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `documents_data_texts`
--

DROP TABLE IF EXISTS `documents_data_texts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `documents_data_texts` (
  `documents_id` int unsigned NOT NULL DEFAULT '0',
  `documents_fields_id` int unsigned NOT NULL DEFAULT '0',
  `value` mediumtext NOT NULL,
  PRIMARY KEY (`documents_id`,`documents_fields_id`),
  KEY `documents_id` (`documents_id`),
  KEY `documents_fields_id` (`documents_fields_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `documents_fields`
--

DROP TABLE IF EXISTS `documents_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `documents_fields` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name_fixed` varchar(255) NOT NULL DEFAULT '',
  `name_desc` varchar(255) NOT NULL DEFAULT '',
  `weight` int NOT NULL DEFAULT '0',
  `type` enum('string','date','int','text') DEFAULT NULL,
  `options` varchar(255) NOT NULL DEFAULT '',
  `size_min` int NOT NULL DEFAULT '0',
  `size_max` int NOT NULL DEFAULT '255',
  `hint_short` varchar(255) NOT NULL DEFAULT '',
  `hint_full` varchar(255) NOT NULL DEFAULT '',
  `is_required` enum('y','n') NOT NULL DEFAULT 'n',
  `is_enabled` enum('y','n') NOT NULL DEFAULT 'y',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `documents_groups`
--

DROP TABLE IF EXISTS `documents_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `documents_groups` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `doc_id` int unsigned NOT NULL,
  `grp_id` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `doc_id` (`doc_id`,`grp_id`),
  KEY `grp_id` (`grp_id`)
) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `documents_history`
--

DROP TABLE IF EXISTS `documents_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `documents_history` (
  `documents_id` int unsigned NOT NULL DEFAULT '0',
  `documents_fields_id` int unsigned NOT NULL DEFAULT '0',
  `date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `value_from_string` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '',
  `value_to_string` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '',
  `value_from_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `value_to_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `value_from_int` int NOT NULL DEFAULT '0',
  `value_to_int` int NOT NULL DEFAULT '0',
  `ip` varchar(255) NOT NULL DEFAULT '',
  `user` varchar(255) NOT NULL DEFAULT '',
  KEY `documents_id` (`documents_id`),
  KEY `documents_fields_id` (`documents_fields_id`),
  KEY `date` (`date`),
  KEY `doc_field_ids` (`documents_id`,`documents_fields_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `documents_owners`
--

DROP TABLE IF EXISTS `documents_owners`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `documents_owners` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `doc_id` int unsigned NOT NULL,
  `mem_id` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `doc_id` (`doc_id`,`mem_id`),
  KEY `mem_id` (`mem_id`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `documents_reviewers`
--

DROP TABLE IF EXISTS `documents_reviewers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `documents_reviewers` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `doc_id` int unsigned NOT NULL,
  `mem_id` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `doc_id` (`doc_id`,`mem_id`),
  KEY `mem_id` (`mem_id`)
) ENGINE=MyISAM AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `events` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `status` enum('active','onhold','inactive') NOT NULL DEFAULT 'onhold',
  `status_change_date` timestamp NULL DEFAULT NULL,
  `status_change_reason` varchar(255) NOT NULL DEFAULT '',
  `last_update_date` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `status` (`status`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `events_data_dates`
--

DROP TABLE IF EXISTS `events_data_dates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `events_data_dates` (
  `events_id` int unsigned NOT NULL DEFAULT '0',
  `events_fields_id` int unsigned NOT NULL DEFAULT '0',
  `value` datetime DEFAULT NULL,
  PRIMARY KEY (`events_id`,`events_fields_id`),
  KEY `events_id` (`events_id`),
  KEY `events_fields_id` (`events_fields_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `events_data_ints`
--

DROP TABLE IF EXISTS `events_data_ints`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `events_data_ints` (
  `events_id` int unsigned NOT NULL DEFAULT '0',
  `events_fields_id` int unsigned NOT NULL DEFAULT '0',
  `value` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`events_id`,`events_fields_id`),
  KEY `events_id` (`events_id`),
  KEY `events_fields_id` (`events_fields_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `events_data_strings`
--

DROP TABLE IF EXISTS `events_data_strings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `events_data_strings` (
  `events_id` int unsigned NOT NULL DEFAULT '0',
  `events_fields_id` int unsigned NOT NULL DEFAULT '0',
  `value` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`events_id`,`events_fields_id`),
  KEY `events_id` (`events_id`),
  KEY `events_fields_id` (`events_fields_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `events_data_texts`
--

DROP TABLE IF EXISTS `events_data_texts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `events_data_texts` (
  `events_id` int unsigned NOT NULL DEFAULT '0',
  `events_fields_id` int unsigned NOT NULL DEFAULT '0',
  `value` mediumtext NOT NULL,
  PRIMARY KEY (`events_id`,`events_fields_id`),
  KEY `events_id` (`events_id`),
  KEY `events_fields_id` (`events_fields_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `events_fields`
--

DROP TABLE IF EXISTS `events_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `events_fields` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name_fixed` varchar(255) NOT NULL DEFAULT '',
  `name_desc` varchar(255) NOT NULL DEFAULT '',
  `weight` int NOT NULL DEFAULT '0',
  `type` enum('string','date','int','text') DEFAULT NULL,
  `options` varchar(255) NOT NULL DEFAULT '',
  `size_min` int NOT NULL DEFAULT '0',
  `size_max` int NOT NULL DEFAULT '255',
  `hint_short` varchar(255) NOT NULL DEFAULT '',
  `hint_full` varchar(255) NOT NULL DEFAULT '',
  `is_required` enum('y','n') NOT NULL DEFAULT 'n',
  `is_enabled` enum('y','n') NOT NULL DEFAULT 'y',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `events_history`
--

DROP TABLE IF EXISTS `events_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `events_history` (
  `events_id` int unsigned NOT NULL DEFAULT '0',
  `events_fields_id` int unsigned NOT NULL DEFAULT '0',
  `date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `value_from_string` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '',
  `value_to_string` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '',
  `value_from_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `value_to_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `value_from_int` int NOT NULL DEFAULT '0',
  `value_to_int` int NOT NULL DEFAULT '0',
  `ip` varchar(255) NOT NULL DEFAULT '',
  `user` varchar(255) NOT NULL DEFAULT '',
  KEY `events_id` (`events_id`),
  KEY `events_fields_id` (`events_fields_id`),
  KEY `date` (`date`),
  KEY `evt_field_ids` (`events_id`,`events_fields_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `groups`
--

DROP TABLE IF EXISTS `groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `groups` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `entryTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `parent` int unsigned NOT NULL DEFAULT '0',
  `status` enum('active','inactive','archived') NOT NULL DEFAULT 'active',
  `private` enum('yes','no') NOT NULL DEFAULT 'no',
  `name` varchar(255) NOT NULL DEFAULT '',
  `category` varchar(255) NOT NULL DEFAULT '',
  `desc` text,
  `email` varchar(255) NOT NULL DEFAULT '',
  `url` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `status` (`status`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `groups_members`
--

DROP TABLE IF EXISTS `groups_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `groups_members` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `entryTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `group_id` int unsigned NOT NULL,
  `member_id` int unsigned NOT NULL,
  `role_id` int unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `group` (`group_id`),
  KEY `member` (`member_id`)
) ENGINE=MyISAM AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `groups_roles`
--

DROP TABLE IF EXISTS `groups_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `groups_roles` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `entryTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `group_id` int unsigned NOT NULL,
  `weight` int NOT NULL DEFAULT '0',
  `role` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `group` (`group_id`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `institutions`
--

DROP TABLE IF EXISTS `institutions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `institutions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `status` enum('active','onhold','inactive') NOT NULL DEFAULT 'onhold',
  `status_change_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status_change_reason` varchar(255) NOT NULL DEFAULT '',
  `last_update_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `join_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `status` (`status`)
) ENGINE=MyISAM AUTO_INCREMENT=119 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `institutions_data_dates`
--

DROP TABLE IF EXISTS `institutions_data_dates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `institutions_data_dates` (
  `institutions_id` int unsigned NOT NULL DEFAULT '0',
  `institutions_fields_id` int unsigned NOT NULL DEFAULT '0',
  `value` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`institutions_id`,`institutions_fields_id`),
  KEY `institutions_fields_id` (`institutions_fields_id`),
  KEY `institutions_id` (`institutions_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `institutions_data_ints`
--

DROP TABLE IF EXISTS `institutions_data_ints`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `institutions_data_ints` (
  `institutions_id` int unsigned NOT NULL DEFAULT '0',
  `institutions_fields_id` int unsigned NOT NULL DEFAULT '0',
  `value` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`institutions_id`,`institutions_fields_id`),
  KEY `institutions_id` (`institutions_id`),
  KEY `institutions_fields_id` (`institutions_fields_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `institutions_data_strings`
--

DROP TABLE IF EXISTS `institutions_data_strings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `institutions_data_strings` (
  `institutions_id` int unsigned NOT NULL DEFAULT '0',
  `institutions_fields_id` int unsigned NOT NULL DEFAULT '0',
  `value` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`institutions_id`,`institutions_fields_id`),
  KEY `institutions_fields_id` (`institutions_fields_id`),
  KEY `institutions_id` (`institutions_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `institutions_data_texts`
--

DROP TABLE IF EXISTS `institutions_data_texts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `institutions_data_texts` (
  `institutions_id` int unsigned NOT NULL DEFAULT '0',
  `institutions_fields_id` int unsigned NOT NULL DEFAULT '0',
  `value` mediumtext,
  PRIMARY KEY (`institutions_id`,`institutions_fields_id`),
  KEY `institutions_fields_id` (`institutions_fields_id`),
  KEY `institutions_id` (`institutions_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `institutions_fields`
--

DROP TABLE IF EXISTS `institutions_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `institutions_fields` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name_fixed` varchar(255) NOT NULL DEFAULT '',
  `name_desc` varchar(255) NOT NULL DEFAULT '',
  `weight` int NOT NULL DEFAULT '0',
  `type` enum('string','date','int','text') DEFAULT NULL,
  `options` text NOT NULL,
  `size_min` int NOT NULL DEFAULT '0',
  `size_max` int NOT NULL DEFAULT '255',
  `hint_short` varchar(255) NOT NULL DEFAULT '',
  `hint_full` varchar(255) NOT NULL DEFAULT '',
  `group` int NOT NULL DEFAULT '1',
  `is_required` enum('y','n') NOT NULL DEFAULT 'n',
  `is_enabled` enum('y','n') NOT NULL DEFAULT 'y',
  `privacy` enum('public','users_auth','users_admin') NOT NULL DEFAULT 'public',
  `always_latest` enum('n','y') NOT NULL DEFAULT 'n',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_short` (`name_fixed`)
) ENGINE=MyISAM AUTO_INCREMENT=57 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `institutions_fields_groups`
--

DROP TABLE IF EXISTS `institutions_fields_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `institutions_fields_groups` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name_short` varchar(255) NOT NULL DEFAULT '',
  `name_full` varchar(255) NOT NULL DEFAULT '',
  `is_enabled` enum('y','n') NOT NULL DEFAULT 'y',
  `weight` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `institutions_history`
--

DROP TABLE IF EXISTS `institutions_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `institutions_history` (
  `institutions_id` int unsigned NOT NULL DEFAULT '0',
  `institutions_fields_id` int unsigned NOT NULL DEFAULT '0',
  `date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `value_from_string` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '',
  `value_to_string` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '',
  `value_from_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `value_to_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `value_from_int` int NOT NULL DEFAULT '0',
  `value_to_int` int NOT NULL DEFAULT '0',
  `ip` varchar(255) NOT NULL DEFAULT '',
  `user` varchar(255) NOT NULL DEFAULT '',
  KEY `institutions_id` (`institutions_id`),
  KEY `institutions_fields_id` (`institutions_fields_id`),
  KEY `date` (`date`),
  KEY `inst_field_ids` (`institutions_id`,`institutions_fields_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `last_update`
--

DROP TABLE IF EXISTS `last_update`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `last_update` (
  `members` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `institutions` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `members`
--

DROP TABLE IF EXISTS `members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `members` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `status` enum('active','onhold','inactive') NOT NULL DEFAULT 'onhold',
  `status_change_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status_change_reason` varchar(255) NOT NULL DEFAULT '',
  `last_update_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `join_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `status` (`status`)
) ENGINE=MyISAM AUTO_INCREMENT=2164 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `members_data_dates`
--

DROP TABLE IF EXISTS `members_data_dates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `members_data_dates` (
  `members_id` int unsigned NOT NULL DEFAULT '0',
  `members_fields_id` int unsigned NOT NULL DEFAULT '0',
  `value` datetime DEFAULT NULL,
  PRIMARY KEY (`members_id`,`members_fields_id`),
  KEY `members_id` (`members_id`),
  KEY `members_fields_id` (`members_fields_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `members_data_ints`
--

DROP TABLE IF EXISTS `members_data_ints`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `members_data_ints` (
  `members_id` int unsigned NOT NULL DEFAULT '0',
  `members_fields_id` int unsigned NOT NULL DEFAULT '0',
  `value` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`members_id`,`members_fields_id`),
  KEY `members_id` (`members_id`),
  KEY `members_fields_id` (`members_fields_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `members_data_strings`
--

DROP TABLE IF EXISTS `members_data_strings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `members_data_strings` (
  `members_id` int unsigned NOT NULL DEFAULT '0',
  `members_fields_id` int unsigned NOT NULL DEFAULT '0',
  `value` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`members_id`,`members_fields_id`),
  KEY `members_id` (`members_id`),
  KEY `members_fields_id` (`members_fields_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `members_data_texts`
--

DROP TABLE IF EXISTS `members_data_texts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `members_data_texts` (
  `members_id` int unsigned NOT NULL DEFAULT '0',
  `members_fields_id` int unsigned NOT NULL DEFAULT '0',
  `value` mediumtext NOT NULL,
  PRIMARY KEY (`members_id`,`members_fields_id`),
  KEY `members_id` (`members_id`),
  KEY `members_fields_id` (`members_fields_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `members_fields`
--

DROP TABLE IF EXISTS `members_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `members_fields` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name_fixed` varchar(255) NOT NULL DEFAULT '',
  `name_desc` varchar(255) NOT NULL DEFAULT '',
  `weight` int NOT NULL DEFAULT '0',
  `type` enum('string','date','int','text') DEFAULT NULL,
  `options` varchar(255) NOT NULL DEFAULT '',
  `size_min` int NOT NULL DEFAULT '0',
  `size_max` int NOT NULL DEFAULT '255',
  `hint_short` varchar(255) NOT NULL DEFAULT '',
  `hint_full` varchar(255) NOT NULL DEFAULT '',
  `group` int NOT NULL DEFAULT '0',
  `is_required` enum('y','n') NOT NULL DEFAULT 'n',
  `is_enabled` enum('y','n') NOT NULL DEFAULT 'y',
  `privacy` enum('public','users_auth','users_user','users_admin') NOT NULL DEFAULT 'public',
  `always_latest` enum('n','y') NOT NULL DEFAULT 'n',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_group` (`name_fixed`,`group`)
) ENGINE=MyISAM AUTO_INCREMENT=110 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `members_fields_groups`
--

DROP TABLE IF EXISTS `members_fields_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `members_fields_groups` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name_short` varchar(255) NOT NULL DEFAULT '',
  `name_full` varchar(255) NOT NULL DEFAULT '',
  `is_enabled` enum('y','n') NOT NULL DEFAULT 'y',
  `weight` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `members_history`
--

DROP TABLE IF EXISTS `members_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `members_history` (
  `members_id` int unsigned NOT NULL DEFAULT '0',
  `members_fields_id` int unsigned NOT NULL DEFAULT '0',
  `date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `value_from_string` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '',
  `value_to_string` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '',
  `value_from_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `value_to_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `value_from_int` int NOT NULL DEFAULT '0',
  `value_to_int` int NOT NULL DEFAULT '0',
  `ip` varchar(255) NOT NULL DEFAULT '',
  `user` varchar(255) NOT NULL DEFAULT '',
  KEY `members_id` (`members_id`),
  KEY `members_fields_id` (`members_fields_id`),
  KEY `date` (`date`),
  KEY `mem_field_ids` (`members_id`,`members_fields_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tasks`
--

DROP TABLE IF EXISTS `tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tasks` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `status` enum('active','onhold','inactive') NOT NULL DEFAULT 'active',
  `status_change_date` timestamp NULL DEFAULT NULL,
  `status_change_reason` varchar(255) NOT NULL DEFAULT '',
  `last_update_date` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `status` (`status`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tasks_assigned`
--

DROP TABLE IF EXISTS `tasks_assigned`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tasks_assigned` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `entryTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `task_id` int unsigned NOT NULL,
  `member_id` int unsigned NOT NULL,
  `group_id` int unsigned NOT NULL,
  `fte` float DEFAULT '0',
  `begin_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `validated` int DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `task` (`task_id`),
  KEY `member` (`member_id`),
  KEY `group` (`group_id`)
) ENGINE=MyISAM AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tasks_data_dates`
--

DROP TABLE IF EXISTS `tasks_data_dates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tasks_data_dates` (
  `tasks_id` int unsigned NOT NULL DEFAULT '0',
  `tasks_fields_id` int unsigned NOT NULL DEFAULT '0',
  `value` datetime DEFAULT NULL,
  PRIMARY KEY (`tasks_id`,`tasks_fields_id`),
  KEY `tasks_id` (`tasks_id`),
  KEY `tasks_fields_id` (`tasks_fields_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tasks_data_ints`
--

DROP TABLE IF EXISTS `tasks_data_ints`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tasks_data_ints` (
  `tasks_id` int unsigned NOT NULL DEFAULT '0',
  `tasks_fields_id` int unsigned NOT NULL DEFAULT '0',
  `value` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`tasks_id`,`tasks_fields_id`),
  KEY `tasks_id` (`tasks_id`),
  KEY `tasks_fields_id` (`tasks_fields_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tasks_data_strings`
--

DROP TABLE IF EXISTS `tasks_data_strings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tasks_data_strings` (
  `tasks_id` int unsigned NOT NULL DEFAULT '0',
  `tasks_fields_id` int unsigned NOT NULL DEFAULT '0',
  `value` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`tasks_id`,`tasks_fields_id`),
  KEY `tasks_id` (`tasks_id`),
  KEY `tasks_fields_id` (`tasks_fields_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tasks_data_texts`
--

DROP TABLE IF EXISTS `tasks_data_texts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tasks_data_texts` (
  `tasks_id` int unsigned NOT NULL DEFAULT '0',
  `tasks_fields_id` int unsigned NOT NULL DEFAULT '0',
  `value` mediumtext NOT NULL,
  PRIMARY KEY (`tasks_id`,`tasks_fields_id`),
  KEY `tasks_id` (`tasks_id`),
  KEY `tasks_fields_id` (`tasks_fields_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tasks_fields`
--

DROP TABLE IF EXISTS `tasks_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tasks_fields` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name_fixed` varchar(255) NOT NULL DEFAULT '',
  `name_desc` varchar(255) NOT NULL DEFAULT '',
  `weight` int NOT NULL DEFAULT '0',
  `type` enum('string','date','int','text') DEFAULT NULL,
  `options` varchar(255) NOT NULL DEFAULT '',
  `size_min` int NOT NULL DEFAULT '0',
  `size_max` int NOT NULL DEFAULT '255',
  `hint_short` varchar(255) NOT NULL DEFAULT '',
  `hint_full` varchar(255) NOT NULL DEFAULT '',
  `is_required` enum('y','n') NOT NULL DEFAULT 'n',
  `is_enabled` enum('y','n') NOT NULL DEFAULT 'y',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tasks_history`
--

DROP TABLE IF EXISTS `tasks_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tasks_history` (
  `tasks_id` int unsigned NOT NULL DEFAULT '0',
  `tasks_fields_id` int unsigned NOT NULL DEFAULT '0',
  `date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `value_from_string` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '',
  `value_to_string` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '',
  `value_from_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `value_to_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `value_from_int` int NOT NULL DEFAULT '0',
  `value_to_int` int NOT NULL DEFAULT '0',
  `ip` varchar(255) NOT NULL DEFAULT '',
  `user` varchar(255) NOT NULL DEFAULT '',
  KEY `tasks_id` (`tasks_id`),
  KEY `tasks_fields_id` (`tasks_fields_id`),
  KEY `date` (`date`),
  KEY `t_field_ids` (`tasks_id`,`tasks_fields_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `workflows`
--

DROP TABLE IF EXISTS `workflows`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `workflows` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `status` enum('active','onhold','inactive') NOT NULL DEFAULT 'onhold',
  `name` varchar(256) NOT NULL DEFAULT '',
  `description` varchar(2048) NOT NULL DEFAULT '',
  `weight` int NOT NULL DEFAULT '0',
  `created` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `created` (`created`)
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `workflows_blocks`
--

DROP TABLE IF EXISTS `workflows_blocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `workflows_blocks` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `status` enum('active','onhold','inactive') NOT NULL DEFAULT 'onhold',
  `weight` int NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` mediumtext NOT NULL,
  `configurable` enum('not-configurable','members','group','group-and-roles') NOT NULL DEFAULT 'not-configurable',
  `block_type_id` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `block_type` (`block_type_id`),
  KEY `status` (`status`)
) ENGINE=MyISAM AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `workflows_comments`
--

DROP TABLE IF EXISTS `workflows_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `workflows_comments` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `created` datetime DEFAULT CURRENT_TIMESTAMP,
  `document_id` int unsigned NOT NULL,
  `workflow_id` int unsigned NOT NULL,
  `member_id` int unsigned NOT NULL,
  `comment` mediumtext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `document` (`document_id`),
  KEY `workflow` (`workflow_id`),
  KEY `member` (`member_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `workflows_configs`
--

DROP TABLE IF EXISTS `workflows_configs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `workflows_configs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `workflow_id` int unsigned NOT NULL DEFAULT '0',
  `block_id` int unsigned NOT NULL DEFAULT '0',
  `step_id` int unsigned NOT NULL DEFAULT '0',
  `member_ids` varchar(2048) NOT NULL DEFAULT '',
  `group_id` int unsigned NOT NULL DEFAULT '0',
  `group_role_ids` varchar(2048) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `workflow_id` (`workflow_id`,`block_id`,`step_id`),
  KEY `workflow` (`workflow_id`),
  KEY `block_id` (`block_id`),
  KEY `step_id` (`step_id`)
) ENGINE=MyISAM AUTO_INCREMENT=320 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `workflows_documents`
--

DROP TABLE IF EXISTS `workflows_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `workflows_documents` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `created` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `document_id` int unsigned NOT NULL,
  `workflow_id` int unsigned NOT NULL,
  `current_step_id` int unsigned NOT NULL DEFAULT '0',
  `status` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `document` (`document_id`),
  KEY `status` (`status`),
  KEY `workflow` (`workflow_id`)
) ENGINE=MyISAM AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `workflows_locks`
--

DROP TABLE IF EXISTS `workflows_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `workflows_locks` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `created` datetime DEFAULT CURRENT_TIMESTAMP,
  `document_id` int unsigned NOT NULL,
  `workflow_id` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `workflow_document` (`workflow_id`,`document_id`)
) ENGINE=MyISAM AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `workflows_progress`
--

DROP TABLE IF EXISTS `workflows_progress`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `workflows_progress` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `created` datetime DEFAULT CURRENT_TIMESTAMP,
  `document_id` int unsigned NOT NULL,
  `workflow_id` int unsigned NOT NULL,
  `step_id` int unsigned NOT NULL,
  `member_id` int unsigned NOT NULL,
  `member_name` varchar(255) NOT NULL DEFAULT '',
  `operation` enum('step-started','assign-docid','notify-member','notify-maillist','decline','accept','comment') NOT NULL DEFAULT 'comment',
  `metadata` varchar(2048) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `document` (`document_id`),
  KEY `workflow` (`workflow_id`),
  KEY `step` (`step_id`),
  KEY `member` (`member_id`)
) ENGINE=MyISAM AUTO_INCREMENT=360 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-11 12:52:28
