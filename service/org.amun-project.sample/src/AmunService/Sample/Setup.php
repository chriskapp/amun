<?php
/*
 * amun
 * A social content managment system based on the psx framework. For
 * the current version and informations visit <http://amun.phpsx.org>
 *
 * Copyright (c) 2010-2013 Christoph Kappestein <k42b3.x@gmail.com>
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

namespace AmunService\Sample;

use Amun\SetupAbstract;
use Amun\Security;
use AmunService\Content\Page as ContentPage;
use AmunService\User\Account as UserAccount;
use AmunService\User\Group as UserGroup;
use AmunService\Oauth;
use AmunService\Page;
use Composer\IO\IOInterface;
use PSX\Data\RecordInterface;
use PSX\DateTime;
use PSX\Sql;
use PSX\Sql\Condition;

/**
 * Setup
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Setup extends SetupAbstract
{
	protected $name;
	protected $pw;
	protected $email;

	public function preInstall(RecordInterface $record)
	{
	}

	public function postInstall(RecordInterface $record)
	{
		$this->insertRegistry();
		$this->insertGroup();
		$this->insertUser();
		$this->insertApi();
		$this->insertPages();
	}

	protected function insertRegistry()
	{
		$this->logger->info('Update registry entries');

		// set title
		$con = new Condition(array('name', '=', 'core.title'));

		$this->sql->update($this->registry['table.core_registry'], array(
			'value' => 'Sample'),
		$con);

		// set sub title
		$con = new Condition(array('name', '=', 'core.sub_title'));

		$this->sql->update($this->registry['table.core_registry'], array(
			'value' => ''),
		$con);
	}

	protected function insertGroup()
	{
		$count = $this->sql->count($this->registry['table.user_group']);

		if($count == 0)
		{
			$this->logger->info('Create user groups');

			$date = new DateTime('NOW');

			// administrator group
			$this->sql->insert($this->registry['table.user_group'], array(
				'title' => 'Administrator',
				'date'  => $date->format(DateTime::SQL),
			));

			$groupId = $this->sql->getLastInsertId();
			$rights  = $this->sql->getCol('SELECT id FROM ' . $this->registry['table.user_right']);

			foreach($rights as $rightId)
			{
				$this->sql->insert($this->registry['table.user_group_right'], array(
					'groupId' => $groupId,
					'rightId' => $rightId,
				));
			}

			$this->logger->info('> Created administrator group');

			$handler = new UserGroup\Handler($this->container);

			// normal group
			$group = $handler->getRecord();
			$group->setTitle('Normal');

			$group = $handler->create($group);

			$this->setRights($group->id, array(
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
				'login_view',
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

			$this->logger->info('> Created normal group');

			// set default user group
			$con = new Condition(array('name', '=', 'core.default_user_group'));

			$this->sql->update($this->registry['table.core_registry'], array(
				'value' => $group->id,
			), $con);

			// anonymous group
			$group = $handler->getRecord();
			$group->setTitle('Anonymous');

			$group = $handler->create($group);

			$this->setRights($group->id, array(
				'user_view',
				'media_view',
				'swagger_view',
				'sitemap_view',
				'content_view',
				'login_view',
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

			$this->logger->info('> Created anonymous group');
		}
	}

	protected function insertUser()
	{
		$count = $this->sql->count($this->registry['table.user_account']);

		if($count == 0)
		{
			$this->logger->info('Create users');

			$this->name  = isset($_POST['name'])  ? $_POST['name']  : null;
			$this->pw    = isset($_POST['pw'])    ? $_POST['pw']    : null;
			$this->email = isset($_POST['email']) ? $_POST['email'] : null;

			$io = $this->container->get('io');

			if($io instanceof IOInterface)
			{
				if(empty($this->name))
				{
					$this->name = $io->ask('Username: ');
				}

				if(empty($this->pw))
				{
					$this->pw = $io->askAndHideAnswer('Password: ');
				}

				if(empty($this->email))
				{
					$this->email = $io->ask('Email: ');
				}
			}

			$security = new Security($this->registry);
			$handler  = new UserAccount\Handler($this->container);

			// admin user
			$record = $handler->getRecord();
			$record->setGroupId(1);
			$record->setStatus(UserAccount\Record::ADMINISTRATOR);
			$record->setIdentity($this->email);
			$record->setName($this->name);
			$record->setPw($this->pw);
			$record->setEmail($this->email);
			$record->setTimezone('UTC');

			$handler->create($record);

			$this->logger->info('> Created administrator user');

			// anonymous user
			$record = $handler->getRecord();
			$record->setGroupId(3);
			$record->setStatus(UserAccount\Record::ANONYMOUS);
			$record->setIdentity('anonymous@anonymous.com');
			$record->setName('Anonymous');
			$record->setPw($security->generatePw());
			$record->setTimezone('UTC');

			$record = $handler->create($record);

			// set anonymous_user
			$con = new Condition(array('name', '=', 'core.anonymous_user'));

			$this->sql->update($this->registry['table.core_registry'], array(
				'value' => $record->id,
			), $con);

			$this->logger->info('> Created anonymous user');
		}
	}

	protected function insertApi()
	{
		$count = $this->sql->count($this->registry['table.oauth']);

		if($count == 0)
		{
			$this->logger->info('Create api');

			if(empty($this->email))
			{
				$this->email = isset($_POST['email']) ? $_POST['email'] : null;

				$io = $this->container->get('io');

				if($io instanceof IOInterface)
				{
					if(empty($this->email))
					{
						$this->email = $io->ask('Email: ');
					}
				}
			}

			// insert api
			$handler = new Oauth\Handler($this->container);

			$api = $handler->getRecord();
			$api->setStatus(Oauth\Record::NORMAL);
			$api->setName('System');
			$api->setEmail($this->email);
			$api->setUrl($this->config['psx_url']);
			$api->setTitle('System');
			$api->setDescription('Default system API to access amun');

			$handler->create($api);

			$this->logger->info('> Created system API');

			// @todo probably send consumerKey and consumerSecret to email
			$row = $this->sql->getRow('SELECT consumerKey, consumerSecret FROM ' . $this->registry['table.oauth'] . ' LIMIT 1');

			if(!empty($row))
			{
				$this->logger->info('> Consumer key: ' . $row['consumerKey']);
				$this->logger->info('> Consumer secret: ' . $row['consumerSecret']);

				/*
				$values = array(
					'host.name'       => $this->base->getHost(),
					'account.name'    => $this->session->get('administratorName'),
					'consumer.key'    => $row['consumerKey'],
					'consumer.secret' => $row['consumerSecret'],
				);
				*/

				//$this->container->get('mail')->send('', $email, $values);
			}
		}
	}

	protected function insertPages()
	{
		// find services
		$servicePageId    = $this->sql->getField('SELECT `id` FROM ' . $this->registry['table.core_service'] . ' WHERE `name` = "amun/page"');
		$serviceProfileId = $this->sql->getField('SELECT `id` FROM ' . $this->registry['table.core_service'] . ' WHERE `name` = "amun/profile"');
		$serviceMyId      = $this->sql->getField('SELECT `id` FROM ' . $this->registry['table.core_service'] . ' WHERE `name` = "amun/my"');
		$serviceLoginId   = $this->sql->getField('SELECT `id` FROM ' . $this->registry['table.core_service'] . ' WHERE `name` = "amun/login"');

		// insert pages
		$count = $this->sql->count($this->registry['table.content_page']);

		if($count == 0)
		{
			$this->logger->info('Create pages');

			$handler = new ContentPage\Handler($this->container);

			// root
			$record = $handler->getRecord();
			$record->setParentId(0);
			$record->setServiceId($servicePageId);
			$record->setStatus(ContentPage\Record::HIDDEN);
			$record->setTitle('Sample');
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

			// login
			$record = $handler->getRecord();
			$record->setParentId(1);
			$record->setServiceId($serviceLoginId);
			$record->setStatus(ContentPage\Record::HIDDEN);
			$record->setTitle('Login');
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
			$this->logger->info('Insert content');

			$handler = new Page\Handler($this->container);

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
			$name    = !empty($this->name) ? $this->name : 'System';
			$content = <<<TEXT
<h3>Mentions</h3>

<h4>Users</h4>
<p>If you want mention a user you can use the @ tag wich automatically creates an hyperlink to the users profile.</p>
<pre>
Hi @{$name} how are you?
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
			$record->setPageId(6);
			$record->setContent($content);

			$handler->create($record);
		}

		// set default_page
		$con = new Condition(array('name', '=', 'core.default_page'));

		$this->sql->update($this->registry['table.core_registry'], array(
			'value' => 'home',
		), $con);
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

