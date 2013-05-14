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
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_comment`
--

LOCK TABLES `amun_comment` WRITE;
/*!40000 ALTER TABLE `amun_comment` DISABLE KEYS */;
INSERT INTO `amun_comment` VALUES (1,'7a834452-14df-5724-b00e-e165b974e61b',7,1,1,'<p>foobar </p>','2013-04-12 21:12:52');
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
  `path` varchar(256) NOT NULL,
  `param` text,
  `cache` tinyint(1) NOT NULL,
  `expire` varchar(25) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `globalId` (`globalId`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_content_gadget`
--

LOCK TABLES `amun_content_gadget` WRITE;
/*!40000 ALTER TABLE `amun_content_gadget` DISABLE KEYS */;
INSERT INTO `amun_content_gadget` VALUES (1,'52a73a5c-d95d-5013-aee4-47d3ee75d712',21,0,'ajax','LATEST_NEWS','Latest News','latestNews.php',NULL,0,'','2013-04-12 20:53:17');
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
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_content_page`
--

LOCK TABLES `amun_content_page` WRITE;
/*!40000 ALTER TABLE `amun_content_page` DISABLE KEYS */;
INSERT INTO `amun_content_page` VALUES (1,0,'5aa63a03-b140-59b4-922a-a3e91b5266fe',19,0,2,3,0,'','test','test','','','',0,'','0000-00-00 00:00:00','2013-04-12 20:51:10'),(2,1,'1b20fc55-32bf-5eca-9dd9-9f54f3552844',19,0,1,3,0,'home','home','Home','','','',0,'','0000-00-00 00:00:00','2013-04-12 20:51:10'),(3,1,'2eb1e5f8-0bb4-58f2-b889-954d18c7163a',17,0,2,3,0,'my','my','My','','','',0,'','0000-00-00 00:00:00','2013-04-12 20:51:10'),(4,1,'a0cf04e4-a229-5f79-b22f-f1d047c73a00',18,0,2,3,0,'profile','profile','Profile','','','',0,'','0000-00-00 00:00:00','2013-04-12 20:51:10'),(5,1,'9b87cd28-07f9-5e33-b11f-f44116ecd64b',19,0,2,3,0,'help','help','Help','','','',0,'','0000-00-00 00:00:00','2013-04-12 20:51:10'),(6,1,'c2f0b0d0-31a2-5b65-a55e-e97b0f4ce5da',22,0,1,3,0,'file','file','File','','','',0,'','0000-00-00 00:00:00','2013-04-12 20:51:47'),(7,1,'eef15285-adac-5465-a88f-fa461ff967c4',21,0,1,3,0,'news','news','News','','','',0,'','0000-00-00 00:00:00','2013-04-12 20:51:59'),(8,1,'a2c1f1ff-2831-5638-9ff9-9617d1a5dc0a',23,0,1,3,0,'php','php','Php','','','',0,'','0000-00-00 00:00:00','2013-04-12 20:52:09'),(9,1,'da47434a-2f57-5bcf-a222-271246d8d9a9',25,0,1,3,0,'pipe','pipe','Pipe','','','',0,'','0000-00-00 00:00:00','2013-04-12 20:52:21'),(10,1,'1fd58c0f-d0da-56f8-8dd8-80035d4bdc71',24,0,1,3,0,'redirect','redirect','Redirect','','','',0,'','0000-00-00 00:00:00','2013-04-12 20:52:34');
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
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=ascii;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_content_page_gadget`
--

LOCK TABLES `amun_content_page_gadget` WRITE;
/*!40000 ALTER TABLE `amun_content_page_gadget` DISABLE KEYS */;
INSERT INTO `amun_content_page_gadget` VALUES (1,2,1,0);
/*!40000 ALTER TABLE `amun_content_page_gadget` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `amun_content_page_option`
--

DROP TABLE IF EXISTS `amun_content_page_option`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amun_content_page_option` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `optionId` int(10) NOT NULL,
  `rightId` int(10) NOT NULL,
  `srcPageId` int(10) NOT NULL,
  `destPageId` int(10) NOT NULL,
  `name` varchar(32) NOT NULL,
  `href` varchar(512) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=ascii;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_content_page_option`
--

LOCK TABLES `amun_content_page_option` WRITE;
/*!40000 ALTER TABLE `amun_content_page_option` DISABLE KEYS */;
/*!40000 ALTER TABLE `amun_content_page_option` ENABLE KEYS */;
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
INSERT INTO `amun_core_event` VALUES (1,'core.service_install',NULL,'Notifies if a service gets installed'),(2,'core.record_change',NULL,'Notifies if a record has changed'),(3,'hostmeta.request',NULL,'Notifies if a hostmeta request occurs'),(4,'lrdd.resource_discovery',NULL,'Notifies if an lrdd lookup occurs');
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
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_core_event_listener`
--

LOCK TABLES `amun_core_event_listener` WRITE;
/*!40000 ALTER TABLE `amun_core_event_listener` DISABLE KEYS */;
INSERT INTO `amun_core_event_listener` VALUES (1,2,0,'AmunService\\Log\\RecordListener'),(2,1,0,'AmunService\\Xrds\\ConfigListener'),(3,3,0,'AmunService\\Lrdd\\HostmetaListener'),(4,2,0,'AmunService\\User\\Activity\\RecordListener'),(5,1,0,'AmunService\\User\\Activity\\ConfigListener'),(6,1,0,'AmunService\\User\\Right\\ConfigListener'),(7,4,0,'AmunService\\User\\LrddListener'),(8,1,0,'AmunService\\Core\\Service\\Option\\ConfigListener'),(9,1,0,'AmunService\\Mail\\ConfigListener'),(10,4,0,'AmunService\\Content\\LrddListener');
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
) ENGINE=MyISAM AUTO_INCREMENT=68 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_core_registry`
--

