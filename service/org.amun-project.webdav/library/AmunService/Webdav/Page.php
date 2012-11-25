<?php
/*
 *  $Id: Page.php 787 2012-07-04 20:10:48Z k42b3.x@googlemail.com $
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
 * Amun_Service_Webdav_Page
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Service_Webdav
 * @version    $Revision: 787 $
 */
class AmunService_Webdav_Page extends Sabre_DAV_Collection
{
	protected $base;
	protected $config;
	protected $sql;
	protected $registry;
	protected $user;

	protected $page;

	public function __construct($id)
	{
		$this->base     = Amun_Base::getInstance();
		$this->config   = $this->base->getConfig();
		$this->sql      = $this->base->getSql();
		$this->registry = $this->base->getRegistry();
		$this->user     = $this->base->getUser();

		$sql = <<<SQL
SELECT

	`page`.`id`       AS `id`,
	`page`.`urlTitle` AS `urlTitle`,
	`page`.`rightId`  AS `rightId`,
	`page`.`date`     AS `date`,
	`service`.`id`    AS `serviceId`,
	`service`.`name`  AS `serviceName`

	FROM {$this->registry['table.core_content_page']} `page`

		INNER JOIN {$this->registry['table.core_content_service']} `service`

		ON `page`.`serviceId` = `service`.`id`

			WHERE `page`.`id` = ?
SQL;

		$this->page = $this->sql->getRow($sql, array($id));

		if(empty($this->page))
		{
			throw new Sabre_DAV_Exception_FileNotFound('Page doesnt exist');
		}

		if(!$this->user->hasRightId($this->page['rightId']))
		{
			throw new Sabre_DAV_Exception_Forbidden('Access not allowed');
		}
	}

	public function getName()
	{
		return $this->page['urlTitle'];
	}

	public function getLastModified()
	{
		return strtotime($this->page['date']);
	}

	public function getChild($name)
	{
		$pos = strpos($name, '.');

		if($pos === false)
		{
			$row = $this->sql->getRow('SELECT id FROM ' . $this->registry['table.core_content_page'] . ' WHERE urlTitle = ?', array($name));

			if(!empty($row))
			{
				return new AmunService_Webdav_Page($row['id']);
			}
			else
			{
				throw new Sabre_DAV_Exception_FileNotFound('Page doesnt exist');
			}
		}
		else
		{
			$name = substr($name, 0, $pos);

			list($service, $id) = explode('_', $name);

			$provider = new Amun_DataProvider($this->page['serviceName'], $this->registry, $this->user);
			$table    = $provider->getTable();
			$row      = null;

			if($table !== null)
			{
				$row = $table->select(array('*'))
					->where('id', '=', $id)
					->getRow(PSX_Sql::FETCH_OBJECT);
			}

			if($row instanceof PSX_Data_RecordInterface)
			{
				return new AmunService_Webdav_File($this->page['serviceName'], $row);
			}
			else
			{
				throw new Sabre_DAV_Exception_FileNotFound('Record doesnt exist');
			}
		}
	}

	public function getChildren()
	{
		$children = array();

		// load page nodes
		$sql = <<<SQL
SELECT

	`page`.`id`      AS `id`,
	`service`.`name` AS `serviceName`

	FROM {$this->registry['table.core_content_page']} `page`

		INNER JOIN {$this->registry['table.core_content_service']} `service`

		ON `page`.`serviceId` = `service`.`id`

			WHERE parentId = ?
SQL;

		$result = $this->sql->getAll($sql, array($this->page['id']));

		foreach($result as $row)
		{
			if($this->user->hasRight($row['serviceName'] . '_view'))
			{
				$children[] = new AmunService_Webdav_Page($row['id']);
			}
		}

		// load record nodes
		$name = 'service_' . $this->page['serviceName'];

		if(isset($this->registry['table.' . $name]))
		{
			$table  = Amun_Sql_Table_Registry::get($name);
			$result = $table->select(array('*'))
				->where('pageId', '=', $this->page['id'])
				->orderBy('date', PSX_Sql::SORT_DESC)
				->limit(0, 16)
				->getAll(PSX_Sql::FETCH_OBJECT);

			foreach($result as $row)
			{
				$children[] = new AmunService_Webdav_File($this->page['serviceName'], $row);
			}
		}

		return $children;
	}

	public function childExists($name)
	{
		$con = new PSX_Sql_Condition();
		$con->add('parentId', '=', $this->page['id']);
		$con->add('urlTitle', '=', $name);

		return $this->sql->count($this->registry['table.core_content_page'], $con) > 0;
	}
}

