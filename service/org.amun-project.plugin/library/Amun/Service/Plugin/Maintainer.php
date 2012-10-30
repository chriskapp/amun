<?php

class Amun_Service_Plugin_Maintainer extends Amun_Data_RecordAbstract
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
		$pluginId = $this->_validate->apply($pluginId, 'integer', array(new Amun_Filter_Id(Amun_Sql_Table_Registry::get('Service_Plugin'))), 'pluginId', 'Plugin Id');

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
		$userId = $this->_validate->apply($userId, 'integer', array(new Amun_Filter_Id(Amun_Sql_Table_Registry::get('User_Account'))), 'userId', 'User Id');

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
			$this->_plugin = Amun_Sql_Table_Registry::get('Service_Plugin')->getRecord($this->pluginId);
		}

		return $this->_plugin;
	}

	public function getUser()
	{
		if($this->_user === null)
		{
			$this->_user = Amun_Sql_Table_Registry::get('User_Account')->getRecord($this->userId);
		}

		return $this->_user;
	}
}
