<?php
/*
 *  $Id: lrdd.php 799 2012-07-08 05:52:43Z k42b3.x@googlemail.com $
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

namespace core\api\meta;

use AmunService_Core_Content_Page_Record;
use AmunService_Core_User_Account_Record;
use Amun_Module_ApiAbstract;
use Amun_Sql_Table_Registry;
use DateTime;
use Exception;
use PSX_Data_Exception;
use PSX_Data_Message;
use PSX_Filter_Email;
use PSX_Filter_Length;
use PSX_Filter_Url;
use PSX_Filter_Urldecode;
use PSX_Sql;
use XMLWriter;

/**
 * lrdd
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @version    $Revision: 799 $
 */
class lrdd extends Amun_Module_ApiAbstract
{
	private $writer;
	private $uri;

	public function onLoad()
	{
		try
		{
			header('Content-type: application/xml');

			$record = $this->getUriRecord();

			$this->writer = new XMLWriter();
			$this->writer->openURI('php://output');
			$this->writer->setIndent(true);
			$this->writer->startDocument('1.0', 'UTF-8');

			$this->writer->startElement('XRD');
			$this->writer->writeAttribute('xmlns', 'http://docs.oasis-open.org/ns/xri/xrd-1.0');

			switch(true)
			{
				case $record instanceof AmunService_Core_User_Account_Record:

					$this->buildAccountXrd($record);

					break;

				case $record instanceof AmunService_Core_Content_Page_Record:

					$this->buildPageXrd($record);

					break;

				default:

					throw new PSX_Data_Exception('Invalid record type');

					break;
			}

			$this->writer->endElement();
			$this->writer->endDocument();
		}
		catch(Exception $e)
		{
			$msg = new PSX_Data_Message($e->getMessage(), false);

			$this->setResponse($msg);
		}
	}

	private function buildAccountXrd(AmunService_Core_User_Account_Record $account)
	{
		// subject
		$this->writer->writeElement('Subject', $this->uri);

		// alias
		$this->writer->writeElement('Alias', $account->profileUrl);

		// id
		$this->writer->startElement('Property');
		$this->writer->writeAttribute('type', 'http://ns.amun-project.org/2011/meta/id');
		$this->writer->text($account->globalId);
		$this->writer->endElement();

		// name
		$this->writer->startElement('Property');
		$this->writer->writeAttribute('type', 'http://ns.amun-project.org/2011/meta/name');
		$this->writer->text($account->name);
		$this->writer->endElement();

		// timezone
		$this->writer->startElement('Property');
		$this->writer->writeAttribute('type', 'http://ns.amun-project.org/2011/meta/timezone');
		$this->writer->text($account->timezone);
		$this->writer->endElement();

		// date
		$this->writer->startElement('Property');
		$this->writer->writeAttribute('type', 'http://ns.amun-project.org/2011/meta/date');
		$this->writer->text($account->getDate()->format(DateTime::ATOM));
		$this->writer->endElement();

		// profile
		$this->writer->startElement('Link');
		$this->writer->writeAttribute('rel', 'profile');
		$this->writer->writeAttribute('type', 'text/html');
		$this->writer->writeAttribute('href', $account->profileUrl);

		$this->writer->endElement();


		if($this->base->hasService('my'))
		{
			// activity atom feed
			$href = $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/my/activity/' . $account->name . '?format=atom';

			$this->writer->startElement('Link');
			$this->writer->writeAttribute('rel', 'alternate');
			$this->writer->writeAttribute('type', 'application/atom+xml');
			$this->writer->writeAttribute('href', $href);
			$this->writer->endElement();

			// json activity streams
			$href = $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/my/activity/' . $account->name . '?format=json';

			$this->writer->startElement('Link');
			$this->writer->writeAttribute('rel', 'alternate');
			$this->writer->writeAttribute('type', 'application/stream+json');
			$this->writer->writeAttribute('href', $href);
			$this->writer->endElement();

			// ostatus subcribe
			$template = $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/my/subscription?topic={uri}';

			$this->writer->startElement('Link');
			$this->writer->writeAttribute('rel', 'http://ostatus.org/schema/1.0/subscribe');
			$this->writer->writeAttribute('template', $template);
			$this->writer->endElement();
		}
	}

	private function buildPageXrd(AmunService_Core_Content_Page_Record $page)
	{
		// subject
		$subject = $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . $page->path . $this->config['amun_page_delimiter'];

		$this->writer->writeElement('Subject', $subject);

		// id
		$this->writer->startElement('Property');
		$this->writer->writeAttribute('type', 'http://ns.amun-project.org/2011/meta/id');
		$this->writer->text($page->globalId);
		$this->writer->endElement();

		// title
		$this->writer->startElement('Property');
		$this->writer->writeAttribute('type', 'http://ns.amun-project.org/2011/meta/title');
		$this->writer->text($page->title);
		$this->writer->endElement();

		// date
		$this->writer->startElement('Property');
		$this->writer->writeAttribute('type', 'http://ns.amun-project.org/2011/meta/date');
		$this->writer->text($page->getDate()->format(DateTime::ATOM));
		$this->writer->endElement();
	}

	private function getUriRecord()
	{
		$this->uri = $this->get->uri('string', array(new PSX_Filter_Urldecode(), new PSX_Filter_Length(7, 256)));

		if(substr($this->uri, 0, 5) == 'acct:')
		{
			$filter = new PSX_Filter_Email();
			$email  = substr($this->uri, 5);

			if($filter->apply($email) === true)
			{
				// split mail
				list($name, $host) = explode('@', $email);

				// get account record
				$account = Amun_Sql_Table_Registry::get('Core_User_Account')
					->select(array('id', 'globalId', 'name', 'profileUrl', 'timezone', 'date'))
					->where('name', '=', $name)
					->getRow(PSX_Sql::FETCH_OBJECT);

				if($account instanceof AmunService_Core_User_Account_Record)
				{
					return $account;
				}
				else
				{
					throw new PSX_Data_Exception('Invalid ACCT');
				}
			}
			else
			{
				throw new PSX_Data_Exception('ACCT must be an email');
			}
		}
		else
		{
			$filter = new PSX_Filter_Url();

			if($filter->apply($this->uri) === true)
			{
				// remove base url
				$uri = substr($this->uri, strlen($this->config['psx_url'] . '/' . $this->config['psx_dispatch']));
				$uri = trim($uri, '/');

				// get path
				$pos = strpos($uri, $this->config['amun_page_delimiter']);

				if($pos !== false)
				{
					$path = substr($uri, 0, $pos);
				}
				else
				{
					throw new PSX_Data_Exception('Page delimiter not found in uri');
				}

				// get page record
				$page = Amun_Sql_Table_Registry::get('Core_Content_Page')
					->select(array('id', 'globalId', 'title', 'path', 'date'))
					->where('path', '=', $path)
					->getRow(PSX_Sql::FETCH_OBJECT);

				if($page instanceof AmunService_Core_Content_Page_Record)
				{
					return $page;
				}
				else
				{
					throw new PSX_Data_Exception('Invalid URL');
				}
			}
			else
			{
				throw new PSX_Data_Exception('Invalid URL format');
			}
		}
	}
}
