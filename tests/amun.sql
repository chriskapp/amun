-- MySQL dump 10.13  Distrib 5.5.8, for Win32 (x86)
--
-- Host: localhost    Database: amun_dev
-- ------------------------------------------------------
-- Server version 5.5.8

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
-- Table structure for table `amun_comment`
--

DROP TABLE IF EXISTS `amun_comment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amun_comment` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `globalId` varchar(36) NOT NULL,
  `pageId` int(10) NOT NULL,
  `userId` int(10) NOT NULL,
  `refId` int(10) NOT NULL,
  `text` longtext NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `globalId` (`globalId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_comment`
--

LOCK TABLES `amun_comment` WRITE;
/*!40000 ALTER TABLE `amun_comment` DISABLE KEYS */;
/*!40000 ALTER TABLE `amun_comment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `amun_content_gadget`
--

DROP TABLE IF EXISTS `amun_content_gadget`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amun_content_gadget` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `globalId` varchar(36) NOT NULL,
  `serviceId` int(10) NOT NULL,
  `rightId` int(10) NOT NULL,
  `type` enum('inline','iframe','ajax') NOT NULL DEFAULT 'inline',
  `name` varchar(64) NOT NULL,
  `title` varchar(32) NOT NULL,
  `class` varchar(128) NOT NULL,
  `param` text,
  `cache` tinyint(1) NOT NULL,
  `expire` varchar(25) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `globalId` (`globalId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_content_gadget`
--

LOCK TABLES `amun_content_gadget` WRITE;
/*!40000 ALTER TABLE `amun_content_gadget` DISABLE KEYS */;
/*!40000 ALTER TABLE `amun_content_gadget` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `amun_content_page`
--

DROP TABLE IF EXISTS `amun_content_page`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amun_content_page` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `parentId` int(10) NOT NULL,
  `globalId` varchar(36) NOT NULL,
  `serviceId` int(10) NOT NULL,
  `rightId` int(10) NOT NULL,
  `status` int(10) NOT NULL,
  `load` int(10) NOT NULL DEFAULT '3',
  `sort` int(10) NOT NULL DEFAULT '0',
  `path` varchar(256) NOT NULL,
  `urlTitle` varchar(32) NOT NULL,
  `title` varchar(32) NOT NULL,
  `template` text NOT NULL,
  `description` varchar(256) NOT NULL,
  `keywords` varchar(256) NOT NULL,
  `cache` tinyint(1) NOT NULL,
  `expire` varchar(25) NOT NULL,
  `publishDate` datetime NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `globalId` (`globalId`),
  UNIQUE KEY `path` (`path`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_content_page`
--

LOCK TABLES `amun_content_page` WRITE;
/*!40000 ALTER TABLE `amun_content_page` DISABLE KEYS */;
INSERT INTO `amun_content_page` VALUES (1,0,'ee131e4b-7165-5088-8bbc-c4521018f9a8',26,0,2,3,0,'','sample','Sample','','','',0,'','0000-00-00 00:00:00','2013-08-26 20:57:46'),(2,1,'30df2065-2531-512a-a998-872951a59cfc',26,0,1,3,0,'home','home','Home','','','',0,'','0000-00-00 00:00:00','2013-08-26 20:57:46'),(3,1,'756d2437-2ddc-53b9-a55e-e1cd1563bde4',16,0,2,3,0,'login','login','Login','','','',0,'','0000-00-00 00:00:00','2013-08-26 20:57:46'),(4,1,'5a81aa51-e08a-5861-a222-2a90194a7a50',17,0,2,3,0,'my','my','My','','','',0,'','0000-00-00 00:00:00','2013-08-26 20:57:46'),(5,1,'13f25b76-48d3-5e8c-a445-558818996a2a',18,0,2,3,0,'profile','profile','Profile','','','',0,'','0000-00-00 00:00:00','2013-08-26 20:57:46'),(6,1,'797fec38-2c87-5ff5-a00e-edcada83841d',26,0,2,3,0,'help','help','Help','','','',0,'','0000-00-00 00:00:00','2013-08-26 20:57:46');
/*!40000 ALTER TABLE `amun_content_page` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `amun_content_page_gadget`
--

DROP TABLE IF EXISTS `amun_content_page_gadget`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amun_content_page_gadget` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `pageId` int(10) NOT NULL,
  `gadgetId` int(10) NOT NULL,
  `sort` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `pageGadgetId` (`pageId`,`gadgetId`)
) ENGINE=MyISAM DEFAULT CHARSET=ascii;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_content_page_gadget`
--

LOCK TABLES `amun_content_page_gadget` WRITE;
/*!40000 ALTER TABLE `amun_content_page_gadget` DISABLE KEYS */;
/*!40000 ALTER TABLE `amun_content_page_gadget` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `amun_core_approval`
--

DROP TABLE IF EXISTS `amun_core_approval`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amun_core_approval` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `table` varchar(64) NOT NULL,
  `field` varchar(32) NOT NULL DEFAULT '',
  `value` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_core_approval`
--

LOCK TABLES `amun_core_approval` WRITE;
/*!40000 ALTER TABLE `amun_core_approval` DISABLE KEYS */;
/*!40000 ALTER TABLE `amun_core_approval` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `amun_core_approval_record`
--

DROP TABLE IF EXISTS `amun_core_approval_record`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amun_core_approval_record` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `userId` int(10) NOT NULL,
  `type` enum('INSERT','UPDATE','DELETE') NOT NULL,
  `table` varchar(64) NOT NULL,
  `record` text NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_core_approval_record`
--

LOCK TABLES `amun_core_approval_record` WRITE;
/*!40000 ALTER TABLE `amun_core_approval_record` DISABLE KEYS */;
/*!40000 ALTER TABLE `amun_core_approval_record` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `amun_core_assoc`
--

DROP TABLE IF EXISTS `amun_core_assoc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amun_core_assoc` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `opEndpoint` varchar(256) NOT NULL,
  `assocHandle` varchar(512) NOT NULL,
  `assocType` enum('HMAC-SHA1','HMAC-SHA256') NOT NULL,
  `sessionType` enum('DH-SHA1','DH-SHA256') NOT NULL,
  `secret` varchar(256) NOT NULL,
  `expires` int(10) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=ascii;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_core_assoc`
--

LOCK TABLES `amun_core_assoc` WRITE;
/*!40000 ALTER TABLE `amun_core_assoc` DISABLE KEYS */;
/*!40000 ALTER TABLE `amun_core_assoc` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `amun_core_event`
--

DROP TABLE IF EXISTS `amun_core_event`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amun_core_event` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `interface` varchar(64) DEFAULT NULL,
  `description` varchar(256) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_core_event`
--

LOCK TABLES `amun_core_event` WRITE;
/*!40000 ALTER TABLE `amun_core_event` DISABLE KEYS */;
INSERT INTO `amun_core_event` VALUES (1,'core.service_install',NULL,'Notifies if a service gets installed'),(2,'core.record_change',NULL,'Notifies if a record has changed');
/*!40000 ALTER TABLE `amun_core_event` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `amun_core_event_listener`
--

DROP TABLE IF EXISTS `amun_core_event_listener`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amun_core_event_listener` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `eventId` int(10) NOT NULL,
  `priority` int(10) NOT NULL,
  `class` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_core_event_listener`
--

LOCK TABLES `amun_core_event_listener` WRITE;
/*!40000 ALTER TABLE `amun_core_event_listener` DISABLE KEYS */;
INSERT INTO `amun_core_event_listener` VALUES (1,1,0,'AmunService\\Core\\Service\\Option\\ConfigListener'),(2,1,0,'AmunService\\Xrds\\ConfigListener'),(3,2,0,'AmunService\\User\\Activity\\RecordListener'),(4,1,0,'AmunService\\User\\Activity\\ConfigListener'),(5,1,0,'AmunService\\User\\Right\\ConfigListener'),(6,2,0,'AmunService\\Log\\RecordListener'),(8,1,0,'AmunService\\Mail\\ConfigListener');
/*!40000 ALTER TABLE `amun_core_event_listener` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `amun_core_host`
--

DROP TABLE IF EXISTS `amun_core_host`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amun_core_host` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `status` int(10) NOT NULL,
  `name` varchar(256) NOT NULL,
  `consumerKey` varchar(40) NOT NULL,
  `consumerSecret` varchar(40) NOT NULL,
  `url` varchar(256) NOT NULL,
  `template` varchar(256) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_core_host`
--

LOCK TABLES `amun_core_host` WRITE;
/*!40000 ALTER TABLE `amun_core_host` DISABLE KEYS */;
/*!40000 ALTER TABLE `amun_core_host` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `amun_core_host_request`
--

DROP TABLE IF EXISTS `amun_core_host_request`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amun_core_host_request` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `hostId` int(10) NOT NULL,
  `userId` int(10) NOT NULL,
  `ip` varchar(40) NOT NULL,
  `token` varchar(40) NOT NULL,
  `tokenSecret` varchar(40) NOT NULL,
  `expire` varchar(25) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=ascii;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_core_host_request`
--

LOCK TABLES `amun_core_host_request` WRITE;
/*!40000 ALTER TABLE `amun_core_host_request` DISABLE KEYS */;
/*!40000 ALTER TABLE `amun_core_host_request` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `amun_core_registry`
--

DROP TABLE IF EXISTS `amun_core_registry`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amun_core_registry` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `type` enum('STRING','INTEGER','FLOAT','BOOLEAN') NOT NULL,
  `class` varchar(64) DEFAULT NULL,
  `value` varchar(128) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=67 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_core_registry`
--

LOCK TABLES `amun_core_registry` WRITE;
/*!40000 ALTER TABLE `amun_core_registry` DISABLE KEYS */;
INSERT INTO `amun_core_registry` VALUES (1,'table.core_approval','STRING',NULL,'amun_core_approval'),(2,'table.core_approval_record','STRING',NULL,'amun_core_approval_record'),(3,'table.core_event','STRING',NULL,'amun_core_event'),(4,'table.core_event_listener','STRING',NULL,'amun_core_event_listener'),(5,'table.core_registry','STRING',NULL,'amun_core_registry'),(6,'table.core_service','STRING',NULL,'amun_core_service'),(7,'core.default_timezone','STRING','DateTimeZone','UTC'),(8,'table.core_service_option','STRING',NULL,'amun_core_service_option'),(9,'table.core_assoc','STRING',NULL,'amun_core_assoc'),(10,'table.core_host','STRING',NULL,'amun_core_host'),(11,'table.core_host_request','STRING',NULL,'amun_core_host_request'),(12,'core.title','STRING',NULL,'Sample'),(13,'core.sub_title','STRING',NULL,''),(14,'core.anonymous_user','INTEGER',NULL,'2'),(15,'core.session_expire','INTEGER',NULL,'1800'),(16,'core.default_user_group','INTEGER',NULL,'2'),(17,'core.default_page','STRING',NULL,'home'),(18,'core.format_datetime','STRING',NULL,'d M. Y, H:i'),(19,'core.format_date','STRING',NULL,'d. F Y'),(20,'core.install_date','STRING',NULL,''),(21,'core.input_limit','INTEGER',NULL,'16'),(22,'core.input_interval','STRING',NULL,'PT30M'),(23,'core.pw_alpha','INTEGER',NULL,'4'),(24,'core.pw_numeric','INTEGER',NULL,'2'),(25,'core.pw_special','INTEGER',NULL,'0'),(26,'table.xrds','STRING',NULL,'amun_xrds'),(27,'table.xrds_type','STRING',NULL,'amun_xrds_type'),(28,'table.user_account','STRING',NULL,'amun_user_account'),(29,'table.user_activity','STRING',NULL,'amun_user_activity'),(30,'table.user_activity_receiver','STRING',NULL,'amun_user_activity_receiver'),(31,'table.user_activity_template','STRING',NULL,'amun_user_activity_template'),(32,'table.user_friend','STRING',NULL,'amun_user_friend'),(33,'table.user_friend_group','STRING',NULL,'amun_user_friend_group'),(34,'table.user_group','STRING',NULL,'amun_user_group'),(35,'table.user_group_right','STRING',NULL,'amun_user_group_right'),(36,'table.user_right','STRING',NULL,'amun_user_right'),(37,'table.log','STRING',NULL,'amun_log'),(38,'table.media','STRING',NULL,'amun_media'),(39,'media.upload_size','INTEGER',NULL,'4194304'),(40,'media.path','STRING',NULL,'../cache'),(41,'table.openid','STRING',NULL,'amun_openid'),(42,'table.openid_access','STRING',NULL,'amun_openid_access'),(43,'table.openid_assoc','STRING',NULL,'amun_openid_assoc'),(44,'table.country','STRING',NULL,'amun_country'),(45,'table.mail','STRING',NULL,'amun_mail'),(46,'table.content_gadget','STRING',NULL,'amun_content_gadget'),(47,'table.content_page','STRING',NULL,'amun_content_page'),(48,'table.content_page_gadget','STRING',NULL,'amun_content_page_gadget'),(49,'table.content_page_option','STRING',NULL,'amun_content_page_option'),(50,'table.content_page_right','STRING',NULL,'amun_content_page_right'),(51,'table.login_attempt','STRING',NULL,'amun_login_attempt'),(52,'login.registration_enabled','BOOLEAN',NULL,'1'),(53,'login.max_wrong','INTEGER',NULL,'8'),(54,'login.provider','STRING',NULL,'google, yahoo, openid, system'),(55,'my.max_group_count','INTEGER',NULL,'12'),(56,'table.comment','STRING',NULL,'amun_comment'),(57,'table.news','STRING',NULL,'amun_news'),(58,'table.file','STRING',NULL,'amun_file'),(59,'table.php','STRING',NULL,'amun_php'),(60,'table.redirect','STRING',NULL,'amun_redirect'),(61,'table.pipe','STRING',NULL,'amun_pipe'),(62,'table.oauth','STRING',NULL,'amun_oauth'),(63,'table.oauth_access','STRING',NULL,'amun_oauth_access'),(64,'table.oauth_access_right','STRING',NULL,'amun_oauth_access_right'),(65,'table.oauth_request','STRING',NULL,'amun_oauth_request'),(66,'table.page','STRING',NULL,'amun_page'),(67,'core.template_dir','STRING',NULL,'../template/default');
/*!40000 ALTER TABLE `amun_core_registry` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `amun_core_service`
--

DROP TABLE IF EXISTS `amun_core_service`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amun_core_service` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `status` int(10) NOT NULL,
  `source` varchar(256) NOT NULL,
  `autoloadPath` varchar(256) NOT NULL,
  `config` varchar(256) NOT NULL,
  `name` varchar(64) NOT NULL,
  `path` varchar(256) NOT NULL,
  `namespace` varchar(64) NOT NULL,
  `type` varchar(256) NOT NULL,
  `link` varchar(256) NOT NULL,
  `license` varchar(256) NOT NULL,
  `version` varchar(16) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=28 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_core_service`
--

LOCK TABLES `amun_core_service` WRITE;
/*!40000 ALTER TABLE `amun_core_service` DISABLE KEYS */;
INSERT INTO `amun_core_service` VALUES 
  (1,2,'org.amun-project.core.zip','vendor/amun/core/src/','vendor/amun/core/config.xml','amun/core','/core','AmunService\\Core','http://ns.amun-project.org/2011/amun/service/core','http://amun-project.org','GPL-3.0','0.0.1','2013-08-26 20:57:06'),
  (2,2,'org.amun-project.xrds.zip','vendor/amun/xrds/src/','vendor/amun/xrds/config.xml','amun/xrds','/xrds','AmunService\\Xrds','http://ns.amun-project.org/2011/amun/service/xrds','http://amun-project.org','GPL-3.0','0.0.1','2013-08-26 20:57:06'),
  (3,2,'org.amun-project.user.zip','vendor/amun/user/src/','vendor/amun/user/config.xml','amun/user','/user','AmunService\\User','http://ns.amun-project.org/2011/amun/service/user','http://amun-project.org','GPL-3.0','0.0.1','2013-08-26 20:57:06'),
  (4,2,'org.amun-project.log.zip','vendor/amun/log/src/','vendor/amun/log/config.xml','amun/log','/log','AmunService\\Log','http://ns.amun-project.org/2011/amun/service/log','http://amun-project.org','GPL-3.0','0.0.1','2013-08-26 20:57:07'),
  (5,2,'org.amun-project.hostmeta.zip','vendor/amun/hostmeta/src/','vendor/amun/hostmeta/config.xml','amun/hostmeta','/hostmeta','AmunService\\Hostmeta','http://ns.amun-project.org/2011/amun/service/hostmeta','http://amun-project.org','GPL-3.0','0.0.1','2013-08-26 20:57:07'),
  (6,2,'org.amun-project.webfinger.zip','vendor/amun/webfinger/src/','vendor/amun/webfinger/config.xml','amun/webfinger','/webfinger','AmunService\\Webfinger','http://ns.amun-project.org/2011/amun/service/webfinger','http://amun-project.org','GPL-3.0','0.0.1','2013-08-26 20:57:07'),
  (7,2,'org.amun-project.asset.zip','vendor/amun/asset/src/','vendor/amun/asset/config.xml','amun/asset','/asset','AmunService\\Asset','http://ns.amun-project.org/2011/amun/service/asset','http://amun-project.org','GPL-3.0','0.0.1','2013-08-26 20:57:07'),
  (8,2,'org.amun-project.media.zip','vendor/amun/media/src/','vendor/amun/media/config.xml','amun/media','/media','AmunService\\Media','http://ns.amun-project.org/2011/amun/service/media','http://amun-project.org','GPL-3.0','0.0.1','2013-08-26 20:57:07'),
  (9,2,'org.amun-project.openid.zip','vendor/amun/openid/src/','vendor/amun/openid/config.xml','amun/openid','/openid','AmunService\\Openid','http://ns.amun-project.org/2011/amun/service/openid','http://amun-project.org','GPL-3.0','0.0.1','2013-08-26 20:57:07'),
  (10,2,'org.amun-project.country.zip','vendor/amun/country/src/','vendor/amun/country/config.xml','amun/country','/country','AmunService\\Country','http://ns.amun-project.org/2011/amun/service/country','http://amun-project.org','GPL-3.0','0.0.1','2013-08-26 20:57:08'),
  (11,2,'org.amun-project.swagger.zip','vendor/amun/swagger/src/','vendor/amun/swagger/config.xml','amun/swagger','/swagger','AmunService\\Swagger','http://ns.amun-project.org/2011/amun/service/swagger','http://amun-project.org','GPL-3.0','0.0.1','2013-08-26 20:57:08'),
  (12,2,'org.amun-project.sitemap.zip','vendor/amun/sitemap/src/','vendor/amun/sitemap/config.xml','amun/sitemap','/sitemap','AmunService\\Sitemap','http://ns.amun-project.org/2011/amun/service/sitemap','http://amun-project.org','GPL-3.0','0.0.1','2013-08-26 20:57:08'),
  (13,2,'org.amun-project.phpinfo.zip','vendor/amun/phpinfo/src/','vendor/amun/phpinfo/config.xml','amun/phpinfo','/phpinfo','AmunService\\Phpinfo','http://ns.amun-project.org/2011/amun/service/phpinfo','http://amun-project.org','GPL-3.0','0.0.1','2013-08-26 20:57:08'),
  (14,2,'org.amun-project.mail.zip','vendor/amun/mail/src/','vendor/amun/mail/config.xml','amun/mail','/mail','AmunService\\Mail','http://ns.amun-project.org/2011/amun/service/mail','http://amun-project.org','GPL-3.0','0.0.1','2013-08-26 20:57:09'),
  (15,2,'org.amun-project.content.zip','vendor/amun/content/src/','vendor/amun/content/config.xml','amun/content','/content','AmunService\\Content','http://ns.amun-project.org/2011/amun/service/content','http://amun-project.org','GPL-3.0','0.0.1','2013-08-26 20:57:09'),
  (16,1,'org.amun-project.login.zip','vendor/amun/login/src/','vendor/amun/login/config.xml','amun/login','/login','AmunService\\Login','http://ns.amun-project.org/2011/amun/service/login','http://amun-project.org','GPL-3.0','0.0.1','2013-08-26 20:57:09'),
  (17,1,'org.amun-project.my.zip','vendor/amun/my/src/','vendor/amun/my/config.xml','amun/my','/my','AmunService\\My','http://ns.amun-project.org/2011/amun/service/my','http://amun-project.org','GPL-3.0','0.0.1','2013-08-26 20:57:10'),
  (18,1,'org.amun-project.profile.zip','vendor/amun/profile/src/','vendor/amun/profile/config.xml','amun/profile','/profile','AmunService\\Profile','http://ns.amun-project.org/2011/amun/service/profile','http://amun-project.org','GPL-3.0','0.0.1','2013-08-26 20:57:10'),
  (19,2,'org.amun-project.comment.zip','vendor/amun/comment/src/','vendor/amun/comment/config.xml','amun/comment','/comment','AmunService\\Comment','http://ns.amun-project.org/2011/amun/service/comment','http://amun-project.org','GPL-3.0','0.0.1','2013-08-26 20:57:10'),
  (20,1,'org.amun-project.news.zip','vendor/amun/news/src/','vendor/amun/news/config.xml','amun/news','/news','AmunService\\News','http://ns.amun-project.org/2011/amun/service/news','http://amun-project.org','GPL-3.0','0.0.1','2013-08-26 20:57:10'),
  (21,1,'org.amun-project.file.zip','vendor/amun/file/src/','vendor/amun/file/config.xml','amun/file','/file','AmunService\\File','http://ns.amun-project.org/2011/amun/service/file','http://amun-project.org','GPL-3.0','0.0.1','2013-08-26 20:57:11'),
  (22,1,'org.amun-project.php.zip','vendor/amun/php/src/','vendor/amun/php/config.xml','amun/php','/php','AmunService\\Php','http://ns.amun-project.org/2011/amun/service/php','http://amun-project.org','GPL-3.0','0.0.1','2013-08-26 20:57:11'),
  (23,1,'org.amun-project.redirect.zip','vendor/amun/redirect/src/','vendor/amun/redirect/config.xml','amun/redirect','/redirect','AmunService\\Redirect','http://ns.amun-project.org/2011/amun/service/redirect','http://amun-project.org','GPL-3.0','0.0.1','2013-08-26 20:57:11'),
  (24,1,'org.amun-project.pipe.zip','vendor/amun/pipe/src/','vendor/amun/pipe/config.xml','amun/pipe','/pipe','AmunService\\Pipe','http://ns.amun-project.org/2011/amun/service/pipe','http://amun-project.org','GPL-3.0','0.0.1','2013-08-26 20:57:11'),
  (25,2,'org.amun-project.oauth.zip','vendor/amun/oauth/src/','vendor/amun/oauth/config.xml','amun/oauth','/oauth','AmunService\\Oauth','http://ns.amun-project.org/2011/amun/service/oauth','http://amun-project.org','GPL-3.0','0.0.1','2013-08-26 20:57:11'),
  (26,1,'org.amun-project.page.zip','vendor/amun/page/src/','vendor/amun/page/config.xml','amun/page','/page','AmunService\\Page','http://ns.amun-project.org/2011/amun/service/page','http://amun-project.org','GPL-3.0','0.0.1','2013-08-26 20:57:12'),
  (27,2,'org.amun-project.sample.zip','vendor/amun/sample/src/','vendor/amun/sample/config.xml','amun/sample','/sample','AmunService\\Sample','http://ns.amun-project.org/2011/amun/service/sample','http://amun-project.org','GPL-3.0','0.0.1','2013-08-26 20:57:12');
/*!40000 ALTER TABLE `amun_core_service` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `amun_country`
--

DROP TABLE IF EXISTS `amun_country`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amun_country` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(64) NOT NULL,
  `code` varchar(2) NOT NULL,
  `longitude` double NOT NULL,
  `latitude` double NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=240 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_country`
--

LOCK TABLES `amun_country` WRITE;
/*!40000 ALTER TABLE `amun_country` DISABLE KEYS */;
INSERT INTO `amun_country` VALUES (1,'Undisclosed','',0,0),(2,'Afghanistan','AF',33,65),(3,'Albania','AL',41,20),(4,'Algeria','DZ',28,3),(5,'American Samoa','AS',-14.3333,-170),(6,'Andorra','AD',42.5,1.5),(7,'Angola','AO',-12.5,18.5),(8,'Anguilla','AI',18.25,-63.1667),(9,'Antarctica','AQ',-90,0),(10,'Antigua and Barbuda','AG',17.05,-61.8),(11,'Argentina','AR',-34,-64),(12,'Armenia','AM',40,45),(13,'Aruba','AW',12.5,-69.9667),(14,'Australia','AU',-27,133),(15,'Austria','AT',47.3333,13.3333),(16,'Azerbaijan','AZ',40.5,47.5),(17,'Bahamas','BS',24.25,-76),(18,'Bahrain','BH',26,50.55),(19,'Bangladesh','BD',24,90),(20,'Barbados','BB',13.1667,-59.5333),(21,'Belarus','BY',53,28),(22,'Belgium','BE',50.8333,4),(23,'Belize','BZ',17.25,-88.75),(24,'Benin','BJ',9.5,2.25),(25,'Bermuda','BM',32.3333,-64.75),(26,'Bhutan','BT',27.5,90.5),(27,'Bolivia','BO',-17,-65),(28,'Bosnia and Herzegovina','BA',44,18),(29,'Botswana','BW',-22,24),(30,'Bouvet Island','BV',-54.4333,3.4),(31,'Brazil','BR',-10,-55),(32,'British Indian Ocean Territory','IO',-6,71.5),(33,'Brunei Darussalam','BN',4.5,114.6667),(34,'Bulgaria','BG',43,25),(35,'Burkina Faso','BF',13,-2),(36,'Burundi','BI',-3.5,30),(37,'Cambodia','KH',13,105),(38,'Cameroon','CM',6,12),(39,'Canada','CA',60,-95),(40,'Cape Verde','CV',16,-24),(41,'Cayman Islands','KY',19.5,-80.5),(42,'Central African Republic','CF',7,21),(43,'Chad','TD',15,19),(44,'Chile','CL',-30,-71),(45,'China','CN',35,105),(46,'Christmas Island','CX',-10.5,105.6667),(47,'Cocos (Keeling) Islands','CC',-12.5,96.8333),(48,'Colombia','CO',4,-72),(49,'Comoros','KM',-12.1667,44.25),(50,'Congo','CG',-1,15),(51,'Congo, The Democratic Republic of The','CD',0,25),(52,'Cook Islands','CK',-21.2333,-159.7667),(53,'Costa Rica','CR',10,-84),(54,'Cote Divoire','CI',8,-5),(55,'Croatia','HR',45.1667,15.5),(56,'Cuba','CU',21.5,-80),(57,'Cyprus','CY',35,33),(58,'Czech Republic','CZ',49.75,15.5),(59,'Denmark','DK',56,10),(60,'Djibouti','DJ',11.5,43),(61,'Dominica','DM',15.4167,-61.3333),(62,'Dominican Republic','DO',19,-70.6667),(63,'Ecuador','EC',-2,-77.5),(64,'Egypt','EG',27,30),(65,'El Salvador','SV',13.8333,-88.9167),(66,'Equatorial Guinea','GQ',2,10),(67,'Eritrea','ER',15,39),(68,'Estonia','EE',59,26),(69,'Ethiopia','ET',8,38),(70,'Falkland Islands (Malvinas)','FK',-51.75,-59),(71,'Faroe Islands','FO',62,-7),(72,'Fiji','FJ',-18,175),(73,'Finland','FI',64,26),(74,'France','FR',46,2),(75,'French Guiana','GF',4,-53),(76,'French Polynesia','PF',-15,-140),(77,'French Southern Territories','TF',-43,67),(78,'Gabon','GA',-1,11.75),(79,'Gambia','GM',13.4667,-16.5667),(80,'Georgia','GE',42,43.5),(81,'Germany','DE',51,9),(82,'Ghana','GH',8,-2),(83,'Gibraltar','GI',36.1833,-5.3667),(84,'Greece','GR',39,22),(85,'Greenland','GL',72,-40),(86,'Grenada','GD',12.1167,-61.6667),(87,'Guadeloupe','GP',16.25,-61.5833),(88,'Guam','GU',13.4667,144.7833),(89,'Guatemala','GT',15.5,-90.25),(90,'Guinea','GN',11,-10),(91,'Guinea-bissau','GW',12,-15),(92,'Guyana','GY',5,-59),(93,'Haiti','HT',19,-72.4167),(94,'Heard Island and Mcdonald Islands','HM',-53.1,72.5167),(95,'Honduras','HN',15,-86.5),(96,'Hong Kong','HK',22.25,114.1667),(97,'Hungary','HU',47,20),(98,'Iceland','IS',65,-18),(99,'India','IN',20,77),(100,'Indonesia','ID',-5,120),(101,'Iran','IR',32,53),(102,'Iraq','IQ',33,44),(103,'Ireland','IE',53,-8),(104,'Israel','IL',31.5,34.75),(105,'Italy','IT',42.8333,12.8333),(106,'Jamaica','JM',18.25,-77.5),(107,'Japan','JP',36,138),(108,'Jordan','JO',31,36),(109,'Kazakhstan','KZ',48,68),(110,'Kenya','KE',1,38),(111,'Kiribati','KI',1.4167,173),(112,'Korea, North','KP',40,127),(113,'Korea, South','KR',37,127.5),(114,'Kuwait','KW',29.3375,47.6581),(115,'Kyrgyzstan','KG',41,75),(116,'Laos','LA',18,105),(117,'Latvia','LV',57,25),(118,'Lebanon','LB',33.8333,35.8333),(119,'Lesotho','LS',-29.5,28.5),(120,'Liberia','LR',6.5,-9.5),(121,'Libyan Arab Jamahiriya','LY',25,17),(122,'Liechtenstein','LI',47.1667,9.5333),(123,'Lithuania','LT',56,24),(124,'Luxembourg','LU',49.75,6.1667),(125,'Macao','MO',22.1667,113.55),(126,'Macedonia','MK',41.8333,22),(127,'Madagascar','MG',-20,47),(128,'Malawi','MW',-13.5,34),(129,'Malaysia','MY',2.5,112.5),(130,'Maldives','MV',3.25,73),(131,'Mali','ML',17,-4),(132,'Malta','MT',35.8333,14.5833),(133,'Marshall Islands','MH',9,168),(134,'Martinique','MQ',14.6667,-61),(135,'Mauritania','MR',20,-12),(136,'Mauritius','MU',-20.2833,57.55),(137,'Mayotte','YT',-12.8333,45.1667),(138,'Mexico','MX',23,-102),(139,'Micronesia, Federated States of','FM',6.9167,158.25),(140,'Moldova, Republic of','MD',47,29),(141,'Monaco','MC',43.7333,7.4),(142,'Mongolia','MN',46,105),(143,'Montenegro','ME',42,19),(144,'Montserrat','MS',16.75,-62.2),(145,'Morocco','MA',32,-5),(146,'Mozambique','MZ',-18.25,35),(147,'Myanmar','MM',22,98),(148,'Namibia','NA',-22,17),(149,'Nauru','NR',-0.5333,166.9167),(150,'Nepal','NP',28,84),(151,'Netherlands','NL',52.5,5.75),(152,'Netherlands Antilles','AN',12.25,-68.75),(153,'New Caledonia','NC',-21.5,165.5),(154,'New Zealand','NZ',-41,174),(155,'Nicaragua','NI',13,-85),(156,'Niger','NE',16,8),(157,'Nigeria','NG',10,8),(158,'Niue','NU',-19.0333,-169.8667),(159,'Norfolk Island','NF',-29.0333,167.95),(160,'Northern Mariana Islands','MP',15.2,145.75),(161,'Norway','NO',62,10),(162,'Oman','OM',21,57),(163,'Pakistan','PK',30,70),(164,'Palau','PW',7.5,134.5),(165,'Palestinian Territory','PS',32,35.25),(166,'Panama','PA',9,-80),(167,'Papua New Guinea','PG',-6,147),(168,'Paraguay','PY',-23,-58),(169,'Peru','PE',-10,-76),(170,'Philippines','PH',13,122),(171,'Poland','PL',52,20),(172,'Portugal','PT',39.5,-8),(173,'Puerto Rico','PR',18.25,-66.5),(174,'Qatar','QA',25.5,51.25),(175,'Reunion','RE',-21.1,55.6),(176,'Romania','RO',46,25),(177,'Russia','RU',60,100),(178,'Rwanda','RW',-2,30),(179,'Saint Helena','SH',-15.9333,-5.7),(180,'Saint Kitts and Nevis','KN',17.3333,-62.75),(181,'Saint Lucia','LC',13.8833,-61.1333),(182,'Saint Pierre and Miquelon','PM',46.8333,-56.3333),(183,'Saint Vincent and The Grenadines','VC',13.25,-61.2),(184,'Samoa','WS',-13.5833,-172.3333),(185,'San Marino','SM',43.7667,12.4167),(186,'Sao Tome and Principe','ST',1,7),(187,'Saudi Arabia','SA',25,45),(188,'Senegal','SN',14,-14),(189,'Serbia and Montenegro','RS',44,21),(190,'Seychelles','SC',-4.5833,55.6667),(191,'Sierra Leone','SL',8.5,-11.5),(192,'Singapore','SG',1.3667,103.8),(193,'Slovakia','SK',48.6667,19.5),(194,'Slovenia','SI',46,15),(195,'Solomon Islands','SB',-8,159),(196,'Somalia','SO',10,49),(197,'South Africa','ZA',-29,24),(198,'South Georgia and The South Sandwich Islands','GS',-54.5,-37),(199,'Spain','ES',40,-4),(200,'Sri Lanka','LK',7,81),(201,'Sudan','SD',15,30),(202,'Suriname','SR',4,-56),(203,'Svalbard and Jan Mayen','SJ',78,20),(204,'Swaziland','SZ',-26.5,31.5),(205,'Sweden','SE',62,15),(206,'Switzerland','CH',47,8),(207,'Syria','SY',35,38),(208,'Taiwan','TW',23.5,121),(209,'Tajikistan','TJ',39,71),(210,'Tanzania, United Republic of','TZ',-6,35),(211,'Thailand','TH',15,100),(212,'Togo','TG',8,1.1667),(213,'Tokelau','TK',-9,-172),(214,'Tonga','TO',-20,-175),(215,'Trinidad and Tobago','TT',11,-61),(216,'Tunisia','TN',34,9),(217,'Turkey','TR',39,35),(218,'Turkmenistan','TM',40,60),(219,'Turks and Caicos Islands','TC',21.75,-71.5833),(220,'Tuvalu','TV',-8,178),(221,'Uganda','UG',1,32),(222,'Ukraine','UA',49,32),(223,'United Arab Emirates','AE',24,54),(224,'United Kingdom','GB',54,-2),(225,'United States','US',38,-97),(226,'United States Minor Outlying Islands','UM',19.2833,166.6),(227,'Uruguay','UY',-33,-56),(228,'Uzbekistan','UZ',41,64),(229,'Vanuatu','VU',-16,167),(230,'Vatican City','VA',41.9,12.45),(231,'Venezuela','VE',8,-66),(232,'Vietnam','VN',16,106),(233,'Virgin Islands, British','VG',18.5,-64.5),(234,'Virgin Islands, U.S.','VI',18.3333,-64.8333),(235,'Wallis and Futuna','WF',-13.3,-176.2),(236,'Western Sahara','EH',24.5,-13),(237,'Yemen','YE',15,48),(238,'Zambia','ZM',-15,30),(239,'Zimbabwe','ZW',-20,30);
/*!40000 ALTER TABLE `amun_country` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `amun_file`
--

DROP TABLE IF EXISTS `amun_file`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amun_file` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `globalId` varchar(36) NOT NULL,
  `pageId` int(10) NOT NULL,
  `userId` int(10) NOT NULL,
  `contentType` varchar(128) NOT NULL,
  `content` longtext NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `globalId` (`globalId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_file`
--

LOCK TABLES `amun_file` WRITE;
/*!40000 ALTER TABLE `amun_file` DISABLE KEYS */;
/*!40000 ALTER TABLE `amun_file` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `amun_log`
--

DROP TABLE IF EXISTS `amun_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amun_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `userId` int(10) NOT NULL,
  `refId` int(10) NOT NULL,
  `type` enum('INSERT','UPDATE','DELETE') NOT NULL,
  `table` varchar(64) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=44 DEFAULT CHARSET=ascii;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_log`
--

LOCK TABLES `amun_log` WRITE;
/*!40000 ALTER TABLE `amun_log` DISABLE KEYS */;
INSERT INTO `amun_log` VALUES (1,1,4,'INSERT','amun_core_service','2013-08-26 20:57:07'),(2,1,5,'INSERT','amun_core_service','2013-08-26 20:57:07'),(3,1,6,'INSERT','amun_core_service','2013-08-26 20:57:07'),(4,1,7,'INSERT','amun_core_service','2013-08-26 20:57:07'),(5,1,8,'INSERT','amun_core_service','2013-08-26 20:57:07'),(6,1,9,'INSERT','amun_core_service','2013-08-26 20:57:07'),(7,1,10,'INSERT','amun_core_service','2013-08-26 20:57:08'),(8,1,11,'INSERT','amun_core_service','2013-08-26 20:57:08'),(9,1,12,'INSERT','amun_core_service','2013-08-26 20:57:08'),(10,1,13,'INSERT','amun_core_service','2013-08-26 20:57:08'),(11,1,14,'INSERT','amun_core_service','2013-08-26 20:57:09'),(12,1,15,'INSERT','amun_core_service','2013-08-26 20:57:09'),(13,1,1,'INSERT','amun_mail','2013-08-26 20:57:10'),(14,1,2,'INSERT','amun_mail','2013-08-26 20:57:10'),(15,1,3,'INSERT','amun_mail','2013-08-26 20:57:10'),(16,1,16,'INSERT','amun_core_service','2013-08-26 20:57:10'),(17,1,17,'INSERT','amun_core_service','2013-08-26 20:57:10'),(18,1,18,'INSERT','amun_core_service','2013-08-26 20:57:10'),(19,1,19,'INSERT','amun_core_service','2013-08-26 20:57:10'),(20,1,20,'INSERT','amun_core_service','2013-08-26 20:57:11'),(21,1,21,'INSERT','amun_core_service','2013-08-26 20:57:11'),(22,1,22,'INSERT','amun_core_service','2013-08-26 20:57:11'),(23,1,23,'INSERT','amun_core_service','2013-08-26 20:57:11'),(24,1,24,'INSERT','amun_core_service','2013-08-26 20:57:11'),(25,1,25,'INSERT','amun_core_service','2013-08-26 20:57:11'),(26,1,26,'INSERT','amun_core_service','2013-08-26 20:57:12'),(27,1,27,'INSERT','amun_core_service','2013-08-26 20:57:12'),(28,1,2,'INSERT','amun_user_group','2013-08-26 20:57:12'),(29,1,3,'INSERT','amun_user_group','2013-08-26 20:57:12'),(30,1,1,'INSERT','amun_user_activity','2013-08-26 20:57:46'),(31,1,1,'INSERT','amun_user_account','2013-08-26 20:57:46'),(32,1,2,'INSERT','amun_user_activity','2013-08-26 20:57:46'),(33,1,2,'INSERT','amun_user_account','2013-08-26 20:57:46'),(34,1,1,'INSERT','amun_oauth','2013-08-26 20:57:46'),(35,1,1,'INSERT','amun_content_page','2013-08-26 20:57:46'),(36,1,2,'INSERT','amun_content_page','2013-08-26 20:57:46'),(37,1,3,'INSERT','amun_content_page','2013-08-26 20:57:46'),(38,1,4,'INSERT','amun_content_page','2013-08-26 20:57:46'),(39,1,5,'INSERT','amun_content_page','2013-08-26 20:57:46'),(40,1,6,'INSERT','amun_content_page','2013-08-26 20:57:46'),(41,1,1,'INSERT','amun_page','2013-08-26 20:57:46'),(42,1,2,'INSERT','amun_page','2013-08-26 20:57:46'),(43,1,3,'INSERT','amun_page','2013-08-26 20:57:46');
/*!40000 ALTER TABLE `amun_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `amun_login_attempt`
--

DROP TABLE IF EXISTS `amun_login_attempt`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amun_login_attempt` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `ip` varchar(40) NOT NULL,
  `count` int(10) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ip` (`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_login_attempt`
--

LOCK TABLES `amun_login_attempt` WRITE;
/*!40000 ALTER TABLE `amun_login_attempt` DISABLE KEYS */;
/*!40000 ALTER TABLE `amun_login_attempt` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `amun_mail`
--

DROP TABLE IF EXISTS `amun_mail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amun_mail` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `from` varchar(64) NOT NULL,
  `subject` varchar(256) NOT NULL,
  `text` text NOT NULL,
  `html` text NOT NULL,
  `values` varchar(512) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `NAME` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_mail`
--

LOCK TABLES `amun_mail` WRITE;
/*!40000 ALTER TABLE `amun_mail` DISABLE KEYS */;
INSERT INTO `amun_mail` VALUES (1,'LOGIN_REGISTRATION','noreply@127.0.0.1','Account activation','\nHello {account.name},\n\nyou have successful registered at {host.name}. Your identity is\n\"{account.identity}\". In order to activate your account please visit the\nfollowing activation link:\n\n{register.link}\n','\nHello {account.name},\n\nyou have successful registered at {host.name}. Your identity is\n\"{account.identity}\". In order to activate your account please visit the\nfollowing activation link:\n\n{register.link}\n','account.name;account.identity;host.name;register.link;register.date'),(2,'LOGIN_RECOVER','noreply@127.0.0.1','Account recover','\nHello {account.name},\n\n{recover.ip} wants to recover your password. In order to get a new password \nvisit the following link. We will create a new password for you and send it to \nthis email address. Ignore this email if you have not requested a new password.\n\n{recover.link}\n','\nHello {account.name},\n\n{recover.ip} wants to recover your password. In order to get a new password \nvisit the following link. We will create a new password for you and send it to \nthis email address. Ignore this email if you have not requested a new password.\n\n{recover.link}\n','account.name;host.name;recover.ip;recover.link;recover.date'),(3,'LOGIN_RECOVER_SUCCESS','noreply@127.0.0.1','Account recover success','\nHello {account.name},\n\nyou have successful recovered your password. It is highly recommended to change\nthe password after a recovery process because the password was maybe transmitted\nover an insecure channel.\n\nNew password: {account.pw}\n\nYou can login with the new password at:\n{recover.link}\n','\nHello {account.name},\n\nyou have successful recovered your password. It is highly recommended to change\nthe password after a recovery process because the password was maybe transmitted\nover an insecure channel.\n\nNew password: {account.pw}\n\nYou can login with the new password at:\n{recover.link}\n','account.name;account.pw;host.name;recover.link;recover.date');
/*!40000 ALTER TABLE `amun_mail` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `amun_media`
--

DROP TABLE IF EXISTS `amun_media`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amun_media` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `globalId` varchar(36) NOT NULL,
  `rightId` int(10) DEFAULT NULL,
  `name` varchar(128) NOT NULL,
  `path` varchar(320) NOT NULL,
  `type` enum('application','audio','text','image','video') NOT NULL,
  `size` int(10) NOT NULL,
  `mimeType` varchar(64) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `globalId` (`globalId`),
  UNIQUE KEY `path` (`path`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_media`
--

LOCK TABLES `amun_media` WRITE;
/*!40000 ALTER TABLE `amun_media` DISABLE KEYS */;
/*!40000 ALTER TABLE `amun_media` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `amun_news`
--

DROP TABLE IF EXISTS `amun_news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amun_news` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `globalId` varchar(36) NOT NULL,
  `pageId` int(10) NOT NULL,
  `userId` int(10) NOT NULL,
  `urlTitle` varchar(128) NOT NULL,
  `title` varchar(128) NOT NULL,
  `text` longtext NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `globalId` (`globalId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_news`
--

LOCK TABLES `amun_news` WRITE;
/*!40000 ALTER TABLE `amun_news` DISABLE KEYS */;
/*!40000 ALTER TABLE `amun_news` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `amun_oauth`
--

DROP TABLE IF EXISTS `amun_oauth`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amun_oauth` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `status` int(10) NOT NULL,
  `name` varchar(64) NOT NULL,
  `email` varchar(64) NOT NULL,
  `url` varchar(256) NOT NULL,
  `title` varchar(64) NOT NULL,
  `description` longtext NOT NULL,
  `consumerKey` varchar(40) NOT NULL,
  `consumerSecret` varchar(40) NOT NULL,
  `callback` varchar(256) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `consumerKey` (`consumerKey`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_oauth`
--

LOCK TABLES `amun_oauth` WRITE;
/*!40000 ALTER TABLE `amun_oauth` DISABLE KEYS */;
INSERT INTO `amun_oauth` VALUES (1,1,'System','test@test.com','http://127.0.0.1/projects/amun/public','System','Default system API to access amun','347ac22499138070c1c689c974d08b14abc2fd62','181841bce9d8f1204faf01476a264e05d73c16d0','','2013-08-26 20:57:46');
/*!40000 ALTER TABLE `amun_oauth` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `amun_oauth_access`
--

DROP TABLE IF EXISTS `amun_oauth_access`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amun_oauth_access` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `apiId` int(10) NOT NULL,
  `userId` int(10) NOT NULL,
  `allowed` tinyint(1) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `apiUserId` (`apiId`,`userId`)
) ENGINE=MyISAM DEFAULT CHARSET=ascii;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_oauth_access`
--

LOCK TABLES `amun_oauth_access` WRITE;
/*!40000 ALTER TABLE `amun_oauth_access` DISABLE KEYS */;
/*!40000 ALTER TABLE `amun_oauth_access` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `amun_oauth_access_right`
--

DROP TABLE IF EXISTS `amun_oauth_access_right`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amun_oauth_access_right` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `accessId` int(10) NOT NULL,
  `rightId` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=ascii;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_oauth_access_right`
--

LOCK TABLES `amun_oauth_access_right` WRITE;
/*!40000 ALTER TABLE `amun_oauth_access_right` DISABLE KEYS */;
/*!40000 ALTER TABLE `amun_oauth_access_right` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `amun_oauth_request`
--

DROP TABLE IF EXISTS `amun_oauth_request`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amun_oauth_request` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `apiId` int(10) NOT NULL,
  `userId` int(10) NOT NULL,
  `status` int(10) NOT NULL,
  `ip` varchar(32) NOT NULL,
  `nonce` varchar(16) NOT NULL,
  `callback` varchar(256) NOT NULL,
  `token` varchar(40) NOT NULL,
  `tokenSecret` varchar(40) NOT NULL,
  `verifier` varchar(32) NOT NULL,
  `timestamp` varchar(25) NOT NULL,
  `expire` varchar(25) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`)
) ENGINE=MyISAM DEFAULT CHARSET=ascii;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_oauth_request`
--

LOCK TABLES `amun_oauth_request` WRITE;
/*!40000 ALTER TABLE `amun_oauth_request` DISABLE KEYS */;
/*!40000 ALTER TABLE `amun_oauth_request` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `amun_openid`
--

DROP TABLE IF EXISTS `amun_openid`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amun_openid` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `userId` int(10) NOT NULL,
  `assocId` int(10) NOT NULL,
  `status` int(10) NOT NULL DEFAULT '1',
  `claimedId` varchar(256) NOT NULL,
  `identity` varchar(256) NOT NULL,
  `returnTo` varchar(256) NOT NULL,
  `responseNonce` varchar(32) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_assoc_id` (`userId`,`assocId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_openid`
--

LOCK TABLES `amun_openid` WRITE;
/*!40000 ALTER TABLE `amun_openid` DISABLE KEYS */;
/*!40000 ALTER TABLE `amun_openid` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `amun_openid_access`
--

DROP TABLE IF EXISTS `amun_openid_access`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amun_openid_access` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `userId` int(10) NOT NULL,
  `returnTo` varchar(256) NOT NULL,
  `allowed` tinyint(1) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId_returnTo` (`userId`,`returnTo`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_openid_access`
--

LOCK TABLES `amun_openid_access` WRITE;
/*!40000 ALTER TABLE `amun_openid_access` DISABLE KEYS */;
/*!40000 ALTER TABLE `amun_openid_access` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `amun_openid_assoc`
--

DROP TABLE IF EXISTS `amun_openid_assoc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amun_openid_assoc` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `assocHandle` varchar(256) NOT NULL,
  `assocType` enum('HMAC-SHA1','HMAC-SHA256') NOT NULL,
  `sessionType` enum('DH-SHA1','DH-SHA256') DEFAULT NULL,
  `secret` varchar(256) NOT NULL,
  `expires` varchar(25) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `assocHandle` (`assocHandle`)
) ENGINE=MyISAM DEFAULT CHARSET=ascii;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_openid_assoc`
--

LOCK TABLES `amun_openid_assoc` WRITE;
/*!40000 ALTER TABLE `amun_openid_assoc` DISABLE KEYS */;
/*!40000 ALTER TABLE `amun_openid_assoc` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `amun_page`
--

DROP TABLE IF EXISTS `amun_page`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amun_page` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `globalId` varchar(36) NOT NULL,
  `pageId` int(10) NOT NULL,
  `userId` int(10) NOT NULL,
  `content` longtext NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `globalId` (`globalId`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_page`
--

LOCK TABLES `amun_page` WRITE;
/*!40000 ALTER TABLE `amun_page` DISABLE KEYS */;
INSERT INTO `amun_page` VALUES (1,'b70adefe-8a99-59d0-8ff9-90e1f10c5b35',1,1,'<h1>It works!</h1>\n<p>This is the default web page for this server.</p>\n<p>The web server software is running but no content has been added, yet.</p>','2013-08-26 20:57:46'),(2,'a7e81ee0-451c-5899-9dd0-0a5c61614f65',2,1,'<h1>Lorem ipsum dolor sit amet</h1>\n<p>consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</p>','2013-08-26 20:57:46'),(3,'84b36817-c73d-5663-b22d-da51038dd418',6,1,'<h3>Mentions</h3>\n\n<h4>Users</h4>\n<p>If you want mention a user you can use the @ tag wich automatically creates an hyperlink to the users profile.</p>\n<pre>\nHi <a href=\"http://127.0.0.1/projects/amun/public/index.php/profile/test\">@test</a> how are you?\n</pre>\n\n<h4>Pages</h4>\n<p>If you want link to a specific page you can use the &amp; tag wich automatically creates an hyperlink to the specific page.</p>\n<pre>\nlook at the <a href=\"http://127.0.0.1/projects/amun/public/index.php/home\">&home</a> page\n</pre>\n\n<h4>Hyperlinks</h4>\n<p>Urls are automatically converted into hyperlinks. If your url points to an video or image Amun tries in some cases to discover informations about the media item and appends a preview to your post.</p>\n<pre>\nlook at this video <a href=\"http://www.youtube.com/watch?v=4EL67mjv1nM\">http://www.youtube.com/watch?v=4EL67mjv1nM</a> ;D\n</pre>\n\n<h3>Formatting content</h3>\n<p>Amun uses a subset of the <a href=\"http://wikipedia.org/wiki/Markdown\">markdown</a> syntax to provide an easy way to format content. Please use the following formatting rules in your content so that readers can enjoy reading your content. Note the markdown syntax is only to simplify creating content without writing html if you prefer you can also write plain html.</p>\n\n<h4>Paragraphs</h4>\n<p>Here an example how text will be converted into paragraphs. Note if you have two trailing spaces at a line a <span class=\"kwd\">&lt;br /&gt;</span> tag will be inserted.</p>\n<pre>\nLorem ipsum dolor sit amet, consetetur sadipscing elitr\n\nsed diam nonumy eirmod tempor invidunt ut labore et.\nAt vero eos et accusam et justo duo dolores\n\ninvidunt ut labore et dolore magna aliquyam<span style=\"background-color:#ccc\">  </span>\nsed diam voluptua\n</pre>\n<hr />\n<pre class=\"prettyprint\">\n<span class=\"kwd\">&lt;p&gt;</span>Lorem ipsum dolor sit amet, consetetur sadipscing elitr <span class=\"kwd\">&lt;/p&gt;</span>\n<span class=\"kwd\">&lt;p&gt;</span>sed diam nonumy eirmod tempor invidunt ut labore et. At vero eos et accusam et justo duo dolores <span class=\"kwd\">&lt;/p&gt;</span>\n<span class=\"kwd\">&lt;p&gt;</span>invidunt ut labore et dolore magna aliquyam<span class=\"kwd\">&lt;br /&gt;</span>sed diam voluptua <span class=\"kwd\">&lt;/p&gt;</span>\n</pre>\n\n<h4>Code</h4>\n<p>Indent four spaces or one tab to create an escaped <span class=\"kwd\">&lt;pre&gt;</span> block. The text will be wrapped in tags, and displayed in a monospaced font. The first four spaces will be stripped off, but all other whitespace will be preserved. Markdown and HTML is ignored within a code block</p>\n<pre>\n<span style=\"background-color:#ccc\">    </span>public static void main(String args[])\n<span style=\"background-color:#ccc\">    </span>{\n<span style=\"background-color:#ccc\">    </span>    System.out.println(\"Hello World !!!\");\n<span style=\"background-color:#ccc\">    </span>}\n</pre>\n<hr />\n<pre class=\"prettyprint\">\n<span class=\"kwd\">&lt;pre class=\"prettyprint\"&gt;</span>\npublic static void main(String args[])\n{\n  System.out.println(\"Hello World !!!\");\n}\n<span class=\"kwd\">&lt;/pre&gt;</span>\n</pre>\n\n<h4>Quotes</h4>\n<p>Add a &gt; to the beginning of any line to create a <span class=\"kwd\">&lt;blockquote&gt;</span>.</p>\n<pre>\n&gt; Lorem ipsum dolor sit amet\n&gt; consetetur sadipscing elitr\n&gt; sed diam nonumy eirmod\n\ntempor invidunt ut labore\n</pre>\n<hr />\n<pre class=\"prettyprint\">\n<span class=\"kwd\">&lt;blockquote&gt;</span>\n  <span class=\"kwd\">&lt;p&gt;</span>Lorem ipsum dolor sit amet consetetur sadipscing elitr sed diam nonumy eirmod <span class=\"kwd\">&lt;/p&gt;</span>\n<span class=\"kwd\">&lt;/blockquote&gt;</span>\n<span class=\"kwd\">&lt;p&gt;</span>tempor invidunt ut labore <span class=\"kwd\">&lt;/p&gt;</span>\n</pre>\n\n<h4>Lists</h4>\n<p>A bulleted <span class=\"kwd\">&lt;ul&gt;</span> list:</p>\n<pre>\n* Lorem ipsum dolor sit amet\n* consetetur sadipscing elitr\n* sed diam nonumy eirmod\n* tempor invidunt ut labore\n</pre>\n<hr />\n<pre class=\"prettyprint\">\n<span class=\"kwd\">&lt;ul&gt;</span>\n  <span class=\"kwd\">&lt;li&gt;</span>Lorem ipsum dolor sit amet<span class=\"kwd\">&lt;/li&gt;</span>\n <span class=\"kwd\">&lt;li&gt;</span>consetetur sadipscing elitr<span class=\"kwd\">&lt;/li&gt;</span>\n  <span class=\"kwd\">&lt;li&gt;</span>sed diam nonumy eirmod<span class=\"kwd\">&lt;/li&gt;</span>\n <span class=\"kwd\">&lt;li&gt;</span>tempor invidunt ut labore<span class=\"kwd\">&lt;/li&gt;</span>\n<span class=\"kwd\">&lt;/ul&gt;</span>\n</pre>\n\n<h3>Restrictions</h3>\n<p>Note all the formating capabilities depends on the html tags wich are allowed for your user group. Website administrators can insert any kind of html tags where anonymous user can use only a small subset inorder to prevent misuse.</p>\n','2013-08-26 20:57:46');
/*!40000 ALTER TABLE `amun_page` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `amun_php`
--

DROP TABLE IF EXISTS `amun_php`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amun_php` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `globalId` varchar(36) NOT NULL,
  `pageId` int(10) NOT NULL,
  `userId` int(10) NOT NULL,
  `content` longtext NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `globalId` (`globalId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_php`
--

LOCK TABLES `amun_php` WRITE;
/*!40000 ALTER TABLE `amun_php` DISABLE KEYS */;
/*!40000 ALTER TABLE `amun_php` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `amun_pipe`
--

DROP TABLE IF EXISTS `amun_pipe`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amun_pipe` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `globalId` varchar(36) NOT NULL,
  `pageId` int(10) NOT NULL,
  `userId` int(10) NOT NULL,
  `mediaId` int(10) NOT NULL,
  `processor` varchar(32) DEFAULT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `globalId` (`globalId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_pipe`
--

LOCK TABLES `amun_pipe` WRITE;
/*!40000 ALTER TABLE `amun_pipe` DISABLE KEYS */;
/*!40000 ALTER TABLE `amun_pipe` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `amun_redirect`
--

DROP TABLE IF EXISTS `amun_redirect`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amun_redirect` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `globalId` varchar(36) NOT NULL,
  `pageId` int(10) NOT NULL,
  `userId` int(10) NOT NULL,
  `href` varchar(512) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `globalId` (`globalId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_redirect`
--

LOCK TABLES `amun_redirect` WRITE;
/*!40000 ALTER TABLE `amun_redirect` DISABLE KEYS */;
/*!40000 ALTER TABLE `amun_redirect` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `amun_user_account`
--

DROP TABLE IF EXISTS `amun_user_account`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amun_user_account` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `globalId` varchar(36) NOT NULL,
  `groupId` int(10) NOT NULL,
  `hostId` int(10) NOT NULL DEFAULT '0',
  `countryId` int(10) NOT NULL,
  `status` int(10) NOT NULL,
  `identity` varchar(40) NOT NULL,
  `name` varchar(32) NOT NULL,
  `pw` varchar(40) NOT NULL,
  `email` varchar(64) DEFAULT NULL,
  `token` varchar(40) NOT NULL,
  `ip` varchar(40) NOT NULL,
  `gender` enum('male','female','undisclosed') NOT NULL DEFAULT 'undisclosed',
  `profileUrl` varchar(256) NOT NULL,
  `thumbnailUrl` varchar(256) DEFAULT NULL,
  `longitude` double NOT NULL DEFAULT '0',
  `latitude` double NOT NULL DEFAULT '0',
  `timezone` varchar(32) NOT NULL DEFAULT 'UTC',
  `lastSeen` datetime NOT NULL,
  `updated` datetime NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `identity` (`identity`),
  UNIQUE KEY `token` (`token`),
  UNIQUE KEY `hostIdName` (`hostId`,`name`),
  UNIQUE KEY `globalId` (`globalId`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_user_account`
--

LOCK TABLES `amun_user_account` WRITE;
/*!40000 ALTER TABLE `amun_user_account` DISABLE KEYS */;
INSERT INTO `amun_user_account` VALUES (1,'cb11be28-9581-5c42-9999-9af7754fc384',1,0,1,5,'60f0054d051b6e8792b6df64968693f60dbb5ef3','test','cf8e508e08ad778f9c471f4fd6d0c93c79938e84','test@test.com','3bc2dc6db46283b2aed38b0dac22e3f22812565d','127.0.0.1','undisclosed','http://127.0.0.1/projects/amun/public/index.php/profile/test','http://www.gravatar.com/avatar/b642b4217b34b1e8d3bd915fc65c4452?d=http%3A%2F%2F127.0.0.1%2Fprojects%2Famun%2Fpublic%2Fimg%2Favatar%2Fno_image.png&s=48',0,0,'UTC','2013-08-26 20:57:46','2013-08-26 20:57:46','2013-08-26 20:57:46'),(2,'e95ee8ea-a985-5ef3-bee3-37927c4ba631',3,0,1,2,'a5b1ec08b2eef7741eacd969c7a17c7c0097439f','Anonymous','7688ce49f69ff5bf88d12fbf056587ae2e83f666',NULL,'a389acee3bc5827fcd86b5f81611cecc6a5351dc','127.0.0.1','undisclosed','http://127.0.0.1/projects/amun/public/index.php/profile/Anonymous','http://127.0.0.1/projects/amun/public/img/avatar/no_image.png',0,0,'UTC','2013-08-26 20:57:46','2013-08-26 20:57:46','2013-08-26 20:57:46');
/*!40000 ALTER TABLE `amun_user_account` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `amun_user_activity`
--

DROP TABLE IF EXISTS `amun_user_activity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amun_user_activity` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `globalId` varchar(36) NOT NULL,
  `parentId` int(10) NOT NULL DEFAULT '0',
  `userId` int(10) NOT NULL,
  `scope` int(10) NOT NULL DEFAULT '0',
  `verb` enum('post','add','cancel','checkin','delete','favorite','follow','give','ignore','invite','join','leave','like','make-friend','play','receive','remove','remove-friend','request-friend','rsvp-maybe','rsvp-no','rsvp-yes','save','share','stop-following','tag','unfavorite','unlike','unsave','update') NOT NULL,
  `object` text NOT NULL,
  `summary` text NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `globalId` (`globalId`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_user_activity`
--

LOCK TABLES `amun_user_activity` WRITE;
/*!40000 ALTER TABLE `amun_user_activity` DISABLE KEYS */;
INSERT INTO `amun_user_activity` VALUES (1,'20f46b77-4eb3-5791-a559-9a30aa6aa85f',0,1,0,'join','','<p>test has created an account</p>','2013-08-26 20:57:46'),(2,'42a1e962-38d2-5c65-b44e-edc3a20725f6',0,1,0,'join','','<p>Anonymous has created an account</p>','2013-08-26 20:57:46');
/*!40000 ALTER TABLE `amun_user_activity` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `amun_user_activity_receiver`
--

DROP TABLE IF EXISTS `amun_user_activity_receiver`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amun_user_activity_receiver` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `status` int(10) NOT NULL DEFAULT '1',
  `activityId` int(10) NOT NULL,
  `userId` int(10) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=ascii;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_user_activity_receiver`
--

LOCK TABLES `amun_user_activity_receiver` WRITE;
/*!40000 ALTER TABLE `amun_user_activity_receiver` DISABLE KEYS */;
INSERT INTO `amun_user_activity_receiver` VALUES (1,1,1,1,'2013-08-26 22:57:46'),(2,1,2,1,'2013-08-26 22:57:46');
/*!40000 ALTER TABLE `amun_user_activity_receiver` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `amun_user_activity_template`
--

DROP TABLE IF EXISTS `amun_user_activity_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amun_user_activity_template` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `type` enum('INSERT','UPDATE','DELETE') NOT NULL,
  `verb` enum('post','add','cancel','checkin','delete','favorite','follow','give','ignore','invite','join','leave','like','make-friend','play','receive','remove','remove-friend','request-friend','rsvp-maybe','rsvp-no','rsvp-yes','save','share','stop-following','tag','unfavorite','unlike','unsave','update') NOT NULL,
  `table` varchar(64) NOT NULL,
  `path` varchar(256) NOT NULL,
  `summary` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `type_table` (`type`,`table`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_user_activity_template`
--

LOCK TABLES `amun_user_activity_template` WRITE;
/*!40000 ALTER TABLE `amun_user_activity_template` DISABLE KEYS */;
INSERT INTO `amun_user_activity_template` VALUES (1,'INSERT','add','amun_news','view?id={record.id}','\n<p><a href=\"{user.profileUrl}\">{user.name}</a> has created a <a href=\"{object.url}\">news</a></p><blockquote>{record.text}</blockquote>\n');
/*!40000 ALTER TABLE `amun_user_activity_template` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `amun_user_friend`
--

DROP TABLE IF EXISTS `amun_user_friend`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amun_user_friend` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `status` int(10) NOT NULL,
  `groupId` int(10) NOT NULL DEFAULT '0',
  `userId` int(10) NOT NULL,
  `friendId` int(10) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userFriendId` (`userId`,`friendId`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=ascii;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_user_friend`
--

LOCK TABLES `amun_user_friend` WRITE;
/*!40000 ALTER TABLE `amun_user_friend` DISABLE KEYS */;
INSERT INTO `amun_user_friend` VALUES (1,2,0,1,1,'2013-08-26 20:57:46'),(2,2,0,2,2,'2013-08-26 20:57:46');
/*!40000 ALTER TABLE `amun_user_friend` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `amun_user_friend_group`
--

DROP TABLE IF EXISTS `amun_user_friend_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amun_user_friend_group` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `userId` int(10) NOT NULL,
  `title` varchar(64) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_user_friend_group`
--

LOCK TABLES `amun_user_friend_group` WRITE;
/*!40000 ALTER TABLE `amun_user_friend_group` DISABLE KEYS */;
/*!40000 ALTER TABLE `amun_user_friend_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `amun_user_group`
--

DROP TABLE IF EXISTS `amun_user_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amun_user_group` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(32) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_user_group`
--

LOCK TABLES `amun_user_group` WRITE;
/*!40000 ALTER TABLE `amun_user_group` DISABLE KEYS */;
INSERT INTO `amun_user_group` VALUES (1,'Administrator','2013-08-26 22:57:12'),(2,'Normal','2013-08-26 20:57:12'),(3,'Anonymous','2013-08-26 20:57:12');
/*!40000 ALTER TABLE `amun_user_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `amun_user_group_right`
--

DROP TABLE IF EXISTS `amun_user_group_right`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amun_user_group_right` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `groupId` int(10) NOT NULL,
  `rightId` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `groupRightId` (`groupId`,`rightId`)
) ENGINE=MyISAM AUTO_INCREMENT=180 DEFAULT CHARSET=ascii;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_user_group_right`
--

LOCK TABLES `amun_user_group_right` WRITE;
/*!40000 ALTER TABLE `amun_user_group_right` DISABLE KEYS */;
INSERT INTO `amun_user_group_right` VALUES (1,1,1),(2,1,2),(3,1,3),(4,1,4),(5,1,5),(6,1,6),(7,1,7),(8,1,8),(9,1,9),(10,1,10),(11,1,11),(12,1,12),(13,1,13),(14,1,14),(15,1,15),(16,1,16),(17,1,17),(18,1,18),(19,1,19),(20,1,20),(21,1,21),(22,1,22),(23,1,23),(24,1,24),(25,1,25),(26,1,26),(27,1,27),(28,1,28),(29,1,29),(30,1,30),(31,1,31),(32,1,32),(33,1,33),(34,1,34),(35,1,35),(36,1,36),(37,1,37),(38,1,38),(39,1,39),(40,1,40),(41,1,41),(42,1,42),(43,1,43),(44,1,44),(45,1,45),(46,1,46),(47,1,47),(48,1,48),(49,1,49),(50,1,50),(51,1,51),(52,1,52),(53,1,53),(54,1,54),(55,1,55),(56,1,56),(57,1,57),(58,1,58),(59,1,59),(60,1,60),(61,1,61),(62,1,62),(63,1,63),(64,1,64),(65,1,65),(66,1,66),(67,1,67),(68,1,68),(69,1,69),(70,1,70),(71,1,71),(72,1,72),(73,1,73),(74,1,74),(75,1,75),(76,1,76),(77,1,77),(78,1,78),(79,1,79),(80,1,80),(81,1,81),(82,1,82),(83,1,83),(84,1,84),(85,1,85),(86,1,86),(87,1,87),(88,1,88),(89,1,89),(90,1,90),(91,1,91),(92,1,92),(93,1,93),(94,1,94),(95,1,95),(96,1,96),(97,1,97),(98,1,98),(99,1,99),(100,1,100),(101,1,101),(102,1,102),(103,1,103),(104,1,104),(105,1,105),(106,1,106),(107,1,107),(108,1,108),(109,1,109),(110,1,110),(111,1,111),(112,1,112),(113,1,113),(114,1,114),(115,1,115),(116,1,116),(117,1,117),(118,1,118),(119,1,119),(120,1,120),(121,1,121),(122,1,122),(123,1,123),(124,1,124),(125,1,125),(126,1,126),(127,1,127),(128,1,128),(129,1,129),(130,1,130),(131,1,131),(132,1,132),(133,1,133),(134,1,134),(135,2,1),(136,2,5),(137,2,7),(138,2,9),(139,2,10),(140,2,17),(141,2,18),(142,2,20),(143,2,21),(144,2,22),(145,2,24),(146,2,40),(147,2,56),(148,2,57),(149,2,63),(150,2,84),(151,2,85),(152,2,86),(153,2,87),(154,2,88),(155,2,89),(156,2,90),(157,2,91),(158,2,94),(159,2,98),(160,2,99),(161,2,103),(162,2,107),(163,2,111),(164,2,131),(165,3,1),(166,3,40),(167,3,56),(168,3,57),(169,3,63),(170,3,84),(171,3,85),(172,3,89),(173,3,90),(174,3,94),(175,3,99),(176,3,103),(177,3,107),(178,3,111),(179,3,131);
/*!40000 ALTER TABLE `amun_user_group_right` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `amun_user_right`
--

DROP TABLE IF EXISTS `amun_user_right`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amun_user_right` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `serviceId` int(10) NOT NULL,
  `name` varchar(64) NOT NULL,
  `description` varchar(128) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=135 DEFAULT CHARSET=ascii;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_user_right`
--

LOCK TABLES `amun_user_right` WRITE;
/*!40000 ALTER TABLE `amun_user_right` DISABLE KEYS */;
INSERT INTO `amun_user_right` VALUES (1,3,'user_view','User view'),(2,3,'user_add','User add'),(3,3,'user_edit','User edit'),(4,3,'user_delete','User delete'),(5,3,'user_account_view','User Account view'),(6,3,'user_account_add','User Account add'),(7,3,'user_account_edit','User Account edit'),(8,3,'user_account_delete','User Account delete'),(9,3,'user_activity_view','User Activity view'),(10,3,'user_activity_add','User Activity add'),(11,3,'user_activity_edit','User Activity edit'),(12,3,'user_activity_delete','User Activity delete'),(13,3,'user_activity_receiver_view','User Activity Receiver view'),(14,3,'user_activity_receiver_add','User Activity Receiver add'),(15,3,'user_activity_receiver_edit','User Activity Receiver edit'),(16,3,'user_activity_receiver_delete','User Activity Receiver delete'),(17,3,'user_friend_view','User Friend view'),(18,3,'user_friend_add','User Friend add'),(19,3,'user_friend_edit','User Friend edit'),(20,3,'user_friend_delete','User Friend delete'),(21,3,'user_friend_group_view','User Friend Group view'),(22,3,'user_friend_group_add','User Friend Group add'),(23,3,'user_friend_group_edit','User Friend Group edit'),(24,3,'user_friend_group_delete','User Friend Group delete'),(25,3,'user_group_view','User Group view'),(26,3,'user_group_add','User Group add'),(27,3,'user_group_edit','User Group edit'),(28,3,'user_group_delete','User Group delete'),(29,3,'user_group_right_view','User Group Right view'),(30,3,'user_group_right_add','User Group Right add'),(31,3,'user_group_right_edit','User Group Right edit'),(32,3,'user_group_right_delete','User Group Right delete'),(33,3,'user_right_view','User Right view'),(34,3,'user_right_add','User Right add'),(35,3,'user_right_edit','User Right edit'),(36,3,'user_right_delete','User Right delete'),(37,4,'log_view','Log view'),(38,5,'hostmeta_view','Host-Meta View'),(39,6,'webfinger_view','Webfinger View'),(40,8,'media_view','Media View'),(41,8,'media_add','Media Add'),(42,8,'media_edit','Media Edit'),(43,8,'media_delete','Media Delete'),(44,9,'openid_view','OpenId View'),(45,9,'openid_add','OpenId add'),(46,9,'openid_edit','OpenId edit'),(47,9,'openid_delete','OpenId delete'),(48,9,'openid_access_view','OpenId Access view'),(49,9,'openid_access_add','OpenId Access add'),(50,9,'openid_access_edit','OpenId Access edit'),(51,9,'openid_access_delete','OpenId Access delete'),(52,10,'country_view','Country View'),(53,10,'country_add','Country Add'),(54,10,'country_edit','Country Edit'),(55,10,'country_delete','Country Delete'),(56,11,'swagger_view','Swagger View'),(57,12,'sitemap_view','Sitemap View'),(58,13,'phpinfo_view','Phpinfo View'),(59,14,'mail_view','Mail View'),(60,14,'mail_add','Mail Add'),(61,14,'mail_edit','Mail Edit'),(62,14,'mail_delete','Mail Delete'),(63,15,'content_view','Content view'),(64,15,'content_add','Content add'),(65,15,'content_edit','Content edit'),(66,15,'content_delete','Content delete'),(67,15,'content_gadget_view','Content Gadget view'),(68,15,'content_gadget_add','Content Gadget add'),(69,15,'content_gadget_edit','Content Gadget edit'),(70,15,'content_gadget_delete','Content Gadget delete'),(71,15,'content_page_view','Content Page view'),(72,15,'content_page_add','Content Page add'),(73,15,'content_page_edit','Content Page edit'),(74,15,'content_page_delete','Content Page delete'),(75,15,'content_page_preview','Content Page preview'),(76,15,'content_page_gadget_view','Content Page Gadget view'),(77,15,'content_page_gadget_add','Content Page Gadget add'),(78,15,'content_page_gadget_edit','Content Page Gadget edit'),(79,15,'content_page_gadget_delete','Content Page Gadget delete'),(80,15,'content_page_option_view','Content Page Option view'),(81,15,'content_page_option_add','Content Page Option add'),(82,15,'content_page_option_edit','Content Page Option edit'),(83,15,'content_page_option_delete','Content Page Option delete'),(84,16,'login_view','Login View'),(85,17,'my_view','My View'),(86,17,'my_friends_view','My Friends View'),(87,17,'my_activities_view','My Activites View'),(88,17,'my_settings_view','My Settings View'),(89,18,'profile_view','Profile View'),(90,19,'comment_view','Comment View'),(91,19,'comment_add','Comment Add'),(92,19,'comment_edit','Comment Edit'),(93,19,'comment_delete','Comment Delete'),(94,20,'news_view','News View'),(95,20,'news_add','News Add'),(96,20,'news_edit','News Edit'),(97,20,'news_delete','News Delete'),(98,20,'news_comment_add','News Comment Add'),(99,21,'file_view','File View'),(100,21,'file_add','File Add'),(101,21,'file_edit','File Edit'),(102,21,'file_delete','File Delete'),(103,22,'php_view','Php View'),(104,22,'php_add','Php Add'),(105,22,'php_edit','Php Edit'),(106,22,'php_delete','Php Delete'),(107,23,'redirect_view','Redirect View'),(108,23,'redirect_add','Redirect Add'),(109,23,'redirect_edit','Redirect Edit'),(110,23,'redirect_delete','Redirect Delete'),(111,24,'pipe_view','Pipe View'),(112,24,'pipe_add','Pipe Add'),(113,24,'pipe_edit','Pipe Edit'),(114,24,'pipe_delete','Pipe Delete'),(115,25,'oauth_view','Oauth view'),(116,25,'oauth_add','Oauth add'),(117,25,'oauth_edit','Oauth edit'),(118,25,'oauth_delete','Oauth delete'),(119,25,'oauth_access_view','Oauth Access view'),(120,25,'oauth_access_add','Oauth Access add'),(121,25,'oauth_access_edit','Oauth Access edit'),(122,25,'oauth_access_delete','Oauth Access delete'),(123,25,'oauth_request_view','Oauth Request view'),(124,25,'oauth_request_add','Oauth Request add'),(125,25,'oauth_request_edit','Oauth Request edit'),(126,25,'oauth_request_delete','Oauth Request delete'),(127,25,'oauth_access_right_view','Oauth Access Right view'),(128,25,'oauth_access_right_add','Oauth Access Right add'),(129,25,'oauth_access_right_edit','Oauth Access Right edit'),(130,25,'oauth_access_right_delete','Oauth Access Right delete'),(131,26,'page_view','Page View'),(132,26,'page_add','Page Add'),(133,26,'page_edit','Page Edit'),(134,26,'page_delete','Page Delete');
/*!40000 ALTER TABLE `amun_user_right` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `amun_xrds`
--

DROP TABLE IF EXISTS `amun_xrds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amun_xrds` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `serviceId` int(10) NOT NULL,
  `priority` int(10) NOT NULL DEFAULT '0',
  `endpoint` varchar(256) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `endpoint` (`endpoint`)
) ENGINE=MyISAM AUTO_INCREMENT=45 DEFAULT CHARSET=ascii;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_xrds`
--

LOCK TABLES `amun_xrds` WRITE;
/*!40000 ALTER TABLE `amun_xrds` DISABLE KEYS */;
INSERT INTO `amun_xrds` VALUES (1,2,0,'/xrds'),(2,3,0,'/user/account'),(3,3,0,'/user/activity'),(4,3,0,'/user/activity/message'),(5,3,0,'/user/friend'),(6,3,0,'/user/friend/relation'),(7,3,0,'/user/friend/group'),(8,3,0,'/user/group'),(9,3,0,'/user/group/right'),(10,3,0,'/user/right'),(11,4,0,'/log'),(12,5,0,'/hostmeta'),(13,6,0,'/webfinger'),(14,7,0,'/asset'),(15,8,0,'/media'),(16,9,0,'/openid'),(17,9,0,'/openid/access'),(18,9,0,'/openid/assoc'),(19,9,0,'/openid/signon'),(20,10,0,'/country'),(21,11,0,'/swagger'),(22,12,0,'/sitemap'),(23,13,0,'/phpinfo'),(24,14,0,'/mail'),(25,15,0,'/content/gadget'),(26,15,0,'/content/page'),(27,15,0,'/content/page/gadget'),(28,15,0,'/content/page/option'),(29,17,0,'/my/people'),(30,17,0,'/my/activity'),(31,17,0,'/my/verifyCredentials'),(32,19,0,'/comment'),(33,20,0,'/news'),(34,21,0,'/file'),(35,22,0,'/php'),(36,23,0,'/redirect'),(37,24,0,'/pipe'),(38,25,0,'/oauth/endpoint/request'),(39,25,0,'/oauth/endpoint/authorization'),(40,25,0,'/oauth/endpoint/access'),(41,25,0,'/oauth'),(42,25,0,'/oauth/access'),(43,25,0,'/oauth/request'),(44,26,0,'/page');
/*!40000 ALTER TABLE `amun_xrds` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `amun_xrds_type`
--

DROP TABLE IF EXISTS `amun_xrds_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amun_xrds_type` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `apiId` int(10) NOT NULL,
  `type` varchar(512) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `apiId_type` (`apiId`,`type`)
) ENGINE=MyISAM AUTO_INCREMENT=78 DEFAULT CHARSET=ascii;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_xrds_type`
--

LOCK TABLES `amun_xrds_type` WRITE;
/*!40000 ALTER TABLE `amun_xrds_type` DISABLE KEYS */;
INSERT INTO `amun_xrds_type` VALUES (1,1,'http://ns.amun-project.org/2011/amun/service/xrds'),(2,2,'http://ns.amun-project.org/2011/amun/service/user/account'),(3,2,'http://ns.amun-project.org/2011/amun/data/1.0'),(4,3,'http://ns.amun-project.org/2011/amun/service/user/activity'),(5,3,'http://ns.amun-project.org/2011/amun/data/1.0'),(6,4,'http://ns.amun-project.org/2012/amun/user/activity/message/1.0'),(7,5,'http://ns.amun-project.org/2011/amun/service/user/friend'),(8,5,'http://ns.amun-project.org/2011/amun/data/1.0'),(9,6,'http://ns.amun-project.org/2011/amun/user/friend/relation/1.0'),(10,7,'http://ns.amun-project.org/2011/amun/service/user/friend/group'),(11,7,'http://ns.amun-project.org/2011/amun/data/1.0'),(12,8,'http://ns.amun-project.org/2011/amun/service/user/group'),(13,8,'http://ns.amun-project.org/2011/amun/data/1.0'),(14,9,'http://ns.amun-project.org/2011/amun/service/user/group/right'),(15,9,'http://ns.amun-project.org/2011/amun/data/1.0'),(16,10,'http://ns.amun-project.org/2011/amun/service/user/right'),(17,10,'http://ns.amun-project.org/2011/amun/data/1.0'),(18,11,'http://ns.amun-project.org/2011/amun/service/log'),(19,11,'http://ns.amun-project.org/2011/amun/data/1.0'),(20,12,'http://ns.amun-project.org/2011/amun/service/hostmeta'),(21,13,'http://ns.amun-project.org/2011/amun/service/webfinger'),(22,14,'http://ns.amun-project.org/2011/amun/service/asset'),(23,15,'http://ns.amun-project.org/2011/amun/service/media'),(24,15,'http://ns.amun-project.org/2011/amun/data/1.0'),(25,16,'http://ns.amun-project.org/2011/amun/service/openid'),(26,16,'http://ns.amun-project.org/2011/amun/data/1.0'),(27,17,'http://ns.amun-project.org/2011/amun/service/openid/access'),(28,17,'http://ns.amun-project.org/2011/amun/data/1.0'),(29,18,'http://ns.amun-project.org/2011/amun/service/openid/assoc'),(30,18,'http://ns.amun-project.org/2011/amun/data/1.0'),(31,19,'http://specs.openid.net/auth/2.0/signon'),(32,19,'http://openid.net/extensions/sreg/1.1'),(33,19,'http://specs.openid.net/extensions/oauth/1.0'),(34,20,'http://ns.amun-project.org/2011/amun/service/country'),(35,20,'http://ns.amun-project.org/2011/amun/data/1.0'),(36,21,'http://ns.amun-project.org/2011/amun/service/swagger'),(37,22,'http://ns.amun-project.org/2011/amun/service/sitemap'),(38,22,'http://www.sitemaps.org/schemas/sitemap/0.9'),(39,23,'http://ns.amun-project.org/2011/amun/service/phpinfo'),(40,24,'http://ns.amun-project.org/2011/amun/service/mail'),(41,24,'http://ns.amun-project.org/2011/amun/data/1.0'),(42,25,'http://ns.amun-project.org/2011/amun/service/content/gadget'),(43,25,'http://ns.amun-project.org/2011/amun/data/1.0'),(44,26,'http://ns.amun-project.org/2011/amun/service/content/page'),(45,26,'http://ns.amun-project.org/2011/amun/data/1.0'),(46,27,'http://ns.amun-project.org/2011/amun/service/content/page/gadget'),(47,27,'http://ns.amun-project.org/2011/amun/data/1.0'),(48,28,'http://ns.amun-project.org/2011/amun/service/content/page/option'),(49,28,'http://ns.amun-project.org/2011/amun/data/1.0'),(50,29,'http://portablecontacts.net/spec/1.0'),(51,29,'http://ns.opensocial.org/2008/opensocial/people'),(52,30,'http://activitystrea.ms/spec/1.0/'),(53,30,'http://ns.opensocial.org/2008/opensocial/activities'),(54,31,'http://ns.amun-project.org/2011/amun/service/my/verifyCredentials'),(55,32,'http://ns.amun-project.org/2011/amun/service/comment'),(56,32,'http://ns.amun-project.org/2011/amun/data/1.0'),(57,33,'http://ns.amun-project.org/2011/amun/service/news'),(58,33,'http://ns.amun-project.org/2011/amun/data/1.0'),(59,34,'http://ns.amun-project.org/2011/amun/service/file'),(60,34,'http://ns.amun-project.org/2011/amun/data/1.0'),(61,35,'http://ns.amun-project.org/2011/amun/service/php'),(62,35,'http://ns.amun-project.org/2011/amun/data/1.0'),(63,36,'http://ns.amun-project.org/2011/amun/service/redirect'),(64,36,'http://ns.amun-project.org/2011/amun/data/1.0'),(65,37,'http://ns.amun-project.org/2011/amun/service/pipe'),(66,37,'http://ns.amun-project.org/2011/amun/data/1.0'),(67,38,'http://oauth.net/core/1.0/endpoint/request'),(68,39,'http://oauth.net/core/1.0/endpoint/authorize'),(69,40,'http://oauth.net/core/1.0/endpoint/access'),(70,41,'http://ns.amun-project.org/2011/amun/service/oauth'),(71,41,'http://ns.amun-project.org/2011/amun/data/1.0'),(72,42,'http://ns.amun-project.org/2011/amun/service/oauth/access'),(73,42,'http://ns.amun-project.org/2011/amun/data/1.0'),(74,43,'http://ns.amun-project.org/2011/amun/service/oauth/request'),(75,43,'http://ns.amun-project.org/2011/amun/data/1.0'),(76,44,'http://ns.amun-project.org/2011/amun/service/page'),(77,44,'http://ns.amun-project.org/2011/amun/data/1.0');
/*!40000 ALTER TABLE `amun_xrds_type` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2013-08-26 22:58:03
