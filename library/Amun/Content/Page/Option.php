<?php
/*
 *  $Id: Option.php 683 2012-06-03 11:52:32Z k42b3.x@googlemail.com $
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
 * Amun_Content_Page_Option
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Content_Page
 * @version    $Revision: 683 $
 */
class Amun_Content_Page_Option extends Amun_Data_RecordAbstract
{
	protected $_srcPage;
	protected $_destPage;

	public function getName()
	{
		return 'option';
	}

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

	public function setOptionId($optionId)
	{
		$optionId = $this->_validate->apply($optionId, 'integer', array(new Amun_Filter_Id(Amun_Sql_Table_Registry::get('Content_Service_Option'))), 'optionId', 'Option Id');

		if(!$this->_validate->hasError())
		{
			$this->optionId = $optionId;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function setRightId($rightId)
	{
		$rightId = $this->_validate->apply($rightId, 'integer', array(new Amun_Filter_Id(Amun_Sql_Table_Registry::get('User_Right'))), 'rightId', 'Right Id');

		if(!$this->_validate->hasError())
		{
			$this->rightId = $rightId;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function setSrcPageId($srcPageId)
	{
		$srcPageId = $this->_validate->apply($srcPageId, 'integer', array(new Amun_Filter_Id(Amun_Sql_Table_Registry::get('Content_Page'))), 'srcPageId', 'Source Page Id');

		if(!$this->_validate->hasError())
		{
			$this->srcPageId = $srcPageId;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function setDestPageId($destPageId)
	{
		$destPageId = $this->_validate->apply($destPageId, 'integer', array(new Amun_Filter_Id(Amun_Sql_Table_Registry::get('Content_Page'))), 'destPageId', 'Destination Page Id');

		if(!$this->_validate->hasError())
		{
			$this->destPageId = $destPageId;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function setName($name)
	{
		$name = $this->_validate->apply($name, 'string', array(new PSX_Filter_Length(2, 32)), 'name', 'Name');

		if(!$this->_validate->hasError())
		{
			$this->name = $name;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function setHref($href)
	{
		$href = $this->_validate->apply($href, 'string', array(new PSX_Filter_Length(0, 256)), 'href', 'Href');

		if(!$this->_validate->hasError())
		{
			$this->href = $href;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function getId()
	{
		return $this->_base->getUrn('content', 'page', 'option', $this->id);
	}

	public function getSrcPage()
	{
		if($this->_srcPage === null)
		{
			$this->_srcPage = Amun_Sql_Table_Registry::get('Content_Page')->getRecord($this->srcPageId);
		}

		return $this->_srcPage;
	}

	public function getDestPage()
	{
		if($this->_destPage === null)
		{
			$this->_destPage = Amun_Sql_Table_Registry::get('Content_Page')->getRecord($this->destPageId);
		}

		return $this->_destPage;
	}
}



