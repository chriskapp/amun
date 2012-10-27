<?php

class Amun_Service_Plugin_Maintainer_Table extends Amun_Sql_TableAbstract
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
		return $this->registry['table.service_plugin_maintainer'];
	}

	public function getColumns()
	{
		return array(

			'id' => self::TYPE_INT | 10 | self::PRIMARY_KEY,
			'pluginId' => self::TYPE_INT | 10,
			'userId' => self::TYPE_INT | 10,

		);
	}
}

