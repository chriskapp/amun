<?php
/*
 *  $Id: Handler.php 662 2012-05-12 17:33:19Z k42b3.x@googlemail.com $
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
 * Amun_Service_Plugin_Handler
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Service_Plugin
 * @version    $Revision: 662 $
 */
class AmunService_Plugin_Maintainer_Record extends Amun_Data_RecordAbstract
{
	protected $_plugin;
	protected $_user;

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

	public function setPluginId($pluginId)
	{
		$pluginId = $this->_validate->apply($pluginId, 'integer', array(new Amun_Filter_Id(Amun_Sql_Table_Registry::get('Plugin'))), 'pluginId', 'Plugin Id');

		if(!$this->_validate->hasError())
		{
			$this->pluginId = $pluginId;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function setUserId($userId)
	{
		$userId = $this->_validate->apply($userId, 'integer', array(new Amun_Filter_Id(Amun_Sql_Table_Registry::get('Core_User_Account'))), 'userId', 'User Id');

		if(!$this->_validate->hasError())
		{
			$this->userId = $userId;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function getPlugin()
	{
		if($this->_plugin === null)
		{
			$this->_plugin = Amun_Sql_Table_Registry::get('Plugin')->getRecord($this->pluginId);
		}

		return $this->_plugin;
	}

	public function getUser()
	{
		if($this->_user === null)
		{
			$this->_user = Amun_Sql_Table_Registry::get('Core_User_Account')->getRecord($this->userId);
		}

		return $this->_user;
	}
}
