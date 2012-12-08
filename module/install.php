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
		'org.amun-project.log',
		'org.amun-project.xrds',
		'org.amun-project.hostmeta',
		'org.amun-project.lrdd',
		'org.amun-project.user',
		'org.amun-project.core',
		'org.amun-project.oauth',
		'org.amun-project.media',
		'org.amun-project.openid',
		'org.amun-project.country',
		'org.amun-project.mail',
		'org.amun-project.swagger',
		'org.amun-project.sitemap',
		'org.amun-project.phpinfo',
		'org.amun-project.content',
		'org.amun-project.my',
		'org.amun-project.profile',
		'org.amun-project.page',
		'org.amun-project.comment',
		'org.amun-project.news',
	);

	public function getDependencies()
	{
		$ct = new Amun_Dependency_Install($this->base->getConfig());

		Amun_DataFactory::setContainer($ct);

		return $ct;
	}

	public function onLoad()
	{
		try
		{
			$con         = new PSX_Sql_Condition(array('name', '=', 'core.install_date'));
			$installDate = $this->sql->select($this->registry['table.core_registry'], array('value'), $con, PSX_Sql::SELECT_FIELD);

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

	/**
	 * @httpMethod GET
	 * @path /
	 */
	public function showInstall()
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

	/**
	 * @httpMethod POST
	 * @path /setupCheckRequirements
	 */
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

	/**
	 * @httpMethod POST
	 * @path /setupCreateTables
	 */
	public function setupCreateTables()
	{
		$q = array();

		try
		{
			$q[] = <<<SQL
CREATE TABLE IF NOT EXISTS `{$this->registry['table.core_approval']}` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `table` varchar(64) NOT NULL,
  `field` varchar(32) NOT NULL DEFAULT '',
  `value` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8
SQL;

			$q[] = <<<SQL
CREATE TABLE IF NOT EXISTS `{$this->registry['table.core_approval_record']}` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `userId` int(10) NOT NULL,
  `type` enum('INSERT','UPDATE','DELETE') NOT NULL,
  `table` varchar(64) NOT NULL,
  `record` text NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8
SQL;

			$q[] = <<<SQL
CREATE TABLE IF NOT EXISTS `{$this->registry['table.core_event']}` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `interface` varchar(64) DEFAULT NULL,
  `description` varchar(256) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8
SQL;

	  $q[] = <<<SQL
CREATE TABLE IF NOT EXISTS `{$this->registry['table.core_event_listener']}` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `eventId` int(10) NOT NULL,
  `priority` int(10) NOT NULL,
  `class` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8
SQL;

			$q[] = <<<SQL
CREATE TABLE IF NOT EXISTS `{$this->registry['table.core_registry']}` (
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
CREATE TABLE IF NOT EXISTS `{$this->registry['table.core_service']}` (
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
INSERT INTO `{$this->registry['table.core_registry']}` (`name`, `type`, `class`, `value`) VALUES
('table.core_approval', 'STRING', NULL, '{$this->registry['table.core_approval']}'),
('table.core_approval_record', 'STRING', NULL, '{$this->registry['table.core_approval_record']}'),
('table.core_event', 'STRING', NULL, '{$this->registry['table.core_event']}'),
('table.core_event_listener', 'STRING', NULL, '{$this->registry['table.core_event_listener']}'),
('table.core_registry', 'STRING', NULL, '{$this->registry['table.core_registry']}'),
('table.core_service', 'STRING', NULL, '{$this->registry['table.core_service']}'),
('core.default_timezone', 'STRING', 'DateTimeZone', 'UTC');
SQL;

			$q[] = <<<SQL
INSERT INTO `{$this->registry['table.core_event']}` (`name`, `interface`, `description`) VALUES
('core.service_install', NULL, 'Notifies if a service gets installed'),
('core.record_change', NULL, 'Notifies if a record has changed');
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

	/**
	 * @httpMethod POST
	 * @path /setupInsertData
	 */
	public function setupInsertData()
	{
		$q = array();

		try
		{
			// nothing todo here at the moment ...

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

	/**
	 * @httpMethod POST
	 * @path /setupInstallService
	 */
	public function setupInstallService()
	{
		try
		{
			// install services
			$count = $this->sql->count($this->registry['table.core_service']);

			if($count == 0)
			{
				$handler = new AmunService_Core_Service_Handler($this->user);
				$errors  = array();

				foreach($this->services as $source)
				{
					try
					{
						$service = Amun_Sql_Table_Registry::get('Core_Service')->getRecord();
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


				// the follwoing rights can not installed through the config xml
				// because the user service is installed after wich handles the 
				// permissions
				$count = $this->sql->count($this->registry['table.user_right']);

				if($count == 0)
				{
					$this->sql->insert($this->registry['table.user_right'], array(

						'name'        => 'log_view',
						'description' => 'Log view',

					));

					$this->sql->insert($this->registry['table.user_right'], array(

						'name'        => 'xrds_view',
						'description' => 'Xrds view',

					));

					$this->sql->insert($this->registry['table.user_right'], array(

						'name'        => 'lrdd_view',
						'description' => 'Lrdd view',

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

	/**
	 * @httpMethod POST
	 * @path /setupInsertRegistry
	 */
	public function setupInsertRegistry()
	{
		try
		{
			$title    = $this->post->title('string', array(new PSX_Filter_Length(3, 64), new PSX_Filter_Html()), 'title', 'Title');
			$subTitle = $this->post->subTitle('string', array(new PSX_Filter_Length(0, 128), new PSX_Filter_Html()), 'subTitle', 'Sub Title');

			if(!$this->validate->hasError())
			{
				// set title
				$con = new PSX_Sql_Condition(array('name', '=', 'core.title'));

				$this->sql->update($this->registry['table.core_registry'], array(

					'value' => $title),

				$con);


				// set sub title
				$con = new PSX_Sql_Condition(array('name', '=', 'core.sub_title'));

				$this->sql->update($this->registry['table.core_registry'], array(

					'value' => $subTitle),

				$con);


				$this->session->set('settingsTitle', $title);
				$this->session->set('settingsSubTitle', $subTitle);
			}
			else
			{
				throw new Amun_Exception($this->validate->getLastError());
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

	/**
	 * @httpMethod POST
	 * @path /setupInsertGroup
	 */
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

				$groupId = $this->sql->getLastInsertId();

				// set rights
				$rights = $this->sql->getCol('SELECT id FROM ' . $this->registry['table.user_right']);

				foreach($rights as $rightId)
				{
					$this->sql->insert($this->registry['table.user_group_right'], array(

						'groupId' => $groupId,
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

	/**
	 * @httpMethod POST
	 * @path /setupInsertAdmin
	 */
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
				$handler = new AmunService_User_Account_Handler($this->user);

				$account = Amun_Sql_Table_Registry::get('User_Account')->getRecord();
				$account->setGroupId(1);
				$account->setStatus(AmunService_User_Account_Record::ADMINISTRATOR);
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

	/**
	 * @httpMethod POST
	 * @path /setupInsertApi
	 */
	public function setupInsertApi()
	{
		try
		{
			$count = $this->sql->count($this->registry['table.oauth']);

			if($count == 0)
			{
				$email = $this->session->get('administratorEmail');


				// insert api
				$handler = new AmunService_Oauth_Handler($this->user);

				$api = Amun_Sql_Table_Registry::get('Oauth')->getRecord();
				$api->setStatus(AmunService_Oauth_Record::NORMAL);
				$api->setName('System');
				$api->setEmail($email);
				$api->setUrl($this->config['psx_url']);
				$api->setTitle('System');
				$api->setDescription('Default system API to access amun');

				$handler->create($api);


				// save consumer key and secret to file
				$row = $this->sql->getRow('SELECT consumerKey, consumerSecret FROM ' . $this->registry['table.oauth'] . ' LIMIT 1');

				if(!empty($row))
				{
					$values = array(

						'host.name'       => $this->base->getHost(),
						'account.name'    => $this->session->administratorName,
						'consumer.key'    => $row['consumerKey'],
						'consumer.secret' => $row['consumerSecret'],

					);

					/*
					try
					{
						$mail = new Amun_Mail($this->registry);
						$mail->send('INSTALL_SUCCESS', $email, $values);
					}
					catch(Zend_Mail_Transport_Exception $e)
					{
						// ignore send mail errors
					}
					*/
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

	/**
	 * @httpMethod POST
	 * @path /setupInstallSample
	 */
	public function setupInstallSample()
	{
		try
		{
			// insert groups
			$count = $this->sql->count($this->registry['table.user_group']);

			if($count == 1)
			{
				$handler = new AmunService_User_Group_Handler($this->user);


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
				$rights = $this->sql->getCol('SELECT id FROM ' . $this->registry['table.user_right'] . ' WHERE (name LIKE "%_view" AND name NOT LIKE "core_%") OR name IN("' . implode('","', $allowedRights) . '")');

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

				$rights = $this->sql->getCol('SELECT id FROM ' . $this->registry['table.user_right'] . ' WHERE name LIKE "%_view" AND name NOT LIKE "core_%"');

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
				$handler = new AmunService_User_Account_Handler($this->user);

				// anonymous
				$record = Amun_Sql_Table_Registry::get('User_Account')->getRecord();
				$record->setGroupId(3);
				$record->setStatus(AmunService_User_Account_Record::ANONYMOUS);
				$record->setIdentity('anonymous@anonymous.com');
				$record->setName('Anonymous');
				$record->setPw(Amun_Security::generatePw());
				$record->setTimezone('UTC');

				$handler->create($record);
			}


			// find services
			$con = new PSX_Sql_Condition(array('source', '=', 'org.amun-project.page'));
			$servicePageId = Amun_Sql_Table_Registry::get('Core_Service')->getField('id', $con);

			$con = new PSX_Sql_Condition(array('source', '=', 'org.amun-project.profile'));
			$serviceProfileId = Amun_Sql_Table_Registry::get('Core_Service')->getField('id', $con);

			$con = new PSX_Sql_Condition(array('source', '=', 'org.amun-project.my'));
			$serviceMyId = Amun_Sql_Table_Registry::get('Core_Service')->getField('id', $con);


			// insert pages
			$count = $this->sql->count($this->registry['table.content_page']);

			if($count == 0)
			{
				$handler = new AmunService_Content_Page_Handler($this->user);

				// root
				$record = Amun_Sql_Table_Registry::get('Content_Page')->getRecord();
				$record->setParentId(0);
				$record->setServiceId($servicePageId);
				$record->setStatus(AmunService_Content_Page_Record::HIDDEN);
				$record->setTitle($_SESSION['settingsTitle']);
				$record->setTemplate(null);

				$handler->create($record);


				// home
				$record = Amun_Sql_Table_Registry::get('Content_Page')->getRecord();
				$record->setParentId(1);
				$record->setServiceId($servicePageId);
				$record->setStatus(AmunService_Content_Page_Record::NORMAL);
				$record->setTitle('Home');
				$record->setTemplate(null);

				$handler->create($record);


				// my
				$record = Amun_Sql_Table_Registry::get('Content_Page')->getRecord();
				$record->setParentId(1);
				$record->setServiceId($serviceMyId);
				$record->setStatus(AmunService_Content_Page_Record::HIDDEN);
				$record->setTitle('My');
				$record->setTemplate(null);

				$handler->create($record);


				// profile
				$record = Amun_Sql_Table_Registry::get('Content_Page')->getRecord();
				$record->setParentId(1);
				$record->setServiceId($serviceProfileId);
				$record->setStatus(AmunService_Content_Page_Record::HIDDEN);
				$record->setTitle('Profile');
				$record->setTemplate(null);

				$handler->create($record);


				// help
				$record = Amun_Sql_Table_Registry::get('Content_Page')->getRecord();
				$record->setParentId(1);
				$record->setServiceId($servicePageId);
				$record->setStatus(AmunService_Content_Page_Record::HIDDEN);
				$record->setTitle('Help');
				$record->setTemplate(null);

				$handler->create($record);
			}


			// insert page content
			$count = $this->sql->count($this->registry['table.page']);

			if($count == 0)
			{
				$handler = new AmunService_Page_Handler($this->user);

				// root
				$content = <<<TEXT
<h1>It works!</h1>
<p>This is the default web page for this server.</p>
<p>The web server software is running but no content has been added, yet.</p>
TEXT;

				$record = Amun_Sql_Table_Registry::get('Page')->getRecord();
				$record->setPageId(1);
				$record->setContent($content);

				$handler->create($record);


				// home
				$content = <<<TEXT
<h1>Lorem ipsum dolor sit amet</h1>
<p>consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</p>
TEXT;

				$record = Amun_Sql_Table_Registry::get('Page')->getRecord();
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

				$record = Amun_Sql_Table_Registry::get('Page')->getRecord();
				$record->setPageId(5);
				$record->setContent($content);

				$handler->create($record);
			}


			// set default_page
			$con = new PSX_Sql_Condition(array('name', '=', 'core.default_page'));

			$this->sql->update($this->registry['table.core_registry'], array(

				'value' => 'home',

			), $con);


			// set default user group
			$con = new PSX_Sql_Condition(array('name', '=', 'core.default_user_group'));

			$this->sql->update($this->registry['table.core_registry'], array(

				'value' => 2,

			), $con);


			// set anonymous_user
			$con = new PSX_Sql_Condition(array('name', '=', 'core.anonymous_user'));

			$this->sql->update($this->registry['table.core_registry'], array(

				'value' => 2,

			), $con);


			// set install date
			$con  = new PSX_Sql_Condition(array('name', '=', 'core.install_date'));
			$date = new DateTime('NOW', $this->registry['core.default_timezone']);

			$this->sql->update($this->registry['table.core_registry'], array(

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

			'table.core_approval'        => $this->config['amun_table_prefix'] . 'core_approval',
			'table.core_approval_record' => $this->config['amun_table_prefix'] . 'core_approval_record',
			'table.core_event'           => $this->config['amun_table_prefix'] . 'core_event',
			'table.core_event_listener'  => $this->config['amun_table_prefix'] . 'core_event_listener',
			'table.core_registry'        => $this->config['amun_table_prefix'] . 'core_registry',
			'table.core_service'         => $this->config['amun_table_prefix'] . 'core_service',
			'core.default_timezone'      => new DateTimeZone('UTC'),

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