LOCK TABLES `amun_core_registry` WRITE;
/*!40000 ALTER TABLE `amun_core_registry` DISABLE KEYS */;
INSERT INTO `amun_core_registry` VALUES (1,'table.core_approval','STRING',NULL,'amun_core_approval'),(2,'table.core_approval_record','STRING',NULL,'amun_core_approval_record'),(3,'table.core_event','STRING',NULL,'amun_core_event'),(4,'table.core_event_listener','STRING',NULL,'amun_core_event_listener'),(5,'table.core_registry','STRING',NULL,'amun_core_registry'),(6,'table.core_service','STRING',NULL,'amun_core_service'),(7,'core.default_timezone','STRING','DateTimeZone','UTC'),(8,'table.log','STRING',NULL,'amun_log'),(9,'table.xrds','STRING',NULL,'amun_xrds'),(10,'table.xrds_type','STRING',NULL,'amun_xrds_type'),(11,'table.user_account','STRING',NULL,'amun_user_account'),(12,'table.user_activity','STRING',NULL,'amun_user_activity'),(13,'table.user_activity_receiver','STRING',NULL,'amun_user_activity_receiver'),(14,'table.user_activity_template','STRING',NULL,'amun_user_activity_template'),(15,'table.user_friend','STRING',NULL,'amun_user_friend'),(16,'table.user_friend_group','STRING',NULL,'amun_user_friend_group'),(17,'table.user_group','STRING',NULL,'amun_user_group'),(18,'table.user_group_right','STRING',NULL,'amun_user_group_right'),(19,'table.user_right','STRING',NULL,'amun_user_right'),(20,'table.core_service_option','STRING',NULL,'amun_core_service_option'),(21,'table.core_assoc','STRING',NULL,'amun_core_assoc'),(22,'table.core_host','STRING',NULL,'amun_core_host'),(23,'table.core_host_request','STRING',NULL,'amun_core_host_request'),(24,'core.title','STRING',NULL,'test'),(25,'core.sub_title','STRING',NULL,''),(26,'core.anonymous_user','INTEGER',NULL,'2'),(27,'core.session_expire','INTEGER',NULL,'1800'),(28,'core.default_user_group','INTEGER',NULL,'2'),(29,'core.default_page','STRING',NULL,'home'),(30,'core.format_datetime','STRING',NULL,'d M. Y, H:i'),(31,'core.format_date','STRING',NULL,'d. F Y'),(32,'core.install_date','STRING',NULL,'2013-04-12 20:51:10'),(33,'core.input_limit','INTEGER',NULL,'16'),(34,'core.input_interval','STRING',NULL,'PT30M'),(35,'core.pw_alpha','INTEGER',NULL,'4'),(36,'core.pw_numeric','INTEGER',NULL,'2'),(37,'core.pw_special','INTEGER',NULL,'0'),(38,'table.oauth','STRING',NULL,'amun_oauth'),(39,'table.oauth_access','STRING',NULL,'amun_oauth_access'),(40,'table.oauth_access_right','STRING',NULL,'amun_oauth_access_right'),(41,'table.oauth_request','STRING',NULL,'amun_oauth_request'),(42,'table.media','STRING',NULL,'amun_media'),(43,'media.upload_size','INTEGER',NULL,'4194304'),(44,'media.path','STRING',NULL,'../cache'),(45,'table.openid','STRING',NULL,'amun_openid'),(46,'table.openid_access','STRING',NULL,'amun_openid_access'),(47,'table.openid_assoc','STRING',NULL,'amun_openid_assoc'),(48,'table.country','STRING',NULL,'amun_country'),(49,'table.mail','STRING',NULL,'amun_mail'),(50,'table.content_gadget','STRING',NULL,'amun_content_gadget'),(51,'table.content_page','STRING',NULL,'amun_content_page'),(52,'table.content_page_gadget','STRING',NULL,'amun_content_page_gadget'),(53,'table.content_page_option','STRING',NULL,'amun_content_page_option'),(54,'table.content_page_right','STRING',NULL,'amun_content_page_right'),(55,'table.my_attempt','STRING',NULL,'amun_my_attempt'),(56,'my.registration_enabled','BOOLEAN',NULL,'1'),(57,'my.max_group_count','INTEGER',NULL,'12'),(58,'my.max_wrong_login','INTEGER',NULL,'8'),(59,'my.login_provider','STRING',NULL,'google, yahoo, openid, system'),(60,'table.page','STRING',NULL,'amun_page'),(61,'table.comment','STRING',NULL,'amun_comment'),(62,'table.news','STRING',NULL,'amun_news'),(63,'table.file','STRING',NULL,'amun_file'),(64,'table.php','STRING',NULL,'amun_php'),(65,'table.redirect','STRING',NULL,'amun_redirect'),(66,'table.pipe','STRING',NULL,'amun_pipe'),(67,'table.core_service_provider','STRING',NULL,'amun_core_service_provider');
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
  `providerId` int(10) NOT NULL DEFAULT '0',
  `status` int(10) NOT NULL,
  `source` varchar(128) NOT NULL,
  `name` varchar(32) NOT NULL,
  `path` varchar(256) NOT NULL,
  `namespace` varchar(64) NOT NULL,
  `type` varchar(256) NOT NULL,
  `link` varchar(256) NOT NULL,
  `author` varchar(512) NOT NULL,
  `license` varchar(256) NOT NULL,
  `version` varchar(16) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=26 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_core_service`
--

LOCK TABLES `amun_core_service` WRITE;
/*!40000 ALTER TABLE `amun_core_service` DISABLE KEYS */;
INSERT INTO `amun_core_service` VALUES (1,2,2,'org.amun-project.log','log','/log','log','http://ns.amun-project.org/2011/amun/service/log','http://amun.phpsx.org','Christoph Kappestein <k42b3.x@gmail.com>','GPLv3','0.0.1','2013-04-12 20:51:03'),(2,2,2,'org.amun-project.xrds','xrds','/xrds','xrds','http://ns.amun-project.org/2011/amun/service/xrds','http://amun.phpsx.org','Christoph Kappestein <k42b3.x@gmail.com>','GPLv3','0.0.1','2013-04-12 20:51:03'),(3,2,2,'org.amun-project.hostmeta','hostmeta','/hostmeta','hostmeta','http://ns.amun-project.org/2011/amun/service/hostmeta','http://amun.phpsx.org','Christoph Kappestein <k42b3.x@gmail.com>','GPLv3','0.0.1','2013-04-12 20:51:03'),(4,2,2,'org.amun-project.lrdd','lrdd','/lrdd','lrdd','http://ns.amun-project.org/2011/amun/service/lrdd','http://amun.phpsx.org','Christoph Kappestein <k42b3.x@gmail.com>','GPLv3','0.0.1','2013-04-12 20:51:03'),(5,2,2,'org.amun-project.user','user','/user','user','http://ns.amun-project.org/2011/amun/service/user','http://amun.phpsx.org','Christoph Kappestein <k42b3.x@gmail.com>','GPLv3','0.0.1','2013-04-12 20:51:04'),(6,2,2,'org.amun-project.core','core','/core','core','http://ns.amun-project.org/2011/amun/service/core','http://amun.phpsx.org','Christoph Kappestein <k42b3.x@gmail.com>','GPLv3','0.0.1','2013-04-12 20:51:05'),(7,2,2,'org.amun-project.asset','asset','/asset','asset','http://ns.amun-project.org/2011/amun/service/asset','http://amun.phpsx.org','Christoph Kappestein <k42b3.x@gmail.com>','GPLv3','0.0.1','2013-04-12 20:51:06'),(8,2,2,'org.amun-project.oauth','oauth','/oauth','oauth','http://ns.amun-project.org/2011/amun/service/oauth','http://amun.phpsx.org','Christoph Kappestein <k42b3.x@gmail.com>','GPLv3','0.0.1','2013-04-12 20:51:06'),(9,2,2,'org.amun-project.media','media','/media','media','http://ns.amun-project.org/2011/amun/service/media','http://amun.phpsx.org','Christoph Kappestein <k42b3.x@gmail.com>','GPLv3','0.0.1','2013-04-12 20:51:06'),(10,2,2,'org.amun-project.openid','openid','/openid','openid','http://ns.amun-project.org/2011/amun/service/openid','http://amun.phpsx.org','Christoph Kappestein <k42b3.x@gmail.com>','GPLv3','0.0.1','2013-04-12 20:51:06'),(11,2,2,'org.amun-project.country','country','/country','country','http://ns.amun-project.org/2011/amun/service/country','http://amun.phpsx.org','Christoph Kappestein <k42b3.x@gmail.com>','GPLv3','0.0.1','2013-04-12 20:51:07'),(12,2,2,'org.amun-project.mail','mail','/mail','mail','http://ns.amun-project.org/2011/amun/service/mail','http://amun.phpsx.org','Christoph Kappestein <k42b3.x@gmail.com>','GPLv3','0.0.1','2013-04-12 20:51:07'),(13,2,2,'org.amun-project.swagger','swagger','/swagger','swagger','http://ns.amun-project.org/2011/amun/service/swagger','http://amun.phpsx.org','Christoph Kappestein <k42b3.x@gmail.com>','GPLv3','0.0.1','2013-04-12 20:51:07'),(14,2,2,'org.amun-project.sitemap','sitemap','/sitemap','sitemap','http://ns.amun-project.org/2011/amun/service/sitemap','http://amun.phpsx.org','Christoph Kappestein <k42b3.x@gmail.com>','GPLv3','0.0.1','2013-04-12 20:51:07'),(15,2,2,'org.amun-project.phpinfo','phpinfo','/phpinfo','phpinfo','http://ns.amun-project.org/2011/amun/service/phpinfo','http://amun.phpsx.org','Christoph Kappestein <k42b3.x@gmail.com>','GPLv3','0.0.1','2013-04-12 20:51:07'),(16,2,2,'org.amun-project.content','content','/content','content','http://ns.amun-project.org/2011/amun/service/content','http://amun.phpsx.org','Christoph Kappestein <k42b3.x@gmail.com>','GPLv3','0.0.1','2013-04-12 20:51:08'),(17,2,1,'org.amun-project.my','my','/my','my','http://ns.amun-project.org/2011/amun/service/my','http://amun.phpsx.org','Christoph Kappestein <k42b3.x@gmail.com>','GPLv3','0.0.1','2013-04-12 20:51:08'),(18,2,1,'org.amun-project.profile','profile','/profile','profile','http://ns.amun-project.org/2011/amun/service/profile','http://amun.phpsx.org','Christoph Kappestein <k42b3.x@gmail.com>','GPLv3','0.0.1','2013-04-12 20:51:08'),(19,2,1,'org.amun-project.page','page','/page','page','http://ns.amun-project.org/2011/amun/service/page','http://amun.phpsx.org','Christoph Kappestein <k42b3.x@gmail.com>','GPLv3','0.0.1','2013-04-12 20:51:09'),(20,2,2,'org.amun-project.comment','comment','/comment','comment','http://ns.amun-project.org/2011/amun/service/comment','http://amun.phpsx.org','Christoph Kappestein <k42b3.x@gmail.com>','GPLv3','0.0.1','2013-04-12 20:51:09'),(21,2,1,'org.amun-project.news','news','/news','news','http://ns.amun-project.org/2011/amun/service/news','http://amun.phpsx.org','Christoph Kappestein <k42b3.x@gmail.com>','GPLv3','0.0.1','2013-04-12 20:51:09'),(22,2,1,'org.amun-project.file','file','/file','file','http://ns.amun-project.org/2011/amun/service/file','http://amun.phpsx.org','Christoph Kappestein <k42b3.x@gmail.com>','GPLv3','0.0.1','2013-04-12 20:51:09'),(23,2,1,'org.amun-project.php','php','/php','php','http://ns.amun-project.org/2011/amun/service/php','http://amun.phpsx.org','Christoph Kappestein <k42b3.x@gmail.com>','GPLv3','0.0.1','2013-04-12 20:51:09'),(24,2,1,'org.amun-project.redirect','redirect','/redirect','redirect','http://ns.amun-project.org/2011/amun/service/redirect','http://amun.phpsx.org','Christoph Kappestein <k42b3.x@gmail.com>','GPLv3','0.0.1','2013-04-12 20:51:10'),(25,2,1,'org.amun-project.pipe','pipe','/pipe','pipe','http://ns.amun-project.org/2011/amun/service/pipe','http://amun.phpsx.org','Christoph Kappestein <k42b3.x@gmail.com>','GPLv3','0.0.1','2013-04-12 20:51:10');
/*!40000 ALTER TABLE `amun_core_service` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `amun_core_service_option`
--

DROP TABLE IF EXISTS `amun_core_service_option`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amun_core_service_option` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `serviceId` int(10) NOT NULL,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=ascii;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_core_service_option`
--

LOCK TABLES `amun_core_service_option` WRITE;
/*!40000 ALTER TABLE `amun_core_service_option` DISABLE KEYS */;
/*!40000 ALTER TABLE `amun_core_service_option` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `amun_core_service_provider`
--

DROP TABLE IF EXISTS `amun_core_service_provider`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amun_core_service_provider` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `url` varchar(256) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_core_service_provider`
--

LOCK TABLES `amun_core_service_provider` WRITE;
/*!40000 ALTER TABLE `amun_core_service_provider` DISABLE KEYS */;
INSERT INTO `amun_core_service_provider` VALUES (1,'localhost','2013-04-28 12:43:28'),(2,'http://amun-project.org','2013-04-28 12:43:28');
/*!40000 ALTER TABLE `amun_core_service_provider` ENABLE KEYS */;
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
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_country`
--

