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

namespace AmunService\Webfinger\Api;

use AmunService\User\Account;
use AmunService\Content\Page;
use Amun\Exception;
use Amun\Module\ApiAbstract;
use PSX\Base;
use PSX\Data\Message;
use PSX\DateTime;
use PSX\Filter;
use PSX\Http;
use PSX\Hostmeta\DocumentAbstract;
use PSX\Hostmeta\Jrd;
use PSX\Hostmeta\Xrd;
use PSX\Hostmeta\Link;
use PSX\Sql;
use XMLWriter;

/**
 * Index
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Index extends ApiAbstract
{
	/**
	 * Get informations about an uri
	 *
	 * @httpMethod GET
	 * @path /
	 * @nickname getWebfinger
	 * @parameter query uri string
	 * @responseClass PSX\Data\Message
	 */
	public function getWebfinger()
	{
		try
		{
			$accept = $this->getHeader('Accept');
			$format = $this->get->format('string');
			$uri    = $this->get->resource('string', array(new Filter\Length(3, 512)));

			if(empty($uri))
			{
				throw new Exception('Resource not available', 400);
			}

			if($format == 'xml' || $accept == 'application/xrd+xml')
			{
				header('Content-Type: application/xrd+xml');

				$document = new Xrd();
			}
			else
			{
				header('Content-Type: application/jrd+json');

				$document = new Jrd();
			}

			if(($page = $this->getPage($uri)) instanceof Page\Record)
			{
				$this->buildPage($document, $page);
			}
			else if(($account = $this->getAccount($uri)) instanceof Account\Record)
			{
				$this->buildAccount($document, $account);
			}
			else
			{
				throw new Exception('Resource not found', 404);
			}

			echo $document->export();
		}
		catch(\Exception $e)
		{
			$code = isset(Http::$codes[$e->getCode()]) ? $e->getCode() : 500;
			$msg  = new Message($e->getMessage(), false);

			$this->setResponse($msg, null, $code);
		}
	}

	protected function buildPage(DocumentAbstract $document, Page\Record $page)
	{
		// subject
		$subject = $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . $page->path;

		$document->setSubject($subject);

		// meta
		$document->addProperty('http://ns.amun-project.org/2011/meta/id', $page->globalId);
		$document->addProperty('http://ns.amun-project.org/2011/meta/title', $page->title);
		$document->addProperty('http://ns.amun-project.org/2011/meta/date', $page->getDate()->format(DateTime::ATOM));
	}

	protected function getPage($uri)
	{
		$filter = new Filter\Url();

		if($filter->apply($uri) === true && (substr($uri, 0, 7) == 'http://' || substr($uri, 0, 8) == 'https://'))
		{
			// remove base url
			$uri = substr($uri, strlen($this->config['psx_url'] . '/' . $this->config['psx_dispatch']));
			$uri = trim($uri, '/');

			// get page
			$handler = $this->hm->getHandler('AmunService\Content\Page');
			return $handler->getOneByPath($uri);
		}
	}

	protected function buildAccount(DocumentAbstract $document, Account\Record $account)
	{
		// subject
		$subject = $account->name . '@' . $this->base->getHost();

		$document->setSubject($subject);

		// alias
		$document->setAliases(array($account->profileUrl));

		// id
		$document->addProperty('http://ns.amun-project.org/2011/meta/id', $account->globalId);
		$document->addProperty('http://ns.amun-project.org/2011/meta/name', $account->name);
		$document->addProperty('http://ns.amun-project.org/2011/meta/date', $account->getDate()->format(DateTime::ATOM));

		// profile
		$link = new Link();
		$link->setRel('profile');
		$link->setType('text/html');
		$link->setHref($account->profileUrl);
		$document->addLink($link);

		// activity atom feed
		$href = $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/my/activity/' . $account->id . '?format=atom';

		$link = new Link();
		$link->setRel('alternate');
		$link->setType('application/atom+xml');
		$link->setHref($href);
		$document->addLink($link);

		// json activity streams
		$href = $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/my/activity/' . $account->id . '?format=jas';

		$link = new Link();
		$link->setRel('alternate');
		$link->setType('application/stream+json');
		$link->setHref($href);
		$document->addLink($link);
	}

	protected function getAccount($uri)
	{
		$filter = new Filter\Email();
		$email  = substr($uri, 5);

		if($filter->apply($email) === true && substr($uri, 0, 5) == 'acct:')
		{
			// split mail
			list($name, $host) = explode('@', $email);

			// get account record
			$handler = $this->hm->getHandler('AmunService\User\Account');
			return $handler->getOneByName($name, array(), Sql::FETCH_OBJECT);
		}
	}
}
