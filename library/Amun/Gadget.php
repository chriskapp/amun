<?php
/*
 * amun
 * A social content managment system based on the psx framework. For
 * the current version and informations visit <http://amun.phpsx.org>
 *
 * Copyright (c) 2010-2013 Christoph Kappestein <k42b3.x@gmail.com>
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

namespace Amun;

use Amun\Gadget\Args;

/**
 * Gadget
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Gadget
{
	public $id;
	public $serviceId;
	public $type;
	public $name;
	public $title;
	public $path;
	public $cache;
	public $expire;
	public $param;

	public $applicationPath;

	private $config;
	private $sql;
	private $registry;
	private $user;
	private $body;

	public function __construct($gadgetId, Registry $registry, User $user)
	{
		$this->config   = $registry->getConfig();
		$this->sql      = $registry->getSql();
		$this->registry = $registry;
		$this->user     = $user;

		$sql = <<<SQL
SELECT
	`gadget`.`id`          AS `gadgetId`,
	`gadget`.`serviceId`   AS `gadgetServiceId`,
	`gadget`.`rightId`     AS `gadgetRightId`,
	`gadget`.`type`        AS `gadgetType`,
	`gadget`.`name`        AS `gadgetName`,
	`gadget`.`title`       AS `gadgetTitle`,
	`gadget`.`path`        AS `gadgetPath`,
	`gadget`.`param`       AS `gadgetParam`,
	`gadget`.`cache`       AS `gadgetCache`,
	`gadget`.`expire`      AS `gadgetExpire`,
	`gadget`.`date`        AS `gadgetDate`,
	`service`.`source`     AS `serviceSource`
FROM 
	{$this->registry['table.content_gadget']} `gadget`
INNER JOIN 
	{$this->registry['table.core_service']} `service`
	ON `gadget`.`serviceId` = `service`.`id`
WHERE 
	`gadget`.`id` = ?
SQL;

		$row = $this->sql->getRow($sql, array($gadgetId));

		if(!empty($row))
		{
			if(!empty($row['gadgetRightId']) && !$this->user->hasRightId($row['gadgetRightId']))
			{
				throw new Exception('Access not allowed', 401);
			}

			$this->id          = $row['gadgetId'];
			$this->serviceId   = $row['gadgetServiceId'];
			$this->type        = $row['gadgetType'];
			$this->name        = $row['gadgetName'];
			$this->title       = $row['gadgetTitle'];
			$this->path        = $row['gadgetPath'];
			$this->param       = $row['gadgetParam'];
			$this->cache       = $row['gadgetCache'];
			$this->expire      = $row['gadgetExpire'];
			$this->date        = $row['gadgetDate'];

			$this->application = $row['serviceSource'];
		}
		else
		{
			throw new Exception('Invalid gadget');
		}
	}

	public function getServiceId()
	{
		return $this->serviceId;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * Returns the settings for the gadget
	 *
	 * @return Amun_Gadget_Args
	 */
	public function getArgs()
	{
		return Args::parse($this->param);
	}
}

