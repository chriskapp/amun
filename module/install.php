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

use Amun\Dependency;
use Amun\Exception;
use Amun\DataFactory;
use Amun\Security;
use Amun\Registry;
use Amun\User;
use AmunService\User\Account;
use AmunService\User\Group;
use AmunService\Content\Page as ContentPage;
use AmunService\Core\Service;
use AmunService\Oauth;
use AmunService\Page;
use PSX\Module\ViewAbstract;
use PSX\Template;
use PSX\Config;
use PSX\Sql;
use PSX\Sql\Condition;
use PSX\Json;
use PSX\Filter;
use PSX\DateTime;

/**
 * install
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @version    $Revision: 880 $
 */
class install extends ViewAbstract
{
	protected $services = array(
		'org.amun-project.log',
		'org.amun-project.xrds',
		'org.amun-project.hostmeta',
		'org.amun-project.lrdd',
		'org.amun-project.user',
		'org.amun-project.core',
		'org.amun-project.asset',
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
		'org.amun-project.file',
		'org.amun-project.php',
		'org.amun-project.redirect',
		'org.amun-project.pipe',
	);

	public function getDependencies()
	{
		$ct = new Dependency\Install($this->base->getConfig());

		return $ct;
	}

	public function onLoad()
	{
		try
		{
			$con         = new Condition(array('name', '=', 'core.install_date'));
			$installDate = $this->sql->select($this->registry['table.core_registry'], array('value'), $con, Sql::SELECT_FIELD);

			if(!empty($installDate))
			{
				throw new Exception('You already have run the installer, for security reasons the installer stops here.');
			}

			$this->registry->load();
		}
		catch(\PDOException $e)
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
				throw new Exception('Cache directory is not writeable');
			}


