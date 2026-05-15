-- MariaDB dump 10.19  Distrib 10.4.28-MariaDB, for osx10.10 (x86_64)
--
-- Host: localhost    Database: e_learning
-- ------------------------------------------------------
-- Server version	10.4.28-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `qb_bloom_levels`
--

DROP TABLE IF EXISTS `qb_bloom_levels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `qb_bloom_levels` (
  `bloom_id` int(11) NOT NULL AUTO_INCREMENT,
  `bloom_name` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`bloom_id`),
  UNIQUE KEY `bloom_name` (`bloom_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `qb_bloom_levels`
--

LOCK TABLES `qb_bloom_levels` WRITE;
/*!40000 ALTER TABLE `qb_bloom_levels` DISABLE KEYS */;
/*!40000 ALTER TABLE `qb_bloom_levels` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `qb_chapters`
--

DROP TABLE IF EXISTS `qb_chapters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `qb_chapters` (
  `chapter_id` int(11) NOT NULL AUTO_INCREMENT,
  `subject_id` int(11) NOT NULL,
  `level_id` int(11) NOT NULL,
  `chapter_number` varchar(20) DEFAULT NULL,
  `chapter_name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`chapter_id`),
  KEY `subject_id` (`subject_id`),
  KEY `level_id` (`level_id`),
  CONSTRAINT `qb_chapters_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `qb_subjects` (`subject_id`),
  CONSTRAINT `qb_chapters_ibfk_2` FOREIGN KEY (`level_id`) REFERENCES `qb_levels` (`level_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `qb_chapters`
--

LOCK TABLES `qb_chapters` WRITE;
/*!40000 ALTER TABLE `qb_chapters` DISABLE KEYS */;
/*!40000 ALTER TABLE `qb_chapters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `qb_curriculum_refs`
--

DROP TABLE IF EXISTS `qb_curriculum_refs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `qb_curriculum_refs` (
  `ref_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `question_id` bigint(20) NOT NULL,
  `competency_code` varchar(100) DEFAULT NULL,
  `learning_outcome` text DEFAULT NULL,
  PRIMARY KEY (`ref_id`),
  KEY `question_id` (`question_id`),
  CONSTRAINT `qb_curriculum_refs_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `qb_questions` (`question_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `qb_curriculum_refs`
--

LOCK TABLES `qb_curriculum_refs` WRITE;
/*!40000 ALTER TABLE `qb_curriculum_refs` DISABLE KEYS */;
/*!40000 ALTER TABLE `qb_curriculum_refs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `qb_difficulty_levels`
--

DROP TABLE IF EXISTS `qb_difficulty_levels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `qb_difficulty_levels` (
  `difficulty_id` int(11) NOT NULL AUTO_INCREMENT,
  `difficulty_name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`difficulty_id`),
  UNIQUE KEY `difficulty_name` (`difficulty_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `qb_difficulty_levels`
--

LOCK TABLES `qb_difficulty_levels` WRITE;
/*!40000 ALTER TABLE `qb_difficulty_levels` DISABLE KEYS */;
/*!40000 ALTER TABLE `qb_difficulty_levels` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `qb_levels`
--

DROP TABLE IF EXISTS `qb_levels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `qb_levels` (
  `level_id` int(11) NOT NULL AUTO_INCREMENT,
  `level_name` varchar(50) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  PRIMARY KEY (`level_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `qb_levels`
--

LOCK TABLES `qb_levels` WRITE;
/*!40000 ALTER TABLE `qb_levels` DISABLE KEYS */;
/*!40000 ALTER TABLE `qb_levels` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `qb_question_media`
--

DROP TABLE IF EXISTS `qb_question_media`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `qb_question_media` (
  `media_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `question_id` bigint(20) NOT NULL,
  `media_type` enum('image','audio','video','document') DEFAULT NULL,
  `media_path` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`media_id`),
  KEY `question_id` (`question_id`),
  CONSTRAINT `qb_question_media_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `qb_questions` (`question_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `qb_question_media`
--

LOCK TABLES `qb_question_media` WRITE;
/*!40000 ALTER TABLE `qb_question_media` DISABLE KEYS */;
/*!40000 ALTER TABLE `qb_question_media` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `qb_question_options`
--

DROP TABLE IF EXISTS `qb_question_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `qb_question_options` (
  `option_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `question_id` bigint(20) NOT NULL,
  `option_label` varchar(10) DEFAULT NULL,
  `option_text` text NOT NULL,
  `is_correct` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`option_id`),
  KEY `question_id` (`question_id`),
  CONSTRAINT `qb_question_options_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `qb_questions` (`question_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `qb_question_options`
--

LOCK TABLES `qb_question_options` WRITE;
/*!40000 ALTER TABLE `qb_question_options` DISABLE KEYS */;
/*!40000 ALTER TABLE `qb_question_options` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `qb_question_usage`
--

DROP TABLE IF EXISTS `qb_question_usage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `qb_question_usage` (
  `usage_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `question_id` bigint(20) NOT NULL,
  `exam_id` bigint(20) DEFAULT NULL,
  `times_used` int(11) DEFAULT 0,
  PRIMARY KEY (`usage_id`),
  KEY `question_id` (`question_id`),
  CONSTRAINT `qb_question_usage_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `qb_questions` (`question_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `qb_question_usage`
--

LOCK TABLES `qb_question_usage` WRITE;
/*!40000 ALTER TABLE `qb_question_usage` DISABLE KEYS */;
/*!40000 ALTER TABLE `qb_question_usage` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `qb_questions`
--

DROP TABLE IF EXISTS `qb_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `qb_questions` (
  `question_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `q_uid` varchar(100) NOT NULL,
  `year_year` int(11) DEFAULT NULL,
  `section_id` int(11) DEFAULT NULL,
  `question_number` varchar(20) DEFAULT NULL,
  `subject_id` int(11) NOT NULL,
  `level_id` int(11) NOT NULL,
  `chapter_id` int(11) NOT NULL,
  `subtopic_id` int(11) NOT NULL,
  `difficulty_id` int(11) DEFAULT NULL,
  `bloom_id` int(11) DEFAULT NULL,
  `question_stem` longtext NOT NULL,
  `correct_answer` varchar(10) DEFAULT NULL,
  `solution_explanation` longtext DEFAULT NULL,
  `swahili_hint` text DEFAULT NULL,
  `estimated_time_seconds` int(11) DEFAULT 60,
  `marks` decimal(5,2) DEFAULT 1.00,
  `cira_flag` tinyint(1) DEFAULT 0,
  `question_type` enum('mcq','true_false','essay','matching','fill_blank') DEFAULT 'mcq',
  `status` enum('draft','review','published','archived') DEFAULT 'draft',
  `created_by` bigint(20) DEFAULT NULL,
  `approved_by` bigint(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`question_id`),
  UNIQUE KEY `q_uid` (`q_uid`),
  KEY `section_id` (`section_id`),
  KEY `subject_id` (`subject_id`),
  KEY `level_id` (`level_id`),
  KEY `chapter_id` (`chapter_id`),
  KEY `subtopic_id` (`subtopic_id`),
  KEY `difficulty_id` (`difficulty_id`),
  KEY `bloom_id` (`bloom_id`),
  CONSTRAINT `qb_questions_ibfk_1` FOREIGN KEY (`section_id`) REFERENCES `qb_sections` (`section_id`),
  CONSTRAINT `qb_questions_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `qb_subjects` (`subject_id`),
  CONSTRAINT `qb_questions_ibfk_3` FOREIGN KEY (`level_id`) REFERENCES `qb_levels` (`level_id`),
  CONSTRAINT `qb_questions_ibfk_4` FOREIGN KEY (`chapter_id`) REFERENCES `qb_chapters` (`chapter_id`),
  CONSTRAINT `qb_questions_ibfk_5` FOREIGN KEY (`subtopic_id`) REFERENCES `qb_subtopics` (`subtopic_id`),
  CONSTRAINT `qb_questions_ibfk_6` FOREIGN KEY (`difficulty_id`) REFERENCES `qb_difficulty_levels` (`difficulty_id`),
  CONSTRAINT `qb_questions_ibfk_7` FOREIGN KEY (`bloom_id`) REFERENCES `qb_bloom_levels` (`bloom_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `qb_questions`
--

LOCK TABLES `qb_questions` WRITE;
/*!40000 ALTER TABLE `qb_questions` DISABLE KEYS */;
/*!40000 ALTER TABLE `qb_questions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `qb_sections`
--

DROP TABLE IF EXISTS `qb_sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `qb_sections` (
  `section_id` int(11) NOT NULL AUTO_INCREMENT,
  `section_name` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`section_id`),
  UNIQUE KEY `section_name` (`section_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `qb_sections`
--

LOCK TABLES `qb_sections` WRITE;
/*!40000 ALTER TABLE `qb_sections` DISABLE KEYS */;
/*!40000 ALTER TABLE `qb_sections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `qb_subjects`
--

DROP TABLE IF EXISTS `qb_subjects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `qb_subjects` (
  `subject_id` int(11) NOT NULL AUTO_INCREMENT,
  `subject_code` varchar(20) DEFAULT NULL,
  `subject_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`subject_id`),
  UNIQUE KEY `subject_code` (`subject_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `qb_subjects`
--

LOCK TABLES `qb_subjects` WRITE;
/*!40000 ALTER TABLE `qb_subjects` DISABLE KEYS */;
/*!40000 ALTER TABLE `qb_subjects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `qb_subtopics`
--

DROP TABLE IF EXISTS `qb_subtopics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `qb_subtopics` (
  `subtopic_id` int(11) NOT NULL AUTO_INCREMENT,
  `chapter_id` int(11) NOT NULL,
  `subtopic_name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`subtopic_id`),
  KEY `chapter_id` (`chapter_id`),
  CONSTRAINT `qb_subtopics_ibfk_1` FOREIGN KEY (`chapter_id`) REFERENCES `qb_chapters` (`chapter_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `qb_subtopics`
--

LOCK TABLES `qb_subtopics` WRITE;
/*!40000 ALTER TABLE `qb_subtopics` DISABLE KEYS */;
/*!40000 ALTER TABLE `qb_subtopics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_all_users`
--

DROP TABLE IF EXISTS `tbl_all_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_all_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usr_code` varchar(200) NOT NULL,
  `first_name` varchar(200) DEFAULT NULL,
  `last_name` varchar(200) DEFAULT NULL,
  `email_address` varchar(200) DEFAULT NULL,
  `phone_number` varchar(200) DEFAULT NULL,
  `user_role` varchar(200) DEFAULT NULL,
  `user_password` varchar(200) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `user_status` enum('Active','Inactive') DEFAULT 'Active',
  `signup_success` varchar(200) NOT NULL DEFAULT 'Incomplete',
  `reset_token` varchar(255) DEFAULT NULL,
  `token_expiry` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usr_code` (`usr_code`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_all_users`
--

LOCK TABLES `tbl_all_users` WRITE;
/*!40000 ALTER TABLE `tbl_all_users` DISABLE KEYS */;
INSERT INTO `tbl_all_users` VALUES (2,'USR1774352917','Said','Muhama','saidmuhama@gmail.com','255765131788','1','$2y$10$PQ4ZtozEsVxxSaTApFlNdOep4LaIHS9j8rJE3D/5euN7ayfgsxxtK','2026-03-24 04:48:37','Active','Completed',NULL,NULL),(3,'USR1774423951','Jamal','Juma','jamaljuma.tz@gmail.com','255764078960','1','$2y$10$RtUn5F4pN8qFUXJHqaKcBOAOZ3kcKEvzdaL2UJNWoQuSJBCSO4c22','2026-03-25 00:32:31','Active','Incomplete',NULL,NULL),(4,'USR1775360573','Hamza','Pazia','muhama.digital@gmail.com','255625490405','3','$2y$10$/PjkNaEYTicWIRGak/KvsuzJvIiODz9XN4xjWv2hcysL.1U9gP2PG','2026-04-05 06:42:53','Active','Completed',NULL,NULL),(6,'USR1777617356','Said','Muhama','james.samwel@gmail.com','255765131788','1','$2y$10$yyYUh8TX3UJZKHadCQhiweQ243jULMjs/DHU6VbwHx8SIXC0T0VFy','2026-05-01 09:35:56','Active','Incomplete',NULL,NULL),(7,'USR1777621416','Jamal','Juma','jamal.juma@gmail.com','255715087593','1','$2y$10$k.rjazll0ByfN0ivfaukmeNDrqdzKLdC8gPNm40y.dAQcvptR/3IS','2026-05-01 10:43:37','Active','Incomplete',NULL,NULL),(8,'USR1777621816','Said','Muhama','super@gmail.com','255765131788','1','$2y$10$GnWK13vr12wk9q7DMgOJneRXprJBVKM8hZWpA6gta/HNaMm.Eqelu','2026-05-01 10:50:16','Active','Incomplete',NULL,NULL),(9,'USR1777623473','Juma','Jabu','juma@gmail.com','255625490405','1','$2y$10$gAI/Tomqdx7bLOVSMQNbTeHhIQdbdvHaO.bQbI0SZWPy0o3Rxexk2','2026-05-01 11:17:53','Active','Incomplete',NULL,NULL);
/*!40000 ALTER TABLE `tbl_all_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_country`
--

DROP TABLE IF EXISTS `tbl_country`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_country` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `iso` char(2) NOT NULL,
  `name` varchar(80) NOT NULL,
  `nicename` varchar(80) NOT NULL,
  `iso3` char(3) DEFAULT NULL,
  `numcode` smallint(6) DEFAULT NULL,
  `phonecode` int(5) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=241 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_country`
--

LOCK TABLES `tbl_country` WRITE;
/*!40000 ALTER TABLE `tbl_country` DISABLE KEYS */;
INSERT INTO `tbl_country` VALUES (1,'AF','AFGHANISTAN','Afghanistan','AFG',4,93),(2,'AL','ALBANIA','Albania','ALB',8,355),(3,'DZ','ALGERIA','Algeria','DZA',12,213),(4,'AS','AMERICAN SAMOA','American Samoa','ASM',16,1684),(5,'AD','ANDORRA','Andorra','AND',20,376),(6,'AO','ANGOLA','Angola','AGO',24,244),(7,'AI','ANGUILLA','Anguilla','AIA',660,1264),(8,'AQ','ANTARCTICA','Antarctica',NULL,NULL,0),(9,'AG','ANTIGUA AND BARBUDA','Antigua and Barbuda','ATG',28,1268),(10,'AR','ARGENTINA','Argentina','ARG',32,54),(11,'AM','ARMENIA','Armenia','ARM',51,374),(12,'AW','ARUBA','Aruba','ABW',533,297),(13,'AU','AUSTRALIA','Australia','AUS',36,61),(14,'AT','AUSTRIA','Austria','AUT',40,43),(15,'AZ','AZERBAIJAN','Azerbaijan','AZE',31,994),(16,'BS','BAHAMAS','Bahamas','BHS',44,1242),(17,'BH','BAHRAIN','Bahrain','BHR',48,973),(18,'BD','BANGLADESH','Bangladesh','BGD',50,880),(19,'BB','BARBADOS','Barbados','BRB',52,1246),(20,'BY','BELARUS','Belarus','BLR',112,375),(21,'BE','BELGIUM','Belgium','BEL',56,32),(22,'BZ','BELIZE','Belize','BLZ',84,501),(23,'BJ','BENIN','Benin','BEN',204,229),(24,'BM','BERMUDA','Bermuda','BMU',60,1441),(25,'BT','BHUTAN','Bhutan','BTN',64,975),(26,'BO','BOLIVIA','Bolivia','BOL',68,591),(27,'BA','BOSNIA AND HERZEGOVINA','Bosnia and Herzegovina','BIH',70,387),(28,'BW','BOTSWANA','Botswana','BWA',72,267),(29,'BV','BOUVET ISLAND','Bouvet Island',NULL,NULL,0),(30,'BR','BRAZIL','Brazil','BRA',76,55),(31,'IO','BRITISH INDIAN OCEAN TERRITORY','British Indian Ocean Territory',NULL,NULL,246),(32,'BN','BRUNEI DARUSSALAM','Brunei Darussalam','BRN',96,673),(33,'BG','BULGARIA','Bulgaria','BGR',100,359),(34,'BF','BURKINA FASO','Burkina Faso','BFA',854,226),(35,'BI','BURUNDI','Burundi','BDI',108,257),(36,'KH','CAMBODIA','Cambodia','KHM',116,855),(37,'CM','CAMEROON','Cameroon','CMR',120,237),(38,'CA','CANADA','Canada','CAN',124,1),(39,'CV','CAPE VERDE','Cape Verde','CPV',132,238),(40,'KY','CAYMAN ISLANDS','Cayman Islands','CYM',136,1345),(41,'CF','CENTRAL AFRICAN REPUBLIC','Central African Republic','CAF',140,236),(42,'TD','CHAD','Chad','TCD',148,235),(43,'CL','CHILE','Chile','CHL',152,56),(44,'CN','CHINA','China','CHN',156,86),(45,'CX','CHRISTMAS ISLAND','Christmas Island',NULL,NULL,61),(46,'CC','COCOS (KEELING) ISLANDS','Cocos (Keeling) Islands',NULL,NULL,672),(47,'CO','COLOMBIA','Colombia','COL',170,57),(48,'KM','COMOROS','Comoros','COM',174,269),(49,'CG','CONGO','Congo','COG',178,242),(50,'CD','CONGO, THE DEMOCRATIC REPUBLIC OF THE','Congo, the Democratic Republic of the','COD',180,242),(51,'CK','COOK ISLANDS','Cook Islands','COK',184,682),(52,'CR','COSTA RICA','Costa Rica','CRI',188,506),(53,'CI','COTE D\'IVOIRE','Cote D\'Ivoire','CIV',384,225),(54,'HR','CROATIA','Croatia','HRV',191,385),(55,'CU','CUBA','Cuba','CUB',192,53),(56,'CY','CYPRUS','Cyprus','CYP',196,357),(57,'CZ','CZECH REPUBLIC','Czech Republic','CZE',203,420),(58,'DK','DENMARK','Denmark','DNK',208,45),(59,'DJ','DJIBOUTI','Djibouti','DJI',262,253),(60,'DM','DOMINICA','Dominica','DMA',212,1767),(61,'DO','DOMINICAN REPUBLIC','Dominican Republic','DOM',214,1809),(62,'EC','ECUADOR','Ecuador','ECU',218,593),(63,'EG','EGYPT','Egypt','EGY',818,20),(64,'SV','EL SALVADOR','El Salvador','SLV',222,503),(65,'GQ','EQUATORIAL GUINEA','Equatorial Guinea','GNQ',226,240),(66,'ER','ERITREA','Eritrea','ERI',232,291),(67,'EE','ESTONIA','Estonia','EST',233,372),(68,'ET','ETHIOPIA','Ethiopia','ETH',231,251),(69,'FK','FALKLAND ISLANDS (MALVINAS)','Falkland Islands (Malvinas)','FLK',238,500),(70,'FO','FAROE ISLANDS','Faroe Islands','FRO',234,298),(71,'FJ','FIJI','Fiji','FJI',242,679),(72,'FI','FINLAND','Finland','FIN',246,358),(73,'FR','FRANCE','France','FRA',250,33),(74,'GF','FRENCH GUIANA','French Guiana','GUF',254,594),(75,'PF','FRENCH POLYNESIA','French Polynesia','PYF',258,689),(76,'TF','FRENCH SOUTHERN TERRITORIES','French Southern Territories',NULL,NULL,0),(77,'GA','GABON','Gabon','GAB',266,241),(78,'GM','GAMBIA','Gambia','GMB',270,220),(79,'GE','GEORGIA','Georgia','GEO',268,995),(80,'DE','GERMANY','Germany','DEU',276,49),(81,'GH','GHANA','Ghana','GHA',288,233),(82,'GI','GIBRALTAR','Gibraltar','GIB',292,350),(83,'GR','GREECE','Greece','GRC',300,30),(84,'GL','GREENLAND','Greenland','GRL',304,299),(85,'GD','GRENADA','Grenada','GRD',308,1473),(86,'GP','GUADELOUPE','Guadeloupe','GLP',312,590),(87,'GU','GUAM','Guam','GUM',316,1671),(88,'GT','GUATEMALA','Guatemala','GTM',320,502),(89,'GN','GUINEA','Guinea','GIN',324,224),(90,'GW','GUINEA-BISSAU','Guinea-Bissau','GNB',624,245),(91,'GY','GUYANA','Guyana','GUY',328,592),(92,'HT','HAITI','Haiti','HTI',332,509),(93,'HM','HEARD ISLAND AND MCDONALD ISLANDS','Heard Island and Mcdonald Islands',NULL,NULL,0),(94,'VA','HOLY SEE (VATICAN CITY STATE)','Holy See (Vatican City State)','VAT',336,39),(95,'HN','HONDURAS','Honduras','HND',340,504),(96,'HK','HONG KONG','Hong Kong','HKG',344,852),(97,'HU','HUNGARY','Hungary','HUN',348,36),(98,'IS','ICELAND','Iceland','ISL',352,354),(99,'IN','INDIA','India','IND',356,91),(100,'ID','INDONESIA','Indonesia','IDN',360,62),(101,'IR','IRAN, ISLAMIC REPUBLIC OF','Iran, Islamic Republic of','IRN',364,98),(102,'IQ','IRAQ','Iraq','IRQ',368,964),(103,'IE','IRELAND','Ireland','IRL',372,353),(104,'IL','ISRAEL','Israel','ISR',376,972),(105,'IT','ITALY','Italy','ITA',380,39),(106,'JM','JAMAICA','Jamaica','JAM',388,1876),(107,'JP','JAPAN','Japan','JPN',392,81),(108,'JO','JORDAN','Jordan','JOR',400,962),(109,'KZ','KAZAKHSTAN','Kazakhstan','KAZ',398,7),(110,'KE','KENYA','Kenya','KEN',404,254),(111,'KI','KIRIBATI','Kiribati','KIR',296,686),(112,'KP','KOREA, DEMOCRATIC PEOPLE\'S REPUBLIC OF','Korea, Democratic People\'s Republic of','PRK',408,850),(113,'KR','KOREA, REPUBLIC OF','Korea, Republic of','KOR',410,82),(114,'KW','KUWAIT','Kuwait','KWT',414,965),(115,'KG','KYRGYZSTAN','Kyrgyzstan','KGZ',417,996),(116,'LA','LAO PEOPLE\'S DEMOCRATIC REPUBLIC','Lao People\'s Democratic Republic','LAO',418,856),(117,'LV','LATVIA','Latvia','LVA',428,371),(118,'LB','LEBANON','Lebanon','LBN',422,961),(119,'LS','LESOTHO','Lesotho','LSO',426,266),(120,'LR','LIBERIA','Liberia','LBR',430,231),(121,'LY','LIBYAN ARAB JAMAHIRIYA','Libyan Arab Jamahiriya','LBY',434,218),(122,'LI','LIECHTENSTEIN','Liechtenstein','LIE',438,423),(123,'LT','LITHUANIA','Lithuania','LTU',440,370),(124,'LU','LUXEMBOURG','Luxembourg','LUX',442,352),(125,'MO','MACAO','Macao','MAC',446,853),(126,'MK','MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF','Macedonia, the Former Yugoslav Republic of','MKD',807,389),(127,'MG','MADAGASCAR','Madagascar','MDG',450,261),(128,'MW','MALAWI','Malawi','MWI',454,265),(129,'MY','MALAYSIA','Malaysia','MYS',458,60),(130,'MV','MALDIVES','Maldives','MDV',462,960),(131,'ML','MALI','Mali','MLI',466,223),(132,'MT','MALTA','Malta','MLT',470,356),(133,'MH','MARSHALL ISLANDS','Marshall Islands','MHL',584,692),(134,'MQ','MARTINIQUE','Martinique','MTQ',474,596),(135,'MR','MAURITANIA','Mauritania','MRT',478,222),(136,'MU','MAURITIUS','Mauritius','MUS',480,230),(137,'YT','MAYOTTE','Mayotte',NULL,NULL,269),(138,'MX','MEXICO','Mexico','MEX',484,52),(139,'FM','MICRONESIA, FEDERATED STATES OF','Micronesia, Federated States of','FSM',583,691),(140,'MD','MOLDOVA, REPUBLIC OF','Moldova, Republic of','MDA',498,373),(141,'MC','MONACO','Monaco','MCO',492,377),(142,'MN','MONGOLIA','Mongolia','MNG',496,976),(143,'MS','MONTSERRAT','Montserrat','MSR',500,1664),(144,'MA','MOROCCO','Morocco','MAR',504,212),(145,'MZ','MOZAMBIQUE','Mozambique','MOZ',508,258),(146,'MM','MYANMAR','Myanmar','MMR',104,95),(147,'NA','NAMIBIA','Namibia','NAM',516,264),(148,'NR','NAURU','Nauru','NRU',520,674),(149,'NP','NEPAL','Nepal','NPL',524,977),(150,'NL','NETHERLANDS','Netherlands','NLD',528,31),(151,'AN','NETHERLANDS ANTILLES','Netherlands Antilles','ANT',530,599),(152,'NC','NEW CALEDONIA','New Caledonia','NCL',540,687),(153,'NZ','NEW ZEALAND','New Zealand','NZL',554,64),(154,'NI','NICARAGUA','Nicaragua','NIC',558,505),(155,'NE','NIGER','Niger','NER',562,227),(156,'NG','NIGERIA','Nigeria','NGA',566,234),(157,'NU','NIUE','Niue','NIU',570,683),(158,'NF','NORFOLK ISLAND','Norfolk Island','NFK',574,672),(159,'MP','NORTHERN MARIANA ISLANDS','Northern Mariana Islands','MNP',580,1670),(160,'NO','NORWAY','Norway','NOR',578,47),(161,'OM','OMAN','Oman','OMN',512,968),(162,'PK','PAKISTAN','Pakistan','PAK',586,92),(163,'PW','PALAU','Palau','PLW',585,680),(164,'PS','PALESTINIAN TERRITORY, OCCUPIED','Palestinian Territory, Occupied',NULL,NULL,970),(165,'PA','PANAMA','Panama','PAN',591,507),(166,'PG','PAPUA NEW GUINEA','Papua New Guinea','PNG',598,675),(167,'PY','PARAGUAY','Paraguay','PRY',600,595),(168,'PE','PERU','Peru','PER',604,51),(169,'PH','PHILIPPINES','Philippines','PHL',608,63),(170,'PN','PITCAIRN','Pitcairn','PCN',612,0),(171,'PL','POLAND','Poland','POL',616,48),(172,'PT','PORTUGAL','Portugal','PRT',620,351),(173,'PR','PUERTO RICO','Puerto Rico','PRI',630,1787),(174,'QA','QATAR','Qatar','QAT',634,974),(175,'RE','REUNION','Reunion','REU',638,262),(176,'RO','ROMANIA','Romania','ROM',642,40),(177,'RU','RUSSIAN FEDERATION','Russian Federation','RUS',643,70),(178,'RW','RWANDA','Rwanda','RWA',646,250),(179,'SH','SAINT HELENA','Saint Helena','SHN',654,290),(180,'KN','SAINT KITTS AND NEVIS','Saint Kitts and Nevis','KNA',659,1869),(181,'LC','SAINT LUCIA','Saint Lucia','LCA',662,1758),(182,'PM','SAINT PIERRE AND MIQUELON','Saint Pierre and Miquelon','SPM',666,508),(183,'VC','SAINT VINCENT AND THE GRENADINES','Saint Vincent and the Grenadines','VCT',670,1784),(184,'WS','SAMOA','Samoa','WSM',882,684),(185,'SM','SAN MARINO','San Marino','SMR',674,378),(186,'ST','SAO TOME AND PRINCIPE','Sao Tome and Principe','STP',678,239),(187,'SA','SAUDI ARABIA','Saudi Arabia','SAU',682,966),(188,'SN','SENEGAL','Senegal','SEN',686,221),(189,'CS','SERBIA AND MONTENEGRO','Serbia and Montenegro',NULL,NULL,381),(190,'SC','SEYCHELLES','Seychelles','SYC',690,248),(191,'SL','SIERRA LEONE','Sierra Leone','SLE',694,232),(192,'SG','SINGAPORE','Singapore','SGP',702,65),(193,'SK','SLOVAKIA','Slovakia','SVK',703,421),(194,'SI','SLOVENIA','Slovenia','SVN',705,386),(195,'SB','SOLOMON ISLANDS','Solomon Islands','SLB',90,677),(196,'SO','SOMALIA','Somalia','SOM',706,252),(197,'ZA','SOUTH AFRICA','South Africa','ZAF',710,27),(198,'GS','SOUTH GEORGIA AND THE SOUTH SANDWICH ISLANDS','South Georgia and the South Sandwich Islands',NULL,NULL,0),(199,'ES','SPAIN','Spain','ESP',724,34),(200,'LK','SRI LANKA','Sri Lanka','LKA',144,94),(201,'SD','SUDAN','Sudan','SDN',736,249),(202,'SR','SURINAME','Suriname','SUR',740,597),(203,'SJ','SVALBARD AND JAN MAYEN','Svalbard and Jan Mayen','SJM',744,47),(204,'SZ','SWAZILAND','Swaziland','SWZ',748,268),(205,'SE','SWEDEN','Sweden','SWE',752,46),(206,'CH','SWITZERLAND','Switzerland','CHE',756,41),(207,'SY','SYRIAN ARAB REPUBLIC','Syrian Arab Republic','SYR',760,963),(208,'TW','TAIWAN, PROVINCE OF CHINA','Taiwan, Province of China','TWN',158,886),(209,'TJ','TAJIKISTAN','Tajikistan','TJK',762,992),(210,'TZ','TANZANIA','Tanzania','TZA',834,255),(211,'TH','THAILAND','Thailand','THA',764,66),(212,'TL','TIMOR-LESTE','Timor-Leste',NULL,NULL,670),(213,'TG','TOGO','Togo','TGO',768,228),(214,'TK','TOKELAU','Tokelau','TKL',772,690),(215,'TO','TONGA','Tonga','TON',776,676),(216,'TT','TRINIDAD AND TOBAGO','Trinidad and Tobago','TTO',780,1868),(217,'TN','TUNISIA','Tunisia','TUN',788,216),(218,'TR','TURKEY','Turkey','TUR',792,90),(219,'TM','TURKMENISTAN','Turkmenistan','TKM',795,7370),(220,'TC','TURKS AND CAICOS ISLANDS','Turks and Caicos Islands','TCA',796,1649),(221,'TV','TUVALU','Tuvalu','TUV',798,688),(222,'UG','UGANDA','Uganda','UGA',800,256),(223,'UA','UKRAINE','Ukraine','UKR',804,380),(224,'AE','UNITED ARAB EMIRATES','United Arab Emirates','ARE',784,971),(225,'GB','UNITED KINGDOM','United Kingdom','GBR',826,44),(226,'US','UNITED STATES','United States','USA',840,1),(227,'UM','UNITED STATES MINOR OUTLYING ISLANDS','United States Minor Outlying Islands',NULL,NULL,1),(228,'UY','URUGUAY','Uruguay','URY',858,598),(229,'UZ','UZBEKISTAN','Uzbekistan','UZB',860,998),(230,'VU','VANUATU','Vanuatu','VUT',548,678),(231,'VE','VENEZUELA','Venezuela','VEN',862,58),(232,'VN','VIET NAM','Viet Nam','VNM',704,84),(233,'VG','VIRGIN ISLANDS, BRITISH','Virgin Islands, British','VGB',92,1284),(234,'VI','VIRGIN ISLANDS, U.S.','Virgin Islands, U.s.','VIR',850,1340),(235,'WF','WALLIS AND FUTUNA','Wallis and Futuna','WLF',876,681),(236,'EH','WESTERN SAHARA','Western Sahara','ESH',732,212),(237,'YE','YEMEN','Yemen','YEM',887,967),(238,'ZM','ZAMBIA','Zambia','ZMB',894,260),(239,'ZW','ZIMBABWE','Zimbabwe','ZWE',716,263),(240,'AC','All Countries','All Countries','AC',1000,1001);
/*!40000 ALTER TABLE `tbl_country` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_course_cart`
--

DROP TABLE IF EXISTS `tbl_course_cart`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_course_cart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(50) DEFAULT NULL,
  `course_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_course_cart`
--

LOCK TABLES `tbl_course_cart` WRITE;
/*!40000 ALTER TABLE `tbl_course_cart` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_course_cart` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_course_categories`
--

DROP TABLE IF EXISTS `tbl_course_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_course_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `category_title` varchar(255) DEFAULT NULL,
  `order` int(11) DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `show_at_trending` tinyint(1) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_course_categories`
--

LOCK TABLES `tbl_course_categories` WRITE;
/*!40000 ALTER TABLE `tbl_course_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_course_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_course_chapter_items`
--

DROP TABLE IF EXISTS `tbl_course_chapter_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_course_chapter_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `instructor_id` bigint(20) unsigned NOT NULL,
  `chapter_id` bigint(20) unsigned NOT NULL,
  `type` enum('lesson','quiz','document','live') DEFAULT 'lesson',
  `order` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `course_chapter_items_instructor_id_foreign` (`instructor_id`),
  KEY `course_chapter_items_chapter_id_foreign` (`chapter_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2451 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_course_chapter_items`
--

LOCK TABLES `tbl_course_chapter_items` WRITE;
/*!40000 ALTER TABLE `tbl_course_chapter_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_course_chapter_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_course_chapter_lessons`
--

DROP TABLE IF EXISTS `tbl_course_chapter_lessons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_course_chapter_lessons` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `lesson_title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `instructor_id` varchar(200) NOT NULL,
  `course_id` bigint(20) unsigned NOT NULL,
  `chapter_id` bigint(20) unsigned DEFAULT NULL,
  `video_id` varchar(255) DEFAULT NULL,
  `library_id` varchar(255) DEFAULT NULL,
  `file_path` text DEFAULT NULL,
  `content_type` varchar(200) DEFAULT 'Video',
  `storage` enum('upload','youtube','vimeo','external_link','google_drive','iframe','aws','wasabi','live') DEFAULT 'upload',
  `file_type` enum('video','audio','pdf','txt','docx','iframe','image','file','live','other') DEFAULT 'video',
  `video_duration` varchar(20) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `isFreePreviewLesson` varchar(200) DEFAULT NULL,
  `enableDiscussions` varchar(200) DEFAULT NULL,
  `isDownloadable` varchar(200) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `course_chapter_lessons_chapter_id_foreign` (`chapter_id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_course_chapter_lessons`
--

LOCK TABLES `tbl_course_chapter_lessons` WRITE;
/*!40000 ALTER TABLE `tbl_course_chapter_lessons` DISABLE KEYS */;
INSERT INTO `tbl_course_chapter_lessons` VALUES (1,'What is Agriculture?','Agriculture is the science, art, and practice of cultivating soil, producing crops, and raising livestock to provide food, fiber, and fuel.','USR1775360573',1,1,NULL,NULL,'https://player.mediadelivery.net/play/637820/8ad51273-b113-483a-a1ad-9706fcb37222','Video','upload','video',NULL,'active','1','1','0','2026-04-06 17:03:35',NULL),(3,'Technologies in Agriculture','Agricultural technology (AgTech) uses innovations like AI, robotics, drones, and IoT sensors to enhance farming efficiency, sustainability, and yield.','USR1775360573',1,1,NULL,NULL,'https://www.youtube.com/embed/Qkpm_Z7Ib88?si=KQuYWWA3mIScF_Kz','Video','upload','video',NULL,'active','1','1','0','2026-04-06 17:35:12',NULL),(4,'What is soil?','<p>Getting started to soil</p>','USR1775360573',1,3,NULL,NULL,'https://www.youtube.com/embed/Qkpm_Z7Ib88?si=KQuYWWA3mIScF_Kz','Video','upload','video',NULL,'active','1','1','0','2026-04-06 18:19:33',NULL),(5,'Types of casava','<p><strong>Getting</strong> to know type of cassava</p>','USR1775360573',1,13,NULL,NULL,'https://www.youtube.com/embed/Qkpm_Z7Ib88?si=KQuYWWA3mIScF_Kz','Video','upload','video',NULL,'active','0','1','1','2026-04-06 18:41:51',NULL),(6,'Where to store crops','<p>Stores and <strong>inbuilt</strong> form stores</p>','USR1775360573',1,15,NULL,NULL,'https://www.youtube.com/embed/Qkpm_Z7Ib88?si=KQuYWWA3mIScF_Kz','Video','upload','video',NULL,'active','0','1','0','2026-04-06 18:44:27',NULL),(7,'Soil types','<p>soil</p>','USR1775360573',1,3,NULL,NULL,'https://youtube.com/watch?v=gtRffa77L0I','Video','upload','video',NULL,'active','0','1','0','2026-04-07 17:57:20',NULL),(8,'Machines Used in Agriculture','<p>Testing contents using PDF</p>','USR1775360573',1,1,NULL,NULL,'uploads/lessons/1775718465_DCM_User_Roles_.pdf','Video','upload','video',NULL,'active','1','1','0','2026-04-09 07:07:45',NULL),(21,'Introduction to Islam','<p>Testing Bunny Storage</p>','USR1775360573',13,32,NULL,NULL,'https://dcmbank.b-cdn.net/DCM/1778312675_HAJIAPPFORM.pdf','pdf','upload','video',NULL,'active','0','0','0',NULL,NULL),(22,'Islamic FOundations','<p>Tetsing storage</p>','USR1775360573',13,32,NULL,NULL,'https://dcmbank.b-cdn.net/DCM/php9iad5p','pdf','upload','video',NULL,'active','0','0','0',NULL,NULL),(23,'Islamic Contracts','<p>Testing</p>','USR1775360573',13,32,NULL,NULL,'https://dcmbank.b-cdn.net/DCM/1778389469_HAJIAPPFORM.pdf','pdf','upload','video',NULL,'active','0','0','0',NULL,NULL),(25,'What is Islam?',NULL,'USR1775360573',14,33,'da12f383-9488-4b55-9f60-7185f69a94ce','659690',NULL,'video','upload','video',NULL,'active',NULL,NULL,NULL,NULL,NULL),(26,'What is sharia?',NULL,'USR1775360573',14,33,'aca1003e-ad1b-482c-82c5-1943e4bcf691','659690',NULL,'video','upload','video',NULL,'active',NULL,NULL,NULL,NULL,NULL),(27,'What is Murabaha?',NULL,'USR1775360573',14,34,NULL,NULL,NULL,'pdf','upload','video',NULL,'active',NULL,NULL,NULL,NULL,NULL),(28,'What is Musharaka?',NULL,'USR1775360573',14,34,NULL,NULL,NULL,'pdf','upload','video',NULL,'active',NULL,NULL,NULL,NULL,NULL),(29,'What is ibada?','<p>Testing Audio Contents</p>','USR1775360573',14,33,NULL,NULL,'https://dcmbank.b-cdn.net/ISLAMIC CONTRACTS/1778842790_file_example_MP3_700KB.mp3','audio','upload','video',NULL,'active','0','0','0',NULL,NULL);
/*!40000 ALTER TABLE `tbl_course_chapter_lessons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_course_chapters`
--

DROP TABLE IF EXISTS `tbl_course_chapters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_course_chapters` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `instructor_id` varchar(200) NOT NULL,
  `chapter_title` varchar(255) NOT NULL,
  `course_id` bigint(20) unsigned NOT NULL,
  `order` int(11) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `course_chapters_course_id_foreign` (`course_id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_course_chapters`
--

LOCK TABLES `tbl_course_chapters` WRITE;
/*!40000 ALTER TABLE `tbl_course_chapters` DISABLE KEYS */;
INSERT INTO `tbl_course_chapters` VALUES (1,'USR1775360573','Introduction to Agriculture',1,NULL,'active',NULL,NULL),(3,'USR1775360573','Types of Soils',1,NULL,'active',NULL,NULL),(5,'USR1775360573','Soil Erosion',1,NULL,'active',NULL,NULL),(7,'USR1775360573','Types of Products',1,NULL,'active',NULL,NULL),(9,'USR1775360573','Agriculture and Marketing',1,NULL,'active',NULL,NULL),(11,'USR1775360573','Cultivation Procedures',1,NULL,'active',NULL,NULL),(13,'USR1775360573','How to cultivate casava',1,NULL,'active',NULL,NULL),(15,'USR1775360573','How to store crops',1,NULL,'active',NULL,NULL),(23,'USR1775360573','Marketing your Crops',1,NULL,'active',NULL,NULL),(24,'USR1775360573','Sales and Marketing',1,NULL,'active',NULL,NULL),(25,'USR1775360573','Jifunze English',7,NULL,'active',NULL,NULL),(26,'USR1775360573','Testing Topic Name',1,NULL,'active',NULL,NULL),(27,'USR1775360573','Introduction to Islamic Financing',10,NULL,'active',NULL,NULL),(28,'USR1775360573','Financing Contracts',10,NULL,'active',NULL,NULL),(31,'USR1775360573','Introduction to Fishing',12,NULL,'active',NULL,NULL),(32,'USR1775360573','What is Islam',13,NULL,'active',NULL,NULL),(33,'USR1775360573','Islamic Laws',14,NULL,'active',NULL,NULL),(34,'USR1775360573','Islamic Contracts',14,NULL,'active',NULL,NULL);
/*!40000 ALTER TABLE `tbl_course_chapters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_course_discussion_answers`
--

DROP TABLE IF EXISTS `tbl_course_discussion_answers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_course_discussion_answers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `discussion_id` int(11) DEFAULT NULL,
  `user_id` varchar(110) DEFAULT NULL,
  `answer` text DEFAULT NULL,
  `is_correct` tinyint(4) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_course_discussion_answers`
--

LOCK TABLES `tbl_course_discussion_answers` WRITE;
/*!40000 ALTER TABLE `tbl_course_discussion_answers` DISABLE KEYS */;
INSERT INTO `tbl_course_discussion_answers` VALUES (1,4,'USR1774352917','Yes there is a way, just see from last 10 minutes video.',0,'2026-04-15 11:04:34'),(2,1,'USR1774352917','Gagagaga',0,'2026-04-16 18:16:22'),(3,2,'USR1774352917','hshshhhs',0,'2026-04-16 18:19:09');
/*!40000 ALTER TABLE `tbl_course_discussion_answers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_course_discussion_likes`
--

DROP TABLE IF EXISTS `tbl_course_discussion_likes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_course_discussion_likes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `discussion_id` int(11) DEFAULT NULL,
  `user_id` varchar(110) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_course_discussion_likes`
--

LOCK TABLES `tbl_course_discussion_likes` WRITE;
/*!40000 ALTER TABLE `tbl_course_discussion_likes` DISABLE KEYS */;
INSERT INTO `tbl_course_discussion_likes` VALUES (1,4,'USR1774352917'),(2,2,'USR1774352917'),(3,1,'USR1774352917'),(4,3,'USR1774352917');
/*!40000 ALTER TABLE `tbl_course_discussion_likes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_course_discussions`
--

DROP TABLE IF EXISTS `tbl_course_discussions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_course_discussions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) DEFAULT NULL,
  `user_id` varchar(110) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_course_discussions`
--

LOCK TABLES `tbl_course_discussions` WRITE;
/*!40000 ALTER TABLE `tbl_course_discussions` DISABLE KEYS */;
INSERT INTO `tbl_course_discussions` VALUES (1,1,'USR1774352917','Je nitaweza kupata cheti kwenye hii kozi','Naomba kufahamu kama nitapata cheti kwenye hii koazi ninayotaka kusoma.','2026-04-11 17:00:56'),(2,1,'USR1774352917','Kupata Cheti','Je nikimaliza hii kozi naweza kupata cheti?','2026-04-11 17:01:31'),(3,1,'USR1774352917','Testing','Testing description','2026-04-11 18:14:03'),(4,1,'USR1774352917','Tech in Agriculture','Is there a way to work on this issue?','2026-04-15 09:14:58');
/*!40000 ALTER TABLE `tbl_course_discussions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_course_enrollments`
--

DROP TABLE IF EXISTS `tbl_course_enrollments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_course_enrollments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `course_id` bigint(20) unsigned NOT NULL,
  `has_access` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `enrollments_order_id_foreign` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_course_enrollments`
--

LOCK TABLES `tbl_course_enrollments` WRITE;
/*!40000 ALTER TABLE `tbl_course_enrollments` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_course_enrollments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_course_invitees`
--

DROP TABLE IF EXISTS `tbl_course_invitees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_course_invitees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `course_id` int(11) DEFAULT NULL,
  `invited_by` varchar(110) DEFAULT NULL,
  `invitation_code` varchar(100) DEFAULT NULL,
  `status` tinyint(4) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_phone_course` (`phone`,`course_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_course_invitees`
--

LOCK TABLES `tbl_course_invitees` WRITE;
/*!40000 ALTER TABLE `tbl_course_invitees` DISABLE KEYS */;
INSERT INTO `tbl_course_invitees` VALUES (4,'Jamal','Juma','255764078960',12,'USR1774352917','REF-FPQJHO',0,'2026-05-01 07:37:46'),(5,'Jamal','Juma','255715087593',12,'USR1774352917','REF-MEUNVH',1,'2026-05-01 07:40:27'),(6,'said','muhama','255625490405',12,'USR1774352917','REF-D2JS6V',0,'2026-05-01 07:41:05'),(7,'Said','Muhama','255765131788',12,'USR1774352917','REF-QZBQNN',1,'2026-05-01 07:45:47');
/*!40000 ALTER TABLE `tbl_course_invitees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_course_progress`
--

DROP TABLE IF EXISTS `tbl_course_progress`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_course_progress` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` varchar(200) DEFAULT NULL,
  `course_id` bigint(20) unsigned DEFAULT NULL,
  `chapter_id` bigint(20) unsigned DEFAULT NULL,
  `lesson_id` bigint(20) unsigned DEFAULT NULL,
  `watched` tinyint(1) NOT NULL DEFAULT 0,
  `current` tinyint(1) NOT NULL DEFAULT 0,
  `type` enum('lesson','quiz','document','live') NOT NULL DEFAULT 'lesson',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_course_progress`
--

LOCK TABLES `tbl_course_progress` WRITE;
/*!40000 ALTER TABLE `tbl_course_progress` DISABLE KEYS */;
INSERT INTO `tbl_course_progress` VALUES (1,'USR1774352917',1,1,1,1,0,'lesson','2026-04-15 07:42:20',NULL),(2,'USR1774352917',1,1,3,1,0,'lesson','2026-04-15 07:47:28',NULL),(3,'USR1774352917',1,1,8,1,0,'lesson','2026-04-15 07:47:37',NULL),(4,'USR1774352917',1,3,4,1,0,'lesson','2026-04-15 12:19:38',NULL),(5,'USR1774352917',1,3,7,1,0,'lesson','2026-04-15 12:19:45',NULL),(6,'USR1774352917',1,13,5,1,0,'lesson','2026-04-16 18:20:09',NULL),(7,'USR1774352917',1,15,6,1,0,'lesson','2026-04-16 18:20:45',NULL),(8,'USR1774352917',12,31,17,1,0,'lesson','2026-05-01 07:54:59',NULL),(9,'USR1774352917',12,31,18,1,0,'lesson','2026-05-01 07:55:02',NULL);
/*!40000 ALTER TABLE `tbl_course_progress` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_course_ratings`
--

DROP TABLE IF EXISTS `tbl_course_ratings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_course_ratings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(50) DEFAULT NULL,
  `course_id` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL,
  `review` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_course_ratings`
--

LOCK TABLES `tbl_course_ratings` WRITE;
/*!40000 ALTER TABLE `tbl_course_ratings` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_course_ratings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_course_wishlist`
--

DROP TABLE IF EXISTS `tbl_course_wishlist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_course_wishlist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(50) DEFAULT NULL,
  `course_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_course_wishlist`
--

LOCK TABLES `tbl_course_wishlist` WRITE;
/*!40000 ALTER TABLE `tbl_course_wishlist` DISABLE KEYS */;
INSERT INTO `tbl_course_wishlist` VALUES (9,'USR1774352917',2,'2026-04-11 04:44:26'),(10,'USR1774352917',1,'2026-04-11 18:16:50');
/*!40000 ALTER TABLE `tbl_course_wishlist` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_courses`
--

DROP TABLE IF EXISTS `tbl_courses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_courses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `instructor_id` varchar(200) NOT NULL,
  `category_id` bigint(20) unsigned DEFAULT NULL,
  `type` enum('course','webinar') NOT NULL DEFAULT 'course',
  `title` varchar(255) NOT NULL,
  `library_id` varchar(200) DEFAULT NULL,
  `library_key` varchar(255) DEFAULT NULL,
  `seo_description` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `timezone` varchar(255) DEFAULT NULL,
  `thumbnail` varchar(255) DEFAULT 'uploads/course_default.png',
  `demo_video_storage` enum('upload','youtube','vimeo','external_link','aws','wasabi') NOT NULL DEFAULT 'upload',
  `demo_video_source` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` double NOT NULL DEFAULT 0,
  `discount` double DEFAULT NULL,
  `certificate` tinyint(1) NOT NULL DEFAULT 0,
  `downloadable` tinyint(1) NOT NULL DEFAULT 0,
  `partner_instructor` tinyint(1) NOT NULL DEFAULT 0,
  `qna` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('active','is_draft','inactive') NOT NULL DEFAULT 'is_draft',
  `is_approved` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_courses`
--

LOCK TABLES `tbl_courses` WRITE;
/*!40000 ALTER TABLE `tbl_courses` DISABLE KEYS */;
INSERT INTO `tbl_courses` VALUES (1,'USR1775360573',NULL,'course','Modern Agriculture 2026',NULL,'',NULL,NULL,NULL,NULL,'uploads/1775847683_Copilot_20260404_081549.png','upload',NULL,'<p>Until the end of this course you will be able to:</p><p><strong>1. Moja<br>2. Mbili<br>3. Tatu</strong></p>',130000,5,1,0,0,1,'active','pending',NULL,'2026-04-21 09:23:00',NULL),(13,'USR1775360573',NULL,'course','ISLAMIC FINANCE','656647','5fa6b796-506e-476e-b1383aa7f610-d300-4f6f',NULL,NULL,NULL,NULL,'uploads/course_default.png','upload',NULL,NULL,0,NULL,0,0,0,0,'is_draft','pending','2026-05-09 04:03:02',NULL,NULL),(14,'USR1775360573',NULL,'course','ISLAMIC CONTRACTS','659690','d83e5426-656a-4976-80f5dfbdae22-b064-49bf',NULL,NULL,NULL,NULL,'uploads/course_default.png','upload',NULL,NULL,0,NULL,0,0,0,0,'is_draft','pending','2026-05-13 12:33:03',NULL,NULL);
/*!40000 ALTER TABLE `tbl_courses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_main_academic_levels`
--

DROP TABLE IF EXISTS `tbl_main_academic_levels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_main_academic_levels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `level_title` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `level_title` (`level_title`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_main_academic_levels`
--

LOCK TABLES `tbl_main_academic_levels` WRITE;
/*!40000 ALTER TABLE `tbl_main_academic_levels` DISABLE KEYS */;
INSERT INTO `tbl_main_academic_levels` VALUES (6,'Courses'),(4,'High School'),(1,'Pre-School'),(2,'Primary School'),(3,'Secondary School'),(5,'Undergraduate');
/*!40000 ALTER TABLE `tbl_main_academic_levels` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_order_items`
--

DROP TABLE IF EXISTS `tbl_order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_order_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` varchar(200) NOT NULL,
  `qty` int(11) NOT NULL DEFAULT 1,
  `price` double NOT NULL,
  `item_type` enum('course','product') NOT NULL DEFAULT 'course',
  `product_id` bigint(20) unsigned DEFAULT NULL,
  `course_id` bigint(20) unsigned NOT NULL,
  `commission_rate` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_id` (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_order_items`
--

LOCK TABLES `tbl_order_items` WRITE;
/*!40000 ALTER TABLE `tbl_order_items` DISABLE KEYS */;
INSERT INTO `tbl_order_items` VALUES (11,'11',1,98000,'course',NULL,12,10,'2026-04-23 12:30:17',NULL);
/*!40000 ALTER TABLE `tbl_order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_order_txn_responce`
--

DROP TABLE IF EXISTS `tbl_order_txn_responce`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_order_txn_responce` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `result` varchar(200) DEFAULT NULL,
  `resultcode` varchar(200) DEFAULT NULL,
  `order_id` varchar(200) DEFAULT NULL,
  `transid` varchar(200) DEFAULT NULL,
  `reference` varchar(200) DEFAULT NULL,
  `channel` varchar(200) DEFAULT NULL,
  `amount` double(16,2) DEFAULT NULL,
  `phone` varchar(200) DEFAULT NULL,
  `payment_status` varchar(200) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_order_txn_responce`
--

LOCK TABLES `tbl_order_txn_responce` WRITE;
/*!40000 ALTER TABLE `tbl_order_txn_responce` DISABLE KEYS */;
INSERT INTO `tbl_order_txn_responce` VALUES (25,'SUCCESS','000','20240702133735','BG28DPWJG2Q','0823194939','MPESA-TZ',1000.00,'255767555958','COMPLETED','2024-07-02 11:38:32'),(26,'SUCCESS','000','20240703120929','7199979274696663804262','0823642368','SELCOMONLINE',2000.00,'255767555958','COMPLETED','2024-07-03 10:12:12'),(27,'SUCCESS','000','20240703122604','7199988929036293904025','0823649667','SELCOMONLINE',1000.00,'255767555958','COMPLETED','2024-07-03 10:28:17'),(28,'SUCCESS','000','20240703135919','BG33DQD43QN','0823689519','MPESA-TZ',1000.00,'255767920027','COMPLETED','2024-07-03 12:03:13'),(29,'SUCCESS','000','20240704212632','BG43DR0ZDDR','0824382405','MPESA-TZ',1000.00,'255767555958','COMPLETED','2024-07-04 19:27:24'),(30,'SUCCESS','000','20240708102750','BG84DSDDCJ6','0825757662','MPESA-TZ',2000.00,'255767555958','COMPLETED','2024-07-08 08:28:24'),(31,'SUCCESS','000','20240709120907','295627132992','9740719926','TIGOPESATZ',2000.00,'255716578678','COMPLETED','2024-07-09 10:09:30'),(32,'SUCCESS','000','20240709121118','BG93DSSYHX5','0826205337','MPESA-TZ',2000.00,'255767555958','COMPLETED','2024-07-09 10:11:59'),(33,'SUCCESS','000','20240709225429','195622546937','9741365592','TIGOPESATZ',2000.00,'255716578678','COMPLETED','2024-07-09 20:55:20'),(34,'SUCCESS','000','20240712092949','BGC0DTYULK8','0827374551','MPESA-TZ',1000.00,'255767555958','COMPLETED','2024-07-12 07:43:54'),(35,'SUCCESS','000','20240713183126','BGD2DULEGO4','0828002367','MPESA-TZ',500.00,'255767555958','COMPLETED','2024-07-13 16:32:12'),(36,'SUCCESS','000','20240713184051','BGD9DULKPW3','0828007350','MPESA-TZ',500.00,'255767555958','COMPLETED','2024-07-13 16:41:45'),(37,'SUCCESS','000','20240716151357','BGG5DVS477F','0829168596','MPESA-TZ',200.00,'255767555958','COMPLETED','2024-07-16 16:03:44'),(38,'SUCCESS','000','20240723125812','BGN6DYQI1WW','0831766065','MPESA-TZ',500.00,'255767555958','COMPLETED','2024-07-23 10:58:56'),(39,'SUCCESS','000','20240723131310','803611599875','0831771642','AIRTELMONEY',500.00,'255684140410','COMPLETED','2024-07-23 11:14:27'),(40,'SUCCESS','000','20240724194612','BGO9DZC85IX','0832349526','MPESA-TZ',2000.00,'255767555958','COMPLETED','2024-07-24 17:46:51'),(41,'SUCCESS','000','20240726000233','BGQ5DZW0U6T','0832867338','MPESA-TZ',50000.00,'255754785515','COMPLETED','2024-07-25 22:03:19'),(42,'SUCCESS','000','20250105133152','CA55G5T5LV3','0891355977','MPESA-TZ',500.00,'255767555958','COMPLETED','2025-01-05 10:32:36'),(43,'SUCCESS','000','20250109125714','CA90G7QIYY0','0892696317','MPESA-TZ',1000.00,'255767555958','COMPLETED','2025-01-09 09:57:57'),(44,'SUCCESS','000','20250109130151','CA98G7QLPMG','0892697940','MPESA-TZ',500.00,'255767555958','COMPLETED','2025-01-09 10:02:24'),(45,'SUCCESS','000','20250109215105','CA96G80YT0Y','0892898655','MPESA-TZ',500.00,'255767555958','COMPLETED','2025-01-09 18:52:11'),(46,'SUCCESS','000','20250205124634','CB58GLCW52U','0901948155','MPESA-TZ',500.00,'255767555958','COMPLETED','2025-02-05 09:47:16'),(47,'SUCCESS','000','20250205125055','CB54GLCYXJ4','0901949580','MPESA-TZ',500.00,'255767555958','COMPLETED','2025-02-05 09:51:35'),(48,'SUCCESS','000','20250206173107','CB69GM1H2F1','0902387994','MPESA-TZ',1000.00,'255767555958','COMPLETED','2025-02-06 14:31:38'),(49,'SUCCESS','000','20250206231212','CB62GM8JXOA','0902521761','MPESA-TZ',35000.00,'255767555958','COMPLETED','2025-02-06 20:13:09'),(50,'SUCCESS','000','20250207155301','CB73GMHV0MZ','0902686623','MPESA-TZ',5000.00,'255767555958','COMPLETED','2025-02-07 12:53:44'),(51,'SUCCESS','000','20250208155842','CB82GN0IC6W','0903033243','MPESA-TZ',5000.00,'255767555958','COMPLETED','2025-02-08 12:59:21'),(52,'SUCCESS','000','20250208175334','CB89GN2P7BZ','0903072714','MPESA-TZ',500.00,'255767555958','COMPLETED','2025-02-08 14:54:09'),(53,'SUCCESS','000','20250208185924','CB87GN42NJP','0903097350','MPESA-TZ',1000.00,'255767555958','COMPLETED','2025-02-08 16:00:03'),(54,'SUCCESS','000','20250208214507','CB86GN87PQ6','0903178941','MPESA-TZ',1000.00,'255767555958','COMPLETED','2025-02-08 18:47:02'),(55,'SUCCESS','000','20250209193538','CB91GNMMIAP','0903436167','MPESA-TZ',500.00,'255767920027','COMPLETED','2025-02-09 16:36:33'),(56,'SUCCESS','000','20250211075321','CBB7GOA83Q5','0903871878','MPESA-TZ',500.00,'255767555958','COMPLETED','2025-02-11 04:54:09'),(57,'SUCCESS','000','20250211075800','CBB7GOAABU1','0903872826','MPESA-TZ',1000.00,'255767555958','COMPLETED','2025-02-11 05:01:07'),(58,'SUCCESS','000','20250212125612','CBC4GOX2PEK','0904286085','MPESA-TZ',500.00,'255767555958','COMPLETED','2025-02-12 09:57:27'),(59,'SUCCESS','000','20250212140948','CBC6GOYACA0','0904307478','MPESA-TZ',500.00,'255767555958','COMPLETED','2025-02-12 11:10:21'),(60,'SUCCESS','000','20250213201102','CBD7GPN46D5','0904770078','MPESA-TZ',500.00,'255767555958','COMPLETED','2025-02-13 17:11:42'),(61,'SUCCESS','000','20250213203906','CBD7GPNW13R','0904783893','MPESA-TZ',500.00,'255767555958','COMPLETED','2025-02-13 17:39:47'),(62,'SUCCESS','000','20250220202653','CBK5GT4P8K3','0907079871','MPESA-TZ',500.00,'255754785515','COMPLETED','2025-02-20 17:27:33'),(63,'SUCCESS','000','20250316214745','CCG2H5RJZXQ','0915283038','MPESA-TZ',20000.00,'255767307655','COMPLETED','2025-03-16 18:48:41'),(64,'SUCCESS','000','20250316215216','CCG7H5RM9U7','0915284073','MPESA-TZ',5000.00,'255767307655','COMPLETED','2025-03-16 18:52:50'),(65,'SUCCESS','000','20250318221607','CCI9H6QWO6X','0915910728','MPESA-TZ',5000.00,'255767555958','COMPLETED','2025-03-18 19:18:50'),(66,'SUCCESS','000','20250514124454','CEE4I14KMY0','0934632510','MPESA-TZ',500.00,'255767555958','COMPLETED','2025-05-14 10:45:38'),(67,'SUCCESS','000','20250519123825','CEJ0I3WPRUU','0936258957','MPESA-TZ',2000.00,'255767555958','COMPLETED','2025-05-19 10:41:04'),(68,'SUCCESS','000','20250520112124','25503601539005','1193045790','TIGOPESATZ',10000.00,'255718228871','COMPLETED','2025-05-20 09:22:32');
/*!40000 ALTER TABLE `tbl_order_txn_responce` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_orders`
--

DROP TABLE IF EXISTS `tbl_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` text DEFAULT NULL,
  `instructor_id` varchar(200) DEFAULT NULL,
  `user_id` varchar(200) DEFAULT NULL,
  `has_coupon` tinyint(1) NOT NULL DEFAULT 0,
  `coupon_code` varchar(255) DEFAULT NULL,
  `coupon_discount_percent` int(11) DEFAULT NULL,
  `coupon_discount_amount` double DEFAULT NULL,
  `payment_method` varchar(255) DEFAULT NULL,
  `payment_status` varchar(255) DEFAULT NULL,
  `payable_amount` double DEFAULT NULL,
  `paid_amount` double DEFAULT NULL,
  `payment_details` text DEFAULT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `commission_rate` int(11) DEFAULT NULL,
  `order_details` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_orders`
--

LOCK TABLES `tbl_orders` WRITE;
/*!40000 ALTER TABLE `tbl_orders` DISABLE KEYS */;
INSERT INTO `tbl_orders` VALUES (11,'INV-1776947417','USR1775360573','USR1774352917',0,NULL,NULL,NULL,'ONLINE','PAID',98000,98000,NULL,NULL,10,NULL,'2026-04-23 12:30:17',NULL);
/*!40000 ALTER TABLE `tbl_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_payment_order`
--

DROP TABLE IF EXISTS `tbl_payment_order`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_payment_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vendor` varchar(200) DEFAULT NULL,
  `order_id` varchar(200) DEFAULT NULL,
  `buyer_email` varchar(200) DEFAULT NULL,
  `buyer_name` varchar(200) DEFAULT NULL,
  `buyer_phone` varchar(200) DEFAULT NULL,
  `amount` double(16,2) DEFAULT NULL,
  `currency` varchar(200) DEFAULT NULL,
  `redirect_url` varchar(200) DEFAULT NULL,
  `cancel_url` varchar(200) DEFAULT NULL,
  `webhook` varchar(200) DEFAULT NULL,
  `buyer_remarks` text DEFAULT NULL,
  `merchant_remarks` text DEFAULT NULL,
  `no_of_items` int(11) DEFAULT NULL,
  `order_status` varchar(100) DEFAULT 'Pending',
  `created_at` datetime DEFAULT current_timestamp(),
  `address_1` varchar(200) DEFAULT NULL,
  `state_or_region` varchar(200) DEFAULT NULL,
  `postcode_or_pobox` varchar(200) DEFAULT NULL,
  `country` varchar(200) DEFAULT NULL,
  `city` varchar(200) DEFAULT NULL,
  `username` varchar(200) DEFAULT NULL,
  `payment_gateway_url` varchar(300) DEFAULT NULL,
  `pay_type` varchar(200) DEFAULT NULL,
  `is_alerted` varchar(30) NOT NULL DEFAULT 'No',
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_id` (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=452 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_payment_order`
--

LOCK TABLES `tbl_payment_order` WRITE;
/*!40000 ALTER TABLE `tbl_payment_order` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_payment_order` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_payments`
--

DROP TABLE IF EXISTS `tbl_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) DEFAULT NULL,
  `subscription_id` int(11) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_date` datetime NOT NULL,
  `payment_method` enum('mobile','card','bank','cash') DEFAULT 'mobile',
  `transaction_ref` varchar(150) DEFAULT NULL,
  `status` enum('paid','pending','failed') DEFAULT 'paid',
  `access_start` date DEFAULT NULL,
  `access_end` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_student` (`student_id`),
  KEY `idx_course` (`course_id`),
  KEY `idx_subscription` (`subscription_id`),
  KEY `idx_payment_date` (`payment_date`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_payments`
--

LOCK TABLES `tbl_payments` WRITE;
/*!40000 ALTER TABLE `tbl_payments` DISABLE KEYS */;
INSERT INTO `tbl_payments` VALUES (1,1,2,NULL,30000.00,'2025-01-05 10:00:00','mobile',NULL,'paid',NULL,NULL,'2026-04-05 16:28:14','2026-04-05 16:28:14'),(2,2,3,NULL,45000.00,'2025-01-10 12:00:00','card',NULL,'paid',NULL,NULL,'2026-04-05 16:28:14','2026-04-05 16:28:14'),(3,1,NULL,NULL,100000.00,'2025-02-01 09:00:00','mobile',NULL,'paid',NULL,NULL,'2026-04-05 16:28:14','2026-04-05 16:28:14'),(4,3,1,NULL,25000.00,'2025-02-15 14:00:00','bank',NULL,'paid',NULL,NULL,'2026-04-05 16:28:14','2026-04-05 16:28:14');
/*!40000 ALTER TABLE `tbl_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_sms_logs`
--

DROP TABLE IF EXISTS `tbl_sms_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_sms_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phone_number` varchar(30) DEFAULT NULL,
  `message_body` text DEFAULT NULL,
  `message_id` varchar(100) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `status_code` varchar(20) DEFAULT NULL,
  `sms_cost` varchar(50) DEFAULT NULL,
  `api_response` longtext DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_sms_logs`
--

LOCK TABLES `tbl_sms_logs` WRITE;
/*!40000 ALTER TABLE `tbl_sms_logs` DISABLE KEYS */;
INSERT INTO `tbl_sms_logs` VALUES (1,'255765131788','Testing if message get Sent',NULL,'FAILED',NULL,NULL,'Table \'e_learning.sms_logs\' doesn\'t exist','2026-05-01 06:23:24'),(2,'255765131788','Testing if message get Sent',NULL,'FAILED',NULL,NULL,'Table \'e_learning.sms_logs\' doesn\'t exist','2026-05-01 06:30:14'),(3,'255765131788','Testing if message get Sent','','FAILED','','','{\"status\":\"success\",\"data\":{\"SMSMessageData\":{\"Message\":\"Sent to 1\\/1 Total Cost: TZS 22.0000\",\"Recipients\":[{\"cost\":\"TZS 22.0000\",\"messageId\":\"ATXid_43a8c72efefa0621e870cbd2d395da76\",\"messageParts\":1,\"number\":\"+255765131788\",\"status\":\"Success\",\"statusCode\":100}]}}}','2026-05-01 06:36:42'),(4,'255765131788','Testing if message get Sent','ATXid_4e52e236452d8a94c04b1d2b5ff5f31c','Success','100','TZS 22.0000','{\"status\":\"success\",\"data\":{\"SMSMessageData\":{\"Message\":\"Sent to 1\\/1 Total Cost: TZS 22.0000\",\"Recipients\":[{\"cost\":\"TZS 22.0000\",\"messageId\":\"ATXid_4e52e236452d8a94c04b1d2b5ff5f31c\",\"messageParts\":1,\"number\":\"+255765131788\",\"status\":\"Success\",\"statusCode\":100}]}}}','2026-05-01 06:41:56'),(5,'255765131788','Testing if message get Sent','ATXid_82252a21d3b452b056617786565c38d9','Success','100','TZS 22.0000','{\"status\":\"success\",\"data\":{\"SMSMessageData\":{\"Message\":\"Sent to 1\\/1 Total Cost: TZS 22.0000\",\"Recipients\":[{\"cost\":\"TZS 22.0000\",\"messageId\":\"ATXid_82252a21d3b452b056617786565c38d9\",\"messageParts\":1,\"number\":\"+255765131788\",\"status\":\"Success\",\"statusCode\":100}]}}}','2026-05-01 06:45:08'),(6,'255765131788','Testing if message get Sent','ATXid_b8f9fa01b1077e5657e21d64346c44b0','Success','100','TZS 22.0000','{\"status\":\"success\",\"data\":{\"SMSMessageData\":{\"Message\":\"Sent to 1\\/1 Total Cost: TZS 22.0000\",\"Recipients\":[{\"cost\":\"TZS 22.0000\",\"messageId\":\"ATXid_b8f9fa01b1077e5657e21d64346c44b0\",\"messageParts\":1,\"number\":\"+255765131788\",\"status\":\"Success\",\"statusCode\":100}]}}}','2026-05-01 06:46:04'),(7,'255765131788','You have been invited to join our course MORDERN FISHING TECH. Your invitation code is REF-39L678. Please visit our website to register.','ATXid_4b1443e91d2cb6d16c82f55846d7953e','Success','100','TZS 22.0000','{\"status\":\"success\",\"data\":{\"SMSMessageData\":{\"Message\":\"Sent to 1\\/1 Total Cost: TZS 22.0000\",\"Recipients\":[{\"cost\":\"TZS 22.0000\",\"messageId\":\"ATXid_4b1443e91d2cb6d16c82f55846d7953e\",\"messageParts\":1,\"number\":\"+255765131788\",\"status\":\"Success\",\"statusCode\":100}]}}}','2026-05-01 08:15:40'),(8,'255255765131788','Congratulations! Your username is: james.samwel@gmail.com and password is: Secure@321!','None','InvalidPhoneNumber','403','0','{\"status\":\"success\",\"data\":{\"SMSMessageData\":{\"Message\":\"Sent to 0\\/1 Total Cost: 0\",\"Recipients\":[{\"cost\":\"0\",\"messageId\":\"None\",\"number\":\"255255765131788\",\"status\":\"InvalidPhoneNumber\",\"statusCode\":403}]}}}','2026-05-01 09:33:17'),(9,'255765131788','Congratulations! Your username is: james.samwel@gmail.com and password is: Secure@321!','ATXid_5a5c886f23e576b560463e813708df69','Success','100','TZS 22.0000','{\"status\":\"success\",\"data\":{\"SMSMessageData\":{\"Message\":\"Sent to 1\\/1 Total Cost: TZS 22.0000\",\"Recipients\":[{\"cost\":\"TZS 22.0000\",\"messageId\":\"ATXid_5a5c886f23e576b560463e813708df69\",\"messageParts\":1,\"number\":\"+255765131788\",\"status\":\"Success\",\"statusCode\":100}]}}}','2026-05-01 09:35:57'),(10,'255764078960','You have been invited to join our course MORDERN FISHING TECHNOLOGIES. Your invitation code is REF-FPQJHO. Please visit our website digitalcoursemedia.com to register.','ATXid_143bcf1b6e96452f9448c6dc032c4ca9','Success','100','TZS 44.0000','{\"status\":\"success\",\"data\":{\"SMSMessageData\":{\"Message\":\"Sent to 1\\/1 Total Cost: TZS 44.0000\",\"Recipients\":[{\"cost\":\"TZS 44.0000\",\"messageId\":\"ATXid_143bcf1b6e96452f9448c6dc032c4ca9\",\"messageParts\":2,\"number\":\"+255764078960\",\"status\":\"Success\",\"statusCode\":100}]}}}','2026-05-01 10:37:47'),(11,'255715087593','You have been invited to join our course MORDERN FISHING TECHNOLOGIES. Your invitation code is REF-MEUNVH. Please visit our website digitalcoursemedia.com to register.','ATXid_696f9b6b226b88ceb4e8f67910a4acb7','Success','100','TZS 44.0000','{\"status\":\"success\",\"data\":{\"SMSMessageData\":{\"Message\":\"Sent to 1\\/1 Total Cost: TZS 44.0000\",\"Recipients\":[{\"cost\":\"TZS 44.0000\",\"messageId\":\"ATXid_696f9b6b226b88ceb4e8f67910a4acb7\",\"messageParts\":2,\"number\":\"+255715087593\",\"status\":\"Success\",\"statusCode\":100}]}}}','2026-05-01 10:40:28'),(12,'255625490405','You have been invited to join our course MORDERN FISHING TECHNOLOGIES. Your invitation code is REF-D2JS6V. Please visit our website digitalcoursemedia.com to register.','ATXid_b6488e82eb14a4f974b29f0bfc72e694','Success','100','TZS 44.0000','{\"status\":\"success\",\"data\":{\"SMSMessageData\":{\"Message\":\"Sent to 1\\/1 Total Cost: TZS 44.0000\",\"Recipients\":[{\"cost\":\"TZS 44.0000\",\"messageId\":\"ATXid_b6488e82eb14a4f974b29f0bfc72e694\",\"messageParts\":2,\"number\":\"+255625490405\",\"status\":\"Success\",\"statusCode\":100}]}}}','2026-05-01 10:41:06'),(13,'255715087593','Congratulations! Your username is: jamal.juma@gmail.com and password is: Test@2026!','ATXid_bef799b9854ebca8a498f86dafdd321c','Success','100','TZS 22.0000','{\"status\":\"success\",\"data\":{\"SMSMessageData\":{\"Message\":\"Sent to 1\\/1 Total Cost: TZS 22.0000\",\"Recipients\":[{\"cost\":\"TZS 22.0000\",\"messageId\":\"ATXid_bef799b9854ebca8a498f86dafdd321c\",\"messageParts\":1,\"number\":\"+255715087593\",\"status\":\"Success\",\"statusCode\":100}]}}}','2026-05-01 10:43:37'),(14,'255765131788','You have been invited to join our course MORDERN FISHING TECHNOLOGIES. Your invitation code is REF-QZBQNN. Please visit our website digitalcoursemedia.com to register.','ATXid_3fe4f4ab438907271d5cb4fecd6a2b76','Success','100','TZS 44.0000','{\"status\":\"success\",\"data\":{\"SMSMessageData\":{\"Message\":\"Sent to 1\\/1 Total Cost: TZS 44.0000\",\"Recipients\":[{\"cost\":\"TZS 44.0000\",\"messageId\":\"ATXid_3fe4f4ab438907271d5cb4fecd6a2b76\",\"messageParts\":2,\"number\":\"+255765131788\",\"status\":\"Success\",\"statusCode\":100}]}}}','2026-05-01 10:45:47'),(15,'255765131788','Congratulations! Your username is: super@gmail.com and password is: Pass@123!','ATXid_ea02abac8468f570b008259e144af60b','Success','100','TZS 22.0000','{\"status\":\"success\",\"data\":{\"SMSMessageData\":{\"Message\":\"Sent to 1\\/1 Total Cost: TZS 22.0000\",\"Recipients\":[{\"cost\":\"TZS 22.0000\",\"messageId\":\"ATXid_ea02abac8468f570b008259e144af60b\",\"messageParts\":1,\"number\":\"+255765131788\",\"status\":\"Success\",\"statusCode\":100}]}}}','2026-05-01 10:50:17');
/*!40000 ALTER TABLE `tbl_sms_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_students`
--

DROP TABLE IF EXISTS `tbl_students`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) DEFAULT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `dob` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `skill` varchar(100) DEFAULT NULL,
  `parent_name` varchar(100) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `school` varchar(100) DEFAULT NULL,
  `course` varchar(100) DEFAULT NULL,
  `image` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `usr_code` varchar(200) NOT NULL,
  `end_year` varchar(110) DEFAULT NULL,
  `start_year` int(11) DEFAULT NULL,
  `sub_academic_level` int(11) DEFAULT NULL,
  `main_academic_level` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usr_code` (`usr_code`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_students`
--

LOCK TABLES `tbl_students` WRITE;
/*!40000 ALTER TABLE `tbl_students` DISABLE KEYS */;
INSERT INTO `tbl_students` VALUES (2,'Said','Muhama','Kitunda','19/03/2024','A student of High School at Elboru SS','NA','Muhammed Said','+255625490405','muhammed_said@gmail.com',NULL,NULL,'uploads/1775279778.png','2026-04-03 16:13:59','USR1774352917','Passed',2026,10,2);
/*!40000 ALTER TABLE `tbl_students` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_sub_academic_levels`
--

DROP TABLE IF EXISTS `tbl_sub_academic_levels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_sub_academic_levels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `main_level` int(11) DEFAULT NULL,
  `sub_level_title` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_sub_academic_levels`
--

LOCK TABLES `tbl_sub_academic_levels` WRITE;
/*!40000 ALTER TABLE `tbl_sub_academic_levels` DISABLE KEYS */;
INSERT INTO `tbl_sub_academic_levels` VALUES (1,6,'N/A'),(2,4,'Form 5'),(3,4,'Form 6'),(4,1,'baby Class'),(5,1,'Pre Unit'),(6,2,'Grade (Class) 1'),(7,2,'Grade (Class) 2'),(8,2,'Grade (Class) 3'),(9,2,'Grade (Class) 4'),(10,2,'Grade (Class) 5'),(11,2,'Grade (Class) 6'),(12,2,'Grade (Class) 7'),(13,3,'Form 1'),(14,3,'Form 2'),(15,3,'Form 3'),(16,3,'Form 4'),(19,5,'N/A');
/*!40000 ALTER TABLE `tbl_sub_academic_levels` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_tutors`
--

DROP TABLE IF EXISTS `tbl_tutors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_tutors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) DEFAULT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `dob` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `skill` varchar(100) DEFAULT NULL,
  `parent_name` varchar(100) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `school` varchar(100) DEFAULT NULL,
  `course` varchar(100) DEFAULT NULL,
  `image` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `usr_code` varchar(200) NOT NULL,
  `end_year` varchar(110) DEFAULT NULL,
  `start_year` int(11) DEFAULT NULL,
  `sub_academic_level` varchar(110) DEFAULT NULL,
  `main_academic_level` varchar(200) DEFAULT NULL,
  `country` varchar(200) DEFAULT NULL,
  `city` varchar(200) DEFAULT NULL,
  `town` varchar(200) DEFAULT NULL,
  `street` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usr_code` (`usr_code`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_tutors`
--

LOCK TABLES `tbl_tutors` WRITE;
/*!40000 ALTER TABLE `tbl_tutors` DISABLE KEYS */;
INSERT INTO `tbl_tutors` VALUES (2,'Hamza','Ramadhani','Pazia','01/05/2023','I\'m a lecturer at University of Dar es salaam based in Tanzania','NA','Hamza Ramadhani Pazia','+255625490405','muhammed_said@gmail.com',NULL,'Bachelor of science in Information Technology','uploads/1775367637.png','2026-04-03 16:13:59','USR1775360573','Passed',2020,'University of dar es salaam','Bachelor','Tanzania','Dar es salaam','Kinondoni','Muhama Street');
/*!40000 ALTER TABLE `tbl_tutors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_user_roles`
--

DROP TABLE IF EXISTS `tbl_user_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_user_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_title` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_user_roles`
--

LOCK TABLES `tbl_user_roles` WRITE;
/*!40000 ALTER TABLE `tbl_user_roles` DISABLE KEYS */;
INSERT INTO `tbl_user_roles` VALUES (1,'Student'),(2,'Parent / Guardian'),(3,'Instructor / Teacher'),(4,'School / Institutional');
/*!40000 ALTER TABLE `tbl_user_roles` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-15 20:48:18
