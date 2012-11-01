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
 * AmunService_Core_Content_Page_Record
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Content_Page
 * @version    $Revision: 880 $
 */
class AmunService_Core_Content_Page_Record extends Amun_Data_RecordAbstract
{
	const RIGHT  = 0x1;
	const NAV    = 0x2;
	const PATH   = 0x4;
	const GADGET = 0x8;

	const NORMAL = 0x1;
	const HIDDEN = 0x2;

	protected $_parent;
	protected $_service;
	protected $_expire;
	protected $_date;

	public function getFields()
	{
		$fields = parent::getFields();

		// add gadgets field
		$fields['gadgets'] = isset($this->gadgets) ? $this->gadgets : null;

		return $fields;
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

	public function setParentId($parentId)
	{
		$parentId = $this->_validate->apply($parentId, 'integer', array(new Amun_Content_Page_Filter_ParentId($this->_table)), 'parentId', 'Parent Id');

		if(!$this->_validate->hasError())
		{
			$this->parentId = $parentId;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function setServiceId($serviceId)
	{
		$serviceId = $this->_validate->apply($serviceId, 'integer', array(new Amun_Filter_Id(Amun_Sql_Table_Registry::get('Content_Service'))), 'serviceId', 'Service Id');

		if(!$this->_validate->hasError())
		{
			$this->serviceId = $serviceId;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function setRightId($rightId)
	{
		$rightId = $this->_validate->apply($rightId, 'integer', array(new Amun_Filter_Id(Amun_Sql_Table_Registry::get('User_Right'), true)), 'rightId', 'Right Id');

		if(!$this->_validate->hasError())
		{
			$this->rightId = $rightId;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function setStatus($status)
	{
		$status = $this->_validate->apply($status, 'integer', array(new Amun_Content_Page_Filter_Status()), 'status', 'Status');

		if(!$this->_validate->hasError())
		{
			$this->status = $status;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function setLoad($load)
	{
		$load = $this->_validate->apply($load, 'integer', array(new Amun_Content_Page_Filter_Load()), 'load', 'Load');

		if(!$this->_validate->hasError())
		{
			$this->load = $load;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function setSort($sort)
	{
		$this->sort = (integer) $sort;
	}

	public function setUrlTitle($urlTitle)
	{
		$urlTitle = $this->_validate->apply($urlTitle, 'string', array(new Amun_Filter_UrlTitle(), new PSX_Filter_Length(2, 32)), 'urlTitle', 'Url Title');

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
		$title = $this->_validate->apply($title, 'string', array(new PSX_Filter_Length(2, 32), new PSX_Filter_Html()), 'title', 'Title');

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

	public function setTemplate($template)
	{
		$template = $this->_validate->apply($template, 'string', array(new Amun_Content_Page_Filter_Template($this->_config)));

		if(!$this->_validate->hasError())
		{
			$this->template = $template;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function setCache($cache)
	{
		$this->cache = $cache ? 1 : 0;
	}

	public function setExpire($expire)
	{
		$expire = $this->_validate->apply($expire, 'string', array(new Amun_Filter_DateInterval()), 'expire', 'Expire');

		if(!$this->_validate->hasError())
		{
			$this->expire = $expire;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function setGadgets($gadgets)
	{
		$gadgets = array_map('intval', explode(',', $gadgets));
		$con     = new PSX_Sql_Condition(array('id', 'IN', $gadgets));

		$this->gadgets = Amun_Sql_Table_Registry::get('Content_Gadget')->getCol('id', $con);
	}

	public function getId()
	{
		return $this->_base->getUrn('content', 'page', $this->id);
	}

	public function getParent()
	{
		if($this->_parent === null)
		{
			$this->_parent = Amun_Sql_Table_Registry::get('Content_Page')->getRecord($this->parentId);
		}

		return $this->_parent;
	}

	public function getService()
	{
		if($this->_service === null)
		{
			$this->_service = Amun_Sql_Table_Registry::get('Content_Service')->getRecord($this->serviceId);
		}

		return $this->_service;
	}

	public function getExpire()
	{
		if($this->_expire === null)
		{
			$this->_expire = new DateInterval($this->expire);
		}

		return $this->_expire;
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
		return $this->_config['psx_url'] . '/' . $this->_config['psx_dispatch'] . $this->path . $this->_config['amun_page_delimiter'];
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
				$entry->addAuthor('System');
				$entry->addLink($this->getUrl(), 'alternate', 'text/html');
				$entry->setContent($this, 'application/xml');

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

			self::NORMAL => 'Normal',
			self::HIDDEN => 'Hidden',

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

	public static function getLoad($load = false)
	{
		$l = array(

			self::NAV    => 'Navigation',
			self::PATH   => 'Path',
			self::GADGET => 'Gadgets',
			self::RIGHT  => 'Right',

		);

		if($load !== false)
		{
			$load = intval($load);
			$sum  = array_sum(array_keys($l));

			if($load >= 0 && $load <= $sum)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return $l;
		}
	}
}


