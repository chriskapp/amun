<?php
/*
 *  $Id: Plugin.php 880 2012-10-27 13:14:26Z k42b3.x@googlemail.com $
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
 * Amun_Service_Plugin
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Service_Plugin
 * @version    $Revision: 880 $
 */
class Amun_Service_Plugin extends Amun_Data_RecordAbstract
{
	const ACTIVE       = 0x1;
	const NOT_APPROVED = 0x2;
	const CLOSED       = 0x3;

	protected $_page;
	protected $_user;
	protected $_date;
	protected $_latestRelease;

	public function setId($id)
	{
		$id = $this->_validate->apply($id, 'integer', array(new Amun_Filter_Id($this->_table)), 'id', 'Id');

		if(!$this->_validate->hasError())
		{
			$this->id = $id;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function setPageId($pageId)
	{
		$pageId = $this->_validate->apply($pageId, 'integer', array(new Amun_Filter_Id(Amun_Sql_Table_Registry::get('Content_Page'))), 'pageId', 'Page Id');

		if(!$this->_validate->hasError())
		{
			$this->pageId = $pageId;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function setUrlTitle($urlTitle)
	{
		$urlTitle = $this->_validate->apply($urlTitle, 'string', array(new Amun_Filter_UrlTitle(), new PSX_Filter_Length(3, 128)), 'urlTitle', 'Url Title');

		if(!$this->_validate->hasError())
		{
			$this->urlTitle = $urlTitle;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function setTitle($title)
	{
		$title = $this->_validate->apply($title, 'string', array(new PSX_Filter_Length(3, 256), new PSX_Filter_Html()), 'title', 'Title');

		if(!$this->_validate->hasError())
		{
			$this->setUrlTitle($title);

			$this->title = $title;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function setDescription($description)
	{
		$description = PSX_Util_Markdown::decode($description);
		$description = $this->_validate->apply($description, 'string', array(new PSX_Filter_Length(3, 4096), new Amun_Filter_Html($this->_config, $this->_base->getUser())), 'description', 'Description');

		if(!$this->_validate->hasError())
		{
			$this->description = $description;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function getPage()
	{
		if($this->_page === null)
		{
			$this->_page = Amun_Sql_Table_Registry::get('Content_Page')->getRecord($this->pageId);
		}

		return $this->_page;
	}

	public function getUser()
	{
		if($this->_user === null)
		{
			$this->_user = Amun_Sql_Table_Registry::get('User_Account')->getRecord($this->userId);
		}

		return $this->_user;
	}

	public function getDate()
	{
		if($this->_date === null)
		{
			$this->_date = new DateTime($this->date, $this->_registry['core.default_timezone']);
		}

		return $this->_date;
	}

	public function getUrl()
	{
		return $this->_config['psx_url'] . '/' . $this->_config['psx_dispatch'] . $this->pagePath . $this->_config['amun_page_delimiter'] . '/view/' . $this->id . '/' . $this->urlTitle;
	}

	public function getLatestRelease()
	{
		if($this->_latestRelease === null)
		{
			$con = new PSX_Sql_Condition();
			$con->add('pluginId', '=', $this->id);

			$releaseId = Amun_Sql_Table_Registry::get('Service_Plugin_Release')->getField('id', $con, 'id', PSX_Sql::SORT_DESC);

			if(!empty($releaseId))
			{
				$this->_latestRelease = Amun_Sql_Table_Registry::get('Service_Plugin_Release')->getRecord($releaseId);
			}
		}

		return $this->_latestRelease;
	}

	public function export(PSX_Data_WriterResult $result)
	{
		switch($result->getType())
		{
			case PSX_Data_WriterInterface::JSON:
			case PSX_Data_WriterInterface::XML:

				return parent::export($result);

				break;

			case PSX_Data_WriterInterface::ATOM:

				$entry = $result->getWriter()->createEntry();

				$entry->setTitle($this->title);
				$entry->setId('urn:uuid:' . $this->globalId);
				$entry->setUpdated($this->getDate());
				$entry->addAuthor($this->authorName, $this->authorProfileUrl);
				$entry->addLink($this->getUrl(), 'alternate', 'text/html');
				$entry->setContent($this->description, 'html');

				return $entry;

				break;

			default:

				throw new PSX_Data_Exception('Writer is not supported');

				break;
		}
	}

	public static function getStatus($status = false)
	{
		$s = array(

			self::ACTIVE       => 'Active',
			self::NOT_APPROVED => 'Not approved',
			self::CLOSED       => 'Closed',

		);

		if($status !== false)
		{
			$status = intval($status);

			if(array_key_exists($status, $s))
			{
				return $s[$status];
			}
			else
			{
				return false;
			}
		}
		else
		{
			return $s;
		}
	}
}