			echo Json::encode(array('success' => true));
		}
		catch(\Exception $e)
		{
			$debug = '';

			if($this->config['psx_debug'] === true)
			{
				$debug.= "\n" . $e->getTraceAsString();
			}

			echo Json::encode(array('success' => false, 'msg' => $e->getMessage() . $debug));
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
INSERT IGNORE INTO `{$this->registry['table.core_registry']}` (`name`, `type`, `class`, `value`) VALUES
('table.core_approval', 'STRING', NULL, '{$this->registry['table.core_approval']}'),
('table.core_approval_record', 'STRING', NULL, '{$this->registry['table.core_approval_record']}'),
('table.core_event', 'STRING', NULL, '{$this->registry['table.core_event']}'),
('table.core_event_listener', 'STRING', NULL, '{$this->registry['table.core_event_listener']}'),
('table.core_registry', 'STRING', NULL, '{$this->registry['table.core_registry']}'),
('table.core_service', 'STRING', NULL, '{$this->registry['table.core_service']}'),
('core.default_timezone', 'STRING', 'DateTimeZone', 'UTC');
SQL;

			$q[] = <<<SQL
INSERT IGNORE INTO `{$this->registry['table.core_event']}` (`name`, `interface`, `description`) VALUES
('core.service_install', NULL, 'Notifies if a service gets installed'),
('core.record_change', NULL, 'Notifies if a record has changed');
SQL;

			foreach($q as $query)
			{
				$this->sql->query($query);
			}

			echo Json::encode(array('success' => true));
		}
		catch(\Exception $e)
		{
			$debug = '';

			if($this->config['psx_debug'] === true)
			{
				$debug.= "\n" . $e->getTraceAsString();
			}

			echo Json::encode(array('success' => false, 'msg' => $e->getMessage() . $debug));
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

			echo Json::encode(array('success' => true));
		}
		catch(\Exception $e)
		{
			$debug = '';

			if($this->config['psx_debug'] === true)
			{
				$debug.= "\n" . $e->getTraceAsString();
			}

			echo Json::encode(array('success' => false, 'msg' => $e->getMessage() . $debug));
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
				$handler = new Service\Handler($this->getContainer(), $this->user);
				$errors  = array();

				foreach($this->services as $source)
				{
					try
					{
						$service = $handler->getRecord();
						$service->setSource($source);

						$handler->create($service);
					}
					catch(\Exception $e)
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
					throw new Exception('The following errors occured while installing the services: ' . "\n" . implode("\n", $errors) . "\n" . '--');
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

			echo Json::encode(array('success' => true));
		}
		catch(\Exception $e)
		{
			$debug = '';

			if($this->config['psx_debug'] === true)
			{
				$debug.= "\n" . $e->getTraceAsString();
			}

			echo Json::encode(array('success' => false, 'msg' => $e->getMessage() . $debug));
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
			$title    = $this->post->title('string', array(new Filter\Length(3, 64), new Filter\Html()), 'title', 'Title');
			$subTitle = $this->post->subTitle('string', array(new Filter\Length(0, 128), new Filter\Html()), 'subTitle', 'Sub Title');

			if(!$this->validate->hasError())
			{
				// set title
				$con = new Condition(array('name', '=', 'core.title'));

				$this->sql->update($this->registry['table.core_registry'], array(

					'value' => $title),

				$con);


				// set sub title
				$con = new Condition(array('name', '=', 'core.sub_title'));

				$this->sql->update($this->registry['table.core_registry'], array(

					'value' => $subTitle),

				$con);


				$this->session->set('settingsTitle', $title);
				$this->session->set('settingsSubTitle', $subTitle);
			}
			else
			{
				throw new Exception($this->validate->getLastError());
			}

			echo Json::encode(array('success' => true));
		}
		catch(\Exception $e)
		{
			$debug = '';

			if($this->config['psx_debug'] === true)
			{
				$debug.= "\n" . $e->getTraceAsString();
			}

			echo Json::encode(array('success' => false, 'msg' => $e->getMessage() . $debug));
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
					'date'  => $date->format(DateTime::SQL),

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

			echo Json::encode(array('success' => true));
		}
		catch(\Exception $e)
		{
			$debug = '';

			if($this->config['psx_debug'] === true)
			{
				$debug.= "\n" . $e->getTraceAsString();
			}

			echo Json::encode(array('success' => false, 'msg' => $e->getMessage() . $debug));
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
				$handler = new Account\Handler($this->getContainer(), $this->user);

				$account = $handler->getRecord();
				$account->setGroupId(1);
				$account->setStatus(Account\Record::ADMINISTRATOR);
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

			echo Json::encode(array('success' => true));
		}
		catch(\Exception $e)
		{
			$debug = '';

			if($this->config['psx_debug'] === true)
			{
				$debug.= "\n" . $e->getTraceAsString();
			}

			echo Json::encode(array('success' => false, 'msg' => $e->getMessage() . $debug));
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
				$handler = new Oauth\Handler($this->getContainer(), $this->user);

				$api = $handler->getRecord();
				$api->setStatus(Oauth\Record::NORMAL);
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
				}
			}


			echo Json::encode(array('success' => true));
		}
		catch(\Exception $e)
		{
			$debug = '';

			if($this->config['psx_debug'] === true)
			{
				$debug.= "\n" . $e->getTraceAsString();
			}

			echo Json::encode(array('success' => false, 'msg' => $e->getMessage() . $debug));
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
				$handler = new Group\Handler($this->getContainer(), $this->user);


				// normal group
				$group = $handler->getRecord();
				$group->setTitle('Normal');

				$handler->create($group);

				$this->setRights(2, array(
					'user_view',
					'user_account_view',
					'user_account_edit',
					'user_activity_view',
					'user_activity_add',
					'user_friend_view',
					'user_friend_add',
					'user_friend_delete',
					'user_friend_group_view',
					'user_friend_group_add',
					'user_friend_group_delete',
					'media_view',
					'swagger_view',
					'sitemap_view',
					'content_view',
					'my_view',
					'my_friends_view',
					'my_activities_view',
					'my_settings_view',
					'profile_view',
					'page_view',
					'comment_view',
					'comment_add',
					'news_view',
					'news_comment_add',
					'file_view',
					'php_view',
					'redirect_view',
					'pipe_view',
				));


				// anonymous group
				$group = $handler->getRecord();
				$group->setTitle('Anonymous');

				$handler->create($group);

				$this->setRights(3, array(
					'user_view',
					'media_view',
					'swagger_view',
					'sitemap_view',
					'content_view',
					'my_view',
					'profile_view',
					'page_view',
					'comment_view',
					'news_view',
					'file_view',
					'php_view',
					'redirect_view',
					'pipe_view',
				));
			}


			// insert user
			$count = $this->sql->count($this->registry['table.user_account']);

			if($count == 1)
			{
				$handler = new Account\Handler($this->getContainer(), $this->user);

				// anonymous
				$record = $handler->getRecord();
				$record->setGroupId(3);
				$record->setStatus(Account\Record::ANONYMOUS);
				$record->setIdentity('anonymous@anonymous.com');
				$record->setName('Anonymous');
				$record->setPw(Security::generatePw());
				$record->setTimezone('UTC');

				$handler->create($record);
			}


			// find services
			$con = new Condition(array('source', '=', 'org.amun-project.page'));
			$servicePageId = DataFactory::getTable('Core_Service')->getField('id', $con);

			$con = new Condition(array('source', '=', 'org.amun-project.profile'));
			$serviceProfileId = DataFactory::getTable('Core_Service')->getField('id', $con);

			$con = new Condition(array('source', '=', 'org.amun-project.my'));
			$serviceMyId = DataFactory::getTable('Core_Service')->getField('id', $con);


			// insert pages
			$count = $this->sql->count($this->registry['table.content_page']);

			if($count == 0)
			{
				$handler = new ContentPage\Handler($this->getContainer(), $this->user);

				// root
				$record = $handler->getRecord();
				$record->setParentId(0);
				$record->setServiceId($servicePageId);
				$record->setStatus(ContentPage\Record::HIDDEN);
				$record->setTitle($_SESSION['settingsTitle']);
				$record->setTemplate(null);

				$handler->create($record);


				// home
				$record = $handler->getRecord();
				$record->setParentId(1);
				$record->setServiceId($servicePageId);
				$record->setStatus(ContentPage\Record::NORMAL);
				$record->setTitle('Home');
				$record->setTemplate(null);

				$handler->create($record);


				// my
				$record = $handler->getRecord();
				$record->setParentId(1);
				$record->setServiceId($serviceMyId);
				$record->setStatus(ContentPage\Record::HIDDEN);
				$record->setTitle('My');
				$record->setTemplate(null);

				$handler->create($record);


				// profile
				$record = $handler->getRecord();
				$record->setParentId(1);
				$record->setServiceId($serviceProfileId);
				$record->setStatus(ContentPage\Record::HIDDEN);
				$record->setTitle('Profile');
				$record->setTemplate(null);

				$handler->create($record);


				// help
				$record = $handler->getRecord();
				$record->setParentId(1);
				$record->setServiceId($servicePageId);
				$record->setStatus(ContentPage\Record::HIDDEN);
				$record->setTitle('Help');
				$record->setTemplate(null);

				$handler->create($record);
			}


			// insert page content
			$count = $this->sql->count($this->registry['table.page']);

			if($count == 0)
			{
				$handler = new Page\Handler($this->getContainer(), $this->user);

				// root
				$content = <<<TEXT
<h1>It works!</h1>
<p>This is the default web page for this server.</p>
<p>The web server software is running but no content has been added, yet.</p>
TEXT;

				$record = $handler->getRecord();
				$record->setPageId(1);
				$record->setContent($content);

				$handler->create($record);


				// home
				$content = <<<TEXT
<h1>Lorem ipsum dolor sit amet</h1>
<p>consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</p>
TEXT;

				$record = $handler->getRecord();
				$record->setPageId(2);
				$record->setContent($content);

				$handler->create($record);


				// help
				$content = <<<TEXT
<h3>Mentions</h3>

<h4>Users</h4>
<p>If you want mention a user you can use the @ tag wich automatically creates an hyperlink to the users profile.</p>
<pre>
Hi @{$this->user->name} how are you?
</pre>

<h4>Pages</h4>
<p>If you want link to a specific page you can use the &amp; tag wich automatically creates an hyperlink to the specific page.</p>
<pre>
look at the &home page
</pre>

<h4>Hyperlinks</h4>
<p>Urls are automatically converted into hyperlinks. If your url points to an video or image Amun tries in some cases to discover informations about the media item and appends a preview to your post.</p>
<pre>
look at this video http://www.youtube.com/watch?v=4EL67mjv1nM ;D
</pre>

<h3>Formatting content</h3>
<p>Amun uses a subset of the <a href="http://wikipedia.org/wiki/Markdown">markdown</a> syntax to provide an easy way to format content. Please use the following formatting rules in your content so that readers can enjoy reading your content. Note the markdown syntax is only to simplify creating content without writing html if you prefer you can also write plain html.</p>

<h4>Paragraphs</h4>
<p>Here an example how text will be converted into paragraphs. Note if you have two trailing spaces at a line a <span class="kwd">&lt;br /&gt;</span> tag will be inserted.</p>
<pre>
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
<pre>
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
<pre>
&gt; Lorem ipsum dolor sit amet
&gt; consetetur sadipscing elitr
&gt; sed diam nonumy eirmod

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
<pre>
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

<h3>Restrictions</h3>
<p>Note all the formating capabilities depends on the html tags wich are allowed for your user group. Website administrators can insert any kind of html tags where anonymous user can use only a small subset inorder to prevent misuse.</p>

TEXT;

				$record = $handler->getRecord();
				$record->setPageId(5);
				$record->setContent($content);

				$handler->create($record);
			}


			// set default_page
			$con = new Condition(array('name', '=', 'core.default_page'));

			$this->sql->update($this->registry['table.core_registry'], array(

				'value' => 'home',

			), $con);


			// set default user group
			$con = new Condition(array('name', '=', 'core.default_user_group'));

			$this->sql->update($this->registry['table.core_registry'], array(

				'value' => 2,

			), $con);


			// set anonymous_user
			$con = new Condition(array('name', '=', 'core.anonymous_user'));

			$this->sql->update($this->registry['table.core_registry'], array(

				'value' => 2,

			), $con);


			// set install date
			$con  = new Condition(array('name', '=', 'core.install_date'));
			$date = new DateTime('NOW', $this->registry['core.default_timezone']);

			$this->sql->update($this->registry['table.core_registry'], array(

				'value' => $date->format(DateTime::SQL),

			), $con);

			echo Json::encode(array('success' => true));
		}
		catch(\Exception $e)
		{
			$debug = '';

			if($this->config['psx_debug'] === true)
			{
				$debug.= "\n" . $e->getTraceAsString();
			}

			echo Json::encode(array('success' => false, 'msg' => $e->getMessage() . $debug));
		}
	}

	private function setRights($groupId, array $rights)
	{
		$groupId = (integer) $groupId;

		if(!empty($groupId) && !empty($rights))
		{
			// remove existing rights
			$this->sql->query('DELETE FROM ' . $this->registry['table.user_group_right'] . ' WHERE `groupId` = ' . $groupId);

			// get rights
			$rights = implode(',', array_map(array($this->sql, 'quote'), $rights));
			$sql    = 'SELECT `id` FROM ' . $this->registry['table.user_right'] . ' WHERE `name` IN (' . $rights . ')';
			$result = $this->sql->getAll($sql);

			if(!empty($result))
			{
				// insert rights
				$sql = 'INSERT INTO ' . $this->registry['table.user_group_right'] . ' (`groupId`, `rightId`) VALUES ';
				$len = count($result);

				foreach($result as $i => $row)
				{
					$sql.= '(' . $groupId . ', ' . $row['id'] . ')';

					if($i < $len - 1)
					{
						$sql.= ',';
					}

					$sql.= "\n";
				}

				$this->sql->query($sql);
			}
		}
	}
}

class RegistryNoDb extends Registry
{
	protected $container = array();
	protected $config;
	protected $sql;

	public function __construct(Config $config, Sql $sql)
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

class UserNoDb extends User
{
	public $id      = 1;
	public $groupId = 1;
	public $name    = 'System';
	public $status  = Account\Record::ADMINISTRATOR;

	public function __construct(Registry $registry)
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

