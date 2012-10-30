<?php
/*
 *  $Id: install.php 880 2012-10-27 13:14:26Z k42b3.x@googlemail.com $
 *
 * amun
 * A social content managment system based on the psx framework. For
 * the current version and informations visit <http://amun.phpsx.org>
 *
 * Copyright (c) 2010-2012 Christoph Kappestein <k42b3.x@gmail.com>
 *
 * This file is part of amun. amun is free software: you can
 * redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or any later version.
 *
 * amun is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with amun. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * install
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @version    $Revision: 880 $
 */
class install extends PSX_Module_ViewAbstract
{
	protected $services = array(
		'org.amun-project.my', 
		'org.amun-project.profile', 
		'org.amun-project.page', 
		'org.amun-project.comment', 
		'org.amun-project.news',
	);

	protected $validate;
	protected $get;
	protected $post;
	protected $session;
	protected $sql;
	protected $registry;
	protected $user;

	public function __construct(PSX_Base $base, $basePath, array $uriFragments)
	{
		parent::__construct($base, $basePath, $uriFragments);

		$this->validate = new PSX_Validate();
		$this->get      = new PSX_Input_Get($this->validate);
		$this->post     = new PSX_Input_Post($this->validate);

		$this->session = new PSX_Session('amun_' . md5($this->config['psx_url']));
		$this->session->start();

		try
		{
			$base = Amun_Base::initInstance($this->config);
			$base->setUser(1);

			$this->sql      = $base->getSql();
			$this->registry = $base->getRegistry();
			$this->user     = $base->getUser();
		}
		catch(Exception $e)
		{
			$this->sql      = new PSX_Sql($this->config['psx_sql_host'],
				$this->config['psx_sql_user'],
				$this->config['psx_sql_pw'],
				$this->config['psx_sql_db']);
			$this->registry = new Amun_Registry_NoDb($this->config, $this->sql);
			$this->user     = new Amun_User_NoDb($this->registry);
		}
	}

	public function onLoad()
	{
		try
		{
			$con         = new PSX_Sql_Condition(array('name', '=', 'core.install_date'));
			$installDate = $this->sql->select($this->registry['table.system_registry'], array('value'), $con, PSX_Sql::SELECT_FIELD);

			if(!empty($installDate))
			{
				throw new Amun_Exception('You already have run the installer, for security reasons the installer stops here.');
			}

			$this->registry->load();
		}
		catch(PSX_Sql_Exception $e)
		{
		}
	}

	public function __index()
	{
		$this->template->assign('administratorName', $this->session->administratorName);
		$this->template->assign('administratorPw', $this->session->administratorPw);
		$this->template->assign('administratorEmail', $this->session->administratorEmail);
		$this->template->assign('settingsTitle', $this->session->settingsTitle);
		$this->template->assign('settingsSubTitle', $this->session->settingsSubTitle);
		$this->template->assign('settingsTimezone', $this->session->settingsTimezone);
		$this->template->assign('settingsSample', $this->session->settingsSample);

		$this->template->set('system/install.tpl');
	}

	public function setupCheckRequirements()
	{
		try
		{
			// php version
			if(floatval(phpversion()) < 5.3)
			{
				throw new Exception('You need PHP greater or equal to 5.3');
			}

			// pdo
			$pdo = new ReflectionExtension('pdo');

			// curl
			$curl = new ReflectionExtension('curl');

			// dom
			$dom = new ReflectionExtension('dom');

			// simplexml
			$simplexml = new ReflectionExtension('simplexml');

			// hash
			$hash = new ReflectionExtension('hash');

			// gd
			$simplexml = new ReflectionExtension('gd');

			// phar
			//$phar = new ReflectionExtension('phar');


			// test whether cache folder is writeable
			$cache = new SplFileInfo($this->config['psx_path_cache']);

			if(!$cache->isWritable())
			{
				throw new Amun_Exception('Cache directory is not writeable');
			}


			echo PSX_Json::encode(array('success' => true));
		}
		catch(Exception $e)
		{
			$debug = '';

			if($this->config['psx_debug'] === true)
			{
				$debug.= "\n" . $e->getTraceAsString();
			}

			echo PSX_Json::encode(array('success' => false, 'msg' => $e->getMessage() . $debug));
		}
	}

