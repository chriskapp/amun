<?php

class Amun_Service_Plugin_Release_Table extends Amun_Sql_TableAbstract
{
	public function getConnections()
	{
		return array(

			'pluginId' => $this->registry['table.service_plugin'],
			'userId'   => $this->registry['table.user_account'],

		);
	}

	public function getName()
	{
		return $this->registry['table.service_plugin_release'];
	}

	public function getColumns()
	{
		return array(

			'id' => self::TYPE_INT | 10 | self::PRIMARY_KEY,
			'globalId' => self::TYPE_VARCHAR | 36,
			'pluginId' => self::TYPE_INT | 10,
			'userId' => self::TYPE_INT | 10,
			'status' => self::TYPE_INT | 10,
			'version' => self::TYPE_VARCHAR | 32,
			'href' => self::TYPE_VARCHAR | 256,
			'date' => self::TYPE_DATETIME,

		);
	}
}

