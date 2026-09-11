/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-11.8.2-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: botsv3
-- ------------------------------------------------------
-- Server version	11.8.2-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Table structure for table `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_logs` (
  `_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` datetime NOT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `user_role_id` tinyint(3) unsigned DEFAULT NULL,
  `vendor_id` int(11) DEFAULT NULL,
  `activity` text DEFAULT NULL,
  PRIMARY KEY (`_id`)
) ENGINE=InnoDB AUTO_INCREMENT=257 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_logs`
--

LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `activity_logs` VALUES
(1,'2024-11-13 17:29:46',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(2,'2024-11-13 17:30:02',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(3,'2024-11-13 17:30:13',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(4,'2024-11-13 17:31:07',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(5,'2024-11-13 18:07:20',NULL,NULL,NULL,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(6,'2024-11-13 18:07:21',2,2,1,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(7,'2024-11-13 18:10:48',2,2,1,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(8,'2024-11-13 18:10:53',NULL,NULL,NULL,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(9,'2024-11-13 18:10:55',2,2,1,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(10,'2024-11-13 18:10:55',2,2,1,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(11,'2024-11-13 18:10:56',2,2,1,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(12,'2024-11-13 18:11:36',2,2,1,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(13,'2024-11-13 18:16:39',NULL,NULL,NULL,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(14,'2024-11-13 18:22:52',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(15,'2024-11-13 18:25:10',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(16,'2024-11-13 18:26:02',NULL,NULL,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(17,'2024-11-13 18:26:05',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(18,'2024-11-13 19:56:23',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(19,'2024-11-13 20:14:21',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(20,'2024-11-13 20:43:59',2,2,1,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(21,'2024-11-13 20:44:03',NULL,NULL,NULL,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(22,'2024-11-13 20:44:04',2,2,1,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(23,'2024-11-13 20:44:04',2,2,1,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(24,'2024-11-13 20:44:05',2,2,1,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(25,'2024-11-13 21:45:35',2,2,1,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(26,'2024-11-13 22:22:16',2,2,1,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(27,'2024-11-14 01:56:50',2,2,1,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(28,'2024-11-14 02:03:56',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(29,'2024-11-14 04:07:43',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(30,'2024-11-14 04:09:39',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(31,'2024-11-14 04:14:08',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(32,'2024-11-14 04:24:59',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(33,'2024-11-14 04:38:42',2,2,1,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(34,'2024-11-14 04:39:24',2,2,1,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(35,'2024-11-14 05:02:39',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(36,'2024-11-14 05:05:28',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(37,'2024-11-16 16:47:24',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(38,'2024-11-16 16:48:03',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(39,'2024-11-16 16:48:25',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(40,'2024-11-16 16:49:10',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(41,'2024-11-16 16:49:26',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(42,'2024-11-16 16:49:49',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(43,'2024-11-16 16:50:02',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(44,'2024-11-16 16:56:06',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(45,'2024-11-16 16:59:56',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(46,'2024-11-16 17:00:13',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(47,'2024-11-16 17:00:20',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(48,'2024-11-16 17:01:44',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(49,'2024-11-16 17:02:14',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(50,'2024-11-16 17:04:26',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(51,'2024-11-16 17:05:01',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(52,'2024-11-16 17:05:54',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(53,'2024-11-16 17:07:32',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(54,'2024-11-16 17:07:57',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(55,'2024-11-16 17:12:23',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(56,'2024-11-17 10:18:58',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(57,'2024-11-17 10:20:40',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(58,'2024-11-17 10:21:37',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(59,'2024-11-17 10:21:56',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(60,'2024-11-17 10:23:43',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(61,'2024-11-17 11:41:15',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(62,'2024-11-17 11:41:39',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(63,'2024-11-17 11:45:16',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(64,'2024-11-17 14:07:52',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(65,'2024-11-17 14:13:35',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(66,'2024-11-17 14:15:35',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(67,'2024-11-17 16:07:45',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(68,'2024-11-17 16:09:01',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(69,'2024-11-17 17:24:46',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(70,'2024-11-17 17:51:13',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(71,'2024-11-17 21:21:55',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(72,'2024-11-17 21:23:39',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(73,'2024-11-17 21:25:31',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(74,'2024-11-17 21:26:26',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(75,'2024-11-18 15:42:45',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(76,'2024-11-18 15:43:23',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(77,'2024-11-18 16:40:14',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(78,'2024-11-18 16:47:01',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(79,'2024-11-18 17:30:51',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(80,'2024-11-19 03:21:54',2,2,1,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(81,'2024-11-19 16:57:09',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(82,'2024-11-19 19:34:51',NULL,NULL,NULL,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(83,'2024-11-19 19:34:52',2,2,1,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(84,'2024-11-19 19:35:35',NULL,NULL,NULL,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(85,'2024-11-19 19:35:36',2,2,1,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(86,'2024-11-19 19:35:36',2,2,1,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(87,'2024-11-19 19:35:37',2,2,1,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(88,'2024-11-19 19:41:59',2,2,1,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(89,'2024-11-19 19:42:03',NULL,NULL,NULL,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(90,'2024-11-19 19:42:04',2,2,1,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(91,'2024-11-19 19:42:04',2,2,1,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(92,'2024-11-19 19:42:05',2,2,1,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(93,'2024-11-19 21:12:38',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(94,'2024-11-19 21:48:23',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(95,'2024-11-21 15:37:47',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(96,'2024-11-21 15:38:36',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(97,'2024-11-21 15:42:53',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(98,'2024-11-21 15:49:20',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(99,'2024-11-21 15:54:53',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(100,'2024-11-21 15:55:01',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(101,'2024-11-21 16:09:15',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(102,'2024-11-21 16:33:25',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(103,'2024-11-21 16:34:09',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(104,'2024-11-21 16:40:02',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(105,'2024-11-21 16:43:55',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(106,'2024-11-21 16:43:57',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(107,'2024-11-21 16:44:03',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(108,'2024-11-21 16:57:56',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(109,'2024-11-21 19:08:14',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(110,'2024-11-22 11:23:48',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(111,'2024-11-22 15:48:59',2,2,1,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(112,'2024-11-22 15:49:03',NULL,NULL,NULL,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(113,'2024-11-22 15:49:05',2,2,1,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(114,'2024-11-22 15:49:05',2,2,1,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(115,'2024-11-22 15:49:06',2,2,1,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(116,'2024-11-23 10:54:21',NULL,NULL,NULL,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(117,'2024-11-23 10:54:22',7,2,6,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(118,'2024-11-23 10:55:11',7,2,6,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(119,'2024-11-23 10:55:15',NULL,NULL,NULL,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(120,'2024-11-23 10:55:16',7,2,6,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(121,'2024-11-23 10:55:16',7,2,6,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(122,'2024-11-23 10:55:17',7,2,6,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(123,'2024-11-23 10:55:36',7,2,6,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(124,'2024-11-23 10:55:36',7,2,6,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(125,'2024-11-23 10:55:51',7,2,6,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(126,'2024-11-23 10:56:01',7,2,6,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(127,'2024-11-23 11:26:27',NULL,NULL,NULL,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(128,'2024-11-24 18:55:17',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(129,'2024-11-24 19:14:50',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(130,'2024-11-24 19:29:36',8,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(131,'2024-11-24 19:29:40',8,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(132,'2024-11-24 19:29:42',8,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(133,'2024-11-24 19:29:46',8,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(134,'2024-11-24 19:30:39',8,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(135,'2024-11-24 19:30:51',8,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(136,'2024-11-24 19:31:34',8,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(137,'2024-11-24 19:31:45',8,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(138,'2024-11-24 19:32:03',8,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(139,'2024-11-24 20:14:31',2,2,1,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(140,'2024-11-25 03:59:48',NULL,NULL,NULL,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(141,'2024-11-25 04:00:52',NULL,NULL,NULL,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(142,'2024-11-25 04:00:52',2,2,1,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(143,'2024-11-25 04:03:18',2,2,1,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(144,'2024-11-25 04:03:23',NULL,NULL,NULL,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(145,'2024-11-25 04:03:24',2,2,1,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(146,'2024-11-25 04:03:24',2,2,1,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(147,'2024-11-25 04:03:25',2,2,1,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(148,'2024-11-25 04:03:48',NULL,NULL,NULL,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(149,'2024-11-25 05:33:01',2,2,1,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(150,'2024-11-25 06:36:33',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(151,'2024-11-25 06:36:45',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(152,'2024-11-25 06:36:48',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(153,'2024-11-25 06:45:00',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(154,'2024-11-25 06:45:03',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(155,'2024-11-25 09:39:18',7,2,6,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(156,'2024-11-30 21:40:58',2,2,1,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(157,'2024-11-30 21:41:02',2,2,1,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(158,'2024-12-02 05:28:27',NULL,NULL,NULL,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(159,'2024-12-02 05:28:29',13,2,11,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(160,'2024-12-02 05:28:30',13,2,11,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(161,'2024-12-02 05:28:33',13,2,11,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(162,'2024-12-02 05:29:20',13,2,11,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(163,'2024-12-02 09:43:47',NULL,NULL,NULL,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(164,'2024-12-02 09:43:49',15,2,13,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(165,'2024-12-02 09:43:50',15,2,13,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(166,'2024-12-02 09:43:53',15,2,13,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(167,'2024-12-02 09:53:17',15,2,13,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(168,'2024-12-02 09:56:21',15,2,13,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(169,'2024-12-02 10:54:39',NULL,NULL,NULL,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(170,'2024-12-02 10:54:40',18,2,16,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(171,'2024-12-02 10:58:53',18,2,16,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(172,'2024-12-02 10:58:57',NULL,NULL,NULL,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(173,'2024-12-02 10:58:58',18,2,16,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(174,'2024-12-02 10:58:58',18,2,16,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(175,'2024-12-02 10:58:59',18,2,16,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(176,'2024-12-02 11:11:14',NULL,NULL,NULL,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(177,'2024-12-06 09:56:47',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(178,'2024-12-06 09:57:21',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(179,'2024-12-06 09:57:35',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(180,'2024-12-06 09:58:08',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(181,'2025-01-09 11:05:18',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(182,'2025-01-09 11:05:28',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(183,'2025-01-09 11:06:04',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(184,'2025-01-09 11:08:29',21,2,19,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(185,'2025-01-09 11:16:43',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(186,'2025-01-09 11:16:50',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(187,'2025-01-09 11:20:57',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(188,'2025-01-09 11:27:20',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(189,'2025-01-09 11:30:08',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(190,'2025-01-09 11:34:00',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(191,'2025-01-09 12:39:55',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(192,'2025-01-09 12:41:19',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(193,'2025-01-09 12:42:17',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(194,'2025-01-09 12:46:46',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(195,'2025-01-09 12:53:58',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(196,'2025-01-09 12:54:32',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(197,'2025-01-10 10:00:19',NULL,NULL,NULL,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(198,'2025-01-10 10:00:56',NULL,NULL,NULL,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(199,'2025-01-10 10:00:56',21,2,19,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(200,'2025-01-10 10:04:30',21,2,19,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(201,'2025-01-10 10:04:34',NULL,NULL,NULL,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(202,'2025-01-10 10:04:35',21,2,19,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(203,'2025-01-10 10:04:35',21,2,19,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(204,'2025-01-10 10:04:36',21,2,19,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(205,'2025-01-10 10:05:16',21,2,19,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(206,'2025-01-10 10:05:28',21,2,19,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(207,'2025-01-10 10:05:38',21,2,19,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(208,'2025-01-10 11:24:04',NULL,NULL,NULL,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(209,'2025-01-10 11:33:20',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(210,'2025-01-11 06:54:03',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(211,'2025-01-11 07:02:01',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(212,'2025-01-11 07:59:13',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(213,'2025-01-11 08:08:11',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(214,'2025-01-11 08:10:49',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(215,'2025-01-11 08:13:17',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(216,'2025-01-11 10:09:21',21,2,19,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(217,'2025-01-11 10:32:17',21,2,19,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(218,'2025-01-11 10:56:06',21,2,19,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(219,'2025-01-11 10:56:10',NULL,NULL,NULL,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(220,'2025-01-11 10:56:11',21,2,19,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(221,'2025-01-11 10:56:11',21,2,19,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(222,'2025-01-11 10:56:12',21,2,19,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(223,'2025-01-11 11:02:00',21,2,19,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(224,'2025-01-11 11:02:04',NULL,NULL,NULL,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(225,'2025-01-11 11:02:07',21,2,19,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(226,'2025-01-11 11:02:07',21,2,19,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(227,'2025-01-11 11:02:08',21,2,19,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(228,'2025-01-11 11:12:21',21,2,19,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(229,'2025-01-11 11:12:43',21,2,19,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(230,'2025-01-11 11:16:48',NULL,NULL,NULL,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(231,'2025-01-11 11:16:48',21,2,19,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(232,'2025-01-11 11:19:03',21,2,19,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(233,'2025-01-11 11:19:18',21,2,19,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(234,'2025-01-11 12:19:36',21,2,19,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(235,'2025-01-16 13:57:31',24,2,21,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(236,'2025-03-08 07:16:16',21,2,19,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(237,'2025-03-08 07:16:28',21,2,19,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(238,'2025-03-08 08:15:08',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(239,'2025-03-08 08:15:16',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(240,'2025-03-08 08:15:24',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(241,'2025-03-08 08:16:41',21,2,19,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(242,'2025-03-08 08:16:45',21,2,19,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(243,'2025-03-10 10:11:28',21,2,19,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(244,'2025-03-10 10:11:32',21,2,19,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(245,'2025-03-11 12:57:25',21,2,19,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(246,'2025-03-12 06:39:33',21,2,19,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(247,'2025-03-12 08:19:10',21,2,19,'{\"message\":\"vendor settings updated\",\"data\":\"[]\"}'),
(248,'2025-04-03 05:17:39',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(249,'2025-04-07 06:19:01',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(250,'2025-04-07 06:19:34',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(251,'2025-04-11 05:00:01',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(252,'2025-04-11 05:00:05',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(253,'2025-04-11 05:00:27',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(254,'2025-04-28 06:54:22',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(255,'2025-04-28 06:54:29',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}'),
(256,'2025-04-28 06:56:25',1,1,NULL,'{\"message\":\"Site configuration settings stored \\/ updated.\",\"data\":\"[]\"}');
/*!40000 ALTER TABLE `activity_logs` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `bot_flows`
--

DROP TABLE IF EXISTS `bot_flows`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bot_flows` (
  `_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `_uid` char(36) NOT NULL,
  `status` tinyint(3) unsigned DEFAULT NULL,
  `whatsapp_flow_id` varchar(255) DEFAULT NULL,
  `whatsapp_sync_status` varchar(255) NOT NULL DEFAULT 'pending',
  `whatsapp_sync_at` timestamp NULL DEFAULT NULL,
  `whatsapp_meta_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`whatsapp_meta_data`)),
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `title` varchar(150) NOT NULL,
  `vendors__id` int(10) unsigned NOT NULL,
  `__data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`__data`)),
  `start_trigger` varchar(255) DEFAULT NULL,
  `trigger_type` varchar(255) NOT NULL DEFAULT 'is',
  PRIMARY KEY (`_id`),
  UNIQUE KEY `_uid` (`_uid`),
  UNIQUE KEY `_uid_UNIQUE` (`_uid`),
  KEY `fk_bot_flows_vendors1_idx` (`vendors__id`),
  KEY `bot_flows_whatsapp_flow_id_index` (`whatsapp_flow_id`),
  KEY `bot_flows_whatsapp_sync_status_index` (`whatsapp_sync_status`),
  CONSTRAINT `fk_bot_flows_vendors1` FOREIGN KEY (`vendors__id`) REFERENCES `vendors` (`_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bot_flows`
--

LOCK TABLES `bot_flows` WRITE;
/*!40000 ALTER TABLE `bot_flows` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `bot_flows` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `bot_replies`
--

DROP TABLE IF EXISTS `bot_replies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bot_replies` (
  `_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `_uid` char(36) NOT NULL,
  `status` tinyint(3) unsigned DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `vendors__id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `reply_text` text NOT NULL,
  `trigger_type` varchar(45) DEFAULT NULL COMMENT 'contains,is',
  `reply_trigger` varchar(255) DEFAULT NULL,
  `priority_index` tinyint(3) unsigned DEFAULT NULL,
  `__data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`__data`)),
  `bot_flows__id` int(10) unsigned DEFAULT NULL,
  `bot_replies__id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`_id`),
  UNIQUE KEY `_uid_UNIQUE` (`_uid`),
  UNIQUE KEY `_uid` (`_uid`),
  KEY `fk_bot_replies_vendors1_idx` (`vendors__id`),
  KEY `fk_bot_replies_bot_flows1_idx` (`bot_flows__id`),
  KEY `fk_bot_replies_bot_replies1_idx` (`bot_replies__id`),
  CONSTRAINT `fk_bot_replies_bot_flows1` FOREIGN KEY (`bot_flows__id`) REFERENCES `bot_flows` (`_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_bot_replies_bot_replies1` FOREIGN KEY (`bot_replies__id`) REFERENCES `bot_replies` (`_id`) ON DELETE SET NULL ON UPDATE NO ACTION,
  CONSTRAINT `fk_bot_replies_vendors1` FOREIGN KEY (`vendors__id`) REFERENCES `vendors` (`_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bot_replies`
--

LOCK TABLES `bot_replies` WRITE;
/*!40000 ALTER TABLE `bot_replies` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `bot_replies` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `campaign_groups`
--

DROP TABLE IF EXISTS `campaign_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `campaign_groups` (
  `_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `_uid` char(36) NOT NULL,
  `status` tinyint(3) unsigned DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `campaigns__id` int(10) unsigned NOT NULL,
  `contact_groups__id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`_id`),
  UNIQUE KEY `_uid_UNIQUE` (`_uid`),
  UNIQUE KEY `_uid` (`_uid`),
  KEY `fk_campaign_groups_campaigns1_idx` (`campaigns__id`),
  KEY `fk_campaign_groups_contact_groups1_idx` (`contact_groups__id`),
  CONSTRAINT `fk_campaign_groups_campaigns1` FOREIGN KEY (`campaigns__id`) REFERENCES `campaigns` (`_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_campaign_groups_contact_groups1` FOREIGN KEY (`contact_groups__id`) REFERENCES `contact_groups` (`_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `campaign_groups`
--

LOCK TABLES `campaign_groups` WRITE;
/*!40000 ALTER TABLE `campaign_groups` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `campaign_groups` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `campaigns`
--

DROP TABLE IF EXISTS `campaigns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `campaigns` (
  `_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `_uid` char(36) NOT NULL,
  `status` tinyint(3) unsigned DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `title` varchar(255) NOT NULL,
  `whatsapp_templates__id` int(10) unsigned DEFAULT NULL,
  `scheduled_at` datetime DEFAULT NULL,
  `users__id` int(10) unsigned DEFAULT NULL,
  `vendors__id` int(10) unsigned NOT NULL,
  `template_name` varchar(255) DEFAULT NULL,
  `__data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`__data`)),
  `template_language` varchar(45) DEFAULT NULL,
  `timezone` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`_id`),
  UNIQUE KEY `_uid_UNIQUE` (`_uid`),
  UNIQUE KEY `_uid` (`_uid`),
  KEY `fk_campaigns_whatsapp_templates1_idx` (`whatsapp_templates__id`),
  KEY `fk_campaigns_users1_idx` (`users__id`),
  KEY `fk_campaigns_vendors1_idx` (`vendors__id`),
  CONSTRAINT `fk_campaigns_users1` FOREIGN KEY (`users__id`) REFERENCES `users` (`_id`) ON DELETE SET NULL ON UPDATE NO ACTION,
  CONSTRAINT `fk_campaigns_vendors1` FOREIGN KEY (`vendors__id`) REFERENCES `vendors` (`_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_campaigns_whatsapp_templates1` FOREIGN KEY (`whatsapp_templates__id`) REFERENCES `whatsapp_templates` (`_id`) ON DELETE SET NULL ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `campaigns`
--

LOCK TABLES `campaigns` WRITE;
/*!40000 ALTER TABLE `campaigns` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `campaigns` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `configurations`
--

DROP TABLE IF EXISTS `configurations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `configurations` (
  `_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `name` varchar(45) NOT NULL,
  `value` text DEFAULT NULL,
  `data_type` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`_id`),
  KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `configurations`
--

LOCK TABLES `configurations` WRITE;
/*!40000 ALTER TABLE `configurations` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `configurations` VALUES
(1,'2024-11-13 17:29:46','2024-11-13 17:29:46','logo_name','67f8a1d186ec9---67e134efc38f0-677fb298276c0-untitled-design-3.png',1),
(2,'2024-11-13 17:30:02','2024-11-13 17:30:02','small_logo_name','67f8a1d574b83---67dbcd0eebf6a-omxiconn.png',1),
(3,'2024-11-13 17:30:13','2024-11-13 17:30:13','favicon_name','67f8a1eb374a7---67dbcd0eebf6a-omxiconn.png',1),
(4,'2024-11-13 17:31:07','2024-11-13 17:31:07','name','OMX FLOW PANEL',1),
(5,'2024-11-13 17:31:07','2024-11-13 17:31:07','description','Welcome to OMX FLOW - Your Meta Verified Tech Solution Provider\r\nAt OMX FLOW, we bring you the power of Meta Verified technology through our streamlined user plan. Our standard plan is designed to cater to businesses of all sizes, offering a seamless integration of advanced tools and features to enhance your communication and automation needs.\r\n\r\nWhy Choose OMX FLOW?\r\nMeta Verified Excellence: Trusted and verified solutions for reliable performance.\r\nComprehensive Features: From bulk messaging to personalized outreach, auto-responders, and chatbots, we empower your business with cutting-edge tools.\r\nWhite-Label Solutions: Customize and brand your experience with our white-label offerings.\r\nUser-Friendly Interface: Simplified panel for easy navigation and usage.\r\nExceptional Support: Our team ensures smooth onboarding and continuous assistance.\r\nTransform your business communication today with OMX FLOW standard plan. Take the first step toward automation and growth.',1),
(6,'2024-11-13 17:31:07','2024-11-13 17:31:07','contact_email','support@omxdigital.in',1),
(7,'2024-11-13 17:31:07','2024-11-13 17:31:07','default_language','en',1),
(8,'2024-11-13 17:31:07','2024-11-13 17:31:07','timezone','Asia/Kolkata',1),
(9,'2024-11-13 18:22:52','2024-11-13 18:22:52','broadcast_connection_driver','pusher',1),
(10,'2024-11-13 18:22:52','2024-11-13 18:22:52','pusher_app_id','eyJpdiI6InZVVUd6SmZHZUxSUE1aZ3JzYmZmbHc9PSIsInZhbHVlIjoieEhLVmcrSE10dEZrK1pOOHFCQlJZQT09IiwibWFjIjoiMWJkMzFiMGM4OGY2MTdhNWZiMjlkOWVkY2M5ZmZlOWUzNTFlOWI0NGY4Y2VmZmE4ODEzZGZkMGIzMTY3NTRjZiIsInRhZyI6IiJ9',1),
(11,'2024-11-13 18:22:52','2024-11-13 18:22:52','pusher_app_key','eyJpdiI6IkQ4Y1pqM2laZmtiZkF3RHFDQzVJWEE9PSIsInZhbHVlIjoiUGRvTlQ2RUN4OGwxMjNwTThIeDZEdUxRV2RLdUN1MU8zNUl2d0loU21Pbz0iLCJtYWMiOiJmYTBlYWMxYmMyZmNlYjM0ZGMxNzNmMzY5Y2FmYmE5YTNkMmU4ZTNmNTk1YzY4YjhmZTMwZGY2NTcwZDk0MWI2IiwidGFnIjoiIn0=',1),
(12,'2024-11-13 18:22:52','2024-11-13 18:22:52','pusher_app_secret','eyJpdiI6ImY2T2xHUVZwdE93MGpLaC95d3BBR3c9PSIsInZhbHVlIjoiRXNycVVLUGdyNG4vekhqZWM5Mkt1TitEei9TUm5rRXZnK215b2JaNk1qTT0iLCJtYWMiOiJkYmZkN2IzMGExNjdhODUzZWIxZWNkOTRmNjg1NDU2YzQ5MTExYzk1ZmJjOTFkMWI0MjZhMDgwMTMwODczNWIxIiwidGFnIjoiIn0=',1),
(13,'2024-11-13 18:22:52','2024-11-13 18:22:52','pusher_app_cluster','eyJpdiI6IittZmxLWllQWWFKcnptMDk1K28xRWc9PSIsInZhbHVlIjoidVpSSGdYeTIzUVRWVnkwYW5SNDRrZz09IiwibWFjIjoiN2M5OTk0MjBlMGZkZmE1Mjg2OGZmZTVmYzI1ZTc2MTJhZGEwZGJmZWE4ZGMwNDA4ZTdkNDQzZjk5ZTk3ZGZkYSIsInRhZyI6IiJ9',1),
(14,'2024-11-13 18:25:10','2024-11-13 18:25:10','cron_setup_done_at','2024-11-13 18:22:53',1),
(15,'2024-11-13 18:26:02','2024-11-13 18:26:02','cron_setup_using_artisan_at','2024-11-13 18:26:02',1),
(16,'2024-11-13 18:30:38','2024-11-13 18:30:38','subscription_plans','{\"paid\":{\"plan_3\":{\"id\":\"plan_3\",\"enabled\":\"on\",\"popular\":false,\"title\":\"Ultimate\",\"trial_days\":0,\"features\":{\"contacts\":{\"description\":\"Contacts\",\"limit\":\"-1\"},\"campaigns\":{\"limit_duration\":\"monthly\",\"limit_duration_title\":\"Per Month\",\"description\":\"Campaigns\",\"limit\":\"-1\"},\"bot_replies\":{\"description\":\"Bot Replies\",\"limit\":\"-1\"},\"bot_flows\":{\"description\":\"Bot Flows\",\"limit\":\"-1\"},\"contact_custom_fields\":{\"description\":\"Contact Custom Fields\",\"limit\":\"-1\"},\"system_users\":{\"description\":\"Team Members\\/Agents\",\"limit\":\"-1\"},\"ai_chat_bot\":{\"type\":\"switch\",\"description\":\"AI Chat Bot\",\"limit\":\"1\"},\"api_access\":{\"type\":\"switch\",\"description\":\"API and Webhook Access\",\"limit\":\"1\"}},\"charges\":{\"monthly\":{\"title\":\"monthly\",\"enabled\":0,\"price_id\":null,\"charge\":30},\"yearly\":{\"title\":\"yearly\",\"enabled\":\"on\",\"price_id\":null,\"charge\":20000}}},\"plan_1\":{\"id\":\"plan_1\",\"enabled\":0,\"popular\":true,\"title\":\"Standard\",\"trial_days\":0,\"features\":{\"contacts\":{\"description\":\"Contacts\",\"limit\":\"-1\"},\"campaigns\":{\"limit_duration\":\"monthly\",\"limit_duration_title\":\"Per Month\",\"description\":\"Campaigns\",\"limit\":\"-1\"},\"bot_replies\":{\"description\":\"Bot Replies\",\"limit\":\"-1\"},\"bot_flows\":{\"description\":\"Bot Flows\",\"limit\":\"-1\"},\"contact_custom_fields\":{\"description\":\"Contact Custom Fields\",\"limit\":\"-1\"},\"system_users\":{\"description\":\"Team Members\\/Agents\",\"limit\":\"-1\"},\"ai_chat_bot\":{\"type\":\"switch\",\"description\":\"AI Chat Bot\",\"limit\":\"1\"},\"api_access\":{\"type\":\"switch\",\"description\":\"API and Webhook Access\",\"limit\":\"1\"}},\"charges\":{\"monthly\":{\"title\":\"monthly\",\"enabled\":0,\"price_id\":null,\"charge\":10},\"yearly\":{\"title\":\"yearly\",\"enabled\":\"on\",\"price_id\":null,\"charge\":6000}}},\"plan_2\":{\"id\":\"plan_2\",\"enabled\":\"on\",\"popular\":false,\"title\":\"Premium\",\"trial_days\":0,\"features\":{\"contacts\":{\"description\":\"Contacts\",\"limit\":\"1000\"},\"campaigns\":{\"limit_duration\":\"monthly\",\"limit_duration_title\":\"Per Month\",\"description\":\"Campaigns\",\"limit\":\"50\"},\"bot_replies\":{\"description\":\"Bot Replies\",\"limit\":\"100\"},\"bot_flows\":{\"description\":\"Bot Flows\",\"limit\":\"10\"},\"contact_custom_fields\":{\"description\":\"Contact Custom Fields\",\"limit\":\"100\"},\"system_users\":{\"description\":\"Team Members\\/Agents\",\"limit\":\"10\"},\"ai_chat_bot\":{\"type\":\"switch\",\"description\":\"AI Chat Bot\",\"limit\":\"1\"},\"api_access\":{\"type\":\"switch\",\"description\":\"API and Webhook Access\",\"limit\":\"1\"}},\"charges\":{\"monthly\":{\"title\":\"monthly\",\"enabled\":0,\"price_id\":null,\"charge\":20},\"yearly\":{\"title\":\"yearly\",\"enabled\":\"on\",\"price_id\":null,\"charge\":15000}}}},\"free\":{\"id\":\"free\",\"enabled\":\"on\",\"title\":\"Free Plan\",\"trial_days\":0,\"features\":{\"contacts\":{\"description\":\"Contacts\",\"limit\":\"10\"},\"campaigns\":{\"limit_duration\":\"monthly\",\"limit_duration_title\":\"Per Month\",\"description\":\"Campaigns\",\"limit\":\"5\"},\"bot_replies\":{\"description\":\"Bot Replies\",\"limit\":\"10\"},\"bot_flows\":{\"description\":\"Bot Flows\",\"limit\":\"10\"},\"contact_custom_fields\":{\"description\":\"Contact Custom Fields\",\"limit\":\"2\"},\"system_users\":{\"description\":\"Team Members\\/Agents\",\"limit\":\"5\"},\"ai_chat_bot\":{\"type\":\"switch\",\"description\":\"AI Chat Bot\",\"limit\":\"1\"},\"api_access\":{\"type\":\"switch\",\"description\":\"API and Webhook Access\",\"limit\":\"1\"}}}}',4),
(17,'2024-11-13 19:56:23','2024-11-13 19:56:23','currency','INR',1),
(18,'2024-11-13 19:56:23','2024-11-13 19:56:23','currency_symbol','&#8377;',1),
(19,'2024-11-13 19:56:23','2024-11-13 19:56:23','currency_value','INR',1),
(20,'2024-11-13 20:14:21','2024-11-13 20:14:21','enable_vendor_registration','1',2),
(21,'2024-11-13 20:14:21','2024-11-13 20:14:21','message_for_disabled_registration','',1),
(22,'2024-11-13 20:14:21','2024-11-13 20:14:21','activation_required_for_new_user','0',2),
(23,'2024-11-13 20:14:21','2024-11-13 20:14:21','send_welcome_email','0',2),
(24,'2024-11-13 20:14:21','2024-11-13 20:14:21','welcome_email_content','Welcome to OMX FLOW – your trusted platform for seamless WhatsApp marketing! We\'re thrilled to have you on board and can’t wait to help you leverage the power of WhatsApp to boost your business.\r\n\r\nHere’s what you can do next:\r\n\r\n🌟 Explore Your Dashboard\r\nLog in to your account to access powerful tools for bulk messaging, chat automation, and personalized marketing campaigns.\r\n\r\n🌟 Learn the Basics\r\nVisit our Getting Started Guide to understand how to use the platform effectively.\r\n\r\n🌟 Need Assistance?\r\nOur support team is here to help you every step of the way. Feel free to reach out anytime.\r\n\r\n🔒 Keep Your Account Secure\r\nWe prfioritize your privacy and data security. Always safeguard your login credentials.\r\n\r\nOnce again, welcome aboard! Let’s create amazing campaigns together.\r\n\r\nWarm Regards,\r\nTeam OMX FLOW\r\n📧 Email: support@omxdigital.in\r\n🌐 Website: https://omxflow.com',1),
(25,'2024-11-13 20:14:21','2024-11-13 20:14:21','disallow_disposable_emails','0',2),
(26,'2024-11-13 20:14:21','2024-11-13 20:14:21','user_terms','Terms and Conditions\r\nEffective Date: 01-01-2025\r\n\r\nWelcome to OMX Flow! These Terms and Conditions (\"Terms\") govern your use of our website (https://omxflow.com) and our services. By accessing or using our platform, you agree to comply with and be bound by these Terms.\r\n\r\n1. Acceptance of Terms\r\nBy using our website or services, you agree to these Terms and our Privacy Policy. If you do not agree, you must stop using the platform immediately.\r\n\r\n2. Eligibility\r\nYou must be at least 18 years old to use OMX Flow. By accessing the platform, you confirm that you meet this age requirement.\r\n\r\n3. Services Provided\r\nOMX Flow offers tools for WhatsApp marketing, including:\r\n\r\nBulk messaging\r\nChat automation\r\nPersonalized marketing campaigns\r\nTask delegation and performance tracking\r\nThe services are subject to change without prior notice.\r\n\r\n4. User Responsibilities\r\nWhen using OMX Flow, you agree to:\r\n\r\nProvide accurate information during account registration.\r\nUse the platform in compliance with applicable laws and regulations.\r\nRefrain from using the platform for unauthorized or harmful activities, including spamming, harassment, or fraudulent actions.\r\n5. Account Security\r\nYou are responsible for maintaining the confidentiality of your login credentials. OMX Flow will not be liable for any loss resulting from unauthorized access to your account.\r\n\r\n6. Payment and Refunds\r\nUsers agree to pay all fees associated with the selected services.\r\nPayments are non-refundable unless stated otherwise in a specific service agreement.\r\n7. Intellectual Property\r\nAll content on the OMX Flow website, including text, graphics, logos, and software, is the property of OMX Digital Pvt Ltd or its licensors. Unauthorized use is prohibited.\r\n\r\n8. Privacy\r\nWe prioritize your privacy. Please review our Privacy Policy to understand how we collect, use, and safeguard your information.\r\n\r\n9. Limitation of Liability\r\nOMX Flow is not liable for any indirect, incidental, or consequential damages arising from the use of our platform.\r\n\r\n10. Termination\r\nWe reserve the right to suspend or terminate your access to OMX Flow at our discretion, without prior notice, for violating these Terms or engaging in harmful activities.\r\n\r\n11. Governing Law\r\nThese Terms are governed by and construed in accordance with the laws of India.\r\n\r\n12. Changes to Terms\r\nWe may update these Terms from time to time. The latest version will always be available on our website.\r\n\r\n13. Contact Us\r\nFor questions about these Terms, reach out to us at:\r\n📧 Email: support@omxdigital.in',1),
(27,'2024-11-13 20:14:21','2024-11-13 20:14:21','vendor_terms','Terms and Conditions\r\nEffective Date: 01-01-2025\r\n\r\nWelcome to OMX Flow! These Terms and Conditions (\"Terms\") govern your use of our website (https://omxflow.com) and our services. By accessing or using our platform, you agree to comply with and be bound by these Terms.\r\n\r\n1. Acceptance of Terms\r\nBy using our website or services, you agree to these Terms and our Privacy Policy. If you do not agree, you must stop using the platform immediately.\r\n\r\n2. Eligibility\r\nYou must be at least 18 years old to use OMX Flow. By accessing the platform, you confirm that you meet this age requirement.\r\n\r\n3. Services Provided\r\nOMX Flow offers tools for WhatsApp marketing, including:\r\n\r\nBulk messaging\r\nChat automation\r\nPersonalized marketing campaigns\r\nTask delegation and performance tracking\r\nThe services are subject to change without prior notice.\r\n\r\n4. User Responsibilities\r\nWhen using OMX Flow, you agree to:\r\n\r\nProvide accurate information during account registration.\r\nUse the platform in compliance with applicable laws and regulations.\r\nRefrain from using the platform for unauthorized or harmful activities, including spamming, harassment, or fraudulent actions.\r\n5. Account Security\r\nYou are responsible for maintaining the confidentiality of your login credentials. OMX Flow will not be liable for any loss resulting from unauthorized access to your account.\r\n\r\n6. Payment and Refunds\r\nUsers agree to pay all fees associated with the selected services.\r\nPayments are non-refundable unless stated otherwise in a specific service agreement.\r\n7. Intellectual Property\r\nAll content on the OMX Flow website, including text, graphics, logos, and software, is the property of OMX Digital Pvt Ltd or its licensors. Unauthorized use is prohibited.\r\n\r\n8. Privacy\r\nWe prioritize your privacy. Please review our Privacy Policy to understand how we collect, use, and safeguard your information.\r\n\r\n9. Limitation of Liability\r\nOMX Flow is not liable for any indirect, incidental, or consequential damages arising from the use of our platform.\r\n\r\n10. Termination\r\nWe reserve the right to suspend or terminate your access to OMX Flow at our discretion, without prior notice, for violating these Terms or engaging in harmful activities.\r\n\r\n11. Governing Law\r\nThese Terms are governed by and construed in accordance with the laws of India.\r\n\r\n12. Changes to Terms\r\nWe may update these Terms from time to time. The latest version will always be available on our website.\r\n\r\n13. Contact Us\r\nFor questions about these Terms, reach out to us at:\r\n📧 Email: support@omxdigital.in',1),
(28,'2024-11-13 20:14:21','2024-11-13 20:14:21','privacy_policy','Privacy Policy\r\nEffective Date: 01-01-2025\r\n\r\nOMX Flow (\"we,\" \"our,\" or \"us\") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our website (https://omxflow.com) or use our services.\r\n\r\nBy accessing or using OMX Flow, you agree to the practices described in this Privacy Policy.\r\n\r\n1. Information We Collect\r\na. Personal Information\r\nWhen you register or interact with our platform, we may collect the following:\r\n\r\nName\r\nEmail address\r\nPhone number\r\nBilling and payment details\r\nb. Non-Personal Information\r\nWe also collect non-identifiable information such as:\r\n\r\nBrowser type and version\r\nIP address\r\nDevice information\r\nWebsite usage data (e.g., pages visited, time spent on the site)\r\nc. Cookies and Tracking Technologies\r\nWe use cookies and similar technologies to enhance your experience, analyze usage, and deliver personalized content.\r\n\r\n2. How We Use Your Information\r\nWe use the collected information for the following purposes:\r\n\r\nTo provide and improve our services.\r\nTo process transactions and send billing information.\r\nTo communicate updates, offers, and promotional content.\r\nTo ensure the security of your account.\r\nTo analyze website traffic and user behavior for optimization.\r\n3. How We Share Your Information\r\nWe do not sell, trade, or rent your personal information. However, we may share it under these circumstances:\r\n\r\nService Providers: With trusted third-party vendors who assist in delivering our services (e.g., payment processors, hosting providers).\r\nLegal Compliance: To comply with legal obligations or respond to valid legal requests.\r\nBusiness Transfers: In case of a merger, acquisition, or asset sale, your information may be transferred.\r\n4. Data Security\r\nWe implement robust security measures to protect your data, including encryption, firewalls, and secure access protocols. However, no method of transmission over the internet is entirely secure.\r\n\r\n5. Data Retention\r\nWe retain your personal data only as long as necessary for the purposes outlined in this Privacy Policy, unless a longer retention period is required by law.\r\n\r\n6. Your Rights\r\nYou have the following rights concerning your data:\r\n\r\nAccess: Request a copy of your personal data.\r\nCorrection: Update or correct inaccuracies in your data.\r\nDeletion: Request the deletion of your personal data.\r\nOpt-Out: Unsubscribe from marketing communications.\r\nTo exercise these rights, please contact us at support@omxdigital.in.\r\n\r\n7. Third-Party Links\r\nOur website may contain links to third-party websites. We are not responsible for the privacy practices of these external sites. Please review their privacy policies before engaging.\r\n\r\n8. Children’s Privacy\r\nOMX Flow is not intended for use by individuals under the age of 18. We do not knowingly collect information from children.\r\n\r\n9. Changes to This Privacy Policy\r\nWe may update this Privacy Policy periodically. Any changes will be posted on this page with the updated effective date.\r\n\r\n10. Contact Us\r\nIf you have questions or concerns about this Privacy Policy, please contact us:\r\n📧 Email: support@omxdigital.in',1),
(29,'2024-11-14 02:03:56','2024-11-14 02:03:56','use_env_default_email_settings','0',2),
(30,'2024-11-14 02:03:56','2024-11-14 02:03:56','mail_driver','smtp',1),
(31,'2024-11-14 02:03:56','2024-11-14 02:03:56','mail_from_address','support@bots.omxflow.com',1),
(32,'2024-11-14 02:03:56','2024-11-14 02:03:56','mail_from_name','OMX FLOW PANEL',1),
(33,'2024-11-14 02:03:56','2024-11-14 02:03:56','smtp_mail_port','465',3),
(34,'2024-11-14 02:03:56','2024-11-14 02:03:56','smtp_mail_host','smtp.hostinger.com',1),
(35,'2024-11-14 02:03:56','2024-11-14 02:03:56','smtp_mail_username','support@bots.omxflow.com',1),
(36,'2024-11-14 02:03:56','2024-11-14 02:03:56','smtp_mail_encryption','ssl',1),
(37,'2024-11-14 02:03:56','2024-11-14 02:03:56','smtp_mail_password_or_apikey','Smtp1.hostinger.com',1),
(38,'2024-11-14 02:03:56','2024-11-14 02:03:56','sparkpost_mail_password_or_apikey','',1),
(39,'2024-11-14 02:03:56','2024-11-14 02:03:56','mailgun_domain','',1),
(40,'2024-11-14 04:09:39','2024-11-14 04:09:39','contact_details','OMX DIGITAL PVT LTD\r\nPh: (M) +91 8170972754 \r\nWeb: omxflow.com | Email: support@omxdigital.in',1),
(41,'2024-11-14 04:14:08','2024-11-14 04:14:08','enable_upi_payment','1',2),
(42,'2024-11-14 04:14:08','2024-11-14 04:14:08','payment_upi_address','bhj@fgf',1),
(43,'2024-11-14 04:14:08','2024-11-14 04:14:08','payment_upi_customer_notes','',1),
(44,'2024-11-14 05:02:39','2024-11-14 05:02:39','current_home_page_view','outer-home-3',1),
(45,'2024-11-14 05:02:39','2024-11-14 05:02:39','other_home_page_url','',1),
(46,'2024-11-16 16:47:24','2024-11-16 16:47:24','disable_bg_image','1',2),
(47,'2024-11-16 16:47:24','2024-11-16 16:47:24','app_bg_color','#f2f4f7',1),
(48,'2024-11-16 16:47:24','2024-11-16 16:47:24','app_sidebar_bg_color','#ffffff',1),
(49,'2024-11-16 16:47:24','2024-11-16 16:47:24','app_sidebar_text_color','#212528',1),
(50,'2024-11-16 16:47:24','2024-11-16 16:47:24','app_bs_color_primary','#75c38c',1),
(51,'2024-11-16 16:47:24','2024-11-16 16:47:24','app_bs_color_default','#172b4d',1),
(52,'2024-11-16 16:47:24','2024-11-16 16:47:24','app_bs_color_secondary','#6c757d',1),
(53,'2024-11-16 16:47:24','2024-11-16 16:47:24','app_bs_color_danger','#ff1928',1),
(54,'2024-11-16 16:47:24','2024-11-16 16:47:24','app_bs_color_light','#adb5bd',1),
(55,'2024-11-16 16:47:24','2024-11-16 16:47:24','app_bs_color_dark','#212528',1),
(56,'2024-11-16 16:47:24','2024-11-16 16:47:24','app_bs_color_warning','#ffc107',1),
(57,'2024-11-16 16:47:24','2024-11-16 16:47:24','app_bs_color_success','#28a745',1),
(58,'2024-11-16 16:47:24','2024-11-16 16:47:24','app_bs_color_muted','#8898aa',1),
(59,'2024-11-18 16:47:01','2024-11-18 16:47:01','enable_embedded_signup','1',2),
(60,'2024-11-18 16:47:01','2024-11-18 16:47:01','embedded_signup_app_id','eyJpdiI6ImJVeUpMRGtlaDJ0SU1LK0oyMjMwWVE9PSIsInZhbHVlIjoiOW9Leldnb1RCbS9nbThnUGtVU0lpNTVCdnI0TjhTNWZySzZ1dFVRek5GMD0iLCJtYWMiOiJmMjRmYjcxYWI0YTAwOGI1YTExNTY3YTgyNzBlNGI5NjdhOGFjNzRiMTQxNzRiMDA4ZTY3MzA0ODUzZWI1ZjEwIiwidGFnIjoiIn0=',1),
(61,'2024-11-18 16:47:01','2024-11-18 16:47:01','embedded_signup_app_secret','eyJpdiI6ImNnWFZ4M2I5Y2tqOXFHdTR6WU8yTVE9PSIsInZhbHVlIjoiYS82SzhOU3dUWC9uWG9PRnNieXpQRUxndi9jcllJNHpkbDIvT3lqRTlGaUJKdURZZHBzNDFGNnZoOWUxNE4vWCIsIm1hYyI6IjYwMTVlMzBlNzlhNWUwNTc0OWYxMzBkOTQ2ZmYwZWE2ZjA1MzEzMjUxNTQ2ZjdjNDYyZWZjMmIxZmE5ZDdmMjgiLCJ0YWciOiIifQ==',1),
(62,'2024-11-21 15:37:47','2024-11-21 15:37:47','embedded_signup_config_id','eyJpdiI6IldZaTZJQzU5MGZjVm5YTndHWTJ5VVE9PSIsInZhbHVlIjoiL1h0NWdOeml2M09YUFdsSDE1ZTFCSm5YMXErck9SNXNkY0VFaStySXRPMD0iLCJtYWMiOiIxZmVhNzA4NWUxOTlkZTM2YmNmYTFiMjNlMDlmNDg5Yjk3NDg3ZjE0OTc4MDVlM2RiZTEzYzBlOTM4YjZhMTJiIiwidGFnIjoiIn0=',1),
(63,'2024-11-21 15:49:20','2024-11-21 15:49:20','enable_whatsapp_manual_signup','1',2),
(64,'2024-11-22 11:23:48','2024-11-22 11:23:48','contacts_import_limit_per_request','10000',3),
(65,'2025-01-09 11:05:18','2025-01-09 11:05:18','queue_setup_done_at','2025-01-09 11:03:12',1),
(66,'2025-01-09 11:05:28','2025-01-09 11:05:28','cron_process_messages_per_lot','35',3),
(67,'2025-01-09 11:05:28','2025-01-09 11:05:28','enable_queue_jobs_for_campaigns','1',2);
/*!40000 ALTER TABLE `configurations` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `contact_custom_field_values`
--

DROP TABLE IF EXISTS `contact_custom_field_values`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `contact_custom_field_values` (
  `_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `_uid` char(36) NOT NULL,
  `status` tinyint(3) unsigned DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `contacts__id` int(10) unsigned NOT NULL,
  `contact_custom_fields__id` int(10) unsigned NOT NULL,
  `field_value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`_id`),
  UNIQUE KEY `_uid` (`_uid`),
  KEY `fk_contact_custom_field_values_contacts1_idx` (`contacts__id`),
  KEY `fk_contact_custom_field_values_contact_custom_fields1_idx` (`contact_custom_fields__id`),
  CONSTRAINT `fk_contact_custom_field_values_contact_custom_fields1` FOREIGN KEY (`contact_custom_fields__id`) REFERENCES `contact_custom_fields` (`_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_contact_custom_field_values_contacts1` FOREIGN KEY (`contacts__id`) REFERENCES `contacts` (`_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contact_custom_field_values`
--

LOCK TABLES `contact_custom_field_values` WRITE;
/*!40000 ALTER TABLE `contact_custom_field_values` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `contact_custom_field_values` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `contact_custom_fields`
--

DROP TABLE IF EXISTS `contact_custom_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `contact_custom_fields` (
  `_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `_uid` char(36) NOT NULL,
  `status` tinyint(3) unsigned DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `input_name` varchar(255) NOT NULL,
  `input_type` varchar(15) DEFAULT NULL COMMENT 'Text,number,email etc',
  `vendors__id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`_id`),
  UNIQUE KEY `_uid_UNIQUE` (`_uid`),
  UNIQUE KEY `_uid` (`_uid`),
  KEY `fk_contact_custom_fields_vendors1_idx` (`vendors__id`),
  CONSTRAINT `fk_contact_custom_fields_vendors1` FOREIGN KEY (`vendors__id`) REFERENCES `vendors` (`_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contact_custom_fields`
--

LOCK TABLES `contact_custom_fields` WRITE;
/*!40000 ALTER TABLE `contact_custom_fields` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `contact_custom_fields` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `contact_groups`
--

DROP TABLE IF EXISTS `contact_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `contact_groups` (
  `_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `_uid` char(36) NOT NULL,
  `status` tinyint(3) unsigned DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` varchar(500) DEFAULT NULL,
  `vendors__id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`_id`),
  UNIQUE KEY `_uid_UNIQUE` (`_uid`),
  UNIQUE KEY `_uid` (`_uid`),
  KEY `fk_groups_vendors1_idx` (`vendors__id`),
  CONSTRAINT `fk_groups_vendors1` FOREIGN KEY (`vendors__id`) REFERENCES `vendors` (`_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contact_groups`
--

LOCK TABLES `contact_groups` WRITE;
/*!40000 ALTER TABLE `contact_groups` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `contact_groups` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `contact_labels`
--

DROP TABLE IF EXISTS `contact_labels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `contact_labels` (
  `_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `_uid` char(36) NOT NULL,
  `status` tinyint(3) unsigned DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `labels__id` int(10) unsigned NOT NULL,
  `contacts__id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`_id`),
  UNIQUE KEY `_uid_UNIQUE` (`_uid`),
  UNIQUE KEY `_uid` (`_uid`),
  KEY `fk_contact_labels_labels1_idx` (`labels__id`),
  KEY `fk_contact_labels_contacts1_idx` (`contacts__id`),
  CONSTRAINT `fk_contact_labels_contacts1` FOREIGN KEY (`contacts__id`) REFERENCES `contacts` (`_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_contact_labels_labels1` FOREIGN KEY (`labels__id`) REFERENCES `labels` (`_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contact_labels`
--

LOCK TABLES `contact_labels` WRITE;
/*!40000 ALTER TABLE `contact_labels` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `contact_labels` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `contacts`
--

DROP TABLE IF EXISTS `contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `contacts` (
  `_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `_uid` char(36) NOT NULL,
  `status` tinyint(3) unsigned DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `first_name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `last_name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `countries__id` smallint(5) unsigned DEFAULT NULL,
  `whatsapp_opt_out` tinyint(3) unsigned DEFAULT NULL,
  `phone_verified_at` datetime DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `email_verified_at` datetime DEFAULT NULL,
  `vendors__id` int(10) unsigned NOT NULL,
  `wa_id` varchar(45) DEFAULT NULL COMMENT 'Its phone number with country code without + or 0 prefix',
  `language_code` varchar(45) DEFAULT NULL,
  `disable_ai_bot` tinyint(3) unsigned DEFAULT NULL,
  `__data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`__data`)),
  `assigned_users__id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`_id`),
  UNIQUE KEY `_uid_UNIQUE` (`_uid`),
  UNIQUE KEY `_uid` (`_uid`),
  KEY `fk_contacts_countries1_idx` (`countries__id`),
  KEY `fk_contacts_vendors1_idx` (`vendors__id`),
  KEY `fk_contacts_users1_idx` (`assigned_users__id`),
  CONSTRAINT `fk_contacts_countries1` FOREIGN KEY (`countries__id`) REFERENCES `countries` (`_id`) ON DELETE SET NULL ON UPDATE NO ACTION,
  CONSTRAINT `fk_contacts_users1` FOREIGN KEY (`assigned_users__id`) REFERENCES `users` (`_id`) ON DELETE SET NULL ON UPDATE NO ACTION,
  CONSTRAINT `fk_contacts_vendors1` FOREIGN KEY (`vendors__id`) REFERENCES `vendors` (`_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contacts`
--

LOCK TABLES `contacts` WRITE;
/*!40000 ALTER TABLE `contacts` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `contacts` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `countries`
--

DROP TABLE IF EXISTS `countries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `countries` (
  `_id` smallint(5) unsigned NOT NULL,
  `iso_code` char(2) DEFAULT NULL,
  `name_capitalized` varchar(100) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `iso3_code` char(3) DEFAULT NULL,
  `iso_num_code` smallint(6) DEFAULT NULL,
  `phone_code` smallint(5) unsigned DEFAULT NULL,
  PRIMARY KEY (`_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `countries`
--

LOCK TABLES `countries` WRITE;
/*!40000 ALTER TABLE `countries` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `countries` VALUES
(1,'AF','AFGHANISTAN','Afghanistan','AFG',4,93),
(2,'AL','ALBANIA','Albania','ALB',8,355),
(3,'DZ','ALGERIA','Algeria','DZA',12,213),
(4,'AS','AMERICAN SAMOA','American Samoa','ASM',16,1684),
(5,'AD','ANDORRA','Andorra','AND',20,376),
(6,'AO','ANGOLA','Angola','AGO',24,244),
(7,'AI','ANGUILLA','Anguilla','AIA',660,1264),
(8,'AQ','ANTARCTICA','Antarctica',NULL,NULL,0),
(9,'AG','ANTIGUA AND BARBUDA','Antigua and Barbuda','ATG',28,1268),
(10,'AR','ARGENTINA','Argentina','ARG',32,54),
(11,'AM','ARMENIA','Armenia','ARM',51,374),
(12,'AW','ARUBA','Aruba','ABW',533,297),
(13,'AU','AUSTRALIA','Australia','AUS',36,61),
(14,'AT','AUSTRIA','Austria','AUT',40,43),
(15,'AZ','AZERBAIJAN','Azerbaijan','AZE',31,994),
(16,'BS','BAHAMAS','Bahamas','BHS',44,1242),
(17,'BH','BAHRAIN','Bahrain','BHR',48,973),
(18,'BD','BANGLADESH','Bangladesh','BGD',50,880),
(19,'BB','BARBADOS','Barbados','BRB',52,1246),
(20,'BY','BELARUS','Belarus','BLR',112,375),
(21,'BE','BELGIUM','Belgium','BEL',56,32),
(22,'BZ','BELIZE','Belize','BLZ',84,501),
(23,'BJ','BENIN','Benin','BEN',204,229),
(24,'BM','BERMUDA','Bermuda','BMU',60,1441),
(25,'BT','BHUTAN','Bhutan','BTN',64,975),
(26,'BO','BOLIVIA','Bolivia','BOL',68,591),
(27,'BA','BOSNIA AND HERZEGOVINA','Bosnia and Herzegovina','BIH',70,387),
(28,'BW','BOTSWANA','Botswana','BWA',72,267),
(29,'BV','BOUVET ISLAND','Bouvet Island',NULL,NULL,0),
(30,'BR','BRAZIL','Brazil','BRA',76,55),
(31,'IO','BRITISH INDIAN OCEAN TERRITORY','British Indian Ocean Territory',NULL,NULL,246),
(32,'BN','BRUNEI DARUSSALAM','Brunei Darussalam','BRN',96,673),
(33,'BG','BULGARIA','Bulgaria','BGR',100,359),
(34,'BF','BURKINA FASO','Burkina Faso','BFA',854,226),
(35,'BI','BURUNDI','Burundi','BDI',108,257),
(36,'KH','CAMBODIA','Cambodia','KHM',116,855),
(37,'CM','CAMEROON','Cameroon','CMR',120,237),
(38,'CA','CANADA','Canada','CAN',124,1),
(39,'CV','CAPE VERDE','Cape Verde','CPV',132,238),
(40,'KY','CAYMAN ISLANDS','Cayman Islands','CYM',136,1345),
(41,'CF','CENTRAL AFRICAN REPUBLIC','Central African Republic','CAF',140,236),
(42,'TD','CHAD','Chad','TCD',148,235),
(43,'CL','CHILE','Chile','CHL',152,56),
(44,'CN','CHINA','China','CHN',156,86),
(45,'CX','CHRISTMAS ISLAND','Christmas Island',NULL,NULL,61),
(46,'CC','COCOS (KEELING) ISLANDS','Cocos (Keeling) Islands',NULL,NULL,672),
(47,'CO','COLOMBIA','Colombia','COL',170,57),
(48,'KM','COMOROS','Comoros','COM',174,269),
(49,'CG','CONGO','Congo','COG',178,242),
(50,'CD','CONGO, THE DEMOCRATIC REPUBLIC OF THE','Congo, the Democratic Republic of the','COD',180,243),
(51,'CK','COOK ISLANDS','Cook Islands','COK',184,682),
(52,'CR','COSTA RICA','Costa Rica','CRI',188,506),
(53,'CI','COTE D\'IVOIRE','Cote D\'Ivoire','CIV',384,225),
(54,'HR','CROATIA','Croatia','HRV',191,385),
(55,'CU','CUBA','Cuba','CUB',192,53),
(56,'CY','CYPRUS','Cyprus','CYP',196,357),
(57,'CZ','CZECH REPUBLIC','Czech Republic','CZE',203,420),
(58,'DK','DENMARK','Denmark','DNK',208,45),
(59,'DJ','DJIBOUTI','Djibouti','DJI',262,253),
(60,'DM','DOMINICA','Dominica','DMA',212,1767),
(61,'DO','DOMINICAN REPUBLIC','Dominican Republic','DOM',214,1809),
(62,'EC','ECUADOR','Ecuador','ECU',218,593),
(63,'EG','EGYPT','Egypt','EGY',818,20),
(64,'SV','EL SALVADOR','El Salvador','SLV',222,503),
(65,'GQ','EQUATORIAL GUINEA','Equatorial Guinea','GNQ',226,240),
(66,'ER','ERITREA','Eritrea','ERI',232,291),
(67,'EE','ESTONIA','Estonia','EST',233,372),
(68,'ET','ETHIOPIA','Ethiopia','ETH',231,251),
(69,'FK','FALKLAND ISLANDS (MALVINAS)','Falkland Islands (Malvinas)','FLK',238,500),
(70,'FO','FAROE ISLANDS','Faroe Islands','FRO',234,298),
(71,'FJ','FIJI','Fiji','FJI',242,679),
(72,'FI','FINLAND','Finland','FIN',246,358),
(73,'FR','FRANCE','France','FRA',250,33),
(74,'GF','FRENCH GUIANA','French Guiana','GUF',254,594),
(75,'PF','FRENCH POLYNESIA','French Polynesia','PYF',258,689),
(76,'TF','FRENCH SOUTHERN TERRITORIES','French Southern Territories',NULL,NULL,0),
(77,'GA','GABON','Gabon','GAB',266,241),
(78,'GM','GAMBIA','Gambia','GMB',270,220),
(79,'GE','GEORGIA','Georgia','GEO',268,995),
(80,'DE','GERMANY','Germany','DEU',276,49),
(81,'GH','GHANA','Ghana','GHA',288,233),
(82,'GI','GIBRALTAR','Gibraltar','GIB',292,350),
(83,'GR','GREECE','Greece','GRC',300,30),
(84,'GL','GREENLAND','Greenland','GRL',304,299),
(85,'GD','GRENADA','Grenada','GRD',308,1473),
(86,'GP','GUADELOUPE','Guadeloupe','GLP',312,590),
(87,'GU','GUAM','Guam','GUM',316,1671),
(88,'GT','GUATEMALA','Guatemala','GTM',320,502),
(89,'GN','GUINEA','Guinea','GIN',324,224),
(90,'GW','GUINEA-BISSAU','Guinea-Bissau','GNB',624,245),
(91,'GY','GUYANA','Guyana','GUY',328,592),
(92,'HT','HAITI','Haiti','HTI',332,509),
(93,'HM','HEARD ISLAND AND MCDONALD ISLANDS','Heard Island and Mcdonald Islands',NULL,NULL,0),
(94,'VA','HOLY SEE (VATICAN CITY STATE)','Holy See (Vatican City State)','VAT',336,39),
(95,'HN','HONDURAS','Honduras','HND',340,504),
(96,'HK','HONG KONG','Hong Kong','HKG',344,852),
(97,'HU','HUNGARY','Hungary','HUN',348,36),
(98,'IS','ICELAND','Iceland','ISL',352,354),
(99,'IN','INDIA','India','IND',356,91),
(100,'ID','INDONESIA','Indonesia','IDN',360,62),
(101,'IR','IRAN, ISLAMIC REPUBLIC OF','Iran, Islamic Republic of','IRN',364,98),
(102,'IQ','IRAQ','Iraq','IRQ',368,964),
(103,'IE','IRELAND','Ireland','IRL',372,353),
(104,'IL','ISRAEL','Israel','ISR',376,972),
(105,'IT','ITALY','Italy','ITA',380,39),
(106,'JM','JAMAICA','Jamaica','JAM',388,1876),
(107,'JP','JAPAN','Japan','JPN',392,81),
(108,'JO','JORDAN','Jordan','JOR',400,962),
(109,'KZ','KAZAKHSTAN','Kazakhstan','KAZ',398,7),
(110,'KE','KENYA','Kenya','KEN',404,254),
(111,'KI','KIRIBATI','Kiribati','KIR',296,686),
(112,'KP','KOREA, DEMOCRATIC PEOPLE\'S REPUBLIC OF','Korea, Democratic People\'s Republic of','PRK',408,850),
(113,'KR','KOREA, REPUBLIC OF','Korea, Republic of','KOR',410,82),
(114,'KW','KUWAIT','Kuwait','KWT',414,965),
(115,'KG','KYRGYZSTAN','Kyrgyzstan','KGZ',417,996),
(116,'LA','LAO PEOPLE\'S DEMOCRATIC REPUBLIC','Lao People\'s Democratic Republic','LAO',418,856),
(117,'LV','LATVIA','Latvia','LVA',428,371),
(118,'LB','LEBANON','Lebanon','LBN',422,961),
(119,'LS','LESOTHO','Lesotho','LSO',426,266),
(120,'LR','LIBERIA','Liberia','LBR',430,231),
(121,'LY','LIBYAN ARAB JAMAHIRIYA','Libyan Arab Jamahiriya','LBY',434,218),
(122,'LI','LIECHTENSTEIN','Liechtenstein','LIE',438,423),
(123,'LT','LITHUANIA','Lithuania','LTU',440,370),
(124,'LU','LUXEMBOURG','Luxembourg','LUX',442,352),
(125,'MO','MACAO','Macao','MAC',446,853),
(126,'MK','MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF','Macedonia, the Former Yugoslav Republic of','MKD',807,389),
(127,'MG','MADAGASCAR','Madagascar','MDG',450,261),
(128,'MW','MALAWI','Malawi','MWI',454,265),
(129,'MY','MALAYSIA','Malaysia','MYS',458,60),
(130,'MV','MALDIVES','Maldives','MDV',462,960),
(131,'ML','MALI','Mali','MLI',466,223),
(132,'MT','MALTA','Malta','MLT',470,356),
(133,'MH','MARSHALL ISLANDS','Marshall Islands','MHL',584,692),
(134,'MQ','MARTINIQUE','Martinique','MTQ',474,596),
(135,'MR','MAURITANIA','Mauritania','MRT',478,222),
(136,'MU','MAURITIUS','Mauritius','MUS',480,230),
(137,'YT','MAYOTTE','Mayotte',NULL,NULL,269),
(138,'MX','MEXICO','Mexico','MEX',484,52),
(139,'FM','MICRONESIA, FEDERATED STATES OF','Micronesia, Federated States of','FSM',583,691),
(140,'MD','MOLDOVA, REPUBLIC OF','Moldova, Republic of','MDA',498,373),
(141,'MC','MONACO','Monaco','MCO',492,377),
(142,'MN','MONGOLIA','Mongolia','MNG',496,976),
(143,'MS','MONTSERRAT','Montserrat','MSR',500,1664),
(144,'MA','MOROCCO','Morocco','MAR',504,212),
(145,'MZ','MOZAMBIQUE','Mozambique','MOZ',508,258),
(146,'MM','MYANMAR','Myanmar','MMR',104,95),
(147,'NA','NAMIBIA','Namibia','NAM',516,264),
(148,'NR','NAURU','Nauru','NRU',520,674),
(149,'NP','NEPAL','Nepal','NPL',524,977),
(150,'NL','NETHERLANDS','Netherlands','NLD',528,31),
(151,'AN','NETHERLANDS ANTILLES','Netherlands Antilles','ANT',530,599),
(152,'NC','NEW CALEDONIA','New Caledonia','NCL',540,687),
(153,'NZ','NEW ZEALAND','New Zealand','NZL',554,64),
(154,'NI','NICARAGUA','Nicaragua','NIC',558,505),
(155,'NE','NIGER','Niger','NER',562,227),
(156,'NG','NIGERIA','Nigeria','NGA',566,234),
(157,'NU','NIUE','Niue','NIU',570,683),
(158,'NF','NORFOLK ISLAND','Norfolk Island','NFK',574,672),
(159,'MP','NORTHERN MARIANA ISLANDS','Northern Mariana Islands','MNP',580,1670),
(160,'NO','NORWAY','Norway','NOR',578,47),
(161,'OM','OMAN','Oman','OMN',512,968),
(162,'PK','PAKISTAN','Pakistan','PAK',586,92),
(163,'PW','PALAU','Palau','PLW',585,680),
(164,'PS','PALESTINIAN TERRITORY, OCCUPIED','Palestinian Territory, Occupied',NULL,NULL,970),
(165,'PA','PANAMA','Panama','PAN',591,507),
(166,'PG','PAPUA NEW GUINEA','Papua New Guinea','PNG',598,675),
(167,'PY','PARAGUAY','Paraguay','PRY',600,595),
(168,'PE','PERU','Peru','PER',604,51),
(169,'PH','PHILIPPINES','Philippines','PHL',608,63),
(170,'PN','PITCAIRN','Pitcairn','PCN',612,0),
(171,'PL','POLAND','Poland','POL',616,48),
(172,'PT','PORTUGAL','Portugal','PRT',620,351),
(173,'PR','PUERTO RICO','Puerto Rico','PRI',630,1787),
(174,'QA','QATAR','Qatar','QAT',634,974),
(175,'RE','REUNION','Reunion','REU',638,262),
(176,'RO','ROMANIA','Romania','ROM',642,40),
(177,'RU','RUSSIAN FEDERATION','Russian Federation','RUS',643,7),
(178,'RW','RWANDA','Rwanda','RWA',646,250),
(179,'SH','SAINT HELENA','Saint Helena','SHN',654,290),
(180,'KN','SAINT KITTS AND NEVIS','Saint Kitts and Nevis','KNA',659,1869),
(181,'LC','SAINT LUCIA','Saint Lucia','LCA',662,1758),
(182,'PM','SAINT PIERRE AND MIQUELON','Saint Pierre and Miquelon','SPM',666,508),
(183,'VC','SAINT VINCENT AND THE GRENADINES','Saint Vincent and the Grenadines','VCT',670,1784),
(184,'WS','SAMOA','Samoa','WSM',882,684),
(185,'SM','SAN MARINO','San Marino','SMR',674,378),
(186,'ST','SAO TOME AND PRINCIPE','Sao Tome and Principe','STP',678,239),
(187,'SA','SAUDI ARABIA','Saudi Arabia','SAU',682,966),
(188,'SN','SENEGAL','Senegal','SEN',686,221),
(190,'SC','SEYCHELLES','Seychelles','SYC',690,248),
(191,'SL','SIERRA LEONE','Sierra Leone','SLE',694,232),
(192,'SG','SINGAPORE','Singapore','SGP',702,65),
(193,'SK','SLOVAKIA','Slovakia','SVK',703,421),
(194,'SI','SLOVENIA','Slovenia','SVN',705,386),
(195,'SB','SOLOMON ISLANDS','Solomon Islands','SLB',90,677),
(196,'SO','SOMALIA','Somalia','SOM',706,252),
(197,'ZA','SOUTH AFRICA','South Africa','ZAF',710,27),
(198,'GS','SOUTH GEORGIA AND THE SOUTH SANDWICH ISLANDS','South Georgia and the South Sandwich Islands',NULL,NULL,0),
(199,'ES','SPAIN','Spain','ESP',724,34),
(200,'LK','SRI LANKA','Sri Lanka','LKA',144,94),
(201,'SD','SUDAN','Sudan','SDN',736,249),
(202,'SR','SURINAME','Suriname','SUR',740,597),
(203,'SJ','SVALBARD AND JAN MAYEN','Svalbard and Jan Mayen','SJM',744,47),
(204,'SZ','SWAZILAND','Swaziland','SWZ',748,268),
(205,'SE','SWEDEN','Sweden','SWE',752,46),
(206,'CH','SWITZERLAND','Switzerland','CHE',756,41),
(207,'SY','SYRIAN ARAB REPUBLIC','Syrian Arab Republic','SYR',760,963),
(208,'TW','TAIWAN, PROVINCE OF CHINA','Taiwan, Province of China','TWN',158,886),
(209,'TJ','TAJIKISTAN','Tajikistan','TJK',762,992),
(210,'TZ','TANZANIA, UNITED REPUBLIC OF','Tanzania, United Republic of','TZA',834,255),
(211,'TH','THAILAND','Thailand','THA',764,66),
(212,'TL','TIMOR-LESTE','Timor-Leste',NULL,NULL,670),
(213,'TG','TOGO','Togo','TGO',768,228),
(214,'TK','TOKELAU','Tokelau','TKL',772,690),
(215,'TO','TONGA','Tonga','TON',776,676),
(216,'TT','TRINIDAD AND TOBAGO','Trinidad and Tobago','TTO',780,1868),
(217,'TN','TUNISIA','Tunisia','TUN',788,216),
(218,'TR','TURKEY','Turkey','TUR',792,90),
(219,'TM','TURKMENISTAN','Turkmenistan','TKM',795,7370),
(220,'TC','TURKS AND CAICOS ISLANDS','Turks and Caicos Islands','TCA',796,1649),
(221,'TV','TUVALU','Tuvalu','TUV',798,688),
(222,'UG','UGANDA','Uganda','UGA',800,256),
(223,'UA','UKRAINE','Ukraine','UKR',804,380),
(224,'AE','UNITED ARAB EMIRATES','United Arab Emirates','ARE',784,971),
(225,'GB','UNITED KINGDOM','United Kingdom','GBR',826,44),
(226,'US','UNITED STATES','United States','USA',840,1),
(227,'UM','UNITED STATES MINOR OUTLYING ISLANDS','United States Minor Outlying Islands',NULL,NULL,1),
(228,'UY','URUGUAY','Uruguay','URY',858,598),
(229,'UZ','UZBEKISTAN','Uzbekistan','UZB',860,998),
(230,'VU','VANUATU','Vanuatu','VUT',548,678),
(231,'VE','VENEZUELA','Venezuela','VEN',862,58),
(232,'VN','VIET NAM','Viet Nam','VNM',704,84),
(233,'VG','VIRGIN ISLANDS, BRITISH','Virgin Islands, British','VGB',92,1284),
(234,'VI','VIRGIN ISLANDS, U.S.','Virgin Islands, U.s.','VIR',850,1340),
(235,'WF','WALLIS AND FUTUNA','Wallis and Futuna','WLF',876,681),
(236,'EH','WESTERN SAHARA','Western Sahara','ESH',732,212),
(237,'YE','YEMEN','Yemen','YEM',887,967),
(238,'ZM','ZAMBIA','Zambia','ZMB',894,260),
(239,'ZW','ZIMBABWE','Zimbabwe','ZWE',716,263),
(240,'RS','SERBIA','Serbia','SRB',688,381),
(241,'AP','ASIA PACIFIC REGION','Asia / Pacific Region','0',0,0),
(242,'ME','MONTENEGRO','Montenegro','MNE',499,382),
(243,'AX','ALAND ISLANDS','Aland Islands','ALA',248,358),
(244,'BQ','BONAIRE, SINT EUSTATIUS AND SABA','Bonaire, Sint Eustatius and Saba','BES',535,599),
(245,'CW','CURACAO','Curacao','CUW',531,599),
(246,'GG','GUERNSEY','Guernsey','GGY',831,44),
(247,'IM','ISLE OF MAN','Isle of Man','IMN',833,44),
(248,'JE','JERSEY','Jersey','JEY',832,44),
(249,'XK','KOSOVO','Kosovo','---',0,381),
(250,'BL','SAINT BARTHELEMY','Saint Barthelemy','BLM',652,590),
(251,'MF','SAINT MARTIN','Saint Martin','MAF',663,590),
(252,'SX','SINT MAARTEN','Sint Maarten','SXM',534,1),
(253,'SS','SOUTH SUDAN','South Sudan','SSD',728,211);
/*!40000 ALTER TABLE `countries` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `group_contacts`
--

DROP TABLE IF EXISTS `group_contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `group_contacts` (
  `_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `_uid` char(36) NOT NULL,
  `status` tinyint(3) unsigned DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `contact_groups__id` int(10) unsigned NOT NULL,
  `contacts__id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`_id`),
  UNIQUE KEY `_uid_UNIQUE` (`_uid`),
  UNIQUE KEY `_uid` (`_uid`),
  KEY `fk_group_contacts_contact_groups1_idx` (`contact_groups__id`),
  KEY `fk_group_contacts_contacts1_idx` (`contacts__id`),
  CONSTRAINT `fk_group_contacts_contact_groups1` FOREIGN KEY (`contact_groups__id`) REFERENCES `contact_groups` (`_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_group_contacts_contacts1` FOREIGN KEY (`contacts__id`) REFERENCES `contacts` (`_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `group_contacts`
--

LOCK TABLES `group_contacts` WRITE;
/*!40000 ALTER TABLE `group_contacts` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `group_contacts` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `labels`
--

DROP TABLE IF EXISTS `labels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `labels` (
  `_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `_uid` char(36) NOT NULL,
  `status` tinyint(3) unsigned DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `title` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `text_color` varchar(10) DEFAULT NULL,
  `bg_color` varchar(10) DEFAULT NULL,
  `vendors__id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`_id`),
  UNIQUE KEY `_uid_UNIQUE` (`_uid`),
  UNIQUE KEY `_uid` (`_uid`),
  KEY `fk_labels_vendors1_idx` (`vendors__id`),
  CONSTRAINT `fk_labels_vendors1` FOREIGN KEY (`vendors__id`) REFERENCES `vendors` (`_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `labels`
--

LOCK TABLES `labels` WRITE;
/*!40000 ALTER TABLE `labels` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `labels` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `login_attempts`
--

DROP TABLE IF EXISTS `login_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `login_attempts` (
  `_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `attempts` tinyint(4) NOT NULL,
  PRIMARY KEY (`_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `login_attempts`
--

LOCK TABLES `login_attempts` WRITE;
/*!40000 ALTER TABLE `login_attempts` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `login_attempts` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `login_logs`
--

DROP TABLE IF EXISTS `login_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `login_logs` (
  `_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `role` tinyint(4) NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  PRIMARY KEY (`_id`)
) ENGINE=InnoDB AUTO_INCREMENT=254 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `login_logs`
--

LOCK TABLES `login_logs` WRITE;
/*!40000 ALTER TABLE `login_logs` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `login_logs` VALUES
(1,'2024-11-13 17:28:59','2024-11-13 17:28:59','superadmin@yourdomain.com',1,1,'106.210.146.16'),
(2,'2024-11-13 17:52:49','2024-11-13 17:52:49','superadmin@yourdomain.com',1,1,'106.210.146.16'),
(3,'2024-11-13 17:56:11','2024-11-13 17:56:11','support@mtechsystems.co.in',2,2,'106.210.146.16'),
(4,'2024-11-13 18:35:07','2024-11-13 18:35:07','support@mtechsystems.co.in',2,2,'106.210.146.16'),
(5,'2024-11-13 20:32:56','2024-11-13 20:32:56','support@mtechsystems.co.in',2,2,'106.210.146.16'),
(6,'2024-11-14 01:48:23','2024-11-14 01:48:23','superadmin@yourdomain.com',1,1,'106.210.146.16'),
(7,'2024-11-14 04:44:21','2024-11-14 04:44:21','mkmayukadam@gmail.com',2,3,'106.220.161.86'),
(8,'2024-11-14 04:56:49','2024-11-14 04:56:49','admin@mtechsystems.co.in',1,1,'106.220.161.86'),
(9,'2024-11-14 05:15:42','2024-11-14 05:15:42','admin@mtechsystems.co.in',1,1,'106.220.161.86'),
(10,'2024-11-14 05:26:05','2024-11-14 05:26:05','support@mtechsystems.co.in',2,2,'106.220.161.86'),
(11,'2024-11-14 08:19:58','2024-11-14 08:19:58','admin@mtechsystems.co.in',1,1,'106.220.161.86'),
(12,'2024-11-14 10:41:52','2024-11-14 10:41:52','admin@mtechsystems.co.in',1,1,'106.220.161.86'),
(13,'2024-11-14 10:42:42','2024-11-14 10:42:42','support@mtechsystems.co.in',2,2,'106.220.161.86'),
(14,'2024-11-14 14:32:39','2024-11-14 14:32:39','admin@mtechsystems.co.in',1,1,'106.210.183.105'),
(15,'2024-11-14 14:41:56','2024-11-14 14:41:56','admin@mtechsystems.co.in',1,1,'106.210.183.105'),
(16,'2024-11-14 15:21:13','2024-11-14 15:21:13','support@mtechsystems.co.in',2,2,'106.210.183.105'),
(17,'2024-11-14 16:38:57','2024-11-14 16:38:57','admin@mtechsystems.co.in',1,1,'106.210.183.105'),
(18,'2024-11-15 06:05:32','2024-11-15 06:05:32','admin@mtechsystems.co.in',1,1,'110.227.0.29'),
(19,'2024-11-15 21:10:20','2024-11-15 21:10:20','admin@mtechsystems.co.in',1,1,'110.227.0.14'),
(20,'2024-11-16 10:16:51','2024-11-16 10:16:51','admin@mtechsystems.co.in',1,1,'106.220.89.206'),
(21,'2024-11-16 13:43:18','2024-11-16 13:43:18','admin@mtechsystems.co.in',1,1,'106.210.219.110'),
(22,'2024-11-16 15:53:56','2024-11-16 15:53:56','admin@mtechsystems.co.in',1,1,'106.210.219.110'),
(23,'2024-11-16 16:57:04','2024-11-16 16:57:04','admin@mtechsystems.co.in',1,1,'106.210.219.110'),
(24,'2024-11-16 17:00:53','2024-11-16 17:00:53','admin@mtechsystems.co.in',1,1,'106.210.219.110'),
(25,'2024-11-17 10:11:46','2024-11-17 10:11:46','admin@mtechsystems.co.in',1,1,'49.15.234.129'),
(26,'2024-11-17 11:07:51','2024-11-17 11:07:51','admin@mtechsystems.co.in',1,1,'49.15.234.129'),
(27,'2024-11-17 11:08:50','2024-11-17 11:08:50','admin@mtechsystems.co.in',1,1,'106.221.0.4'),
(28,'2024-11-18 09:11:40','2024-11-18 09:11:40','admin@mtechsystems.co.in',1,1,'110.227.0.27'),
(29,'2024-11-18 10:58:10','2024-11-18 10:58:10','admin@mtechsystems.co.in',1,1,'110.227.0.27'),
(30,'2024-11-18 12:51:19','2024-11-18 12:51:19','admin@mtechsystems.co.in',1,1,'110.227.0.27'),
(31,'2024-11-18 13:01:12','2024-11-18 13:01:12','admin@mtechsystems.co.in',1,1,'110.227.0.27'),
(32,'2024-11-18 15:45:52','2024-11-18 15:45:52','shubhammore003@gmail.com',2,4,'2401:4900:79d8:eb71:88b8:ff:fec3:bcd8'),
(33,'2024-11-18 16:03:53','2024-11-18 16:03:53','admin@mtechsystems.co.in',1,1,'110.227.0.27'),
(34,'2024-11-18 22:02:28','2024-11-18 22:02:28','admin@mtechsystems.co.in',1,1,'106.210.239.196'),
(35,'2024-11-18 22:57:24','2024-11-18 22:57:24','support@mtechsystems.co.in',2,2,'106.210.239.196'),
(36,'2024-11-18 23:56:59','2024-11-18 23:56:59','admin@mtechsystems.co.in',1,1,'106.210.239.196'),
(37,'2024-11-18 23:57:35','2024-11-18 23:57:35','support@mtechsystems.co.in',2,2,'106.210.239.196'),
(38,'2024-11-19 03:16:56','2024-11-19 03:16:56','admin@mtechsystems.co.in',1,1,'106.210.239.196'),
(39,'2024-11-19 06:11:31','2024-11-19 06:11:31','admin@mtechsystems.co.in',1,1,'106.210.239.196'),
(40,'2024-11-19 13:18:55','2024-11-19 13:18:55','admin@mtechsystems.co.in',1,1,'106.210.239.196'),
(41,'2024-11-19 14:04:42','2024-11-19 14:04:42','admin@mtechsystems.co.in',1,1,'106.210.239.196'),
(42,'2024-11-19 16:53:32','2024-11-19 16:53:32','admin@mtechsystems.co.in',1,1,'106.210.239.196'),
(43,'2024-11-19 17:25:30','2024-11-19 17:25:30','admin@mtechsystems.co.in',1,1,'106.216.251.144'),
(44,'2024-11-19 18:46:50','2024-11-19 18:46:50','admin@mtechsystems.co.in',1,1,'106.216.251.144'),
(45,'2024-11-19 19:05:06','2024-11-19 19:05:06','admin@mtechsystems.co.in',1,1,'106.216.251.144'),
(46,'2024-11-19 19:07:40','2024-11-19 19:07:40','admin@mtechsystems.co.in',1,1,'2409:4042:2e4d:77e1:1c30:6f96:c9ae:29c9'),
(47,'2024-11-19 19:49:02','2024-11-19 19:49:02','admin@mtechsystems.co.in',1,1,'2409:40c2:504c:fbd0:8000::'),
(48,'2024-11-19 20:45:53','2024-11-19 20:45:53','admin@mtechsystems.co.in',1,1,'106.193.218.40'),
(49,'2024-11-19 21:13:30','2024-11-19 21:13:30','admin@mtechsystems.co.in',1,1,'106.193.218.40'),
(50,'2024-11-19 21:16:59','2024-11-19 21:16:59','superadmin@yourdomain.com',1,1,'106.193.218.40'),
(51,'2024-11-19 21:47:07','2024-11-19 21:47:07','superadmin@yourdomain.com',1,1,'106.193.218.40'),
(52,'2024-11-20 04:28:58','2024-11-20 04:28:58','superadmin@yourdomain.com',1,1,'106.220.129.121'),
(53,'2024-11-20 13:35:00','2024-11-20 13:35:00','superadmin@yourdomain.com',1,1,'106.220.129.121'),
(54,'2024-11-21 15:25:33','2024-11-21 15:25:33','superadmin@yourdomain.com',1,1,'106.220.129.121'),
(55,'2024-11-21 15:45:22','2024-11-21 15:45:22','pratikadhikari003@gmail.com',2,5,'106.220.129.121'),
(56,'2024-11-21 16:00:48','2024-11-21 16:00:48','superadmin@yourdomain.com',1,1,'106.220.129.121'),
(57,'2024-11-21 17:41:06','2024-11-21 17:41:06','superadmin@yourdomain.com',1,1,'106.220.129.121'),
(58,'2024-11-21 17:41:49','2024-11-21 17:41:49','superadmin@yourdomain.com',1,1,'106.220.129.121'),
(59,'2024-11-21 18:26:45','2024-11-21 18:26:45','superadmin@yourdomain.com',1,1,'110.227.0.6'),
(60,'2024-11-21 18:27:06','2024-11-21 18:27:06','superadmin@yourdomain.com',1,1,'110.227.0.6'),
(61,'2024-11-22 06:55:17','2024-11-22 06:55:17','superadmin@yourdomain.com',1,1,'110.227.0.6'),
(62,'2024-11-22 13:22:01','2024-11-22 13:22:01','superadmin@yourdomain.com',1,1,'106.195.10.131'),
(63,'2024-11-22 13:56:29','2024-11-22 13:56:29','superadmin@yourdomain.com',1,1,'2409:4081:1c99:4267:549d:6d94:9e7f:695e'),
(64,'2024-11-22 16:32:01','2024-11-22 16:32:01','superadmin@yourdomain.com',1,1,'2409:4081:1c99:4267:549d:6d94:9e7f:695e'),
(65,'2024-11-22 20:20:14','2024-11-22 20:20:14','superadmin@yourdomain.com',1,1,'110.227.0.29'),
(66,'2024-11-23 04:09:18','2024-11-23 04:09:18','superadmin@yourdomain.com',1,1,'110.227.0.29'),
(67,'2024-11-23 05:01:53','2024-11-23 05:01:53','superadmin@yourdomain.com',1,1,'110.227.0.29'),
(68,'2024-11-23 06:11:00','2024-11-23 06:11:00','superadmin@yourdomain.com',1,1,'2409:4081:1c99:4267:ac67:5c5:b87b:1fc8'),
(69,'2024-11-23 10:21:05','2024-11-23 10:21:05','superadmin@yourdomain.com',1,1,'110.227.0.29'),
(70,'2024-11-23 10:50:02','2024-11-23 10:50:02','anjanicreations09@gmail.com',2,7,'2409:40d4:116:21a3:fc5e:eb9f:1e43:1209'),
(71,'2024-11-24 16:33:00','2024-11-24 16:33:00','superadmin@yourdomain.com',1,1,'106.220.190.14'),
(72,'2024-11-24 16:34:20','2024-11-24 16:34:20','admin@mtechsystems.co.in',1,1,'106.220.190.14'),
(73,'2024-11-24 18:10:31','2024-11-24 18:10:31','yogh84267@gmail.com',2,8,'2409:4042:2d92:40c4:4544:8057:bf8b:6a5d'),
(74,'2024-11-24 18:29:20','2024-11-24 18:29:20','yogh84267@gmail.com',2,8,'2409:4042:2d92:40c4:4544:8057:bf8b:6a5d'),
(75,'2024-11-24 18:43:09','2024-11-24 18:43:09','admin@mtechsystems.co.in',1,1,'106.220.190.14'),
(76,'2024-11-24 18:43:26','2024-11-24 18:43:26','admin@mtechsystems.co.in',1,1,'106.220.190.14'),
(77,'2024-11-24 18:52:51','2024-11-24 18:52:51','admin@mtechsystems.co.in',1,1,'106.220.190.14'),
(78,'2024-11-24 18:56:42','2024-11-24 18:56:42','yogh84267@gmail.com',2,8,'2409:4042:2d92:40c4:4544:8057:bf8b:6a5d'),
(79,'2024-11-24 19:00:19','2024-11-24 19:00:19','admin@mtechsystems.co.in',1,1,'106.220.190.14'),
(80,'2024-11-24 21:03:58','2024-11-24 21:03:58','yogh84267@gmail.com',1,8,'2409:4042:2d92:40c4:5c25:1598:f310:bd09'),
(81,'2024-11-24 21:08:15','2024-11-24 21:08:15','admin@mtechsystems.co.in',1,1,'106.220.190.14'),
(82,'2024-11-24 21:18:01','2024-11-24 21:18:01','admin@mtechsystems.co.in',1,1,'106.220.190.14'),
(83,'2024-11-24 21:46:05','2024-11-24 21:46:05','yogh84267@gmail.com',1,8,'2409:4042:2d92:40c4:5c25:1598:f310:bd09'),
(84,'2024-11-24 22:13:11','2024-11-24 22:13:11','admin@mtechsystems.co.in',1,1,'106.220.190.14'),
(85,'2024-11-24 23:23:56','2024-11-24 23:23:56','admin@mtechsystems.co.in',1,1,'106.220.190.14'),
(86,'2024-11-24 23:26:13','2024-11-24 23:26:13','support@mtechsystems.co.in',2,2,'106.220.190.14'),
(87,'2024-11-24 23:28:16','2024-11-24 23:28:16','support@mtechsystems.co.in',2,2,'106.220.190.14'),
(88,'2024-11-24 23:37:28','2024-11-24 23:37:28','support@mtechsystems.co.in',2,2,'106.220.190.14'),
(89,'2024-11-24 23:37:39','2024-11-24 23:37:39','support@mtechsystems.co.in',2,2,'106.220.190.14'),
(90,'2024-11-25 02:21:24','2024-11-25 02:21:24','admin@mtechsystems.co.in',1,1,'106.220.190.14'),
(91,'2024-11-25 03:12:34','2024-11-25 03:12:34','admin@mtechsystems.co.in',1,1,'106.220.190.14'),
(92,'2024-11-25 03:57:57','2024-11-25 03:57:57','admin@mtechsystems.co.in',1,1,'106.220.190.14'),
(93,'2024-11-25 04:17:54','2024-11-25 04:17:54','admin@mtechsystems.co.in',1,1,'106.220.190.14'),
(94,'2024-11-25 04:23:16','2024-11-25 04:23:16','admin@mtechsystems.co.in',1,1,'106.220.190.14'),
(95,'2024-11-25 04:49:18','2024-11-25 04:49:18','yogh84267@gmail.com',1,8,'2409:4042:2d92:40c4:5c25:1598:f310:bd09'),
(96,'2024-11-25 05:01:37','2024-11-25 05:01:37','admin@mtechsystems.co.in',1,1,'106.220.190.14'),
(97,'2024-11-25 05:14:37','2024-11-25 05:14:37','shubham@mtechsystems.co.in',3,9,'106.220.190.14'),
(98,'2024-11-25 05:18:19','2024-11-25 05:18:19','admin@mtechsystems.co.in',1,1,'106.220.190.14'),
(99,'2024-11-25 06:35:29','2024-11-25 06:35:29','admin@mtechsystems.co.in',1,1,'2409:4042:2d92:40c4:7963:403a:5236:df46'),
(100,'2024-11-25 06:44:41','2024-11-25 06:44:41','admin@mtechsystems.co.in',1,1,'106.220.190.14'),
(101,'2024-11-25 06:53:02','2024-11-25 06:53:02','admin@mtechsystems.co.in',1,1,'106.220.190.14'),
(102,'2024-11-25 06:53:40','2024-11-25 06:53:40','test@test.com',2,10,'106.220.190.14'),
(103,'2024-11-25 07:29:09','2024-11-25 07:29:09','admin@mtechsystems.co.in',1,1,'106.220.190.14'),
(104,'2024-11-25 07:53:48','2024-11-25 07:53:48','test@test.com',2,10,'106.220.190.14'),
(105,'2024-11-25 08:36:46','2024-11-25 08:36:46','admin@mtechsystems.co.in',1,1,'106.220.190.14'),
(106,'2024-11-25 09:47:44','2024-11-25 09:47:44','admin@mtechsystems.co.in',1,1,'106.220.190.14'),
(107,'2024-11-25 09:53:13','2024-11-25 09:53:13','admin@mtechsystems.co.in',1,1,'106.220.190.14'),
(108,'2024-11-25 09:58:26','2024-11-25 09:58:26','admin@mtechsystems.co.in',1,1,'2409:40c2:500a:f229:8000::'),
(109,'2024-11-25 09:58:37','2024-11-25 09:58:37','admin@mtechsystems.co.in',1,1,'106.220.190.14'),
(110,'2024-11-25 11:10:44','2024-11-25 11:10:44','admin@mtechsystems.co.in',1,1,'106.220.190.14'),
(111,'2024-11-25 13:30:07','2024-11-25 13:30:07','admin@mtechsystems.co.in',1,1,'106.220.190.14'),
(112,'2024-11-25 13:34:06','2024-11-25 13:34:06','support@mtechsystems.co.in',2,2,'106.220.190.14'),
(113,'2024-11-25 15:09:43','2024-11-25 15:09:43','admin@mtechsystems.co.in',1,1,'106.220.190.14'),
(114,'2024-11-25 16:22:19','2024-11-25 16:22:19','admin@mtechsystems.co.in',1,1,'106.220.190.14'),
(115,'2024-11-25 16:28:18','2024-11-25 16:28:18','support@mtechsystems.co.in',2,2,'106.220.190.14'),
(116,'2024-11-25 21:44:20','2024-11-25 21:44:20','support@mtechsystems.co.in',2,2,'106.220.190.14'),
(117,'2024-11-25 23:36:20','2024-11-25 23:36:20','support@mtechsystems.co.in',2,2,'106.220.190.14'),
(118,'2024-11-26 02:51:16','2024-11-26 02:51:16','admin@mtechsystems.co.in',1,1,'106.220.190.14'),
(119,'2024-11-26 02:54:22','2024-11-26 02:54:22','support@mtechsystems.co.in',2,2,'106.220.190.14'),
(120,'2024-11-26 03:19:09','2024-11-26 03:19:09','admin@mtechsystems.co.in',1,1,'106.220.190.14'),
(121,'2024-11-26 10:35:21','2024-11-26 10:35:21','admin@mtechsystems.co.in',1,1,'106.220.190.14'),
(122,'2024-11-26 10:59:18','2024-11-26 10:59:18','admin@mtechsystems.co.in',1,1,'2401:4900:79d2:a7cf:b0d7:8887:295:da9a'),
(123,'2024-11-26 11:48:00','2024-11-26 11:48:00','manisaibitspilani@gmail.com',2,11,'203.110.83.42'),
(124,'2024-11-26 14:24:16','2024-11-26 14:24:16','support@mtechsystems.co.in',2,2,'106.220.190.14'),
(125,'2024-11-26 16:40:04','2024-11-26 16:40:04','admin@mtechsystems.co.in',1,1,'2401:4900:79d2:a7cf:b0d7:8887:295:da9a'),
(126,'2024-11-26 16:58:05','2024-11-26 16:58:05','support@mtechsystems.co.in',2,2,'106.220.190.14'),
(127,'2024-11-26 17:38:50','2024-11-26 17:38:50','admin@mtechsystems.co.in',1,1,'106.220.190.14'),
(128,'2024-11-26 18:11:43','2024-11-26 18:11:43','support@mtechsystems.co.in',2,2,'2401:4900:1c45:c9f0:f0ea:2194:c66b:6900'),
(129,'2024-11-26 19:02:59','2024-11-26 19:02:59','admin@mtechsystems.co.in',1,1,'2409:4081:911:9d0f:7890:3dd2:9616:935d'),
(130,'2024-11-26 19:13:48','2024-11-26 19:13:48','admin@mtechsystems.co.in',1,1,'2409:4081:911:9d0f:7890:3dd2:9616:935d'),
(131,'2024-11-26 21:13:18','2024-11-26 21:13:18','pratikadhikari003@gmail.com',2,5,'106.220.190.14'),
(132,'2024-11-26 21:24:04','2024-11-26 21:24:04','support@mtechsystems.co.in',2,2,'106.220.190.14'),
(133,'2024-11-26 21:33:54','2024-11-26 21:33:54','pratikadhikari003@gmail.com',2,5,'106.220.190.14'),
(134,'2024-11-26 21:41:50','2024-11-26 21:41:50','support@mtechsystems.co.in',2,2,'106.220.190.14'),
(135,'2024-11-27 03:48:26','2024-11-27 03:48:26','admin@mtechsystems.co.in',1,1,'106.220.190.14'),
(136,'2024-11-27 04:05:54','2024-11-27 04:05:54','admin@mtechsystems.co.in',1,1,'106.220.190.14'),
(137,'2024-11-27 05:35:03','2024-11-27 05:35:03','admin@mtechsystems.co.in',1,1,'2409:4081:911:9d0f:7890:3dd2:9616:935d'),
(138,'2024-11-27 06:15:50','2024-11-27 06:15:50','admin@mtechsystems.co.in',1,1,'106.220.190.14'),
(139,'2024-11-27 09:56:40','2024-11-27 09:56:40','onlinefungame247@gmail.com',2,13,'2405:201:6009:f01f:edee:26bb:f7f:7dfb'),
(140,'2024-11-27 10:45:08','2024-11-27 10:45:08','admin@mtechsystems.co.in',1,1,'106.220.190.14'),
(141,'2024-11-27 11:02:46','2024-11-27 11:02:46','vjha.367@gmail.com',2,14,'2401:4900:1c00:3394:bc:9865:ee15:fe2'),
(142,'2024-11-27 14:34:36','2024-11-27 14:34:36','admin@mtechsystems.co.in',1,1,'106.220.190.14'),
(143,'2024-11-28 06:11:48','2024-11-28 06:11:48','admin@mtechsystems.co.in',1,1,'223.178.144.22'),
(144,'2024-11-28 08:10:44','2024-11-28 08:10:44','admin@mtechsystems.co.in',1,1,'223.178.144.22'),
(145,'2024-11-28 08:17:46','2024-11-28 08:17:46','admin@mtechsystems.co.in',1,1,'223.178.144.22'),
(146,'2024-11-28 08:32:10','2024-11-28 08:32:10','admin@mtechsystems.co.in',1,1,'223.178.144.22'),
(147,'2024-11-28 08:37:37','2024-11-28 08:37:37','admin@mtechsystems.co.in',1,1,'223.178.144.22'),
(148,'2024-11-28 09:31:46','2024-11-28 09:31:46','admin@mtechsystems.co.in',1,1,'223.178.144.22'),
(149,'2024-11-28 09:35:22','2024-11-28 09:35:22','admin@mtechsystems.co.in',1,1,'223.178.144.22'),
(150,'2024-11-28 09:36:35','2024-11-28 09:36:35','admin@mtechsystems.co.in',1,1,'223.178.144.22'),
(151,'2024-11-28 11:11:35','2024-11-28 11:11:35','admin@mtechsystems.co.in',1,1,'223.178.144.22'),
(152,'2024-11-28 11:14:08','2024-11-28 11:14:08','shital@cellx.in',2,15,'123.201.245.34'),
(153,'2024-11-28 17:49:32','2024-11-28 17:49:32','admin@mtechsystems.co.in',1,1,'223.178.144.22'),
(154,'2024-11-28 19:20:17','2024-11-28 19:20:17','support@mtechsystems.co.in',2,2,'223.178.144.22'),
(155,'2024-11-28 22:06:47','2024-11-28 22:06:47','admin@mtechsystems.co.in',1,1,'223.178.144.22'),
(156,'2024-11-28 22:09:13','2024-11-28 22:09:13','support@mtechsystems.co.in',2,2,'223.178.144.22'),
(157,'2024-11-29 05:22:55','2024-11-29 05:22:55','admin@mtechsystems.co.in',1,1,'106.193.80.13'),
(158,'2024-11-29 07:58:44','2024-11-29 07:58:44','shital@cellx.in',2,15,'219.91.175.91'),
(159,'2024-11-29 08:14:35','2024-11-29 08:14:35','shital@cellx.in',2,15,'219.91.175.91'),
(160,'2024-11-29 08:57:42','2024-11-29 08:57:42','onlinefungame247@gmail.com',2,13,'2405:201:6009:f01f:eda1:e19d:bcdc:f5be'),
(161,'2024-11-29 09:41:48','2024-11-29 09:41:48','admin@mtechsystems.co.in',1,1,'106.193.80.13'),
(162,'2024-11-29 09:45:23','2024-11-29 09:45:23','admin@mtechsystems.co.in',1,1,'106.193.80.13'),
(163,'2024-11-29 12:47:24','2024-11-29 12:47:24','onlinefungame247@gmail.com',2,13,'2405:201:6009:f01f:eda1:e19d:bcdc:f5be'),
(164,'2024-11-29 13:13:19','2024-11-29 13:13:19','admin@mtechsystems.co.in',1,1,'106.193.80.13'),
(165,'2024-11-29 13:18:16','2024-11-29 13:18:16','admin@mtechsystems.co.in',1,1,'106.193.80.13'),
(166,'2024-11-29 14:52:21','2024-11-29 14:52:21','admin@mtechsystems.co.in',1,1,'106.193.80.13'),
(167,'2024-11-29 15:46:01','2024-11-29 15:46:01','support@mtechsystems.co.in',2,2,'106.193.80.13'),
(168,'2024-11-29 16:34:14','2024-11-29 16:34:14','onlinefungame247@gmail.com',2,13,'2405:201:6019:5854:a9c8:c121:934a:78c5'),
(169,'2024-11-29 19:22:25','2024-11-29 19:22:25','support@mtechsystems.co.in',2,2,'49.15.230.81'),
(170,'2024-11-29 23:04:56','2024-11-29 23:04:56','admin@mtechsystems.co.in',1,1,'2409:40c2:5059:a6db:8000::'),
(171,'2024-11-30 06:21:51','2024-11-30 06:21:51','admin@mtechsystems.co.in',1,1,'106.220.85.238'),
(172,'2024-11-30 06:22:25','2024-11-30 06:22:25','admin@mtechsystems.co.in',1,1,'106.220.85.238'),
(173,'2024-11-30 07:44:35','2024-11-30 07:44:35','admin@mtechsystems.co.in',1,1,'106.220.85.238'),
(174,'2024-11-30 08:23:13','2024-11-30 08:23:13','admin@mtechsystems.co.in',1,1,'106.220.85.238'),
(175,'2024-11-30 08:53:40','2024-11-30 08:53:40','support@mtechsystems.co.in',2,2,'51.158.237.230'),
(176,'2024-11-30 09:04:25','2024-11-30 09:04:25','onlinefungame247@gmail.com',2,13,'2401:4900:3149:6f1b:48aa:4ef2:7174:f6d9'),
(177,'2024-11-30 09:07:34','2024-11-30 09:07:34','akshaygraphics789@gmail.com',2,16,'2401:4900:1c44:ebc7:2591:38b6:ce07:14e1'),
(178,'2024-11-30 09:09:00','2024-11-30 09:09:00','support@mtechsystems.co.in',2,2,'51.158.237.230'),
(179,'2024-11-30 09:18:10','2024-11-30 09:18:10','admin@mtechsystems.co.in',1,1,'106.220.85.238'),
(180,'2024-11-30 09:19:07','2024-11-30 09:19:07','admin@mtechsystems.co.in',1,1,'106.220.85.238'),
(181,'2024-11-30 09:20:04','2024-11-30 09:20:04','support@mtechsystems.co.in',2,2,'106.220.85.238'),
(182,'2024-11-30 09:21:47','2024-11-30 09:21:47','admin@mtechsystems.co.in',1,1,'106.220.85.238'),
(183,'2024-11-30 11:38:03','2024-11-30 11:38:03','admin@mtechsystems.co.in',1,1,'106.220.85.238'),
(184,'2024-11-30 11:59:01','2024-11-30 11:59:01','admin@mtechsystems.co.in',1,1,'106.220.85.238'),
(185,'2024-11-30 12:11:24','2024-11-30 12:11:24','admin@mtechsystems.co.in',1,1,'106.220.85.238'),
(186,'2024-11-30 13:33:18','2024-11-30 13:33:18','support@mtechsystems.co.in',2,2,'106.220.85.238'),
(187,'2024-11-30 16:21:40','2024-11-30 16:21:40','admin@mtechsystems.co.in',1,1,'106.220.85.238'),
(188,'2024-11-30 16:36:49','2024-11-30 16:36:49','admin@mtechsystems.co.in',1,1,'106.220.85.238'),
(189,'2024-11-30 16:57:31','2024-11-30 16:57:31','admin@mtechsystems.co.in',1,1,'2409:4042:2602:f010:f0a5:6fcf:a3b8:fa7c'),
(190,'2024-11-30 17:24:02','2024-11-30 17:24:02','admin@mtechsystems.co.in',1,1,'106.220.85.238'),
(191,'2024-11-30 18:32:27','2024-11-30 18:32:27','admin@mtechsystems.co.in',1,1,'106.220.85.238'),
(192,'2024-11-30 18:36:58','2024-11-30 18:36:58','support@mtechsystems.co.in',2,2,'106.220.85.238'),
(193,'2024-11-30 19:50:43','2024-11-30 19:50:43','admin@mtechsystems.co.in',1,1,'106.220.85.238'),
(194,'2024-11-30 19:51:50','2024-11-30 19:51:50','admin@mtechsystems.co.in',1,1,'106.220.85.238'),
(195,'2024-11-30 20:35:01','2024-11-30 20:35:01','support@mtechsystems.co.in',2,2,'106.220.85.238'),
(196,'2024-12-01 04:18:27','2024-12-01 04:18:27','admin@mtechsystems.co.in',1,1,'2409:4042:2602:f010:157f:ac04:9f18:e94a'),
(197,'2024-12-01 07:27:43','2024-12-01 07:27:43','admin@mtechsystems.co.in',1,1,'106.220.85.238'),
(198,'2024-12-01 19:26:57','2024-12-01 19:26:57','admin@mtechsystems.co.in',1,1,'106.193.80.15'),
(199,'2024-12-02 03:18:35','2024-12-02 03:18:35','admin@mtechsystems.co.in',1,1,'2409:4042:4e49:afcb:d004:627e:8176:f1f9'),
(200,'2024-12-02 05:25:50','2024-12-02 05:25:50','admin@mtechsystems.co.in',1,1,'106.193.80.15'),
(201,'2024-12-02 06:18:35','2024-12-02 06:18:35','info@agtsindia.com',2,17,'14.194.181.190'),
(202,'2024-12-02 06:34:44','2024-12-02 06:34:44','admin@mtechsystems.co.in',1,1,'110.227.16.25'),
(203,'2024-12-02 08:51:22','2024-12-02 08:51:22','shital@cellx.in',2,15,'219.91.175.69'),
(204,'2024-12-02 09:34:41','2024-12-02 09:34:41','info@agtsindia.com',2,17,'14.194.181.190'),
(205,'2024-12-02 09:40:20','2024-12-02 09:40:20','admin@mtechsystems.co.in',1,1,'110.227.16.25'),
(206,'2024-12-02 09:48:54','2024-12-02 09:48:54','admin@mtechsystems.co.in',1,1,'110.227.16.25'),
(207,'2024-12-02 11:05:40','2024-12-02 11:05:40','mr.ayansaha@gmail.com',2,19,'115.99.186.161'),
(208,'2024-12-06 09:54:44','2024-12-06 09:54:44','admin@mtechsystems.co.in',1,1,'106.193.246.238'),
(209,'2024-12-11 06:34:27','2024-12-11 06:34:27','admin@example.in',1,1,'106.193.96.26'),
(210,'2024-12-11 06:43:38','2024-12-11 06:43:38','test@test.com',2,20,'106.193.96.26'),
(211,'2025-01-09 11:02:12','2025-01-09 11:02:12','admin@example.in',1,1,'2401:4900:1c85:e82e:856d:1d9b:28d:41b'),
(212,'2025-01-09 11:32:31','2025-01-09 11:32:31','admin@example.in',1,1,'2401:4900:1c85:e82e:856d:1d9b:28d:41b'),
(213,'2025-01-09 12:13:35','2025-01-09 12:13:35','admin@example.in',1,1,'2401:4900:1c85:e82e:856d:1d9b:28d:41b'),
(214,'2025-01-10 05:11:24','2025-01-10 05:11:24','admin@example.in',1,1,'2401:4900:1c85:e82e:9ddb:cb37:32a5:e640'),
(215,'2025-01-10 09:50:38','2025-01-10 09:50:38','admin@example.in',1,1,'2401:4900:1c85:e82e:9ddb:cb37:32a5:e640'),
(216,'2025-01-10 10:01:35','2025-01-10 10:01:35','admin@example.in',1,1,'2401:4900:1c85:e82e:6842:e496:390d:fa70'),
(217,'2025-01-10 12:04:26','2025-01-10 12:04:26','admin@example.in',1,1,'106.210.205.170'),
(218,'2025-01-11 05:22:04','2025-01-11 05:22:04','admin@example.in',1,1,'2401:4900:1c00:59ed:6cd8:1099:f4bb:1847'),
(219,'2025-01-11 05:58:35','2025-01-11 05:58:35','admin@example.in',1,1,'2401:4900:1c00:59ed:6cd8:1099:f4bb:1847'),
(220,'2025-01-11 06:00:36','2025-01-11 06:00:36','admin@example.in',1,1,'2401:4900:1c00:59ed:6cd8:1099:f4bb:1847'),
(221,'2025-01-11 07:02:41','2025-01-11 07:02:41','admin@example.in',1,1,'2401:4900:1c00:59ed:918a:8715:1496:7014'),
(222,'2025-01-11 07:53:15','2025-01-11 07:53:15','admin@example.in',1,1,'2401:4900:1c00:59ed:a043:5a58:7fbe:1564'),
(223,'2025-01-11 07:55:35','2025-01-11 07:55:35','admin@example.in',1,1,'2401:4900:1c00:59ed:a043:5a58:7fbe:1564'),
(224,'2025-01-11 09:56:51','2025-01-11 09:56:51','admin@example.in',1,1,'2401:4900:1c00:59ed:918a:8715:1496:7014'),
(225,'2025-01-13 05:32:00','2025-01-13 05:32:00','admin@example.in',1,1,'2401:4900:1c00:59ed:4c50:3e89:317b:26ce'),
(226,'2025-01-13 06:38:37','2025-01-13 06:38:37','admin@example.in',1,1,'2401:4900:1c00:59ed:dc87:ac7f:d604:efdd'),
(227,'2025-01-13 07:15:54','2025-01-13 07:15:54','admin@example.in',1,1,'2401:4900:1c00:59ed:4c50:3e89:317b:26ce'),
(228,'2025-01-14 07:41:11','2025-01-14 07:41:11','admin@example.in',1,1,'2401:4900:1c84:38ee:8dc3:6b56:a55a:eb25'),
(229,'2025-01-15 10:14:43','2025-01-15 10:14:43','admin@example.in',1,1,'2401:4900:1c84:38ee:f0db:2558:e540:9fa'),
(230,'2025-01-16 13:57:06','2025-01-16 13:57:06','kishanpau09@gmail.com',2,24,'2a02:26f7:d6cc:6803:0:a98b:1839:f9f1'),
(231,'2025-01-17 09:46:41','2025-01-17 09:46:41','admin@example.in',1,1,'2409:40e1:1142:4f69:dfb3:494c:7e23:6b57'),
(232,'2025-03-08 07:15:33','2025-03-08 07:15:33','admin@example.in',1,1,'2401:4900:1c01:b4e:c4cb:c687:68e6:82ea'),
(233,'2025-03-08 07:17:43','2025-03-08 07:17:43','admin@example.in',1,1,'106.201.141.138'),
(234,'2025-03-13 06:00:07','2025-03-13 06:00:07','admin@example.in',1,1,'106.215.99.160'),
(235,'2025-03-13 08:18:37','2025-03-13 08:18:37','admin@example.in',1,1,'2401:4900:1c85:1663:d43d:ee35:7c3a:514e'),
(236,'2025-03-18 05:07:31','2025-03-18 05:07:31','admin@example.in',1,1,'122.163.43.233'),
(237,'2025-03-21 12:09:16','2025-03-21 12:09:16','admin@example.in',1,1,'152.59.166.100'),
(238,'2025-03-22 05:54:04','2025-03-22 05:54:04','admin@example.in',1,1,'223.235.112.165'),
(239,'2025-03-22 08:13:11','2025-03-22 08:13:11','admin@example.in',1,1,'2401:4900:1c85:bd12:702a:908c:6684:5d91'),
(240,'2025-03-25 11:14:34','2025-03-25 11:14:34','admin@example.in',1,1,'152.56.133.63'),
(241,'2025-03-29 07:21:08','2025-03-29 07:21:08','admin@example.in',1,1,'122.164.26.178'),
(242,'2025-04-01 04:49:54','2025-04-01 04:49:54','admin@example.in',1,1,'122.164.26.178'),
(243,'2025-04-01 10:29:32','2025-04-01 10:29:32','admin@example.in',1,1,'122.164.26.178'),
(244,'2025-04-03 05:17:31','2025-04-03 05:17:31','admin@example.in',1,1,'106.200.244.135'),
(245,'2025-04-03 09:58:01','2025-04-03 09:58:01','admin@example.in',1,1,'106.200.244.135'),
(246,'2025-04-07 06:15:40','2025-04-07 06:15:40','admin@example.in',1,1,'117.99.93.236'),
(247,'2025-04-07 12:54:22','2025-04-07 12:54:22','admin@example.in',1,1,'117.99.93.236'),
(248,'2025-04-08 06:19:17','2025-04-08 06:19:17','admin@example.in',1,1,'117.99.93.236'),
(249,'2025-04-10 20:46:26','2025-04-10 20:46:26','admin@example.in',1,1,'127.0.0.1'),
(250,'2025-04-11 04:55:19','2025-04-11 04:55:19','admin@example.in',1,1,'127.0.0.1'),
(251,'2025-04-22 05:04:19','2025-04-22 05:04:19','admin@example.in',1,1,'127.0.0.1'),
(252,'2025-04-28 06:49:33','2025-04-28 06:49:33','admin@example.in',1,1,'2401:4900:1c01:6d96:a265:3bf5:84d4:8aa3'),
(253,'2025-08-02 06:29:17','2025-08-02 06:29:17','admin@example.in',1,1,'127.0.0.1');
/*!40000 ALTER TABLE `login_logs` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `manual_subscriptions`
--

DROP TABLE IF EXISTS `manual_subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `manual_subscriptions` (
  `_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `_uid` char(36) NOT NULL,
  `status` varchar(10) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `plan_id` varchar(100) DEFAULT NULL,
  `ends_at` datetime DEFAULT NULL,
  `remarks` varchar(500) DEFAULT NULL,
  `vendors__id` int(10) unsigned NOT NULL,
  `charges` decimal(13,4) DEFAULT NULL,
  `__data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`__data`)),
  `charges_frequency` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`_id`),
  UNIQUE KEY `_uid` (`_uid`),
  KEY `fk_manual_subscriptions_vendors1_idx` (`vendors__id`),
  CONSTRAINT `fk_manual_subscriptions_vendors1` FOREIGN KEY (`vendors__id`) REFERENCES `vendors` (`_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `manual_subscriptions`
--

LOCK TABLES `manual_subscriptions` WRITE;
/*!40000 ALTER TABLE `manual_subscriptions` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `manual_subscriptions` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `message_labels`
--

DROP TABLE IF EXISTS `message_labels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `message_labels` (
  `_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `_uid` char(36) NOT NULL,
  `status` tinyint(3) unsigned DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `labels__id` int(10) unsigned NOT NULL,
  `whatsapp_message_logs__id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`_id`),
  UNIQUE KEY `_uid_UNIQUE` (`_uid`),
  UNIQUE KEY `_uid` (`_uid`),
  KEY `fk_message_labels_labels1_idx` (`labels__id`),
  KEY `fk_message_labels_whatsapp_message_logs1_idx` (`whatsapp_message_logs__id`),
  CONSTRAINT `fk_message_labels_labels1` FOREIGN KEY (`labels__id`) REFERENCES `labels` (`_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_message_labels_whatsapp_message_logs1` FOREIGN KEY (`whatsapp_message_logs__id`) REFERENCES `whatsapp_message_logs` (`_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `message_labels`
--

LOCK TABLES `message_labels` WRITE;
/*!40000 ALTER TABLE `message_labels` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `message_labels` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `migrations` VALUES
(1,'2025_06_10_040000_create_whatsapp_orders_table',1),
(2,'2025_06_10_040001_create_whatsapp_user_states_table',1),
(3,'2025_06_10_040002_create_whatsapp_payments_table',1),
(4,'2025_06_11_100000_add_manual_payment_status_to_whatsapp_orders',1),
(5,'2025_01_17_000000_update_bot_flows_for_new_structure',2),
(6,'2025_03_31_055528_create_flow_management_tables',2),
(7,'2025_06_10_030000_add_type_column_to_whatsapp_message_logs',2),
(8,'2025_06_10_032340_add_data_column_to_user_active_flows_table',2),
(9,'2025_06_10_033340_add_uuid_column_to_user_active_flows_table',2),
(10,'2025_07_18_000000_add_trigger_type_to_bot_flows',2),
(11,'2025_08_04_064048_create_shopify_integrations_table',2),
(12,'2025_08_04_064049_create_woocommerce_integrations_table',2),
(13,'2025_08_04_064057_create_shopify_orders_table',2),
(14,'2025_08_04_064058_create_woocommerce_orders_table',2),
(15,'2025_08_04_064108_create_shopify_order_notifications_table',2),
(16,'2025_08_04_064109_create_woocommerce_order_notifications_table',2);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `pages`
--

DROP TABLE IF EXISTS `pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pages` (
  `_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `_uid` char(36) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `status` tinyint(3) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `show_in_menu` tinyint(3) unsigned DEFAULT NULL,
  `content` text DEFAULT NULL,
  `type` tinyint(3) unsigned NOT NULL,
  `vendors__id` int(10) unsigned DEFAULT NULL,
  `slug` varchar(255) NOT NULL,
  `image_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`_id`),
  UNIQUE KEY `_uid_UNIQUE` (`_uid`),
  UNIQUE KEY `title_UNIQUE` (`title`),
  UNIQUE KEY `_uid` (`_uid`),
  KEY `fk_pages_vendors1_idx` (`vendors__id`),
  CONSTRAINT `fk_pages_vendors1` FOREIGN KEY (`vendors__id`) REFERENCES `vendors` (`_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pages`
--

LOCK TABLES `pages` WRITE;
/*!40000 ALTER TABLE `pages` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `pages` VALUES
(1,'09035dc7-ee9f-40dd-90df-06a986f34584','2024-11-13 20:23:31','2025-01-09 12:44:23',1,'Privacy Policy',1,'1. Introduction\r\nAt OMX FLOW , we are committed to protecting the privacy of our users. This Privacy Policy outlines how we collect, use, and protect personal data when you use our WhatsApp Business API platform.\r\n\r\n2. Data Collection\r\nUser Data: We collect information, including name, contact details, and payment information, during registration and use of the platform.\r\nUsage Data: We may collect technical data such as IP addresses, device types, and browsing patterns to improve platform functionality and ensure security.\r\nEnd-User Data: Users are responsible for obtaining consent from end-users before collecting or processing any personal information through WhatsApp messages.\r\n3. Data Usage\r\nService Provision: We use collected data to provide, support, and improve our WhatsApp Business API services.\r\nCommunication: We may use your contact information to notify you of updates, service changes, or support-related communications.\r\nCompliance: We process data as needed to comply with legal and regulatory requirements, including Meta’s policies.\r\n4. Data Sharing\r\nWe do not sell, rent, or trade user data. However, we may share information with trusted third-party service providers who support our operations, strictly under confidentiality obligations.\r\nWe may disclose data if required by law or to protect our legal rights.\r\n5. Data Security\r\nWe implement security measures to protect against unauthorized access, disclosure, alteration, or destruction of personal data. Users are also responsible for securing their account credentials.\r\n6. Data Retention\r\nWe retain user data only for as long as necessary to fulfill the purposes for which it was collected or to comply with legal obligations.\r\n7. User Rights\r\nUsers may access, modify, or delete their data as permitted by applicable law. For assistance, please contact our support team.\r\n8. Changes to this Privacy Policy\r\nWe may update this Privacy Policy periodically. Users will be notified of significant changes, and continued use of the platform signifies acceptance of the revised policy.\r\n9. Contact Information\r\nFor questions about this Privacy Policy, contact us at:\r\n\r\nEmail: support@omxdigital.in\r\nAddress: Siliguri-734010',1,NULL,'privacy',NULL),
(2,'4917936f-a1bd-4dfb-8ae1-d9f04885bd18','2024-11-13 20:25:18','2024-11-13 20:25:51',1,'Terms And Conditions',1,'1. User Responsibilities\r\n- Users must use the Platform only for lawful purposes and follow Meta’s WhatsApp Business policies.\r\n- Fraudulent, deceptive, or misleading marketing practices are prohibited.\r\n\r\n2.  Account Security\r\n- Users are responsible for safeguarding their account credentials and must report unauthorized access immediately.\r\n\r\n3. Payments and Billing\r\n- Users agree to pay all service fees on time. Failure to pay may result in account suspension.\r\n\r\n4. Data Protection\r\n- Users must obtain necessary consent from end-users before sending WhatsApp messages and comply with all data privacy laws.\r\n\r\n5. Compliance with Meta Policies\r\n- Users must adhere to Meta’s guidelines to avoid suspension or termination of access to the WhatsApp Business API.',1,NULL,'terms',NULL);
/*!40000 ALTER TABLE `pages` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_resets` (
  `_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` datetime NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  PRIMARY KEY (`_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `password_resets` VALUES
(7,'2025-01-11 08:13:37','vinit.367@gmail.com','$2y$10$SfT5tpkemLXYoFABpQjdAeEUYzukdZGgKvJPIU0K.0nNXOBbEtbC2');
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `shopify_integrations`
--

DROP TABLE IF EXISTS `shopify_integrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `shopify_integrations` (
  `_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `_uid` varchar(255) NOT NULL,
  `vendors__id` int(10) unsigned NOT NULL,
  `shop_domain` varchar(255) NOT NULL,
  `access_token` text NOT NULL,
  `webhook_id` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `notification_types` text DEFAULT NULL,
  `webhook_url` varchar(255) DEFAULT NULL,
  `connected_at` timestamp NULL DEFAULT NULL,
  `last_sync_at` timestamp NULL DEFAULT NULL,
  `__data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`__data`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`_id`),
  UNIQUE KEY `shopify_integrations__uid_unique` (`_uid`),
  UNIQUE KEY `shopify_integrations_shop_domain_unique` (`shop_domain`),
  KEY `shopify_integrations_vendors__id_is_active_index` (`vendors__id`,`is_active`),
  KEY `shopify_integrations_shop_domain_index` (`shop_domain`),
  CONSTRAINT `shopify_integrations_vendors__id_foreign` FOREIGN KEY (`vendors__id`) REFERENCES `vendors` (`_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shopify_integrations`
--

LOCK TABLES `shopify_integrations` WRITE;
/*!40000 ALTER TABLE `shopify_integrations` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `shopify_integrations` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `shopify_order_notifications`
--

DROP TABLE IF EXISTS `shopify_order_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `shopify_order_notifications` (
  `_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `shopify_orders__id` bigint(20) unsigned NOT NULL,
  `vendors__id` int(10) unsigned NOT NULL,
  `contacts__id` int(10) unsigned DEFAULT NULL,
  `notification_type` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `message_id` varchar(255) DEFAULT NULL,
  `whatsapp_message_id` varchar(255) DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `__data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`__data`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`_id`),
  KEY `shopify_order_notifications_contacts__id_foreign` (`contacts__id`),
  KEY `shopify_notifications_vendor_status` (`vendors__id`,`status`),
  KEY `shopify_notifications_vendor_type` (`vendors__id`,`notification_type`),
  KEY `shopify_notifications_order_type` (`shopify_orders__id`,`notification_type`),
  CONSTRAINT `shopify_order_notifications_contacts__id_foreign` FOREIGN KEY (`contacts__id`) REFERENCES `contacts` (`_id`) ON DELETE SET NULL,
  CONSTRAINT `shopify_order_notifications_shopify_orders__id_foreign` FOREIGN KEY (`shopify_orders__id`) REFERENCES `shopify_orders` (`_id`) ON DELETE CASCADE,
  CONSTRAINT `shopify_order_notifications_vendors__id_foreign` FOREIGN KEY (`vendors__id`) REFERENCES `vendors` (`_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shopify_order_notifications`
--

LOCK TABLES `shopify_order_notifications` WRITE;
/*!40000 ALTER TABLE `shopify_order_notifications` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `shopify_order_notifications` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `shopify_orders`
--

DROP TABLE IF EXISTS `shopify_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `shopify_orders` (
  `_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `shopify_integrations__id` bigint(20) unsigned NOT NULL,
  `vendors__id` int(10) unsigned NOT NULL,
  `contacts__id` int(10) unsigned DEFAULT NULL,
  `shopify_order_id` varchar(255) NOT NULL,
  `order_number` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `currency` varchar(3) NOT NULL DEFAULT 'USD',
  `financial_status` varchar(255) NOT NULL DEFAULT 'pending',
  `fulfillment_status` varchar(255) NOT NULL DEFAULT 'unfulfilled',
  `total_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `subtotal_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_tax` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_discounts` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_weight` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_items` int(11) NOT NULL DEFAULT 0,
  `tags` text DEFAULT NULL,
  `note` text DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'open',
  `processed_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `closed_at` timestamp NULL DEFAULT NULL,
  `created_at_shopify` timestamp NULL DEFAULT NULL,
  `updated_at_shopify` timestamp NULL DEFAULT NULL,
  `processed_at_shopify` timestamp NULL DEFAULT NULL,
  `cancelled_at_shopify` timestamp NULL DEFAULT NULL,
  `closed_at_shopify` timestamp NULL DEFAULT NULL,
  `__data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`__data`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`_id`),
  UNIQUE KEY `shopify_orders_shopify_order_id_unique` (`shopify_order_id`),
  KEY `shopify_orders_shopify_integrations__id_foreign` (`shopify_integrations__id`),
  KEY `shopify_orders_contacts__id_foreign` (`contacts__id`),
  KEY `shopify_orders_vendors__id_status_index` (`vendors__id`,`status`),
  KEY `shopify_orders_vendors__id_financial_status_index` (`vendors__id`,`financial_status`),
  KEY `shopify_orders_vendors__id_fulfillment_status_index` (`vendors__id`,`fulfillment_status`),
  KEY `shopify_orders_phone_vendors__id_index` (`phone`,`vendors__id`),
  KEY `shopify_orders_email_vendors__id_index` (`email`,`vendors__id`),
  CONSTRAINT `shopify_orders_contacts__id_foreign` FOREIGN KEY (`contacts__id`) REFERENCES `contacts` (`_id`) ON DELETE SET NULL,
  CONSTRAINT `shopify_orders_shopify_integrations__id_foreign` FOREIGN KEY (`shopify_integrations__id`) REFERENCES `shopify_integrations` (`_id`) ON DELETE CASCADE,
  CONSTRAINT `shopify_orders_vendors__id_foreign` FOREIGN KEY (`vendors__id`) REFERENCES `vendors` (`_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shopify_orders`
--

LOCK TABLES `shopify_orders` WRITE;
/*!40000 ALTER TABLE `shopify_orders` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `shopify_orders` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `subscription_items`
--

DROP TABLE IF EXISTS `subscription_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `subscription_items` (
  `id` bigint(19) unsigned NOT NULL AUTO_INCREMENT,
  `stripe_id` varchar(255) NOT NULL,
  `stripe_product` varchar(255) DEFAULT NULL,
  `stripe_price` varchar(255) NOT NULL,
  `quantity` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `subscription_id` bigint(19) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stripe_plan_UNIQUE` (`stripe_price`,`subscription_id`),
  KEY `subscription_items_stripe_id_index` (`stripe_id`),
  KEY `fk_subscription_items_subscriptions1_idx` (`subscription_id`),
  KEY `stripe_id` (`stripe_id`),
  CONSTRAINT `fk_subscription_items_subscriptions1` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subscription_items`
--

LOCK TABLES `subscription_items` WRITE;
/*!40000 ALTER TABLE `subscription_items` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `subscription_items` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `subscriptions`
--

DROP TABLE IF EXISTS `subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `subscriptions` (
  `id` bigint(19) unsigned NOT NULL AUTO_INCREMENT,
  `vendor_model__id` bigint(19) unsigned NOT NULL,
  `type` varchar(255) NOT NULL,
  `stripe_id` varchar(255) NOT NULL,
  `stripe_status` varchar(255) NOT NULL,
  `stripe_price` varchar(255) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `trial_ends_at` timestamp NULL DEFAULT NULL,
  `ends_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subscriptions_user_id_stripe_status_index` (`vendor_model__id`,`stripe_status`),
  KEY `stripe_status` (`stripe_status`),
  KEY `vendor_model__id` (`vendor_model__id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subscriptions`
--

LOCK TABLES `subscriptions` WRITE;
/*!40000 ALTER TABLE `subscriptions` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `subscriptions` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `tickets`
--

DROP TABLE IF EXISTS `tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tickets` (
  `_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `_uid` char(36) NOT NULL,
  `status` tinyint(3) unsigned DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `contacts__id` int(10) unsigned NOT NULL,
  `vendors__id` int(10) unsigned NOT NULL,
  `subject` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `priority` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `vendor_users__id` int(10) unsigned DEFAULT NULL,
  `__data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`__data`)),
  `assigned_users__id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`_id`),
  UNIQUE KEY `_uid_UNIQUE` (`_uid`),
  UNIQUE KEY `_uid` (`_uid`),
  KEY `fk_tickets_contacts1_idx` (`contacts__id`),
  KEY `fk_tickets_vendors1_idx` (`vendors__id`),
  KEY `fk_tickets_vendor_users1_idx` (`vendor_users__id`),
  KEY `fk_tickets_users1_idx` (`assigned_users__id`),
  CONSTRAINT `fk_tickets_contacts1` FOREIGN KEY (`contacts__id`) REFERENCES `contacts` (`_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_tickets_users1` FOREIGN KEY (`assigned_users__id`) REFERENCES `users` (`_id`) ON DELETE SET NULL ON UPDATE NO ACTION,
  CONSTRAINT `fk_tickets_vendor_users1` FOREIGN KEY (`vendor_users__id`) REFERENCES `vendor_users` (`_id`) ON DELETE SET NULL ON UPDATE NO ACTION,
  CONSTRAINT `fk_tickets_vendors1` FOREIGN KEY (`vendors__id`) REFERENCES `vendors` (`_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tickets`
--

LOCK TABLES `tickets` WRITE;
/*!40000 ALTER TABLE `tickets` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `tickets` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `transactions` (
  `_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `_uid` char(36) NOT NULL,
  `status` tinyint(3) unsigned DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `amount` decimal(13,4) DEFAULT NULL,
  `reference_id` varchar(45) NOT NULL,
  `notes` varchar(500) DEFAULT NULL,
  `__data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`__data`)),
  `vendors__id` int(10) unsigned DEFAULT NULL,
  `subscriptions_id` bigint(19) unsigned DEFAULT NULL,
  `type` varchar(45) DEFAULT NULL,
  `manual_subscriptions__id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`_id`),
  UNIQUE KEY `_uid_UNIQUE` (`_uid`),
  UNIQUE KEY `reference_id_UNIQUE` (`reference_id`),
  UNIQUE KEY `_uid` (`_uid`),
  KEY `fk_transactions_vendors1_idx` (`vendors__id`),
  KEY `fk_transactions_subscriptions1_idx` (`subscriptions_id`),
  KEY `fk_transactions_manual_subscriptions1_idx` (`manual_subscriptions__id`),
  CONSTRAINT `fk_transactions_manual_subscriptions1` FOREIGN KEY (`manual_subscriptions__id`) REFERENCES `manual_subscriptions` (`_id`) ON DELETE SET NULL ON UPDATE NO ACTION,
  CONSTRAINT `fk_transactions_subscriptions1` FOREIGN KEY (`subscriptions_id`) REFERENCES `subscriptions` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION,
  CONSTRAINT `fk_transactions_vendors1` FOREIGN KEY (`vendors__id`) REFERENCES `vendors` (`_id`) ON DELETE SET NULL ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transactions`
--

LOCK TABLES `transactions` WRITE;
/*!40000 ALTER TABLE `transactions` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `transactions` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `user_active_flows`
--

DROP TABLE IF EXISTS `user_active_flows`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_active_flows` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `flow_id` bigint(20) unsigned NOT NULL,
  `current_node_uid` varchar(255) DEFAULT NULL,
  `next_node_uid` varchar(255) DEFAULT NULL,
  `phone_number` varchar(255) NOT NULL,
  `activated_at` timestamp NOT NULL,
  `__data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`__data`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_active_flows_user_id_phone_number_unique` (`user_id`,`phone_number`),
  KEY `user_active_flows_phone_number_index` (`phone_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_active_flows`
--

LOCK TABLES `user_active_flows` WRITE;
/*!40000 ALTER TABLE `user_active_flows` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `user_active_flows` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `user_roles`
--

DROP TABLE IF EXISTS `user_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_roles` (
  `_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `_uid` char(36) NOT NULL,
  `status` tinyint(3) unsigned NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`_id`),
  UNIQUE KEY `_uid_UNIQUE` (`_uid`),
  UNIQUE KEY `_uid` (`_uid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_roles`
--

LOCK TABLES `user_roles` WRITE;
/*!40000 ALTER TABLE `user_roles` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `user_roles` VALUES
(1,'15f21c9f-88bb-4fec-bad4-03eb9d9065f8',1,'2024-11-13 17:26:47','2024-11-13 17:26:47','Super Admin'),
(2,'287133c4-2afc-4f65-ab3c-28b0df8a099a',1,'2024-11-13 17:26:47','2024-11-13 17:26:47','Vendor Admin'),
(3,'30ee1967-4nfc-4f65-87bb-g2ea0722b178',1,'2024-11-13 17:26:47','2024-11-13 17:26:47','Vendor User');
/*!40000 ALTER TABLE `user_roles` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `user_settings`
--

DROP TABLE IF EXISTS `user_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_settings` (
  `_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `key_name` varchar(45) NOT NULL,
  `value` text DEFAULT NULL,
  `data_type` tinyint(4) DEFAULT NULL,
  `users__id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`_id`),
  KEY `name` (`key_name`),
  KEY `fk_user_settings_users1_idx` (`users__id`),
  CONSTRAINT `fk_user_settings_users1` FOREIGN KEY (`users__id`) REFERENCES `users` (`_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_settings`
--

LOCK TABLES `user_settings` WRITE;
/*!40000 ALTER TABLE `user_settings` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `user_settings` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `_uid` char(36) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `username` varchar(45) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `status` tinyint(3) unsigned NOT NULL,
  `remember_token` varchar(255) DEFAULT NULL,
  `first_name` varchar(45) NOT NULL,
  `last_name` varchar(45) DEFAULT NULL,
  `mobile_number` varchar(15) DEFAULT NULL COMMENT 'Make unique with country phone code',
  `timezone` varchar(45) DEFAULT NULL,
  `registered_via` varchar(15) DEFAULT NULL COMMENT 'Social account',
  `ban_reason` varchar(255) DEFAULT NULL,
  `countries__id` smallint(5) unsigned DEFAULT NULL,
  `two_factor_secret` text DEFAULT NULL,
  `two_factor_recovery_codes` text DEFAULT NULL,
  `email_verified_at` datetime DEFAULT NULL,
  `user_roles__id` tinyint(3) unsigned NOT NULL,
  `vendors__id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`_id`),
  UNIQUE KEY `_uid_UNIQUE` (`_uid`),
  UNIQUE KEY `email_UNIQUE` (`email`),
  KEY `fk_users_countries1_idx` (`countries__id`),
  KEY `fk_users_user_roles1_idx` (`user_roles__id`),
  KEY `fk_users_vendors1_idx` (`vendors__id`),
  CONSTRAINT `fk_users_countries1` FOREIGN KEY (`countries__id`) REFERENCES `countries` (`_id`) ON DELETE SET NULL ON UPDATE NO ACTION,
  CONSTRAINT `fk_users_user_roles1` FOREIGN KEY (`user_roles__id`) REFERENCES `user_roles` (`_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_users_vendors1` FOREIGN KEY (`vendors__id`) REFERENCES `vendors` (`_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `users` VALUES
(1,'50ee1967-7341-4c3a-b071-f2ea0722b179','2024-11-13 17:26:47','2025-04-28 07:06:27','superadmin','admin@example.in','$2y$10$MSZxndE0sizE2b9rWU/RI.7BEfm3ioPFigDTL0u2A8GqTFsTzoi6u',1,'B2axi4SFPckEN7BY9PG0d6DrCtOZMsIRIDuZxdtl7mCuDbi5YXk4IOutXpE3','Super','Administrator','82170972754',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL),
(23,'f3e073e9-7921-4a58-be40-25c0dfacf0da','2025-01-11 10:06:12','2025-01-11 10:06:12','amit','support@omxdigital.in','$2y$10$DxrNe6LNnmZw2YVAQnT//OBook6A/9FwwgW0b3ukr6nLG/lH1xZGS',1,'1703cf4d-6c2a-4906-b303-c8fbbbf407e0','Amit','OM','919832531462',NULL,NULL,NULL,NULL,NULL,NULL,NULL,3,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `vendor_settings`
--

DROP TABLE IF EXISTS `vendor_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vendor_settings` (
  `_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `_uid` char(36) NOT NULL,
  `status` tinyint(3) unsigned DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `vendors__id` int(10) unsigned NOT NULL,
  `name` varchar(45) NOT NULL,
  `value` longtext DEFAULT NULL,
  `data_type` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`_id`),
  UNIQUE KEY `_uid_UNIQUE` (`_uid`),
  UNIQUE KEY `_uid` (`_uid`),
  KEY `fk_vendor_settings_vendors1_idx` (`vendors__id`),
  CONSTRAINT `fk_vendor_settings_vendors1` FOREIGN KEY (`vendors__id`) REFERENCES `vendors` (`_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=131 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vendor_settings`
--

LOCK TABLES `vendor_settings` WRITE;
/*!40000 ALTER TABLE `vendor_settings` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `vendor_settings` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `vendor_users`
--

DROP TABLE IF EXISTS `vendor_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vendor_users` (
  `_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `_uid` char(36) NOT NULL,
  `status` tinyint(3) unsigned DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `vendors__id` int(10) unsigned NOT NULL,
  `users__id` int(10) unsigned NOT NULL,
  `__data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`__data`)),
  PRIMARY KEY (`_id`),
  UNIQUE KEY `_uid_UNIQUE` (`_uid`),
  UNIQUE KEY `_uid` (`_uid`),
  KEY `fk_vendor_users_vendors1_idx` (`vendors__id`),
  KEY `fk_vendor_users_users1_idx` (`users__id`),
  CONSTRAINT `fk_vendor_users_users1` FOREIGN KEY (`users__id`) REFERENCES `users` (`_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_vendor_users_vendors1` FOREIGN KEY (`vendors__id`) REFERENCES `vendors` (`_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vendor_users`
--

LOCK TABLES `vendor_users` WRITE;
/*!40000 ALTER TABLE `vendor_users` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `vendor_users` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `vendors`
--

DROP TABLE IF EXISTS `vendors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vendors` (
  `_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `_uid` char(36) NOT NULL,
  `status` tinyint(3) unsigned DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `ban_reason` varchar(255) DEFAULT NULL,
  `type` tinyint(3) unsigned DEFAULT NULL COMMENT 'Restaurent',
  `stripe_id` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `pm_type` varchar(255) DEFAULT NULL,
  `pm_last_four` varchar(4) DEFAULT NULL,
  `trial_ends_at` timestamp NULL DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `logo_image` varchar(255) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `domain` varchar(255) DEFAULT NULL,
  `favicon` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`_id`),
  UNIQUE KEY `_uid_UNIQUE` (`_uid`),
  UNIQUE KEY `_uid` (`_uid`),
  KEY `stripe_id` (`stripe_id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vendors`
--

LOCK TABLES `vendors` WRITE;
/*!40000 ALTER TABLE `vendors` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `vendors` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `whatsapp_message_logs`
--

DROP TABLE IF EXISTS `whatsapp_message_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `whatsapp_message_logs` (
  `_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `_uid` char(36) NOT NULL,
  `status` varchar(10) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `contacts__id` int(10) unsigned DEFAULT NULL,
  `campaigns__id` int(10) unsigned DEFAULT NULL,
  `vendors__id` int(10) unsigned NOT NULL,
  `contact_wa_id` varchar(45) DEFAULT NULL,
  `wamid` varchar(255) DEFAULT NULL,
  `wab_phone_number_id` varchar(45) DEFAULT NULL,
  `is_incoming_message` tinyint(3) unsigned DEFAULT NULL COMMENT 'Incoming,outgoing',
  `type` varchar(255) DEFAULT NULL,
  `__data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`__data`)),
  `messaged_at` datetime DEFAULT NULL,
  `is_forwarded` tinyint(1) DEFAULT NULL,
  `replied_to_whatsapp_message_logs__uid` char(36) DEFAULT NULL,
  `messaged_by_users__id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`_id`),
  UNIQUE KEY `_uid` (`_uid`),
  KEY `fk_whatsapp_message_status_logs_contacts1_idx` (`contacts__id`),
  KEY `fk_whatsapp_message_status_logs_campaigns1_idx` (`campaigns__id`),
  KEY `fk_whatsapp_message_status_logs_vendors1_idx` (`vendors__id`),
  KEY `fk_whatsapp_message_logs_users1_idx` (`messaged_by_users__id`),
  KEY `whatsapp_message_logs_type_index` (`type`),
  CONSTRAINT `fk_whatsapp_message_logs_users1` FOREIGN KEY (`messaged_by_users__id`) REFERENCES `users` (`_id`) ON DELETE SET NULL ON UPDATE NO ACTION,
  CONSTRAINT `fk_whatsapp_message_status_logs_campaigns1` FOREIGN KEY (`campaigns__id`) REFERENCES `campaigns` (`_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_whatsapp_message_status_logs_contacts1` FOREIGN KEY (`contacts__id`) REFERENCES `contacts` (`_id`) ON DELETE SET NULL ON UPDATE NO ACTION,
  CONSTRAINT `fk_whatsapp_message_status_logs_vendors1` FOREIGN KEY (`vendors__id`) REFERENCES `vendors` (`_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=601 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `whatsapp_message_logs`
--

LOCK TABLES `whatsapp_message_logs` WRITE;
/*!40000 ALTER TABLE `whatsapp_message_logs` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `whatsapp_message_logs` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `whatsapp_message_queue`
--

DROP TABLE IF EXISTS `whatsapp_message_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `whatsapp_message_queue` (
  `_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `_uid` char(36) NOT NULL,
  `status` tinyint(3) unsigned DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `vendors__id` int(10) unsigned NOT NULL,
  `scheduled_at` datetime DEFAULT NULL,
  `__data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`__data`)),
  `phone_with_country_code` varchar(45) NOT NULL,
  `campaigns__id` int(10) unsigned NOT NULL,
  `contacts__id` int(10) unsigned DEFAULT NULL,
  `retries` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`_id`),
  UNIQUE KEY `_uid` (`_uid`),
  KEY `fk_whatsapp_message_queue_vendors1_idx` (`vendors__id`),
  KEY `fk_whatsapp_message_queue_campaigns1_idx` (`campaigns__id`),
  KEY `fk_whatsapp_message_queue_contacts1_idx` (`contacts__id`),
  CONSTRAINT `fk_whatsapp_message_queue_campaigns1` FOREIGN KEY (`campaigns__id`) REFERENCES `campaigns` (`_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_whatsapp_message_queue_contacts1` FOREIGN KEY (`contacts__id`) REFERENCES `contacts` (`_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_whatsapp_message_queue_vendors1` FOREIGN KEY (`vendors__id`) REFERENCES `vendors` (`_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `whatsapp_message_queue`
--

LOCK TABLES `whatsapp_message_queue` WRITE;
/*!40000 ALTER TABLE `whatsapp_message_queue` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `whatsapp_message_queue` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `whatsapp_orders`
--

DROP TABLE IF EXISTS `whatsapp_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `whatsapp_orders` (
  `_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `_uid` varchar(255) NOT NULL,
  `order_id` varchar(50) NOT NULL,
  `vendors__id` bigint(20) unsigned NOT NULL,
  `contacts__id` bigint(20) unsigned DEFAULT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `items` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`items`)),
  `total_amount` decimal(10,2) NOT NULL,
  `tax_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `shipping_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `delivery_address` text DEFAULT NULL,
  `payment_id` varchar(100) DEFAULT NULL,
  `payment_status` varchar(50) DEFAULT NULL,
  `currency` varchar(3) NOT NULL DEFAULT 'INR',
  `status` enum('pending','awaiting_address','awaiting_payment','payment_processing','paid','confirmed','shipped','delivered','cancelled','refunded','manual_payment_required') NOT NULL DEFAULT 'pending',
  `__data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`__data`)),
  `ordered_at` timestamp NULL DEFAULT NULL,
  `payment_completed_at` timestamp NULL DEFAULT NULL,
  `shipped_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`_id`),
  UNIQUE KEY `whatsapp_orders__uid_unique` (`_uid`),
  UNIQUE KEY `whatsapp_orders_order_id_unique` (`order_id`),
  KEY `whatsapp_orders_vendors__id_status_index` (`vendors__id`),
  KEY `whatsapp_orders_customer_phone_vendors__id_index` (`customer_phone`,`vendors__id`),
  KEY `whatsapp_orders_order_id_vendors__id_index` (`order_id`,`vendors__id`),
  KEY `whatsapp_orders_payment_id_index` (`payment_id`),
  KEY `whatsapp_orders_ordered_at_index` (`ordered_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `whatsapp_orders`
--

LOCK TABLES `whatsapp_orders` WRITE;
/*!40000 ALTER TABLE `whatsapp_orders` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `whatsapp_orders` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `whatsapp_payments`
--

DROP TABLE IF EXISTS `whatsapp_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `whatsapp_payments` (
  `_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `_uid` varchar(255) NOT NULL,
  `payment_id` varchar(100) NOT NULL,
  `vendors__id` bigint(20) unsigned NOT NULL,
  `order_id` varchar(50) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) NOT NULL DEFAULT 'INR',
  `status` enum('pending','processing','completed','failed','cancelled','refunded','partially_refunded') NOT NULL DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `payment_link_id` varchar(100) DEFAULT NULL,
  `payment_link_url` varchar(500) DEFAULT NULL,
  `gateway` varchar(50) NOT NULL DEFAULT 'razorpay',
  `gateway_response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`gateway_response`)),
  `__data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`__data`)),
  `payment_initiated_at` timestamp NULL DEFAULT NULL,
  `payment_completed_at` timestamp NULL DEFAULT NULL,
  `payment_failed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`_id`),
  UNIQUE KEY `whatsapp_payments__uid_unique` (`_uid`),
  UNIQUE KEY `whatsapp_payments_payment_id_unique` (`payment_id`),
  KEY `whatsapp_payments_vendors__id_status_index` (`vendors__id`,`status`),
  KEY `whatsapp_payments_order_id_vendors__id_index` (`order_id`,`vendors__id`),
  KEY `whatsapp_payments_payment_id_index` (`payment_id`),
  KEY `whatsapp_payments_transaction_id_index` (`transaction_id`),
  KEY `whatsapp_payments_payment_link_id_index` (`payment_link_id`),
  KEY `whatsapp_payments_gateway_index` (`gateway`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `whatsapp_payments`
--

LOCK TABLES `whatsapp_payments` WRITE;
/*!40000 ALTER TABLE `whatsapp_payments` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `whatsapp_payments` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `whatsapp_templates`
--

DROP TABLE IF EXISTS `whatsapp_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `whatsapp_templates` (
  `_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `_uid` char(36) NOT NULL,
  `status` varchar(15) DEFAULT NULL,
  `template_name` varchar(512) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `template_id` varchar(45) DEFAULT NULL,
  `category` varchar(45) DEFAULT NULL,
  `language` varchar(45) DEFAULT NULL,
  `__data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`__data`)),
  `vendors__id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`_id`),
  UNIQUE KEY `_uid_UNIQUE` (`_uid`),
  UNIQUE KEY `_uid` (`_uid`),
  KEY `fk_whatsapp_templates_vendors1_idx` (`vendors__id`),
  CONSTRAINT `fk_whatsapp_templates_vendors1` FOREIGN KEY (`vendors__id`) REFERENCES `vendors` (`_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `whatsapp_templates`
--

LOCK TABLES `whatsapp_templates` WRITE;
/*!40000 ALTER TABLE `whatsapp_templates` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `whatsapp_templates` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `whatsapp_user_states`
--

DROP TABLE IF EXISTS `whatsapp_user_states`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `whatsapp_user_states` (
  `_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `_uid` varchar(255) NOT NULL,
  `vendors__id` bigint(20) unsigned NOT NULL,
  `contacts__id` bigint(20) unsigned DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `state` varchar(50) NOT NULL,
  `order_id` varchar(50) DEFAULT NULL,
  `context` varchar(100) DEFAULT NULL,
  `__data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`__data`)),
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`_id`),
  UNIQUE KEY `whatsapp_user_states_vendors__id_phone_unique` (`vendors__id`,`phone`),
  UNIQUE KEY `whatsapp_user_states__uid_unique` (`_uid`),
  KEY `whatsapp_user_states_vendors__id_phone_index` (`vendors__id`,`phone`),
  KEY `whatsapp_user_states_vendors__id_state_index` (`vendors__id`,`state`),
  KEY `whatsapp_user_states_order_id_index` (`order_id`),
  KEY `whatsapp_user_states_expires_at_index` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `whatsapp_user_states`
--

LOCK TABLES `whatsapp_user_states` WRITE;
/*!40000 ALTER TABLE `whatsapp_user_states` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `whatsapp_user_states` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `woocommerce_integrations`
--

DROP TABLE IF EXISTS `woocommerce_integrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `woocommerce_integrations` (
  `_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `_uid` varchar(255) NOT NULL,
  `vendors__id` int(10) unsigned NOT NULL,
  `site_url` varchar(255) NOT NULL,
  `consumer_key` text NOT NULL,
  `consumer_secret` text NOT NULL,
  `webhook_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`webhook_ids`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `notification_types` text DEFAULT NULL,
  `webhook_url` varchar(255) DEFAULT NULL,
  `connected_at` timestamp NULL DEFAULT NULL,
  `disconnected_at` timestamp NULL DEFAULT NULL,
  `last_sync_at` timestamp NULL DEFAULT NULL,
  `settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`settings`)),
  `__data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`__data`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`_id`),
  UNIQUE KEY `woocommerce_integrations__uid_unique` (`_uid`),
  UNIQUE KEY `woocommerce_integrations_site_url_unique` (`site_url`),
  KEY `woocommerce_integrations_vendors__id_is_active_index` (`vendors__id`,`is_active`),
  KEY `woocommerce_integrations_site_url_index` (`site_url`),
  CONSTRAINT `woocommerce_integrations_vendors__id_foreign` FOREIGN KEY (`vendors__id`) REFERENCES `vendors` (`_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `woocommerce_integrations`
--

LOCK TABLES `woocommerce_integrations` WRITE;
/*!40000 ALTER TABLE `woocommerce_integrations` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `woocommerce_integrations` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `woocommerce_order_notifications`
--

DROP TABLE IF EXISTS `woocommerce_order_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `woocommerce_order_notifications` (
  `_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `_uid` varchar(255) NOT NULL,
  `woocommerce_orders__id` bigint(20) unsigned NOT NULL,
  `vendors__id` int(10) unsigned NOT NULL,
  `contacts__id` int(10) unsigned DEFAULT NULL,
  `whatsapp_templates__id` int(10) unsigned DEFAULT NULL,
  `notification_type` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `message_id` varchar(255) DEFAULT NULL,
  `whatsapp_message_id` varchar(255) DEFAULT NULL,
  `variables` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`variables`)),
  `response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`response`)),
  `retry_count` int(11) NOT NULL DEFAULT 0,
  `sent_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `__data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`__data`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`_id`),
  UNIQUE KEY `woocommerce_order_notifications__uid_unique` (`_uid`),
  KEY `woocommerce_order_notifications_contacts__id_foreign` (`contacts__id`),
  KEY `woocommerce_order_notifications_whatsapp_templates__id_foreign` (`whatsapp_templates__id`),
  KEY `woocommerce_notifications_vendor_status` (`vendors__id`,`status`),
  KEY `woocommerce_notifications_vendor_type` (`vendors__id`,`notification_type`),
  KEY `woocommerce_notifications_order_type` (`woocommerce_orders__id`,`notification_type`),
  CONSTRAINT `woocommerce_order_notifications_contacts__id_foreign` FOREIGN KEY (`contacts__id`) REFERENCES `contacts` (`_id`) ON DELETE SET NULL,
  CONSTRAINT `woocommerce_order_notifications_vendors__id_foreign` FOREIGN KEY (`vendors__id`) REFERENCES `vendors` (`_id`) ON DELETE CASCADE,
  CONSTRAINT `woocommerce_order_notifications_whatsapp_templates__id_foreign` FOREIGN KEY (`whatsapp_templates__id`) REFERENCES `whatsapp_templates` (`_id`) ON DELETE SET NULL,
  CONSTRAINT `woocommerce_order_notifications_woocommerce_orders__id_foreign` FOREIGN KEY (`woocommerce_orders__id`) REFERENCES `woocommerce_orders` (`_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `woocommerce_order_notifications`
--

LOCK TABLES `woocommerce_order_notifications` WRITE;
/*!40000 ALTER TABLE `woocommerce_order_notifications` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `woocommerce_order_notifications` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `woocommerce_orders`
--

DROP TABLE IF EXISTS `woocommerce_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `woocommerce_orders` (
  `_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `_uid` varchar(255) NOT NULL,
  `woocommerce_integrations__id` bigint(20) unsigned NOT NULL,
  `vendors__id` int(10) unsigned NOT NULL,
  `contacts__id` int(10) unsigned DEFAULT NULL,
  `woocommerce_order_id` int(10) unsigned NOT NULL,
  `order_number` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(3) NOT NULL DEFAULT 'USD',
  `payment_method` varchar(255) DEFAULT NULL,
  `payment_method_title` varchar(255) DEFAULT NULL,
  `shipping_method` varchar(255) DEFAULT NULL,
  `shipping_method_title` varchar(255) DEFAULT NULL,
  `total_tax` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_shipping` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_items` int(11) NOT NULL DEFAULT 0,
  `customer_note` text DEFAULT NULL,
  `order_notes` text DEFAULT NULL,
  `date_created` timestamp NULL DEFAULT NULL,
  `date_modified` timestamp NULL DEFAULT NULL,
  `date_completed` timestamp NULL DEFAULT NULL,
  `date_paid` timestamp NULL DEFAULT NULL,
  `date_processing` timestamp NULL DEFAULT NULL,
  `date_on_hold` timestamp NULL DEFAULT NULL,
  `date_cancelled` timestamp NULL DEFAULT NULL,
  `date_refunded` timestamp NULL DEFAULT NULL,
  `customer_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`customer_data`)),
  `order_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`order_data`)),
  `__data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`__data`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`_id`),
  UNIQUE KEY `woocommerce_orders__uid_unique` (`_uid`),
  UNIQUE KEY `woocommerce_orders_woocommerce_order_id_unique` (`woocommerce_order_id`),
  KEY `woocommerce_orders_woocommerce_integrations__id_foreign` (`woocommerce_integrations__id`),
  KEY `woocommerce_orders_contacts__id_foreign` (`contacts__id`),
  KEY `woocommerce_orders_vendors__id_status_index` (`vendors__id`,`status`),
  KEY `woocommerce_orders_vendors__id_payment_method_index` (`vendors__id`,`payment_method`),
  KEY `woocommerce_orders_vendors__id_date_created_index` (`vendors__id`,`date_created`),
  KEY `woocommerce_orders_woocommerce_order_id_vendors__id_index` (`woocommerce_order_id`,`vendors__id`),
  CONSTRAINT `woocommerce_orders_contacts__id_foreign` FOREIGN KEY (`contacts__id`) REFERENCES `contacts` (`_id`) ON DELETE SET NULL,
  CONSTRAINT `woocommerce_orders_vendors__id_foreign` FOREIGN KEY (`vendors__id`) REFERENCES `vendors` (`_id`) ON DELETE CASCADE,
  CONSTRAINT `woocommerce_orders_woocommerce_integrations__id_foreign` FOREIGN KEY (`woocommerce_integrations__id`) REFERENCES `woocommerce_integrations` (`_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `woocommerce_orders`
--

LOCK TABLES `woocommerce_orders` WRITE;
/*!40000 ALTER TABLE `woocommerce_orders` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `woocommerce_orders` ENABLE KEYS */;
UNLOCK TABLES;
commit;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2025-08-12 18:01:57
