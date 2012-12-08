<?php
/*
 *  $Id: Page.php 880 2012-10-27 13:14:26Z k42b3.x@googlemail.com $
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
 * Amun_Service_Page
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Service_Page
 * @version    $Revision: 880 $
 */
class AmunService_Page_Record extends Amun_Data_RecordAbstract
{
	protected $_page;
	protected $_user;
	protected $_date;

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

	public function setContent($content)
	{
		$content = $this->_validate->apply($content, 'string', array(new PSX_Filter_Length(3, 65536), new Amun_Filter_Html($this->_config, $this->_base->getUser())), 'content', 'Content');

		if(!$this->_validate->hasError())
		{
			$this->content = $content;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function getId()
	{
		return $this->_base->getUrn('service', 'page', $this->id);
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

	public function getContent()
	{
		return htmlspecialchars($this->content, ENT_NOQUOTES, 'UTF-8');
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
		return $this->_config['psx_url'] . '/' . $this->_config['psx_dispatch'] . $this->pagePath;
	}
}


