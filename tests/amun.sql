-- MySQL dump 10.13  Distrib 5.5.8, for Win32 (x86)
--
-- Host: localhost    Database: amun_dev
-- ------------------------------------------------------
-- Server version	5.5.8

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
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
-- Table structure for table `amun_core_service`
--

DROP TABLE IF EXISTS `amun_core_service`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amun_core_service` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
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
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=ascii;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  `refId` int(10) NOT NULL DEFAULT '0',
  `table` varchar(32) DEFAULT NULL,
  `scope` int(10) NOT NULL DEFAULT '0',
  `verb` enum('post','add','cancel','checkin','delete','favorite','follow','give','ignore','invite','join','leave','like','make-friend','play','receive','remove','remove-friend','request-friend','rsvp-maybe','rsvp-no','rsvp-yes','save','share','stop-following','tag','unfavorite','unlike','unsave','update') NOT NULL,
  `summary` text NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `globalId` (`globalId`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=MyISAM AUTO_INCREMENT=197 DEFAULT CHARSET=ascii;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=MyISAM AUTO_INCREMENT=154 DEFAULT CHARSET=ascii;
/*!40101 SET character_set_client = @saved_cs_client */;

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
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2013-04-25  0:23:02