	public function setupCreateTables()
	{
		$q = array();

		try
		{
			// tables
			$q[] = <<<SQL
CREATE TABLE IF NOT EXISTS `{$this->registry['table.content_api']}` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `serviceId` int(10) NOT NULL,
  `priority` int(10) NOT NULL DEFAULT '0',
  `endpoint` varchar(256) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `endpoint` (`endpoint`)
) ENGINE=MyISAM DEFAULT CHARSET=ascii;
SQL;

			$q[] = <<<SQL
CREATE TABLE IF NOT EXISTS `{$this->registry['table.content_api_type']}` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `apiId` int(10) NOT NULL,
  `type` varchar(512) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `apiId_type` (`apiId`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=ascii;
SQL;

			$q[] = <<<SQL
CREATE TABLE IF NOT EXISTS `{$this->registry['table.content_gadget']}` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `globalId` varchar(36) NOT NULL,
  `name` varchar(32) NOT NULL,
  `title` varchar(32) NOT NULL,
  `path` varchar(256) NOT NULL,
  `param` text,
  `cache` tinyint(1) NOT NULL,
  `expire` varchar(25) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `globalId` (`globalId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SQL;

			$q[] = <<<SQL
CREATE TABLE IF NOT EXISTS `{$this->registry['table.content_media']}` (
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
SQL;

			$q[] = <<<SQL
CREATE TABLE IF NOT EXISTS `{$this->registry['table.content_page']}` (
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
  `cache` tinyint(1) NOT NULL,
  `expire` varchar(25) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `globalId` (`globalId`),
  UNIQUE KEY `path` (`path`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
SQL;

			$q[] = <<<SQL
CREATE TABLE IF NOT EXISTS `{$this->registry['table.content_page_gadget']}` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `pageId` int(10) NOT NULL,
  `gadgetId` int(10) NOT NULL,
  `sort` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `pageGadgetId` (`pageId`,`gadgetId`)
) ENGINE=MyISAM DEFAULT CHARSET=ascii;
SQL;

			$q[] = <<<SQL
CREATE TABLE IF NOT EXISTS `{$this->registry['table.content_page_option']}` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `optionId` int(10) NOT NULL,
  `rightId` int(10) NOT NULL,
  `srcPageId` int(10) NOT NULL,
  `destPageId` int(10) NOT NULL,
  `name` varchar(32) NOT NULL,
  `href` varchar(512) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=ascii;
SQL;

			$q[] = <<<SQL
CREATE TABLE IF NOT EXISTS `{$this->registry['table.content_page_right']}` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `pageId` int(10) NOT NULL,
  `groupId` int(10) NOT NULL,
  `newGroupId` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pageGroupId` (`pageId`,`groupId`)
) ENGINE=MyISAM DEFAULT CHARSET=ascii;
SQL;

			$q[] = <<<SQL
CREATE TABLE IF NOT EXISTS `{$this->registry['table.content_service']}` (
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
SQL;

			$q[] = <<<SQL
CREATE TABLE IF NOT EXISTS `{$this->registry['table.content_service_option']}` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `serviceId` int(10) NOT NULL,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=ascii;
SQL;

			$q[] = <<<SQL
CREATE TABLE IF NOT EXISTS `{$this->registry['table.system_api']}` (
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
SQL;

			$q[] = <<<SQL
CREATE TABLE IF NOT EXISTS `{$this->registry['table.system_api_access']}` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `apiId` int(10) NOT NULL,
  `userId` int(10) NOT NULL,
  `allowed` tinyint(1) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `apiUserId` (`apiId`,`userId`)
) ENGINE=MyISAM DEFAULT CHARSET=ascii;
SQL;

			$q[] = <<<SQL
CREATE TABLE IF NOT EXISTS `{$this->registry['table.system_api_request']}` (
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
) ENGINE=MyISAM  DEFAULT CHARSET=ascii;
SQL;

			$q[] = <<<SQL
CREATE TABLE IF NOT EXISTS `{$this->registry['table.system_approval']}` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `table` varchar(64) NOT NULL,
  `field` varchar(32) NOT NULL DEFAULT '',
  `value` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SQL;

			$q[] = <<<SQL
CREATE TABLE IF NOT EXISTS `{$this->registry['table.system_approval_record']}` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `userId` int(10) NOT NULL,
  `type` enum('INSERT','UPDATE','DELETE') NOT NULL,
  `table` varchar(64) NOT NULL,
  `record` text NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SQL;

			$q[] = <<<SQL
CREATE TABLE IF NOT EXISTS `{$this->registry['table.system_assoc']}` (
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
SQL;

			$q[] = <<<SQL
CREATE TABLE IF NOT EXISTS `{$this->registry['table.system_connect']}` (
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
SQL;

			$q[] = <<<SQL
CREATE TABLE IF NOT EXISTS `{$this->registry['table.system_connect_access']}` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `userId` int(10) NOT NULL,
  `returnTo` varchar(256) NOT NULL,
  `allowed` tinyint(1) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId_returnTo` (`userId`,`returnTo`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SQL;

			$q[] = <<<SQL
CREATE TABLE IF NOT EXISTS `{$this->registry['table.system_connect_assoc']}` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `assocHandle` varchar(256) NOT NULL,
  `assocType` enum('HMAC-SHA1','HMAC-SHA256') NOT NULL,
  `sessionType` enum('DH-SHA1','DH-SHA256'),
  `secret` varchar(256) NOT NULL,
  `expires` varchar(25) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `assocHandle` (`assocHandle`)
) ENGINE=MyISAM DEFAULT CHARSET=ascii;
SQL;

			$q[] = <<<SQL
CREATE TABLE IF NOT EXISTS `{$this->registry['table.system_country']}` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(64) NOT NULL,
  `code` varchar(2) NOT NULL,
  `longitude` double NOT NULL,
  `latitude` double NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8
SQL;

			$q[] = <<<SQL
CREATE TABLE IF NOT EXISTS `{$this->registry['table.system_host']}` (
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8
SQL;

			$q[] = <<<SQL
CREATE TABLE IF NOT EXISTS `{$this->registry['table.system_host_request']}` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `hostId` int(10) NOT NULL,
  `userId` int(10) NOT NULL,
  `ip` varchar(40) NOT NULL,
  `token` varchar(40) NOT NULL,
  `tokenSecret` varchar(40) NOT NULL,
  `expire` varchar(25) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=ascii
SQL;

			$q[] = <<<SQL
CREATE TABLE IF NOT EXISTS `{$this->registry['table.system_log']}` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `userId` int(10) NOT NULL,
  `refId` int(10) NOT NULL,
  `type` enum('INSERT','UPDATE','DELETE') NOT NULL,
  `table` varchar(64) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=ascii
SQL;

			$q[] = <<<SQL
CREATE TABLE IF NOT EXISTS `{$this->registry['table.system_mail']}` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `from` varchar(64) NOT NULL,
  `subject` varchar(256) NOT NULL,
  `text` text NOT NULL,
  `html` text NOT NULL,
  `values` varchar(512) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `NAME` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8
SQL;

			$q[] = <<<SQL
CREATE TABLE IF NOT EXISTS `{$this->registry['table.system_notify']}` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `priority` int(10) NOT NULL DEFAULT '0',
  `table` varchar(64) NOT NULL,
  `class` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=ascii
SQL;

			$q[] = <<<SQL
CREATE TABLE IF NOT EXISTS `{$this->registry['table.system_registry']}` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `type` enum('STRING','INTEGER','FLOAT','BOOLEAN') NOT NULL,
  `class` varchar(64) DEFAULT NULL,
  `value` varchar(128) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8
SQL;

			$q[] = <<<SQL
CREATE TABLE IF NOT EXISTS `{$this->registry['table.user_account']}` (
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
SQL;

			$q[] = <<<SQL
CREATE TABLE IF NOT EXISTS `{$this->registry['table.user_activity']}` (
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8
SQL;

			$q[] = <<<SQL
CREATE TABLE IF NOT EXISTS `{$this->registry['table.user_activity_receiver']}` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `status` int(10) NOT NULL DEFAULT '1',
  `activityId` int(10) NOT NULL,
  `userId` int(10) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=ascii
SQL;

			$q[] = <<<SQL
CREATE TABLE IF NOT EXISTS `{$this->registry['table.user_activity_template']}` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `type` enum('INSERT','UPDATE','DELETE') NOT NULL,
  `verb` enum('post','add','cancel','checkin','delete','favorite','follow','give','ignore','invite','join','leave','like','make-friend','play','receive','remove','remove-friend','request-friend','rsvp-maybe','rsvp-no','rsvp-yes','save','share','stop-following','tag','unfavorite','unlike','unsave','update') NOT NULL,
  `table` varchar(64) NOT NULL,
  `path` varchar(256) NOT NULL,
  `summary` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `type_table` (`type`,`table`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8
SQL;

			$q[] = <<<SQL
CREATE TABLE IF NOT EXISTS `{$this->registry['table.user_friend']}` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `status` int(10) NOT NULL,
  `groupId` int(10) NOT NULL DEFAULT '0',
  `userId` int(10) NOT NULL,
  `friendId` int(10) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userFriendId` (`userId`,`friendId`)
) ENGINE=MyISAM DEFAULT CHARSET=ascii
SQL;

			$q[] = <<<SQL
CREATE TABLE IF NOT EXISTS `{$this->registry['table.user_friend_group']}` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `userId` int(10) NOT NULL,
  `title` varchar(64) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8
SQL;

			$q[] = <<<SQL
CREATE TABLE IF NOT EXISTS `{$this->registry['table.user_group']}` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(32) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8
SQL;

			$q[] = <<<SQL
CREATE TABLE IF NOT EXISTS `{$this->registry['table.user_group_right']}` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `groupId` int(10) NOT NULL,
  `rightId` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `groupRightId` (`groupId`,`rightId`)
) ENGINE=MyISAM DEFAULT CHARSET=ascii;
SQL;

			$q[] = <<<SQL
CREATE TABLE IF NOT EXISTS `{$this->registry['table.user_right']}` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `description` varchar(128) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=ascii
SQL;

			foreach($q as $query)
			{
				$this->sql->query($query);
			}

			echo PSX_Json::encode(array('success' => true));
		}
		catch(Exception $e)
		{
			$debug = '';

			if($this->config['psx_debug'] === true)
			{
				$debug.= "\n" . $e->getTraceAsString();
			}

			echo PSX_Json::encode(array('success' => false, 'msg' => $e->getMessage() . $debug));
		}
	}

	public function setupInsertData()
	{
		try
		{
			$query = <<<SQL
INSERT INTO `{$this->registry['table.system_country']}` (`title`, `code`, `longitude`, `latitude`) VALUES
('Undisclosed', '', '', ''),
('Afghanistan', 'AF', '33.0000', '65.0000'),
('Albania', 'AL', '41.0000', '20.0000'),
('Algeria', 'DZ', '28.0000', '3.0000'),
('American Samoa', 'AS', '-14.3333', '-170.0000'),
('Andorra', 'AD', '42.5000', '1.5000'),
('Angola', 'AO', '-12.5000', '18.5000'),
('Anguilla', 'AI', '18.2500', '-63.1667'),
('Antarctica', 'AQ', '-90.0000', '0.0000'),
('Antigua and Barbuda', 'AG', '17.0500', '-61.8000'),
('Argentina', 'AR', '-34.0000', '-64.0000'),
('Armenia', 'AM', '40.0000', '45.0000'),
('Aruba', 'AW', '12.5000', '-69.9667'),
('Australia', 'AU', '-27.0000', '133.0000'),
('Austria', 'AT', '47.3333', '13.3333'),
('Azerbaijan', 'AZ', '40.5000', '47.5000'),
('Bahamas', 'BS', '24.2500', '-76.0000'),
('Bahrain', 'BH', '26.0000', '50.5500'),
('Bangladesh', 'BD', '24.0000', '90.0000'),
('Barbados', 'BB', '13.1667', '-59.5333'),
('Belarus', 'BY', '53.0000', '28.0000'),
('Belgium', 'BE', '50.8333', '4.0000'),
('Belize', 'BZ', '17.2500', '-88.7500'),
('Benin', 'BJ', '9.5000', '2.2500'),
('Bermuda', 'BM', '32.3333', '-64.7500'),
('Bhutan', 'BT', '27.5000', '90.5000'),
('Bolivia', 'BO', '-17.0000', '-65.0000'),
('Bosnia and Herzegovina', 'BA', '44.0000', '18.0000'),
('Botswana', 'BW', '-22.0000', '24.0000'),
('Bouvet Island', 'BV', '-54.4333', '3.4000'),
('Brazil', 'BR', '-10.0000', '-55.0000'),
('British Indian Ocean Territory', 'IO', '-6.0000', '71.5000'),
('Brunei Darussalam', 'BN', '4.5000', '114.6667'),
('Bulgaria', 'BG', '43.0000', '25.0000'),
('Burkina Faso', 'BF', '13.0000', '-2.0000'),
('Burundi', 'BI', '-3.5000', '30.0000'),
('Cambodia', 'KH', '13.0000', '105.0000'),
('Cameroon', 'CM', '6.0000', '12.0000'),
('Canada', 'CA', '60.0000', '-95.0000'),
('Cape Verde', 'CV', '16.0000', '-24.0000'),
('Cayman Islands', 'KY', '19.5000', '-80.5000'),
('Central African Republic', 'CF', '7.0000', '21.0000'),
('Chad', 'TD', '15.0000', '19.0000'),
('Chile', 'CL', '-30.0000', '-71.0000'),
('China', 'CN', '35.0000', '105.0000'),
('Christmas Island', 'CX', '-10.5000', '105.6667'),
('Cocos (Keeling) Islands', 'CC', '-12.5000', '96.8333'),
('Colombia', 'CO', '4.0000', '-72.0000'),
('Comoros', 'KM', '-12.1667', '44.2500'),
('Congo', 'CG', '-1.0000', '15.0000'),
('Congo, The Democratic Republic of The', 'CD', '0.0000', '25.0000'),
('Cook Islands', 'CK', '-21.2333', '-159.7667'),
('Costa Rica', 'CR', '10.0000', '-84.0000'),
('Cote Divoire', 'CI', '8.0000', '-5.0000'),
('Croatia', 'HR', '45.1667', '15.5000'),
('Cuba', 'CU', '21.5000', '-80.0000'),
('Cyprus', 'CY', '35.0000', '33.0000'),
('Czech Republic', 'CZ', '49.7500', '15.5000'),
('Denmark', 'DK', '56.0000', '10.0000'),
('Djibouti', 'DJ', '11.5000', '43.0000'),
('Dominica', 'DM', '15.4167', '-61.3333'),
('Dominican Republic', 'DO', '19.0000', '-70.6667'),
('Ecuador', 'EC', '-2.0000', '-77.5000'),
('Egypt', 'EG', '27.0000', '30.0000'),
('El Salvador', 'SV', '13.8333', '-88.9167'),
('Equatorial Guinea', 'GQ', '2.0000', '10.0000'),
('Eritrea', 'ER', '15.0000', '39.0000'),
('Estonia', 'EE', '59.0000', '26.0000'),
('Ethiopia', 'ET', '8.0000', '38.0000'),
('Falkland Islands (Malvinas)', 'FK', '-51.7500', '-59.0000'),
('Faroe Islands', 'FO', '62.0000', '-7.0000'),
('Fiji', 'FJ', '-18.0000', '175.0000'),
('Finland', 'FI', '64.0000', '26.0000'),
('France', 'FR', '46.0000', '2.0000'),
('French Guiana', 'GF', '4.0000', '-53.0000'),
('French Polynesia', 'PF', '-15.0000', '-140.0000'),
('French Southern Territories', 'TF', '-43.0000', '67.0000'),
('Gabon', 'GA', '-1.0000', '11.7500'),
('Gambia', 'GM', '13.4667', '-16.5667'),
('Georgia', 'GE', '42.0000', '43.5000'),
('Germany', 'DE', '51.0000', '9.0000'),
('Ghana', 'GH', '8.0000', '-2.0000'),
('Gibraltar', 'GI', '36.1833', '-5.3667'),
('Greece', 'GR', '39.0000', '22.0000'),
('Greenland', 'GL', '72.0000', '-40.0000'),
('Grenada', 'GD', '12.1167', '-61.6667'),
('Guadeloupe', 'GP', '16.2500', '-61.5833'),
('Guam', 'GU', '13.4667', '144.7833'),
('Guatemala', 'GT', '15.5000', '-90.2500'),
('Guinea', 'GN', '11.0000', '-10.0000'),
('Guinea-bissau', 'GW', '12.0000', '-15.0000'),
('Guyana', 'GY', '5.0000', '-59.0000'),
('Haiti', 'HT', '19.0000', '-72.4167'),
('Heard Island and Mcdonald Islands', 'HM', '-53.1000', '72.5167'),
('Honduras', 'HN', '15.0000', '-86.5000'),
('Hong Kong', 'HK', '22.2500', '114.1667'),
('Hungary', 'HU', '47.0000', '20.0000'),
('Iceland', 'IS', '65.0000', '-18.0000'),
('India', 'IN', '20.0000', '77.0000'),
('Indonesia', 'ID', '-5.0000', '120.0000'),
('Iran', 'IR', '32.0000', '53.0000'),
('Iraq', 'IQ', '33.0000', '44.0000'),
('Ireland', 'IE', '53.0000', '-8.0000'),
('Israel', 'IL', '31.5000', '34.7500'),
('Italy', 'IT', '42.8333', '12.8333'),
('Jamaica', 'JM', '18.2500', '-77.5000'),
('Japan', 'JP', '36.0000', '138.0000'),
('Jordan', 'JO', '31.0000', '36.0000'),
('Kazakhstan', 'KZ', '48.0000', '68.0000'),
('Kenya', 'KE', '1.0000', '38.0000'),
('Kiribati', 'KI', '1.4167', '173.0000'),
('Korea, North', 'KP', '40.0000', '127.0000'),
('Korea, South', 'KR', '37.0000', '127.5000'),
('Kuwait', 'KW', '29.3375', '47.6581'),
('Kyrgyzstan', 'KG', '41.0000', '75.0000'),
('Laos', 'LA', '18.0000', '105.0000'),
('Latvia', 'LV', '57.0000', '25.0000'),
('Lebanon', 'LB', '33.8333', '35.8333'),
('Lesotho', 'LS', '-29.5000', '28.5000'),
('Liberia', 'LR', '6.5000', '-9.5000'),
('Libyan Arab Jamahiriya', 'LY', '25.0000', '17.0000'),
('Liechtenstein', 'LI', '47.1667', '9.5333'),
('Lithuania', 'LT', '56.0000', '24.0000'),
('Luxembourg', 'LU', '49.7500', '6.1667'),
('Macao', 'MO', '22.1667', '113.5500'),
('Macedonia', 'MK', '41.8333', '22.0000'),
('Madagascar', 'MG', '-20.0000', '47.0000'),
('Malawi', 'MW', '-13.5000', '34.0000'),
('Malaysia', 'MY', '2.5000', '112.5000'),
('Maldives', 'MV', '3.2500', '73.0000'),
('Mali', 'ML', '17.0000', '-4.0000'),
('Malta', 'MT', '35.8333', '14.5833'),
('Marshall Islands', 'MH', '9.0000', '168.0000'),
('Martinique', 'MQ', '14.6667', '-61.0000'),
('Mauritania', 'MR', '20.0000', '-12.0000'),
('Mauritius', 'MU', '-20.2833', '57.5500'),
('Mayotte', 'YT', '-12.8333', '45.1667'),
('Mexico', 'MX', '23.0000', '-102.0000'),
('Micronesia, Federated States of', 'FM', '6.9167', '158.2500'),
('Moldova, Republic of', 'MD', '47.0000', '29.0000'),
('Monaco', 'MC', '43.7333', '7.4000'),
('Mongolia', 'MN', '46.0000', '105.0000'),
('Montenegro', 'ME', '42.0000', '19.0000'),
('Montserrat', 'MS', '16.7500', '-62.2000'),
('Morocco', 'MA', '32.0000', '-5.0000'),
('Mozambique', 'MZ', '-18.2500', '35.0000'),
('Myanmar', 'MM', '22.0000', '98.0000'),
('Namibia', 'NA', '-22.0000', '17.0000'),
('Nauru', 'NR', '-0.5333', '166.9167'),
('Nepal', 'NP', '28.0000', '84.0000'),
('Netherlands', 'NL', '52.5000', '5.7500'),
('Netherlands Antilles', 'AN', '12.2500', '-68.7500'),
('New Caledonia', 'NC', '-21.5000', '165.5000'),
('New Zealand', 'NZ', '-41.0000', '174.0000'),
('Nicaragua', 'NI', '13.0000', '-85.0000'),
('Niger', 'NE', '16.0000', '8.0000'),
('Nigeria', 'NG', '10.0000', '8.0000'),
('Niue', 'NU', '-19.0333', '-169.8667'),
('Norfolk Island', 'NF', '-29.0333', '167.9500'),
('Northern Mariana Islands', 'MP', '15.2000', '145.7500'),
('Norway', 'NO', '62.0000', '10.0000'),
('Oman', 'OM', '21.0000', '57.0000'),
('Pakistan', 'PK', '30.0000', '70.0000'),
('Palau', 'PW', '7.5000', '134.5000'),
('Palestinian Territory', 'PS', '32.0000', '35.2500'),
('Panama', 'PA', '9.0000', '-80.0000'),
('Papua New Guinea', 'PG', '-6.0000', '147.0000'),
('Paraguay', 'PY', '-23.0000', '-58.0000'),
('Peru', 'PE', '-10.0000', '-76.0000'),
('Philippines', 'PH', '13.0000', '122.0000'),
('Poland', 'PL', '52.0000', '20.0000'),
('Portugal', 'PT', '39.5000', '-8.0000'),
('Puerto Rico', 'PR', '18.2500', '-66.5000'),
('Qatar', 'QA', '25.5000', '51.2500'),
('Reunion', 'RE', '-21.1000', '55.6000'),
('Romania', 'RO', '46.0000', '25.0000'),
('Russia', 'RU', '60.0000', '100.0000'),
('Rwanda', 'RW', '-2.0000', '30.0000'),
('Saint Helena', 'SH', '-15.9333', '-5.7000'),
('Saint Kitts and Nevis', 'KN', '17.3333', '-62.7500'),
('Saint Lucia', 'LC', '13.8833', '-61.1333'),
('Saint Pierre and Miquelon', 'PM' ,'46.8333', '-56.3333'),
('Saint Vincent and The Grenadines', 'VC', '13.2500', '-61.2000'),
('Samoa', 'WS', '-13.5833', '-172.3333'),
('San Marino', 'SM', '43.7667', '12.4167'),
('Sao Tome and Principe', 'ST', '1.0000', '7.0000'),
('Saudi Arabia', 'SA', '25.0000', '45.0000'),
('Senegal', 'SN', '14.0000', '-14.0000'),
('Serbia and Montenegro', 'RS', '44.0000', '21.0000'),
('Seychelles', 'SC', '-4.5833', '55.6667'),
('Sierra Leone', 'SL', '8.5000', '-11.5000'),
('Singapore', 'SG', '1.3667', '103.8000'),
('Slovakia', 'SK', '48.6667', '19.5000'),
('Slovenia', 'SI', '46.0000', '15.0000'),
('Solomon Islands', 'SB', '-8.0000', '159.0000'),
('Somalia', 'SO', '10.0000', '49.0000'),
('South Africa', 'ZA', '-29.0000', '24.0000'),
('South Georgia and The South Sandwich Islands', 'GS', '-54.5000', '-37.0000'),
('Spain', 'ES', '40.0000', '-4.0000'),
('Sri Lanka', 'LK', '7.0000', '81.0000'),
('Sudan', 'SD', '15.0000', '30.0000'),
('Suriname', 'SR', '4.0000', '-56.0000'),
('Svalbard and Jan Mayen', 'SJ', '78.0000', '20.0000'),
('Swaziland', 'SZ', '-26.5000', '31.5000'),
('Sweden', 'SE', '62.0000', '15.0000'),
('Switzerland', 'CH', '47.0000', '8.0000'),
('Syria', 'SY', '35.0000', '38.0000'),
('Taiwan', 'TW', '23.5000', '121.0000'),
('Tajikistan', 'TJ', '39.0000', '71.0000'),
('Tanzania, United Republic of', 'TZ', '-6.0000', '35.0000'),
('Thailand', 'TH', '15.0000', '100.0000'),
('Togo', 'TG', '8.0000', '1.1667'),
('Tokelau', 'TK', '-9.0000', '-172.0000'),
('Tonga', 'TO', '-20.0000', '-175.0000'),
('Trinidad and Tobago', 'TT', '11.0000', '-61.0000'),
('Tunisia', 'TN', '34.0000', '9.0000'),
('Turkey', 'TR', '39.0000', '35.0000'),
('Turkmenistan', 'TM', '40.0000', '60.0000'),
('Turks and Caicos Islands', 'TC', '21.7500', '-71.5833'),
('Tuvalu', 'TV', '-8.0000', '178.0000'),
('Uganda', 'UG', '1.0000', '32.0000'),
('Ukraine', 'UA', '49.0000', '32.0000'),
('United Arab Emirates', 'AE', '24.0000', '54.0000'),
('United Kingdom', 'GB', '54.0000', '-2.0000'),
('United States', 'US', '38.0000', '-97.0000'),
('United States Minor Outlying Islands', 'UM', '19.2833', '166.6000'),
('Uruguay', 'UY', '-33.0000', '-56.0000'),
('Uzbekistan', 'UZ', '41.0000', '64.0000'),
('Vanuatu', 'VU', '-16.0000', '167.0000'),
('Vatican City', 'VA', '41.9000', '12.4500'),
('Venezuela', 'VE', '8.0000', '-66.0000'),
('Vietnam', 'VN', '16.0000', '106.0000'),
('Virgin Islands, British', 'VG', '18.5000', '-64.5000'),
('Virgin Islands, U.S.', 'VI', '18.3333', '-64.8333'),
('Wallis and Futuna', 'WF', '-13.3000', '-176.2000'),
('Western Sahara', 'EH', '24.5000', '-13.0000'),
('Yemen', 'YE', '15.0000', '48.0000'),
('Zambia', 'ZM', '-15.0000', '30.0000'),
('Zimbabwe', 'ZW', '-20.0000', '30.0000');
SQL;

			$count = $this->sql->count($this->registry['table.system_country']);

			if($count == 0)
			{
				$this->sql->query($query);
			}


			$query = <<<SQL
INSERT INTO `{$this->registry['table.user_right']}` (`name`, `description`) VALUES
('content_gadget_view', 'Content Gadget view'),
('content_gadget_add', 'Content Gadget add'),
('content_gadget_edit', 'Content Gadget edit'),
('content_gadget_delete', 'Content Gadget delete'),
('content_media_view', 'Content Media view'),
('content_media_add', 'Content Media add'),
('content_media_edit', 'Content Media edit'),
('content_media_delete', 'Content Media delete'),
('content_page_view', 'Content Page view'),
('content_page_add', 'Content Page add'),
('content_page_edit', 'Content Page edit'),
('content_page_delete', 'Content Page delete'),
('content_page_gadget_view', 'Content Page Gadget view'),
('content_page_gadget_add', 'Content Page Gadget add'),
('content_page_gadget_edit', 'Content Page Gadget edit'),
('content_page_gadget_delete', 'Content Page Gadget delete'),
('content_page_option_view', 'Content Page Option view'),
('content_page_option_add', 'Content Page Option add'),
('content_page_option_edit', 'Content Page Option edit'),
('content_page_option_delete', 'Content Page Option delete'),
('content_page_right_view', 'Content Page Right view'),
('content_page_right_add', 'Content Page Right add'),
('content_page_right_edit', 'Content Page Right edit'),
('content_page_right_delete', 'Content Page Right delete'),
('content_service_view', 'Content Service view'),
('content_service_add', 'Content Service add'),
('content_service_edit', 'Content Service edit'),
('content_service_delete', 'Content Service delete'),
('system_api_view', 'System API view'),
('system_api_add', 'System API add'),
('system_api_edit', 'System API edit'),
('system_api_delete', 'System API delete'),
('system_api_access_view', 'System API Access view'),
('system_api_access_add', 'System API Access add'),
('system_api_access_edit', 'System API Access edit'),
('system_api_access_delete', 'System API Access delete'),
('system_api_request_view', 'System API Request view'),
('system_api_request_add', 'System API Request add'),
('system_api_request_edit', 'System API Request edit'),
('system_api_request_delete', 'System API Request delete'),
('system_approval_view', 'System Approval view'),
('system_approval_add', 'System Approval add'),
('system_approval_edit', 'System Approval edit'),
('system_approval_delete', 'System Approval delete'),
('system_approval_record_view', 'System Approval Record view'),
('system_approval_record_add', 'System Approval Record add'),
('system_approval_record_edit', 'System Approval Record edit'),
('system_approval_record_delete', 'System Approval Record delete'),
('system_connect_view', 'System Connect view'),
('system_connect_add', 'System Connect add'),
('system_connect_edit', 'System Connect edit'),
('system_connect_delete', 'System Connect delete'),
('system_connect_access_view', 'System Connect Access view'),
('system_connect_access_add', 'System Connect Access add'),
('system_connect_access_edit', 'System Connect Access edit'),
('system_connect_access_delete', 'System Connect Access delete'),
('system_country_view', 'System Country view'),
('system_country_add', 'System Country add'),
('system_country_edit', 'System Country edit'),
('system_country_delete', 'System Country delete'),
('system_host_view', 'System Host view'),
('system_host_add', 'System Host add'),
('system_host_edit', 'System Host edit'),
('system_host_delete', 'System Host delete'),
('system_log_view', 'System Log view'),
('system_log_add', 'System Log add'),
('system_log_edit', 'System Log edit'),
('system_log_delete', 'System Log delete'),
('system_mail_view', 'System Mail view'),
('system_mail_add', 'System Mail add'),
('system_mail_edit', 'System Mail edit'),
('system_mail_delete', 'System Mail delete'),
('system_registry_view', 'System Registry view'),
('system_registry_add', 'System Registry add'),
('system_registry_edit', 'System Registry edit'),
('system_registry_delete', 'System Registry delete'),
('user_account_view', 'User Account view'),
('user_account_add', 'User Account add'),
('user_account_edit', 'User Account edit'),
('user_account_delete', 'User Account delete'),
('user_activity_view', 'User Activity view'),
('user_activity_add', 'User Activity add'),
('user_activity_edit', 'User Activity edit'),
('user_activity_delete', 'User Activity delete'),
('user_activity_receiver_view', 'User Activity Receiver view'),
('user_activity_receiver_add', 'User Activity Receiver add'),
('user_activity_receiver_edit', 'User Activity Receiver edit'),
('user_activity_receiver_delete', 'User Activity Receiver delete'),
('user_friend_view', 'User Friend view'),
('user_friend_add', 'User Friend add'),
('user_friend_edit', 'User Friend edit'),
('user_friend_delete', 'User Friend delete'),
('user_friend_group_view', 'User Friend Group view'),
('user_friend_group_add', 'User Friend Group add'),
('user_friend_group_edit', 'User Friend Group edit'),
('user_friend_group_delete', 'User Friend Group delete'),
('user_group_view', 'User Group view'),
('user_group_add', 'User Group add'),
('user_group_edit', 'User Group edit'),
('user_group_delete', 'User Group delete'),
('user_group_right_view', 'User Group Right view'),
('user_group_right_add', 'User Group Right add'),
('user_group_right_edit', 'User Group Right edit'),
('user_group_right_delete', 'User Group Right delete'),
('user_right_view', 'User Right view'),
('user_right_add', 'User Right add'),
('user_right_edit', 'User Right edit'),
('user_right_delete', 'User Right delete')
SQL;

			$count = $this->sql->count($this->registry['table.user_right']);

			if($count == 0)
			{
				$this->sql->query($query);
			}


			$query = <<<SQL
INSERT INTO `{$this->registry['table.content_api']}` (`id`, `endpoint`) VALUES
(1, 'auth/request'),
(2, 'auth/authorization'),
(3, 'auth/access'),
(4, 'content/gadget'),
(5, 'content/media'),
(6, 'content/page'),
(7, 'content/page/gadget'),
(8, 'content/page/option'),
(9, 'content/page/right'),
(10, 'content/service'),
(11, 'system/api'),
(12, 'system/api/access'),
(13, 'system/api/request'),
(14, 'system/approval'),
(15, 'system/approval/record'),
(16, 'system/connect'),
(17, 'system/connect/access'),
(18, 'system/connect/assoc'),
(19, 'system/country'),
(20, 'system/host'),
(21, 'system/log'),
(22, 'system/mail'),
(23, 'system/registry'),
(24, 'user/account'),
(25, 'user/activity'),
(26, 'user/activity/message'),
(27, 'user/friend'),
(28, 'user/friend/relation'),
(29, 'user/friend/group'),
(30, 'user/group'),
(31, 'user/group/right'),
(32, 'user/right')
SQL;

			$count = $this->sql->count($this->registry['table.content_api']);

			if($count == 0)
			{
				$this->sql->query($query);
			}


			$query = <<<SQL
INSERT INTO `{$this->registry['table.content_api_type']}` (`apiId`, `type`) VALUES
(1, 'http://oauth.net/core/1.0/endpoint/request'),
(1, 'http://ns.amun-project.org/2011/amun/auth/request'),
(2, 'http://oauth.net/core/1.0/endpoint/authorize'),
(2, 'http://ns.amun-project.org/2011/amun/auth/authorization'),
(3, 'http://oauth.net/core/1.0/endpoint/access'),
(3, 'http://ns.amun-project.org/2011/amun/auth/access'),
(4, 'http://ns.amun-project.org/2011/amun/content/gadget'),
(4, 'http://ns.amun-project.org/2011/amun/data/1.0'),
(5, 'http://ns.amun-project.org/2011/amun/content/media'),
(5, 'http://ns.amun-project.org/2011/amun/data/1.0'),
(6, 'http://ns.amun-project.org/2011/amun/content/page'),
(6, 'http://ns.amun-project.org/2011/amun/data/1.0'),
(7, 'http://ns.amun-project.org/2011/amun/content/page/gadget'),
(7, 'http://ns.amun-project.org/2011/amun/data/1.0'),
(8, 'http://ns.amun-project.org/2011/amun/content/page/option'),
(8, 'http://ns.amun-project.org/2011/amun/data/1.0'),
(9, 'http://ns.amun-project.org/2011/amun/content/page/right'),
(9, 'http://ns.amun-project.org/2011/amun/data/1.0'),
(10, 'http://ns.amun-project.org/2011/amun/content/service'),
(10, 'http://ns.amun-project.org/2011/amun/data/1.0'),
(11, 'http://ns.amun-project.org/2011/amun/system/api'),
(11, 'http://ns.amun-project.org/2011/amun/data/1.0'),
(12, 'http://ns.amun-project.org/2011/amun/system/api/access'),
(12, 'http://ns.amun-project.org/2011/amun/data/1.0'),
(13, 'http://ns.amun-project.org/2011/amun/system/api/request'),
(13, 'http://ns.amun-project.org/2011/amun/data/1.0'),
(14, 'http://ns.amun-project.org/2011/amun/system/approval'),
(14, 'http://ns.amun-project.org/2011/amun/data/1.0'),
(15, 'http://ns.amun-project.org/2011/amun/system/approval/record'),
(15, 'http://ns.amun-project.org/2011/amun/data/1.0'),
(16, 'http://ns.amun-project.org/2011/amun/system/connect'),
(16, 'http://ns.amun-project.org/2011/amun/data/1.0'),
(17, 'http://ns.amun-project.org/2011/amun/system/connect/access'),
(17, 'http://ns.amun-project.org/2011/amun/data/1.0'),
(18, 'http://ns.amun-project.org/2011/amun/system/connect/assoc'),
(18, 'http://ns.amun-project.org/2011/amun/data/1.0'),
(19, 'http://ns.amun-project.org/2011/amun/system/country'),
(19, 'http://ns.amun-project.org/2011/amun/data/1.0'),
(20, 'http://ns.amun-project.org/2011/amun/system/host'),
(20, 'http://ns.amun-project.org/2011/amun/data/1.0'),
(21, 'http://ns.amun-project.org/2011/amun/system/log'),
(21, 'http://ns.amun-project.org/2011/amun/data/1.0'),
(22, 'http://ns.amun-project.org/2011/amun/system/mail'),
(22, 'http://ns.amun-project.org/2011/amun/data/1.0'),
(23, 'http://ns.amun-project.org/2011/amun/system/registry'),
(23, 'http://ns.amun-project.org/2011/amun/data/1.0'),
(24, 'http://ns.amun-project.org/2011/amun/user/account'),
(24, 'http://ns.amun-project.org/2011/amun/data/1.0'),
(25, 'http://ns.amun-project.org/2011/amun/user/activity'),
(25, 'http://ns.amun-project.org/2011/amun/data/1.0'),
(26, 'http://ns.amun-project.org/2012/amun/user/activity/message/1.0'),
(27, 'http://ns.amun-project.org/2011/amun/user/friend'),
(27, 'http://ns.amun-project.org/2011/amun/data/1.0'),
(28, 'http://ns.amun-project.org/2011/amun/user/friend/relation/1.0'),
(29, 'http://ns.amun-project.org/2011/amun/user/friend/group'),
(29, 'http://ns.amun-project.org/2011/amun/data/1.0'),
(30, 'http://ns.amun-project.org/2011/amun/user/group'),
(30, 'http://ns.amun-project.org/2011/amun/data/1.0'),
(31, 'http://ns.amun-project.org/2011/amun/user/group/right'),
(31, 'http://ns.amun-project.org/2011/amun/data/1.0'),
(32, 'http://ns.amun-project.org/2011/amun/user/right'),
(32, 'http://ns.amun-project.org/2011/amun/data/1.0')
SQL;

			$count = $this->sql->count($this->registry['table.content_api_type']);

			if($count == 0)
			{
				$this->sql->query($query);
			}


			$query = <<<SQL
INSERT INTO `{$this->registry['table.user_activity_template']}` (`type`, `verb`, `table`, `path`, `summary`) VALUES
('INSERT', 'add', 'amun_user_account', '', '<p><a href="{record.profileUrl}">{record.name}</a> has created an account</p>')
SQL;

			$count = $this->sql->count($this->registry['table.user_activity_template']);

			if($count == 0)
			{
				$this->sql->query($query);
			}


			$query = <<<SQL
INSERT INTO `{$this->registry['table.system_mail']}` (`name`, `from`, `subject`, `text`, `html`, `values`) VALUES
('INSTALL_SUCCESS', 'system@{$this->base->getHost()}', '{$this->base->getHost()} installation of Amun', 'Hello {account.name},

you have successful installed Amun at {host.name}. In order to administer
the website you can use the following consumer key and secret to access the system API.
To get support and help please visit http://amun.phpsx.org.

Consumer key: {consumer.key}
Consumer secret: {consumer.secret}

Thank you for installing Amun!', '<html>
<body>
<p>Hello {account.name},</p>

<p>you have successful installed Amun at {host.name}. In order to administer
the website you can use the following consumer key and secret to access the system API.
To get support and help please visit <a href="http://amun.phpsx.org">amun.phpsx.org</a>.</p>

<p><pre>Consumer key: {consumer.key}
Consumer secret: {consumer.secret}</pre></p>

<p>Thank you for installing Amun!</p>
</body>
</html>', 'host.name;account.name;consumer.key;consumer.secret')
SQL;

			$count = $this->sql->count($this->registry['table.system_mail']);

			if($count == 0)
			{
				$this->sql->query($query);
			}


			echo PSX_Json::encode(array('success' => true));
		}
		catch(Exception $e)
		{
			$debug = '';

			if($this->config['psx_debug'] === true)
			{
				$debug.= "\n" . $e->getTraceAsString();
			}

			echo PSX_Json::encode(array('success' => false, 'msg' => $e->getMessage() . $debug));
		}
	}

	public function setupInsertRegistry()
	{
		try
		{
			$title    = $this->post->title('string', array(new PSX_Filter_Length(3, 64), new PSX_Filter_Html()), 'title', 'Title');
			$subTitle = $this->post->subTitle('string', array(new PSX_Filter_Length(0, 128), new PSX_Filter_Html()), 'subTitle', 'Sub Title');

			$count = $this->sql->count($this->registry['table.system_registry']);

			if($count == 0)
			{
				if(!$this->validate->hasError())
				{
					// insert core settings
					$registry = array(

						array('core.title',              'STRING',  null,           $title),
						array('core.sub_title',          'STRING',  null,           $subTitle),
						array('core.anonymous_user',     'INTEGER', null,           ''),
						array('core.session_expire',     'INTEGER', null,           1800),
						array('core.default_user_group', 'INTEGER', null,           ''),
						array('core.default_page',       'STRING',  null,           ''),
						array('core.default_timezone',   'STRING',  'DateTimeZone', 'UTC'),
						array('core.format_datetime',    'STRING',  null,           'd M. Y, H:i'),
						array('core.format_date',        'STRING',  null,           'd. F Y'),
						array('core.install_date',       'STRING',  null,           ''),
						array('core.media_path',         'STRING',  null,           '../cache'),
						array('core.media_upload_size',  'INTEGER', null,           1024 * 1024 * 5),
						array('security.input_limit',    'INTEGER', null,           16),
						array('security.input_interval', 'STRING',  null,           'PT30M'),
						array('security.pw_alpha',       'INTEGER', null,           4),
						array('security.pw_numeric',     'INTEGER', null,           2),
						array('security.pw_special',     'INTEGER', null,           0),

					);

					$keys = array('name', 'type', 'class', 'value');

					foreach($registry as $values)
					{
						$this->sql->insert($this->registry['table.system_registry'], array_combine($keys, $values));
					}


					// insert tables
					$query = <<<SQL
INSERT INTO `{$this->registry['table.system_registry']}` (`name`, `type`, `class`, `value`) VALUES
('table.amun_content_api', 'STRING', NULL, '{$this->registry['table.content_api']}'),
('table.amun_content_api_type', 'STRING', NULL, '{$this->registry['table.content_api_type']}'),
('table.amun_content_gadget', 'STRING', NULL, '{$this->registry['table.content_gadget']}'),
('table.amun_content_media', 'STRING', NULL, '{$this->registry['table.content_media']}'),
('table.amun_content_page', 'STRING', NULL, '{$this->registry['table.content_page']}'),
('table.amun_content_page_gadget', 'STRING', NULL, '{$this->registry['table.content_page_gadget']}'),
('table.amun_content_page_option', 'STRING', NULL, '{$this->registry['table.content_page_option']}'),
('table.amun_content_page_right', 'STRING', NULL, '{$this->registry['table.content_page_right']}'),
('table.amun_content_service', 'STRING', NULL, '{$this->registry['table.content_service']}'),
('table.amun_content_service_option', 'STRING', NULL, '{$this->registry['table.content_service_option']}'),
('table.amun_system_api', 'STRING', NULL, '{$this->registry['table.system_api']}'),
('table.amun_system_api_access', 'STRING', NULL, '{$this->registry['table.system_api_access']}'),
('table.amun_system_api_request', 'STRING', NULL, '{$this->registry['table.system_api_request']}'),
('table.amun_system_approval', 'STRING', NULL, '{$this->registry['table.system_approval']}'),
('table.amun_system_approval_record', 'STRING', NULL, '{$this->registry['table.system_approval_record']}'),
('table.amun_system_assoc', 'STRING', NULL, '{$this->registry['table.system_assoc']}'),
('table.amun_system_connect', 'STRING', NULL, '{$this->registry['table.system_connect']}'),
('table.amun_system_connect_access', 'STRING', NULL, '{$this->registry['table.system_connect_access']}'),
('table.amun_system_connect_assoc', 'STRING', NULL, '{$this->registry['table.system_connect_assoc']}'),
('table.amun_system_country', 'STRING', NULL, '{$this->registry['table.system_country']}'),
('table.amun_system_host', 'STRING', NULL, '{$this->registry['table.system_host']}'),
('table.amun_system_host_request', 'STRING', NULL, '{$this->registry['table.system_host_request']}'),
('table.amun_system_log', 'STRING', NULL, '{$this->registry['table.system_log']}'),
('table.amun_system_mail', 'STRING', NULL, '{$this->registry['table.system_mail']}'),
('table.amun_system_notify', 'STRING', NULL, '{$this->registry['table.system_notify']}'),
('table.amun_system_registry', 'STRING', NULL, '{$this->registry['table.system_registry']}'),
('table.amun_user_account', 'STRING', NULL, '{$this->registry['table.user_account']}'),
('table.amun_user_activity', 'STRING', NULL, '{$this->registry['table.user_activity']}'),
('table.amun_user_activity_receiver', 'STRING', NULL, '{$this->registry['table.user_activity_receiver']}'),
('table.amun_user_activity_template', 'STRING', NULL, '{$this->registry['table.user_activity_template']}'),
('table.amun_user_friend', 'STRING', NULL, '{$this->registry['table.user_friend']}'),
('table.amun_user_friend_group', 'STRING', NULL, '{$this->registry['table.user_friend_group']}'),
('table.amun_user_group', 'STRING', NULL, '{$this->registry['table.user_group']}'),
('table.amun_user_group_right', 'STRING', NULL, '{$this->registry['table.user_group_right']}'),
('table.amun_user_right', 'STRING', NULL, '{$this->registry['table.user_right']}')
SQL;

					$this->sql->query($query);


					$this->session->set('settingsTitle', $title);
					$this->session->set('settingsSubTitle', $subTitle);
				}
				else
				{
					throw new Amun_Exception($this->validate->getLastError());
				}
			}

			echo PSX_Json::encode(array('success' => true));
		}
		catch(Exception $e)
		{
			$debug = '';

			if($this->config['psx_debug'] === true)
			{
				$debug.= "\n" . $e->getTraceAsString();
			}

			echo PSX_Json::encode(array('success' => false, 'msg' => $e->getMessage() . $debug));
		}
	}

	public function setupInsertGroup()
	{
		try
		{
			$count = $this->sql->count($this->registry['table.user_group']);

			if($count == 0)
			{
				$date = new DateTime('NOW');

				$this->sql->insert($this->registry['table.user_group'], array(

					'title' => 'Administrator',
					'date'  => $date->format(PSX_DateTime::SQL),

				));
			}

			echo PSX_Json::encode(array('success' => true));
		}
		catch(Exception $e)
		{
			$debug = '';

			if($this->config['psx_debug'] === true)
			{
				$debug.= "\n" . $e->getTraceAsString();
			}

			echo PSX_Json::encode(array('success' => false, 'msg' => $e->getMessage() . $debug));
		}
	}

	public function setupInsertAdmin()
	{
		try
		{
			$name  = $this->post->name('string');
			$pw    = $this->post->pw('string');
			$email = $this->post->email('string');

			$count = $this->sql->count($this->registry['table.user_account']);

			if($count == 0)
			{
				$handler = new Amun_User_Account_Handler($this->user);

				$account = Amun_Sql_Table_Registry::get('User_Account')->getRecord();
				$account->setGroupId(1);
				$account->setStatus(Amun_User_Account::ADMINISTRATOR);
				$account->setIdentity($email);
				$account->setName($name);
				$account->setPw($pw);
				$account->setEmail($email);
				$account->setTimezone('UTC');

				$handler->create($account);


				$this->session->set('administratorName', $name);
				$this->session->set('administratorPw', $pw);
				$this->session->set('administratorEmail', $email);
			}

			echo PSX_Json::encode(array('success' => true));
		}
		catch(Exception $e)
		{
			$debug = '';

			if($this->config['psx_debug'] === true)
			{
				$debug.= "\n" . $e->getTraceAsString();
			}

			echo PSX_Json::encode(array('success' => false, 'msg' => $e->getMessage() . $debug));
		}
	}

	public function setupInsertApi()
	{
		try
		{
			$count = $this->sql->count($this->registry['table.system_api']);

			if($count == 0)
			{
				$email = $this->session->get('administratorEmail');


				// insert api
				$handler = new Amun_System_Api_Handler($this->user);

				$api = Amun_Sql_Table_Registry::get('System_Api')->getRecord();
				$api->setStatus(Amun_System_Api::NORMAL);
				$api->setName('System');
				$api->setEmail($email);
				$api->setUrl($this->config['psx_url']);
				$api->setTitle('System');
				$api->setDescription('Default system API to access amun');

				$handler->create($api);


				// save consumer key and secret to file
				$row = $this->sql->getRow('SELECT consumerKey, consumerSecret FROM ' . $this->registry['table.system_api'] . ' LIMIT 1');

				if(!empty($row))
				{
					$values = array(

						'host.name'       => $this->base->getHost(),
						'account.name'    => $this->session->administratorName,
						'consumer.key'    => $row['consumerKey'],
						'consumer.secret' => $row['consumerSecret'],

					);

					try
					{
						$mail = new Amun_Mail($this->registry);
						$mail->send('INSTALL_SUCCESS', $email, $values);
					}
					catch(Zend_Mail_Transport_Exception $e)
					{
						// ignore send mail errors
					}
				}
			}


			echo PSX_Json::encode(array('success' => true));
		}
		catch(Exception $e)
		{
			$debug = '';

			if($this->config['psx_debug'] === true)
			{
				$debug.= "\n" . $e->getTraceAsString();
			}

			echo PSX_Json::encode(array('success' => false, 'msg' => $e->getMessage() . $debug));
		}
	}

	public function setupInstallService()
	{
		try
		{
			// install services
			$count = $this->sql->count($this->registry['table.content_service']);

			if($count == 0)
			{
				$handler = new Amun_Content_Service_Handler($this->user);
				$errors  = array();

				foreach($this->services as $source)
				{
					try
					{
						$service = Amun_Sql_Table_Registry::get('Content_Service')->getRecord();
						$service->setSource($source);

						$handler->create($service);
					}
					catch(Exception $e)
					{
						$debug = '';

						if($this->config['psx_debug'] === true)
						{
							$debug.= "\n" . $e->getTraceAsString();
						}

						$errors[] = '[' . $source . ']: ' . $e->getMessage() . $debug;
					}
				}


				// check errors
				if(count($errors) > 0)
				{
					throw new Amun_Exception('The following errors occured while installing the services: ' . "\n" . implode("\n", $errors) . "\n" . '--');
				}


				// set rights
				$rights = $this->sql->getCol('SELECT id FROM ' . $this->registry['table.user_right']);

				foreach($rights as $rightId)
				{
					$this->sql->insert($this->registry['table.user_group_right'], array(

						'groupId' => 1,
						'rightId' => $rightId,

					));
				}
			}

			echo PSX_Json::encode(array('success' => true));
		}
		catch(Exception $e)
		{
			$debug = '';

			if($this->config['psx_debug'] === true)
			{
				$debug.= "\n" . $e->getTraceAsString();
			}

			echo PSX_Json::encode(array('success' => false, 'msg' => $e->getMessage() . $debug));
		}
	}

	public function setupInstallSample()
	{
		try
		{
			// insert notifier
			$count = $this->sql->count($this->registry['table.system_notify']);

			if($count == 0)
			{
				$handler = new Amun_System_Notify_Handler($this->user);

				// log notifier
				$notify = Amun_Sql_Table_Registry::get('System_Notify')->getRecord();
				$notify->setPriority(32);
				$notify->setTable('.*');
				$notify->setClass('Amun_Notify_Log');

				$handler->create($notify);


				// pshb notifier
				$notify = Amun_Sql_Table_Registry::get('System_Notify')->getRecord();
				$notify->setTable('amun_service_');
				$notify->setClass('Amun_Notify_Hub');

				$handler->create($notify);


				// activity notifier
				$notify = Amun_Sql_Table_Registry::get('System_Notify')->getRecord();
				$notify->setTable('amun_service_|amun_user_account');
				$notify->setClass('Amun_Notify_Activity');

				$handler->create($notify);
			}


			// insert groups
			$count = $this->sql->count($this->registry['table.user_group']);

			if($count == 1)
			{
				$handler = new Amun_User_Group_Handler($this->user);


				// normal group
				$group = Amun_Sql_Table_Registry::get('User_Group')->getRecord();
				$group->setTitle('Normal');

				$handler->create($group);

				$allowedRights = array(
					'user_account_view',
					'user_account_edit',
					'user_activity_view',
					'user_activity_add',
					'user_friend_add',
					'user_friend_delete',
					'user_friend_group_add',
					'user_friend_group_delete',
				);
				$rights = $this->sql->getCol('SELECT id FROM ' . $this->registry['table.user_right'] . ' WHERE name LIKE "service_%_view" OR name IN("' . implode('","', $allowedRights) . '")');

				foreach($rights as $rightId)
				{
					$this->sql->insert($this->registry['table.user_group_right'], array(

						'groupId' => 2,
						'rightId' => $rightId,

					));
				}


				// anonymous group
				$group = Amun_Sql_Table_Registry::get('User_Group')->getRecord();
				$group->setTitle('Anonymous');

				$handler->create($group);

				$rights = $this->sql->getCol('SELECT id FROM ' . $this->registry['table.user_right'] . ' WHERE name LIKE "service_%_view"');

				foreach($rights as $rightId)
				{
					$this->sql->insert($this->registry['table.user_group_right'], array(

						'groupId' => 3,
						'rightId' => $rightId,

					));
				}
			}


			// insert user
			$count = $this->sql->count($this->registry['table.user_account']);

			if($count == 1)
			{
				$handler = new Amun_User_Account_Handler($this->user);

				// anonymous
				$record = Amun_Sql_Table_Registry::get('User_Account')->getRecord();
				$record->setGroupId(3);
				$record->setStatus(Amun_User_Account::ANONYMOUS);
				$record->setIdentity('anonymous@anonymous.com');
				$record->setName('Anonymous');
				$record->setPw(Amun_Security::generatePw());
				$record->setTimezone('UTC');

				$handler->create($record);
			}


			// insert pages
			$count = $this->sql->count($this->registry['table.content_page']);

			if($count == 0)
			{
				$handler = new Amun_Content_Page_Handler($this->user);

				// root
				$record = Amun_Sql_Table_Registry::get('Content_Page')->getRecord();
				$record->setParentId(0);
				$record->setServiceId(3);
				$record->setStatus(Amun_Content_Page::HIDDEN);
				$record->setTitle($_SESSION['settingsTitle']);
				$record->setTemplate(null);

				$handler->create($record);


				// home
				$record = Amun_Sql_Table_Registry::get('Content_Page')->getRecord();
				$record->setParentId(1);
				$record->setServiceId(3);
				$record->setStatus(Amun_Content_Page::NORMAL);
				$record->setTitle('Home');
				$record->setTemplate(null);

				$handler->create($record);


				// my
				$record = Amun_Sql_Table_Registry::get('Content_Page')->getRecord();
				$record->setParentId(1);
				$record->setServiceId(1);
				$record->setStatus(Amun_Content_Page::HIDDEN);
				$record->setTitle('My');
				$record->setTemplate(null);

				$handler->create($record);


				// profile
				$record = Amun_Sql_Table_Registry::get('Content_Page')->getRecord();
				$record->setParentId(1);
				$record->setServiceId(2);
				$record->setStatus(Amun_Content_Page::HIDDEN);
				$record->setTitle('Profile');
				$record->setTemplate(null);

				$handler->create($record);


				// help
				$record = Amun_Sql_Table_Registry::get('Content_Page')->getRecord();
				$record->setParentId(1);
				$record->setServiceId(3);
				$record->setStatus(Amun_Content_Page::HIDDEN);
				$record->setTitle('Help');
				$record->setTemplate(null);

				$handler->create($record);
			}


			// insert page content
			$count = $this->sql->count($this->registry['table.service_page']);

			if($count == 0)
			{
				$handler = new Amun_Service_Page_Handler($this->user);

				// home
				$content = <<<TEXT
<h1>It works!</h1>
<p>This is the default web page for this server.</p>
<p>The web server software is running but no content has been added, yet.</p>
TEXT;

				$record = Amun_Sql_Table_Registry::get('Service_Page')->getRecord();
				$record->setPageId(2);
				$record->setContent($content);

				$handler->create($record);


				// help
				$content = <<<TEXT
<h3>Formatting content</h3>
<p>Amun uses a subset of the <a href="http://wikipedia.org/wiki/Markdown">markdown</a> syntax to provide an easy way to format content. Please use the following formatting rules in your content so that readers can enjoy reading your content. Note the markdown syntax is only to simplify creating content without writing html if you prefer you can also write plain html. If the content contains any html block level element the content will not treated as markdown to prevent double encoding. The following examples shows how you can use the syntax:</p>

<h4>Paragraphs</h4>
<p>Here an example how text will be converted into paragraphs. Note if you have two trailing spaces at a line a <span class="kwd">&lt;br /&gt;</span> tag will be inserted.</p>
<pre class="prettyprint">
Lorem ipsum dolor sit amet, consetetur sadipscing elitr

sed diam nonumy eirmod tempor invidunt ut labore et.
At vero eos et accusam et justo duo dolores

invidunt ut labore et dolore magna aliquyam<span style="background-color:#ccc">  </span>
sed diam voluptua
</pre>
<hr />
<pre class="prettyprint">
<span class="kwd">&lt;p&gt;</span>Lorem ipsum dolor sit amet, consetetur sadipscing elitr <span class="kwd">&lt;/p&gt;</span>
<span class="kwd">&lt;p&gt;</span>sed diam nonumy eirmod tempor invidunt ut labore et. At vero eos et accusam et justo duo dolores <span class="kwd">&lt;/p&gt;</span>
<span class="kwd">&lt;p&gt;</span>invidunt ut labore et dolore magna aliquyam<span class="kwd">&lt;br /&gt;</span>sed diam voluptua <span class="kwd">&lt;/p&gt;</span>
</pre>

<h4>Code</h4>
<p>Indent four spaces or one tab to create an escaped <span class="kwd">&lt;pre&gt;</span> block. The text will be wrapped in tags, and displayed in a monospaced font. The first four spaces will be stripped off, but all other whitespace will be preserved. Markdown and HTML is ignored within a code block</p>
<pre class="prettyprint">
<span style="background-color:#ccc">    </span>public static void main(String args[])
<span style="background-color:#ccc">    </span>{
<span style="background-color:#ccc">    </span>    System.out.println("Hello World !!!");
<span style="background-color:#ccc">    </span>}
</pre>
<hr />
<pre class="prettyprint">
<span class="kwd">&lt;pre class="prettyprint"&gt;</span>
public static void main(String args[])
{
    System.out.println("Hello World !!!");
}
<span class="kwd">&lt;/pre&gt;</span>
</pre>

<h4>Quotes</h4>
<p>Add a &gt; to the beginning of any line to create a <span class="kwd">&lt;blockquote&gt;</span>.</p>
<pre class="prettyprint">
> Lorem ipsum dolor sit amet
> consetetur sadipscing elitr
> sed diam nonumy eirmod

tempor invidunt ut labore
</pre>
<hr />
<pre class="prettyprint">
<span class="kwd">&lt;blockquote&gt;</span>
    <span class="kwd">&lt;p&gt;</span>Lorem ipsum dolor sit amet consetetur sadipscing elitr sed diam nonumy eirmod <span class="kwd">&lt;/p&gt;</span>
<span class="kwd">&lt;/blockquote&gt;</span>
<span class="kwd">&lt;p&gt;</span>tempor invidunt ut labore <span class="kwd">&lt;/p&gt;</span>
</pre>

<h4>Lists</h4>
<p>A bulleted <span class="kwd">&lt;ul&gt;</span> list:</p>
<pre class="prettyprint">
* Lorem ipsum dolor sit amet
* consetetur sadipscing elitr
* sed diam nonumy eirmod
* tempor invidunt ut labore
</pre>
<hr />
<pre class="prettyprint">
<span class="kwd">&lt;ul&gt;</span>
    <span class="kwd">&lt;li&gt;</span>Lorem ipsum dolor sit amet<span class="kwd">&lt;/li&gt;</span>
    <span class="kwd">&lt;li&gt;</span>consetetur sadipscing elitr<span class="kwd">&lt;/li&gt;</span>
    <span class="kwd">&lt;li&gt;</span>sed diam nonumy eirmod<span class="kwd">&lt;/li&gt;</span>
    <span class="kwd">&lt;li&gt;</span>tempor invidunt ut labore<span class="kwd">&lt;/li&gt;</span>
<span class="kwd">&lt;/ul&gt;</span>
</pre>
TEXT;

				$record = Amun_Sql_Table_Registry::get('Service_Page')->getRecord();
				$record->setPageId(5);
				$record->setContent($content);

				$handler->create($record);
			}


			// set default_page
			$con = new PSX_Sql_Condition(array('name', '=', 'core.default_page'));

			$this->sql->update($this->registry['table.system_registry'], array(

				'value' => 'home',

			), $con);


			// set default user group
			$con = new PSX_Sql_Condition(array('name', '=', 'core.default_user_group'));

			$this->sql->update($this->registry['table.system_registry'], array(

				'value' => 2,

			), $con);


			// set anonymous_user
			$con = new PSX_Sql_Condition(array('name', '=', 'core.anonymous_user'));

			$this->sql->update($this->registry['table.system_registry'], array(

				'value' => 2,

			), $con);


			// set install date
			$con  = new PSX_Sql_Condition(array('name', '=', 'core.install_date'));
			$date = new DateTime('NOW', $this->registry['core.default_timezone']);

			$this->sql->update($this->registry['table.system_registry'], array(

				'value' => $date->format(PSX_DateTime::SQL),

			), $con);

			echo PSX_Json::encode(array('success' => true));
		}
		catch(Exception $e)
		{
			$debug = '';

			if($this->config['psx_debug'] === true)
			{
				$debug.= "\n" . $e->getTraceAsString();
			}

			echo PSX_Json::encode(array('success' => false, 'msg' => $e->getMessage() . $debug));
		}
	}
}

class Amun_Registry_NoDb extends Amun_Registry
{
	protected $container = array();
	protected $config;
	protected $sql;

	public function __construct(PSX_Config $config, PSX_Sql $sql)
	{
		$this->config = $config;
		$this->sql    = $sql;

		$this->exchangeArray(array(

			'table.amun_content_api'            => $this->config['amun_table_prefix'] . 'content_api',
			'table.amun_content_api_type'       => $this->config['amun_table_prefix'] . 'content_api_type',
			'table.amun_content_gadget'         => $this->config['amun_table_prefix'] . 'content_gadget',
			'table.amun_content_media'          => $this->config['amun_table_prefix'] . 'content_media',
			'table.amun_content_page'           => $this->config['amun_table_prefix'] . 'content_page',
			'table.amun_content_page_gadget'    => $this->config['amun_table_prefix'] . 'content_page_gadget',
			'table.amun_content_page_option'    => $this->config['amun_table_prefix'] . 'content_page_option',
			'table.amun_content_page_right'     => $this->config['amun_table_prefix'] . 'content_page_right',
			'table.amun_content_service'        => $this->config['amun_table_prefix'] . 'content_service',
			'table.amun_content_service_option' => $this->config['amun_table_prefix'] . 'content_service_option',
			'table.amun_system_api'             => $this->config['amun_table_prefix'] . 'system_api',
			'table.amun_system_api_access'      => $this->config['amun_table_prefix'] . 'system_api_access',
			'table.amun_system_api_request'     => $this->config['amun_table_prefix'] . 'system_api_request',
			'table.amun_system_approval'        => $this->config['amun_table_prefix'] . 'system_approval',
			'table.amun_system_approval_record' => $this->config['amun_table_prefix'] . 'system_approval_record',
			'table.amun_system_assoc'           => $this->config['amun_table_prefix'] . 'system_assoc',
			'table.amun_system_connect'         => $this->config['amun_table_prefix'] . 'system_connect',
			'table.amun_system_connect_access'  => $this->config['amun_table_prefix'] . 'system_connect_access',
			'table.amun_system_connect_assoc'   => $this->config['amun_table_prefix'] . 'system_connect_assoc',
			'table.amun_system_country'         => $this->config['amun_table_prefix'] . 'system_country',
			'table.amun_system_host'            => $this->config['amun_table_prefix'] . 'system_host',
			'table.amun_system_host_request'    => $this->config['amun_table_prefix'] . 'system_host_request',
			'table.amun_system_log'             => $this->config['amun_table_prefix'] . 'system_log',
			'table.amun_system_mail'            => $this->config['amun_table_prefix'] . 'system_mail',
			'table.amun_system_notify'          => $this->config['amun_table_prefix'] . 'system_notify',
			'table.amun_system_registry'        => $this->config['amun_table_prefix'] . 'system_registry',
			'table.amun_user_account'           => $this->config['amun_table_prefix'] . 'user_account',
			'table.amun_user_activity'          => $this->config['amun_table_prefix'] . 'user_activity',
			'table.amun_user_activity_receiver' => $this->config['amun_table_prefix'] . 'user_activity_receiver',
			'table.amun_user_activity_template' => $this->config['amun_table_prefix'] . 'user_activity_template',
			'table.amun_user_friend'            => $this->config['amun_table_prefix'] . 'user_friend',
			'table.amun_user_friend_group'      => $this->config['amun_table_prefix'] . 'user_friend_group',
			'table.amun_user_group'             => $this->config['amun_table_prefix'] . 'user_group',
			'table.amun_user_group_right'       => $this->config['amun_table_prefix'] . 'user_group_right',
			'table.amun_user_right'             => $this->config['amun_table_prefix'] . 'user_right',
			'core.default_timezone'             => new DateTimeZone('UTC'),

		));
	}
}

class Amun_User_NoDb extends Amun_User
{
	public $id      = 1;
	public $groupId = 1;
	public $name    = 'System';

	public function __construct(Amun_Registry $registry)
	{
		$this->registry = $registry;
		$this->config   = $registry->getConfig();
		$this->sql      = $registry->getSql();

		$this->id       = 1;
		$this->groupId  = 1;
		$this->name     = 'System';
	}

	public function hasRight($key)
	{
		return true;
	}

	public function isAdministrator()
	{
		return true;
	}

	public function isAnonymous()
	{
		return false;
	}
}