LOCK TABLES `amun_country` WRITE;
/*!40000 ALTER TABLE `amun_country` DISABLE KEYS */;
INSERT INTO `amun_country` VALUES (1,'Undisclosed','',0,0);
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
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_file`
--

LOCK TABLES `amun_file` WRITE;
/*!40000 ALTER TABLE `amun_file` DISABLE KEYS */;
INSERT INTO `amun_file` VALUES (1,'e72e963b-7c34-5a39-8bb8-825213c99ade',6,1,'text/plain','foobar','2013-04-12 20:54:02');
/*!40000 ALTER TABLE `amun_file` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `amun_forum`
--

DROP TABLE IF EXISTS `amun_forum`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amun_forum` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `globalId` varchar(36) NOT NULL,
  `pageId` int(10) NOT NULL,
  `userId` int(10) NOT NULL,
  `sticky` tinyint(1) NOT NULL DEFAULT '0',
  `closed` tinyint(1) NOT NULL DEFAULT '0',
  `urlTitle` varchar(128) NOT NULL,
  `title` varchar(128) NOT NULL,
  `text` longtext NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `globalId` (`globalId`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_forum`
--

LOCK TABLES `amun_forum` WRITE;
/*!40000 ALTER TABLE `amun_forum` DISABLE KEYS */;
INSERT INTO `amun_forum` VALUES (1,'9c0e282b-fe13-55b1-b33f-f2c9e1dab114',13,1,0,0,'erwer','erwer','<p>werwerwer </p>\n','2013-04-28 19:39:00');
/*!40000 ALTER TABLE `amun_forum` ENABLE KEYS */;
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
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=ascii;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_log`
--

LOCK TABLES `amun_log` WRITE;
/*!40000 ALTER TABLE `amun_log` DISABLE KEYS */;
INSERT INTO `amun_log` VALUES (1,1,1,'INSERT','amun_core_service','2013-04-12 20:51:03');
/*!40000 ALTER TABLE `amun_log` ENABLE KEYS */;
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
INSERT INTO `amun_mail` VALUES (1,'MY_REGISTRATION','noreply@127.0.0.1','Account activation','\nHello {account.name},\n\nyou have successful registered at {host.name}. Your identity is\n\"{account.identity}\". In order to activate your account please visit the\nfollowing activation link:\n\n{register.link}\n','\nHello {account.name},\n\nyou have successful registered at {host.name}. Your identity is\n\"{account.identity}\". In order to activate your account please visit the\nfollowing activation link:\n\n{register.link}\n','account.name;account.identity;host.name;register.link;register.date'),(2,'MY_RECOVER','noreply@127.0.0.1','Account recover','\nHello {account.name},\n\n{recover.ip} wants to recover your password. In order to get a new password \nvisit the following link. We will create a new password for you and send it to \nthis email address. Ignore this email if you have not requested a new password.\n\n{recover.link}\n','\nHello {account.name},\n\n{recover.ip} wants to recover your password. In order to get a new password \nvisit the following link. We will create a new password for you and send it to \nthis email address. Ignore this email if you have not requested a new password.\n\n{recover.link}\n','account.name;host.name;recover.ip;recover.link;recover.date'),(3,'MY_RECOVER_SUCCESS','noreply@127.0.0.1','Account recover success','\nHello {account.name},\n\nyou have successful recovered your password. It is highly recommended to change\nthe password after a recovery process because the password was maybe transmitted\nover an insecure channel.\n\nNew password: {account.pw}\n\nYou can login with the new password at:\n{recover.link}\n','\nHello {account.name},\n\nyou have successful recovered your password. It is highly recommended to change\nthe password after a recovery process because the password was maybe transmitted\nover an insecure channel.\n\nNew password: {account.pw}\n\nYou can login with the new password at:\n{recover.link}\n','account.name;account.pw;host.name;recover.link;recover.date');
/*!40000 ALTER TABLE `amun_mail` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `amun_marketplace`
--

DROP TABLE IF EXISTS `amun_marketplace`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amun_marketplace` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `globalId` varchar(36) NOT NULL,
  `status` int(10) NOT NULL,
  `source` varchar(128) NOT NULL,
  `name` varchar(32) NOT NULL,
  `description` text NOT NULL,
  `link` varchar(256) NOT NULL,
  `author` varchar(256) NOT NULL,
  `license` varchar(256) NOT NULL,
  `version` varchar(16) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `source` (`source`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_marketplace`
--

LOCK TABLES `amun_marketplace` WRITE;
/*!40000 ALTER TABLE `amun_marketplace` DISABLE KEYS */;
/*!40000 ALTER TABLE `amun_marketplace` ENABLE KEYS */;
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
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_media`
--

LOCK TABLES `amun_media` WRITE;
/*!40000 ALTER TABLE `amun_media` DISABLE KEYS */;
INSERT INTO `amun_media` VALUES (1,'0f52d88e-ac8f-5aad-8004-4c2169eef2f4',77,'empty','empty','text',0,'text/plain','2013-04-12 20:52:58');
/*!40000 ALTER TABLE `amun_media` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `amun_my_attempt`
--

DROP TABLE IF EXISTS `amun_my_attempt`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amun_my_attempt` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `ip` varchar(40) NOT NULL,
  `count` int(10) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ip` (`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_my_attempt`
--

LOCK TABLES `amun_my_attempt` WRITE;
/*!40000 ALTER TABLE `amun_my_attempt` DISABLE KEYS */;
/*!40000 ALTER TABLE `amun_my_attempt` ENABLE KEYS */;
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
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_news`
--

LOCK TABLES `amun_news` WRITE;
/*!40000 ALTER TABLE `amun_news` DISABLE KEYS */;
INSERT INTO `amun_news` VALUES (1,'f46f108a-3c2b-5cda-855f-f63ea190b9e6',7,1,'foobar','foobar','<p>content </p>\n','2013-04-12 20:54:19');
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
INSERT INTO `amun_oauth` VALUES (1,1,'System','test@test.com','http://127.0.0.1/projects/amun/public','System','Default system API to access amun','c419e3a6ff4079fad1ce61a153b6551ab405409c','e40fe562de6b4c3970684e2c597824ec91edf9d4','','2013-04-12 20:51:10');
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
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=ascii;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_oauth_access`
--

LOCK TABLES `amun_oauth_access` WRITE;
/*!40000 ALTER TABLE `amun_oauth_access` DISABLE KEYS */;
INSERT INTO `amun_oauth_access` VALUES (1,1,1,1,'2013-03-20 19:49:22');
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
) ENGINE=MyISAM AUTO_INCREMENT=161 DEFAULT CHARSET=ascii;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_oauth_access_right`
--

LOCK TABLES `amun_oauth_access_right` WRITE;
/*!40000 ALTER TABLE `amun_oauth_access_right` DISABLE KEYS */;
INSERT INTO `amun_oauth_access_right` VALUES (1,1,1),(2,1,2),(3,1,3),(4,1,4),(5,1,5),(6,1,6),(7,1,7),(8,1,8),(9,1,9),(10,1,10),(11,1,11),(12,1,12),(13,1,13),(14,1,14),(15,1,15),(16,1,16),(17,1,17),(18,1,18),(19,1,19),(20,1,20),(21,1,21),(22,1,22),(23,1,23),(24,1,24),(25,1,25),(26,1,26),(27,1,27),(28,1,28),(29,1,29),(30,1,30),(31,1,31),(32,1,32),(33,1,33),(34,1,34),(35,1,35),(36,1,36),(37,1,37),(38,1,38),(39,1,39),(40,1,40),(41,1,41),(42,1,42),(43,1,43),(44,1,44),(45,1,45),(46,1,46),(47,1,47),(48,1,48),(49,1,49),(50,1,50),(51,1,51),(52,1,52),(53,1,53),(54,1,54),(55,1,55),(56,1,56),(57,1,57),(58,1,58),(59,1,59),(60,1,60),(61,1,61),(62,1,62),(63,1,63),(64,1,64),(65,1,65),(66,1,66),(67,1,67),(68,1,68),(69,1,69),(70,1,70),(71,1,71),(72,1,72),(73,1,73),(74,1,74),(75,1,75),(76,1,76),(77,1,77),(78,1,78),(79,1,79),(80,1,80),(81,1,81),(82,1,82),(83,1,83),(84,1,84),(85,1,85),(86,1,86),(87,1,87),(88,1,88),(89,1,89),(90,1,90),(91,1,91),(92,1,92),(93,1,93),(94,1,94),(95,1,95),(96,1,96),(97,1,97),(98,1,98),(99,1,99),(100,1,100),(101,1,101),(102,1,102),(103,1,103),(104,1,104),(105,1,105),(106,1,106),(107,1,107),(108,1,108),(109,1,109),(110,1,110),(111,1,111),(112,1,112),(113,1,113),(114,1,114),(115,1,115),(116,1,116),(117,1,117),(118,1,118),(119,1,119),(120,1,120),(121,1,121),(122,1,122),(123,1,123),(124,1,124),(125,1,125),(126,1,126),(127,1,127),(128,1,128),(129,1,129),(130,1,130),(131,1,131),(132,1,132),(133,1,133),(134,1,134),(135,1,135),(136,1,136),(137,1,137),(138,1,138),(139,1,139),(140,1,140),(141,1,141),(142,1,142),(143,1,143),(144,1,144),(145,1,145),(146,1,146),(147,1,147),(148,1,148),(149,1,149),(150,1,150),(151,1,151),(152,1,152),(153,1,153),(154,1,154),(155,1,155),(156,1,156),(157,1,157),(158,1,158),(159,1,159),(160,1,160);
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
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=ascii;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_oauth_request`
--

LOCK TABLES `amun_oauth_request` WRITE;
/*!40000 ALTER TABLE `amun_oauth_request` DISABLE KEYS */;
INSERT INTO `amun_oauth_request` VALUES (1,1,1,3,'127.0.0.1','4460f8e54130cb1a','oob','7214c19eb59fefaf1ab425a9ccd964b370dbb976','930f1aad0a6f8073c5ea5c0105b9953d145b8d22','8f28b3b18a6eabb92854cd937397b042','1363808954','P6M','2013-03-20 19:49:26');
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
INSERT INTO `amun_page` VALUES (1,'f6a9fc77-27a4-5a48-9228-8dd911485427',1,1,'<h1>It works!</h1>\n<p>This is the default web page for this server.</p>\n<p>The web server software is running but no content has been added, yet.</p>','2013-04-12 20:51:10'),(2,'65c981c6-b65b-54a7-b88c-c75f6cb9bc2b',2,1,'<h1>Lorem ipsum dolor sit amet</h1>\n<p>consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</p>','2013-04-12 20:51:10'),(3,'c8220a4f-70de-5ddf-bffc-c1b4c094a1c5',5,1,'<h1>Help ...</h1>','2013-04-12 20:51:10');
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
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_php`
--

LOCK TABLES `amun_php` WRITE;
/*!40000 ALTER TABLE `amun_php` DISABLE KEYS */;
INSERT INTO `amun_php` VALUES (1,'25c0ee81-0259-57cb-b330-088af988010f',8,1,'\necho $_SERVER[\'REMOTE_ADDR\'];','2013-04-12 20:54:42');
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
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_pipe`
--

LOCK TABLES `amun_pipe` WRITE;
/*!40000 ALTER TABLE `amun_pipe` DISABLE KEYS */;
INSERT INTO `amun_pipe` VALUES (1,'5bc21e8d-137a-5127-b997-79b38865e80e',9,1,1,'passthru','2013-04-12 20:54:52');
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
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_redirect`
--

LOCK TABLES `amun_redirect` WRITE;
/*!40000 ALTER TABLE `amun_redirect` DISABLE KEYS */;
INSERT INTO `amun_redirect` VALUES (1,'7d999670-b906-595f-944c-c32af2a46c8f',10,1,'http://localhost','2013-04-12 20:55:48');
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
INSERT INTO `amun_user_account` VALUES (1,'8499868f-4213-529f-bff8-89c8cd9b6fd4',1,0,1,5,'60f0054d051b6e8792b6df64968693f60dbb5ef3','test','cf8e508e08ad778f9c471f4fd6d0c93c79938e84','test@test.com','e1c61f2775c7be6b7be08831817928afa46a60a3','127.0.0.1','undisclosed','http://127.0.0.1/index.php/profile/test','http://www.gravatar.com/avatar/b642b4217b34b1e8d3bd915fc65c4452?d=http%3A%2F%2F127.0.0.1%2Fprojects%2Famun%2Fpublic%2Fimg%2Favatar%2Fno_image.png&s=48',0,0,'UTC','2013-04-30 14:47:48','2013-04-12 20:51:10','2013-04-12 20:51:10'),(2,'3059e82f-e408-5567-900b-b554ac386fb3',3,0,1,2,'a5b1ec08b2eef7741eacd969c7a17c7c0097439f','Anonymous','c3cfe7665e0071c17a3aeb3cac58601586863dee',NULL,'00a8ba2ce22463426d80130017f91b4e0171379f','127.0.0.1','undisclosed','http://127.0.0.1/projects/amun/public/index.php/profile/Anonymous','http://127.0.0.1/projects/amun/public/img/avatar/no_image.png',0,0,'UTC','2013-04-12 20:51:34','2013-04-12 20:51:10','2013-04-12 20:51:10');
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
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_user_activity`
--

LOCK TABLES `amun_user_activity` WRITE;
/*!40000 ALTER TABLE `amun_user_activity` DISABLE KEYS */;
INSERT INTO `amun_user_activity` (`id`, `globalId`, `parentId`, `userId`, `scope`, `verb`, `object`, `summary`, `date`) VALUES (1, '4354b228-7315-5d6a-8662-249559c0ad78', 0, 1, 0, 'join', '', 'test has created an account', '2013-04-12 20:51:10'),(2, 'b3c6df5f-f3f2-5440-affb-b31264c44bb1', 0, 2, 0, 'join', '', 'Anonymous has created an account', '2013-04-12 20:51:10'),(3, '76000200-41ca-58f3-bbb0-09bd16bda9d1', 0, 1, 0, 'add', '', '\n<p><a href="http://127.0.0.1/projects/amun/public/index.php/profile/test">test</a> has created a <a href="http://127.0.0.1/projects/amun/public/index.php/news/view?id=1">news</a></p><blockquote>content \n</blockquote>\n', '2013-04-12 20:54:19');
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
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=ascii;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_user_activity_receiver`
--

LOCK TABLES `amun_user_activity_receiver` WRITE;
/*!40000 ALTER TABLE `amun_user_activity_receiver` DISABLE KEYS */;
INSERT INTO `amun_user_activity_receiver` VALUES (1,1,1,1,'2013-04-12 22:51:10'),(2,1,2,2,'2013-04-12 22:51:10'),(3,1,3,1,'2013-04-12 22:54:19');
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
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_user_activity_template`
--

LOCK TABLES `amun_user_activity_template` WRITE;
/*!40000 ALTER TABLE `amun_user_activity_template` DISABLE KEYS */;
INSERT INTO `amun_user_activity_template` VALUES (1,'INSERT','add','amun_comment','view?id={record.refId}#comment-{record.id}','\n<p><a href=\"{user.profileUrl}\">{user.name}</a> has created an <a href=\"{object.url}\">comment</a></p><blockquote>{record.text}</blockquote>\n'),(2,'INSERT','add','amun_news','view?id={record.id}','\n<p><a href=\"{user.profileUrl}\">{user.name}</a> has created a <a href=\"{object.url}\">news</a></p><blockquote>{record.text}</blockquote>\n');
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
INSERT INTO `amun_user_friend` VALUES (1,2,0,1,1,'2013-04-12 20:51:10'),(2,2,0,2,2,'2013-04-12 20:51:10');
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
INSERT INTO `amun_user_group` VALUES (1,'Administrator','2013-04-12 22:51:10'),(2,'Normal','2013-04-12 20:51:10'),(3,'Anonymous','2013-04-12 20:51:10');
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
) ENGINE=MyISAM AUTO_INCREMENT=201 DEFAULT CHARSET=ascii;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_user_group_right`
--

LOCK TABLES `amun_user_group_right` WRITE;
/*!40000 ALTER TABLE `amun_user_group_right` DISABLE KEYS */;
INSERT INTO `amun_user_group_right` VALUES (1,1,1),(2,1,2),(3,1,3),(4,1,4),(5,1,5),(6,1,6),(7,1,7),(8,1,8),(9,1,9),(10,1,10),(11,1,11),(12,1,12),(13,1,13),(14,1,14),(15,1,15),(16,1,16),(17,1,17),(18,1,18),(19,1,19),(20,1,20),(21,1,21),(22,1,22),(23,1,23),(24,1,24),(25,1,25),(26,1,26),(27,1,27),(28,1,28),(29,1,29),(30,1,30),(31,1,31),(32,1,32),(33,1,33),(34,1,34),(35,1,35),(36,1,36),(37,1,37),(38,1,38),(39,1,39),(40,1,40),(41,1,41),(42,1,42),(43,1,43),(44,1,44),(45,1,45),(46,1,46),(47,1,47),(48,1,48),(49,1,49),(50,1,50),(51,1,51),(52,1,52),(53,1,53),(54,1,54),(55,1,55),(56,1,56),(57,1,57),(58,1,58),(59,1,59),(60,1,60),(61,1,61),(62,1,62),(63,1,63),(64,1,64),(65,1,65),(66,1,66),(67,1,67),(68,1,68),(69,1,69),(70,1,70),(71,1,71),(72,1,72),(73,1,73),(74,1,74),(75,1,75),(76,1,76),(77,1,77),(78,1,78),(79,1,79),(80,1,80),(81,1,81),(82,1,82),(83,1,83),(84,1,84),(85,1,85),(86,1,86),(87,1,87),(88,1,88),(89,1,89),(90,1,90),(91,1,91),(92,1,92),(93,1,93),(94,1,94),(95,1,95),(96,1,96),(97,1,97),(98,1,98),(99,1,99),(100,1,100),(101,1,101),(102,1,102),(103,1,103),(104,1,104),(105,1,105),(106,1,106),(107,1,107),(108,1,108),(109,1,109),(110,1,110),(111,1,111),(112,1,112),(113,1,113),(114,1,114),(115,1,115),(116,1,116),(117,1,117),(118,1,118),(119,1,119),(120,1,120),(121,1,121),(122,1,122),(123,1,123),(124,1,124),(125,1,125),(126,1,126),(127,1,127),(128,1,128),(129,1,129),(130,1,130),(131,1,131),(132,1,132),(133,1,133),(134,1,134),(135,1,135),(136,1,136),(137,1,137),(138,1,138),(139,1,139),(140,1,140),(141,1,141),(142,1,142),(143,1,143),(144,1,144),(145,1,145),(146,1,146),(147,1,147),(148,1,148),(149,1,149),(150,1,150),(151,1,151),(152,1,152),(153,1,153),(154,2,1),(155,2,5),(156,2,7),(157,2,9),(158,2,10),(159,2,17),(160,2,18),(161,2,20),(162,2,21),(163,2,22),(164,2,24),(165,2,77),(166,2,97),(167,2,98),(168,2,100),(169,2,120),(170,2,121),(171,2,122),(172,2,123),(173,2,124),(174,2,125),(175,2,129),(176,2,130),(177,2,133),(178,2,137),(179,2,138),(180,2,142),(181,2,146),(182,2,150),(183,3,129),(184,3,100),(185,3,138),(186,3,77),(187,3,120),(188,3,133),(189,3,125),(190,3,142),(191,3,150),(192,3,124),(193,3,146),(194,3,98),(195,3,97),(196,3,1),(197,1,154),(198,1,155),(199,1,156),(200,1,157);
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
) ENGINE=MyISAM AUTO_INCREMENT=158 DEFAULT CHARSET=ascii;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_user_right`
--

LOCK TABLES `amun_user_right` WRITE;
/*!40000 ALTER TABLE `amun_user_right` DISABLE KEYS */;
INSERT INTO `amun_user_right` VALUES (1,5,'user_view','User view'),(2,5,'user_add','User add'),(3,5,'user_edit','User edit'),(4,5,'user_delete','User delete'),(5,5,'user_account_view','User Account view'),(6,5,'user_account_add','User Account add'),(7,5,'user_account_edit','User Account edit'),(8,5,'user_account_delete','User Account delete'),(9,5,'user_activity_view','User Activity view'),(10,5,'user_activity_add','User Activity add'),(11,5,'user_activity_edit','User Activity edit'),(12,5,'user_activity_delete','User Activity delete'),(13,5,'user_activity_receiver_view','User Activity Receiver view'),(14,5,'user_activity_receiver_add','User Activity Receiver add'),(15,5,'user_activity_receiver_edit','User Activity Receiver edit'),(16,5,'user_activity_receiver_delete','User Activity Receiver delete'),(17,5,'user_friend_view','User Friend view'),(18,5,'user_friend_add','User Friend add'),(19,5,'user_friend_edit','User Friend edit'),(20,5,'user_friend_delete','User Friend delete'),(21,5,'user_friend_group_view','User Friend Group view'),(22,5,'user_friend_group_add','User Friend Group add'),(23,5,'user_friend_group_edit','User Friend Group edit'),(24,5,'user_friend_group_delete','User Friend Group delete'),(25,5,'user_group_view','User Group view'),(26,5,'user_group_add','User Group add'),(27,5,'user_group_edit','User Group edit'),(28,5,'user_group_delete','User Group delete'),(29,5,'user_group_right_view','User Group Right view'),(30,5,'user_group_right_add','User Group Right add'),(31,5,'user_group_right_edit','User Group Right edit'),(32,5,'user_group_right_delete','User Group Right delete'),(33,5,'user_right_view','User Right view'),(34,5,'user_right_add','User Right add'),(35,5,'user_right_edit','User Right edit'),(36,5,'user_right_delete','User Right delete'),(37,6,'core_view','Core view'),(38,6,'core_add','Core add'),(39,6,'core_edit','Core edit'),(40,6,'core_delete','Core delete'),(41,6,'core_service_view','Core Service view'),(42,6,'core_service_add','Core Service add'),(43,6,'core_service_edit','Core Service edit'),(44,6,'core_service_delete','Core Service delete'),(45,6,'core_approval_view','Core Approval view'),(46,6,'core_approval_add','Core Approval add'),(47,6,'core_approval_edit','Core Approval edit'),(48,6,'core_approval_delete','Core Approval delete'),(49,6,'core_approval_record_view','Core Approval Record view'),(50,6,'core_approval_record_add','Core Approval Record add'),(51,6,'core_approval_record_edit','Core Approval Record edit'),(52,6,'core_approval_record_delete','Core Approval Record delete'),(53,6,'core_host_view','Core Host view'),(54,6,'core_host_add','Core Host add'),(55,6,'core_host_edit','Core Host edit'),(56,6,'core_host_delete','Core Host delete'),(57,6,'core_registry_view','Core Registry view'),(58,6,'core_registry_add','Core Registry add'),(59,6,'core_registry_edit','Core Registry edit'),(60,6,'core_registry_delete','Core Registry delete'),(61,8,'oauth_view','Oauth view'),(62,8,'oauth_add','Oauth add'),(63,8,'oauth_edit','Oauth edit'),(64,8,'oauth_delete','Oauth delete'),(65,8,'oauth_access_view','Oauth Access view'),(66,8,'oauth_access_add','Oauth Access add'),(67,8,'oauth_access_edit','Oauth Access edit'),(68,8,'oauth_access_delete','Oauth Access delete'),(69,8,'oauth_request_view','Oauth Request view'),(70,8,'oauth_request_add','Oauth Request add'),(71,8,'oauth_request_edit','Oauth Request edit'),(72,8,'oauth_request_delete','Oauth Request delete'),(73,8,'oauth_access_right_view','Oauth Access Right view'),(74,8,'oauth_access_right_add','Oauth Access Right add'),(75,8,'oauth_access_right_edit','Oauth Access Right edit'),(76,8,'oauth_access_right_delete','Oauth Access Right delete'),(77,9,'media_view','Media View'),(78,9,'media_add','Media Add'),(79,9,'media_edit','Media Edit'),(80,9,'media_delete','Media Delete'),(81,10,'openid_view','OpenId View'),(82,10,'openid_add','OpenId add'),(83,10,'openid_edit','OpenId edit'),(84,10,'openid_delete','OpenId delete'),(85,10,'openid_access_view','OpenId Access view'),(86,10,'openid_access_add','OpenId Access add'),(87,10,'openid_access_edit','OpenId Access edit'),(88,10,'openid_access_delete','OpenId Access delete'),(89,11,'country_view','Country View'),(90,11,'country_add','Country Add'),(91,11,'country_edit','Country Edit'),(92,11,'country_delete','Country Delete'),(93,12,'mail_view','Mail View'),(94,12,'mail_add','Mail Add'),(95,12,'mail_edit','Mail Edit'),(96,12,'mail_delete','Mail Delete'),(97,13,'swagger_view','Swagger View'),(98,14,'sitemap_view','Sitemap View'),(99,15,'phpinfo_view','Phpinfo View'),(100,16,'content_view','Content view'),(101,16,'content_add','Content add'),(102,16,'content_edit','Content edit'),(103,16,'content_delete','Content delete'),(104,16,'content_gadget_view','Content Gadget view'),(105,16,'content_gadget_add','Content Gadget add'),(106,16,'content_gadget_edit','Content Gadget edit'),(107,16,'content_gadget_delete','Content Gadget delete'),(108,16,'content_page_view','Content Page view'),(109,16,'content_page_add','Content Page add'),(110,16,'content_page_edit','Content Page edit'),(111,16,'content_page_delete','Content Page delete'),(112,16,'content_page_gadget_view','Content Page Gadget view'),(113,16,'content_page_gadget_add','Content Page Gadget add'),(114,16,'content_page_gadget_edit','Content Page Gadget edit'),(115,16,'content_page_gadget_delete','Content Page Gadget delete'),(116,16,'content_page_option_view','Content Page Option view'),(117,16,'content_page_option_add','Content Page Option add'),(118,16,'content_page_option_edit','Content Page Option edit'),(119,16,'content_page_option_delete','Content Page Option delete'),(120,17,'my_view','My View'),(121,17,'my_friends_view','My Friends View'),(122,17,'my_activities_view','My Activites View'),(123,17,'my_settings_view','My Settings View'),(124,18,'profile_view','Profile View'),(125,19,'page_view','Page View'),(126,19,'page_add','Page Add'),(127,19,'page_edit','Page Edit'),(128,19,'page_delete','Page Delete'),(129,20,'comment_view','Comment View'),(130,20,'comment_add','Comment Add'),(131,20,'comment_edit','Comment Edit'),(132,20,'comment_delete','Comment Delete'),(133,21,'news_view','News View'),(134,21,'news_add','News Add'),(135,21,'news_edit','News Edit'),(136,21,'news_delete','News Delete'),(137,21,'news_comment_add','News Comment Add'),(138,22,'file_view','File View'),(139,22,'file_add','File Add'),(140,22,'file_edit','File Edit'),(141,22,'file_delete','File Delete'),(142,23,'php_view','Php View'),(143,23,'php_add','Php Add'),(144,23,'php_edit','Php Edit'),(145,23,'php_delete','Php Delete'),(146,24,'redirect_view','Redirect View'),(147,24,'redirect_add','Redirect Add'),(148,24,'redirect_edit','Redirect Edit'),(149,24,'redirect_delete','Redirect Delete'),(150,25,'pipe_view','Pipe View'),(151,25,'pipe_add','Pipe Add'),(152,25,'pipe_edit','Pipe Edit'),(153,25,'pipe_delete','Pipe Delete'),(154,6,'core_service_provider_view','Core Service Provider view'),(155,6,'core_service_provider_add','Core Service Provider add'),(156,6,'core_service_provider_edit','Core Service Provider edit'),(157,6,'core_service_provider_delete','Core Service Provider delete');
/*!40000 ALTER TABLE `amun_user_right` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `amun_vcshook`
--

DROP TABLE IF EXISTS `amun_vcshook`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amun_vcshook` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `globalId` varchar(36) NOT NULL,
  `userId` int(10) NOT NULL,
  `type` enum('github','googleproject') NOT NULL,
  `url` varchar(32) NOT NULL,
  `secret` varchar(40) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `globalId` (`globalId`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_vcshook`
--

LOCK TABLES `amun_vcshook` WRITE;
/*!40000 ALTER TABLE `amun_vcshook` DISABLE KEYS */;
INSERT INTO `amun_vcshook` VALUES (1,'bf103161-5d63-520a-a668-847a9a275f1c',1,'github','https://github.com/k42b3/amun','5e66c57e67e7685ad79af6bcd5e615f058926347','2013-04-27 19:47:24'),(2,'ce8b3335-a543-5b46-a994-40f2f2c2be12',1,'googleproject','https://code.google.com/p/ckedit','fc0d2526e1db32c05f2f01b894865274071f4ae8','2013-04-27 20:02:19');
/*!40000 ALTER TABLE `amun_vcshook` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `amun_vcshook_author`
--

DROP TABLE IF EXISTS `amun_vcshook_author`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amun_vcshook_author` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `userId` int(10) DEFAULT NULL,
  `name` varchar(32) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_vcshook_author`
--

LOCK TABLES `amun_vcshook_author` WRITE;
/*!40000 ALTER TABLE `amun_vcshook_author` DISABLE KEYS */;
INSERT INTO `amun_vcshook_author` VALUES (1,0,'mparent61','2013-04-27 22:53:42'),(2,1,'octokitty','2013-04-27 22:53:56');
/*!40000 ALTER TABLE `amun_vcshook_author` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `amun_vcshook_commit`
--

DROP TABLE IF EXISTS `amun_vcshook_commit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amun_vcshook_commit` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `globalId` varchar(36) DEFAULT NULL,
  `projectId` int(10) DEFAULT NULL,
  `authorId` int(10) DEFAULT NULL,
  `url` varchar(256) DEFAULT NULL,
  `message` varchar(512) DEFAULT NULL,
  `commitDate` datetime DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `globalId` (`globalId`),
  UNIQUE KEY `url` (`url`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_vcshook_commit`
--

LOCK TABLES `amun_vcshook_commit` WRITE;
/*!40000 ALTER TABLE `amun_vcshook_commit` DISABLE KEYS */;
INSERT INTO `amun_vcshook_commit` VALUES (1,'25b39929-c545-5218-abbc-cd2ead7ca46c',2,1,'http://atlas-build-tool.googlecode.com/svn-history/r33/','working on easy_install','2008-12-16 23:38:19','2013-04-27 20:53:42'),(2,'7ae76f7f-365d-5ffd-b775-5d8988a19d25',1,2,'https://github.com/octokitty/testing/commit/c441029cf673f84c8b7db52d0a5944ee5c52ff89','Test','2013-02-22 21:50:07','2013-04-27 20:53:56'),(3,'cb339e52-66ef-580b-9668-8bc06740a251',1,2,'https://github.com/octokitty/testing/commit/36c5f2243ed24de58284a96f2a643bed8c028658','This is me testing the windows client.','2013-02-22 22:07:13','2013-04-27 20:53:56'),(4,'c75dce5e-47ca-5112-abb4-4f694180cbfe',1,2,'https://github.com/octokitty/testing/commit/1481a2de7b2a7d02428ad93446ab166be7793fbb','Rename madame-bovary.txt to words/madame-bovary.txt','2013-03-12 15:14:29','2013-04-27 20:53:56');
/*!40000 ALTER TABLE `amun_vcshook_commit` ENABLE KEYS */;
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
) ENGINE=MyISAM AUTO_INCREMENT=50 DEFAULT CHARSET=ascii;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_xrds`
--

LOCK TABLES `amun_xrds` WRITE;
/*!40000 ALTER TABLE `amun_xrds` DISABLE KEYS */;
INSERT INTO `amun_xrds` VALUES (1,2,0,'/xrds'),(2,3,0,'/hostmeta'),(3,4,0,'/lrdd'),(4,5,0,'/user/account'),(5,5,0,'/user/activity'),(6,5,0,'/user/activity/message'),(7,5,0,'/user/friend'),(8,5,0,'/user/friend/relation'),(9,5,0,'/user/friend/group'),(10,5,0,'/user/group'),(11,5,0,'/user/group/right'),(12,5,0,'/user/right'),(13,6,0,'/core/service'),(14,6,0,'/core/service/navigation'),(15,6,0,'/core/approval'),(16,6,0,'/core/approval/record'),(17,6,0,'/core/host'),(18,6,0,'/core/registry'),(19,7,0,'/asset'),(20,8,0,'/oauth/endpoint/request'),(21,8,0,'/oauth/endpoint/authorization'),(22,8,0,'/oauth/endpoint/access'),(23,8,0,'/oauth'),(24,8,0,'/oauth/access'),(25,8,0,'/oauth/request'),(26,9,0,'/media'),(27,10,0,'/openid'),(28,10,0,'/openid/access'),(29,10,0,'/openid/assoc'),(30,11,0,'/country'),(31,12,0,'/mail'),(32,13,0,'/swagger'),(33,14,0,'/sitemap'),(34,15,0,'/phpinfo'),(35,16,0,'/content/gadget'),(36,16,0,'/content/page'),(37,16,0,'/content/page/gadget'),(38,16,0,'/content/page/option'),(39,17,0,'/my/signon'),(40,17,0,'/my/people'),(41,17,0,'/my/activity'),(42,17,0,'/my/verifyCredentials'),(43,19,0,'/page'),(44,20,0,'/comment'),(45,21,0,'/news'),(46,22,0,'/file'),(47,23,0,'/php'),(48,24,0,'/redirect'),(49,25,0,'/pipe');
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
) ENGINE=MyISAM AUTO_INCREMENT=87 DEFAULT CHARSET=ascii;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amun_xrds_type`
--

