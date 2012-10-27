<?php

class Amun_System_Mail_Table extends Amun_Sql_TableAbstract
{
	public function getConnections()
	{
		return array();
	}

	public function getName()
	{
		return $this->registry['table.system_mail'];
	}

	public function getColumns()
	{
		return array(

			'id' => self::TYPE_INT | 10 | self::PRIMARY_KEY,
			'name' => self::TYPE_VARCHAR | 32,
			'from' => self::TYPE_VARCHAR | 64,
			'subject' => self::TYPE_VARCHAR | 256,
			'text' => self::TYPE_TEXT,
			'html' => self::TYPE_TEXT,
			'values' => self::TYPE_VARCHAR | 512,

		);
	}
}

