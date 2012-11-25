<?php
/*
 *  $Id: Page.php 845 2012-09-16 17:50:03Z k42b3.x@googlemail.com $
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
 * Amun_Service_Pipe
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Service_Pipe
 * @version    $Revision: 845 $
 */
class AmunService_Pipe_Record extends Amun_Data_RecordAbstract
{
	protected $_page;
	protected $_user;
	protected $_media;
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
		$pageId = $this->_validate->apply($pageId, 'integer', array(new Amun_Filter_Id(Amun_Sql_Table_Registry::get('Core_Content_Page'))), 'pageId', 'Page Id');

		if(!$this->_validate->hasError())
		{
			$this->pageId = $pageId;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function setMediaId($mediaId)
	{
		$mediaId = $this->_validate->apply($mediaId, 'integer', array(new Amun_Filter_Id(Amun_Sql_Table_Registry::get('Core_Content_Media'))), 'mediaId', 'Media Id');

		if(!$this->_validate->hasError())
		{
			$this->mediaId = $mediaId;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function getId()
	{
		return $this->_base->getUrn('service', 'media', $this->id);
	}

	public function getContent()
	{
		$path = $this->_registry['core.media_path'] . '/' . $this->mediaPath;
		$ext  = pathinfo($path, PATHINFO_EXTENSION);

		if(!PSX_File::exists($path))
		{
			throw new Amun_Exception('File not found', 404);
		}

		switch($ext)
		{
			// html
			case 'htm':
			case 'html':
				return file_get_contents($path);
				break;

			// text
			case 'txt':
				return '<pre>' . file_get_contents($path) . '</pre>';
				break;

			// markdown
			case 'md':
				return PSX_Util_Markdown::decode(file_get_contents($path));
				break;

			// source code files
			case 'bsh':
			case 'c':
			case 'cc':
			case 'cpp':
			case 'cs':
			case 'csh':
			case 'css':
			case 'cyc':
			case 'cv':
			case 'java':
			case 'js':
			case 'm':
			case 'mxml':
			case 'perl':
			case 'php':
			case 'pl':
			case 'pm':
			case 'py':
			case 'rb':
			case 'sh':
			case 'sql':
			case 'xml':
			case 'xsl':
				return '<pre class="prettyprint">' . htmlspecialchars(file_get_contents($path)) . '</pre>';
				break;

			default:
				return 'Unknown file extension';
				break;
		}
	}

	public function getPage()
	{
		if($this->_page === null)
		{
			$this->_page = Amun_Sql_Table_Registry::get('Core_Content_Page')->getRecord($this->pageId);
		}

		return $this->_page;
	}

	public function getUser()
	{
		if($this->_user === null)
		{
			$this->_user = Amun_Sql_Table_Registry::get('Core_User_Account')->getRecord($this->userId);
		}

		return $this->_user;
	}

	public function getMedia()
	{
		if($this->_media === null)
		{
			$this->_media = Amun_Sql_Table_Registry::get('Core_Content_Media')->getRecord($this->mediaId);
		}

		return $this->_media;
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