LOCK TABLES `amun_xrds_type` WRITE;
/*!40000 ALTER TABLE `amun_xrds_type` DISABLE KEYS */;
INSERT INTO `amun_xrds_type` VALUES (1,1,'http://ns.amun-project.org/2011/amun/service/xrds'),(2,2,'http://ns.amun-project.org/2011/amun/service/hostmeta'),(3,3,'http://ns.amun-project.org/2011/amun/service/lrdd'),(4,4,'http://ns.amun-project.org/2011/amun/service/user/account'),(5,4,'http://ns.amun-project.org/2011/amun/data/1.0'),(6,5,'http://ns.amun-project.org/2011/amun/service/user/activity'),(7,5,'http://ns.amun-project.org/2011/amun/data/1.0'),(8,6,'http://ns.amun-project.org/2012/amun/user/activity/message/1.0'),(9,7,'http://ns.amun-project.org/2011/amun/service/user/friend'),(10,7,'http://ns.amun-project.org/2011/amun/data/1.0'),(11,8,'http://ns.amun-project.org/2011/amun/user/friend/relation/1.0'),(12,9,'http://ns.amun-project.org/2011/amun/service/user/friend/group'),(13,9,'http://ns.amun-project.org/2011/amun/data/1.0'),(14,10,'http://ns.amun-project.org/2011/amun/service/user/group'),(15,10,'http://ns.amun-project.org/2011/amun/data/1.0'),(16,11,'http://ns.amun-project.org/2011/amun/service/user/group/right'),(17,11,'http://ns.amun-project.org/2011/amun/data/1.0'),(18,12,'http://ns.amun-project.org/2011/amun/service/user/right'),(19,12,'http://ns.amun-project.org/2011/amun/data/1.0'),(20,13,'http://ns.amun-project.org/2011/amun/service/core/service'),(21,13,'http://ns.amun-project.org/2011/amun/data/1.0'),(22,14,'http://ns.amun-project.org/2011/amun/service/core/service/navigation'),(23,15,'http://ns.amun-project.org/2011/amun/service/core/approval'),(24,15,'http://ns.amun-project.org/2011/amun/data/1.0'),(25,16,'http://ns.amun-project.org/2011/amun/service/core/approval/record'),(26,16,'http://ns.amun-project.org/2011/amun/data/1.0'),(27,17,'http://ns.amun-project.org/2011/amun/service/core/host'),(28,17,'http://ns.amun-project.org/2011/amun/data/1.0'),(29,18,'http://ns.amun-project.org/2011/amun/service/core/registry'),(30,18,'http://ns.amun-project.org/2011/amun/data/1.0'),(31,19,'http://ns.amun-project.org/2011/amun/service/asset'),(32,20,'http://oauth.net/core/1.0/endpoint/request'),(33,21,'http://oauth.net/core/1.0/endpoint/authorize'),(34,22,'http://oauth.net/core/1.0/endpoint/access'),(35,23,'http://ns.amun-project.org/2011/amun/service/oauth'),(36,23,'http://ns.amun-project.org/2011/amun/data/1.0'),(37,24,'http://ns.amun-project.org/2011/amun/service/oauth/access'),(38,24,'http://ns.amun-project.org/2011/amun/data/1.0'),(39,25,'http://ns.amun-project.org/2011/amun/service/oauth/request'),(40,25,'http://ns.amun-project.org/2011/amun/data/1.0'),(41,26,'http://ns.amun-project.org/2011/amun/service/media'),(42,26,'http://ns.amun-project.org/2011/amun/data/1.0'),(43,27,'http://ns.amun-project.org/2011/amun/service/openid'),(44,27,'http://ns.amun-project.org/2011/amun/data/1.0'),(45,28,'http://ns.amun-project.org/2011/amun/service/openid/access'),(46,28,'http://ns.amun-project.org/2011/amun/data/1.0'),(47,29,'http://ns.amun-project.org/2011/amun/service/openid/assoc'),(48,29,'http://ns.amun-project.org/2011/amun/data/1.0'),(49,30,'http://ns.amun-project.org/2011/amun/service/country'),(50,30,'http://ns.amun-project.org/2011/amun/data/1.0'),(51,31,'http://ns.amun-project.org/2011/amun/service/mail'),(52,31,'http://ns.amun-project.org/2011/amun/data/1.0'),(53,32,'http://ns.amun-project.org/2011/amun/service/swagger'),(54,33,'http://ns.amun-project.org/2011/amun/service/sitemap'),(55,33,'http://www.sitemaps.org/schemas/sitemap/0.9'),(56,34,'http://ns.amun-project.org/2011/amun/service/phpinfo'),(57,35,'http://ns.amun-project.org/2011/amun/service/content/gadget'),(58,35,'http://ns.amun-project.org/2011/amun/data/1.0'),(59,36,'http://ns.amun-project.org/2011/amun/service/content/page'),(60,36,'http://ns.amun-project.org/2011/amun/data/1.0'),(61,37,'http://ns.amun-project.org/2011/amun/service/content/page/gadget'),(62,37,'http://ns.amun-project.org/2011/amun/data/1.0'),(63,38,'http://ns.amun-project.org/2011/amun/service/content/page/option'),(64,38,'http://ns.amun-project.org/2011/amun/data/1.0'),(65,39,'http://specs.openid.net/auth/2.0/signon'),(66,39,'http://openid.net/extensions/sreg/1.1'),(67,39,'http://specs.openid.net/extensions/oauth/1.0'),(68,40,'http://portablecontacts.net/spec/1.0'),(69,40,'http://ns.opensocial.org/2008/opensocial/people'),(70,41,'http://activitystrea.ms/spec/1.0/'),(71,41,'http://ns.opensocial.org/2008/opensocial/activities'),(72,42,'http://ns.amun-project.org/2011/amun/service/my/verifyCredentials'),(73,43,'http://ns.amun-project.org/2011/amun/service/page'),(74,43,'http://ns.amun-project.org/2011/amun/data/1.0'),(75,44,'http://ns.amun-project.org/2011/amun/service/comment'),(76,44,'http://ns.amun-project.org/2011/amun/data/1.0'),(77,45,'http://ns.amun-project.org/2011/amun/service/news'),(78,45,'http://ns.amun-project.org/2011/amun/data/1.0'),(79,46,'http://ns.amun-project.org/2011/amun/service/file'),(80,46,'http://ns.amun-project.org/2011/amun/data/1.0'),(81,47,'http://ns.amun-project.org/2011/amun/service/php'),(82,47,'http://ns.amun-project.org/2011/amun/data/1.0'),(83,48,'http://ns.amun-project.org/2011/amun/service/redirect'),(84,48,'http://ns.amun-project.org/2011/amun/data/1.0'),(85,49,'http://ns.amun-project.org/2011/amun/service/pipe'),(86,49,'http://ns.amun-project.org/2011/amun/data/1.0');
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

-- Dump completed on 2013-04-30 16:48:35
