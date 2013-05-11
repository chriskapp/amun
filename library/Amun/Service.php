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

use AmunService\Core\Service\Record;

/**
 * Service
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Service
{
	public $id;
	public $status;
	public $source;
	public $name;
	public $path;
	public $namespace;
	public $type;
	public $link;
	public $author;
	public $license;
	public $version;
	public $date;

	private $config;
	private $sql;
	private $registry;

	private $_description;

	public function __construct($id, Registry $registry)
	{
		$this->config   = $registry->getConfig();
		$this->sql      = $registry->getSql();
		$this->registry = $registry;

		$status = Record::NORMAL;
		$sql    = <<<SQL
SELECT
	`service`.`id`        AS `serviceId`,
	`service`.`status`    AS `serviceStatus`,
	`service`.`source`    AS `serviceSource`,
	`service`.`name`      AS `serviceName`,
	`service`.`path`      AS `servicePath`,
	`service`.`namespace` AS `serviceNamespace`,
	`service`.`type`      AS `serviceType`,
	`service`.`version`   AS `serviceVersion`,
	`service`.`date`      AS `serviceDate`
FROM 
	{$this->registry['table.core_service']} `service`
WHERE 
	`service`.`id` = ?
SQL;

		$row = $this->sql->getRow($sql, array($id));

		if(!empty($row))
		{
			$this->id        = $row['serviceId'];
			$this->status    = $row['serviceStatus'];
			$this->source    = $row['serviceSource'];
			$this->name      = $row['serviceName'];
			$this->path      = $row['servicePath'];
			$this->namespace = $row['serviceNamespace'];
			$this->type      = $row['serviceType'];
			$this->version   = $row['serviceVersion'];
			$this->date      = $row['serviceDate'];
		}
		else
		{
			throw new Exception('Invalid service');
		}
	}

	public function getApiEndpoint()
	{
		return $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api' . $this->path;
	}
}
