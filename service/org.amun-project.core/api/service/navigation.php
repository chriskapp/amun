<?php
/*
 *  $Id: tree.php 856 2012-09-28 20:27:35Z k42b3.x@googlemail.com $
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

namespace core\api\service;

use Amun\Module\ApiAbstract;
use Amun\DataFactory;
use PSX\Data\Message;
use PSX\Data\Record;
use PSX\Sql;
use PSX\Sql\Join;

/**
 * tree
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @package    api
 * @subpackage content_page
 * @version    $Revision: 856 $
 */
class navigation extends ApiAbstract
{
	/**
	 * Returns a navigation structure for all installed services
	 *
	 * @httpMethod GET
	 * @path /
	 * @nickname getNavigation
	 * @responseClass PSX_Data_Record
	 */
	public function getNavigation()
	{
		if($this->user->hasRight('core_view'))
		{
			try
			{
				$this->setResponse(new Record('navigation', array('item' => $this->buildNavigation())));
			}
			catch(Exception $e)
			{
				$msg = new Message($e->getMessage(), false);

				$this->setResponse($msg);
			}
		}
		else
		{
			$msg = new Message('Access not allowed', false);

			$this->setResponse($msg, null, $this->user->isAnonymous() ? 401 : 403);
		}
	}

	private function buildNavigation()
	{
		$sql = <<<SQL
SELECT

	`service`.`id`,
	`service`.`name`,
	`service`.`type`

	FROM {$this->registry['table.core_service']} `service`

		ORDER BY `service`.`name` ASC
SQL;

		$result   = $this->sql->getAll($sql);
		$services = $this->getXrds();
		$nav      = array();

		foreach($result as $row)
		{
			$children = $this->buildSubNavigation($row['id'], $services);

			if(!empty($children))
			{
				$nav[] = array(
					'name'     => ucfirst($row['name']),
					'type'     => $row['type'],
					'children' => $children,
				);
			}
		}

		return $nav;
	}

	private function buildSubNavigation($serviceId, $services)
	{
		$nav = array();

		foreach($services as $service)
		{
			if($service['serviceId'] == $serviceId && in_array('http://ns.amun-project.org/2011/amun/data/1.0', $service['types']))
			{
				$nav[] = array(
					'name'     => $service['name'],
					'endpoint' => $service['endpoint'],
					'type'     => end($service['types']),
				);
			}
		}

		return $nav;
	}

	private function getEndpointName($path)
	{
		$path  = trim($path, '/');
		$parts = array_map('ucfirst', explode('/', $path));

		unset($parts[0]);

		if(empty($parts))
		{
			$name = 'Main';
		}
		else
		{
			$name = implode(' / ', $parts);
		}

		return $name;
	}

	private function getXrds()
	{
		$result = DataFactory::getTable('Xrds_Type')
			->select(array('apiId', 'type'))
			->join(Join::INNER, DataFactory::getTable('Xrds')
				->select(array('serviceId', 'priority', 'endpoint'), 'api')
			)
			->orderBy('apiId', Sql::SORT_ASC)
			->getAll();

		$baseUrl  = $this->config['psx_url'] . '/' . $this->config['psx_dispatch'];
		$services = array();

		foreach($result as $row)
		{
			if(!isset($services[$row['apiId']]))
			{
				$services[$row['apiId']] = array(
					'priority'  => (integer) $row['apiPriority'],
					'name'      => $this->getEndpointName($row['apiEndpoint']),
					'endpoint'  => $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api' . $row['apiEndpoint'],
					'serviceId' => $row['apiServiceId'],
					'types'     => array(),
				);
			}

			$services[$row['apiId']]['types'][] = $row['type'];
		}

		return $services;
	}
}

