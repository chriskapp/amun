<?php

class Amun_Service_Plugin_Release extends Amun_Data_RecordAbstract
{
	const STABLE = 'STABLE';
	const BETA   = 'BETA';
	const ALPHA  = 'ALPHA';

	protected $_plugin;
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

	public function setStatus($status)
	{
		$this->status = $status;
	}

	public function setVersion($version)
	{
		$this->version = $version;
	}

	public function setHref($href)
	{
		$href = $this->_validate->apply($href, 'string', array(new PSX_Filter_Length(3, 256), new PSX_Filter_Url()), 'href', 'Href');

		if(!$this->_validate->hasError())
		{
			$this->href = $href;
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

	public function getDate()
	{
		if($this->_date === null)
		{
			$this->_date = new DateTime($this->date, $this->_registry['core.default_timezone']);
		}

		return $this->_date;
	}

	public static function getStatus($status = false)
	{
		$s = array(

			self::STABLE => 'Stable',
			self::BETA   => 'Beta',
			self::ALPHA  => 'Alpha',

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


