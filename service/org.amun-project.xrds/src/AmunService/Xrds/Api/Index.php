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

namespace AmunService\Xrds\Api;

use Amun\Module\ApiAbstract;
use Amun\Exception;
use PSX\Data\Message;
use PSX\Sql;
use PSX\Sql\Join;
use XMLWriter;
use PSX\Xrds\Writer;

/**
 * Index
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Index extends ApiAbstract
{
	private $writer;

	/**
	 * Returns all registered services
	 *
	 * @httpMethod GET
	 * @path /
	 * @nickname getXrds
	 * @responseClass PSX_Data_ResultSet
	 */
	public function getXrds()
	{
		try
		{
			$this->writer = new Writer();

			$result   = $this->getHandler('AmunService\Xrds\Type')->getAll(array(), 0, 1024);
			$baseUrl  = $this->config['psx_url'] . '/' . $this->config['psx_dispatch'];
			$services = array();

			foreach($result as $row)
			{
				if(!isset($services[$row['apiId']]))
				{
					$services[$row['apiId']] = array(
						'priority' => (integer) $row['apiPriority'],
						'endpoint' => $row['apiEndpoint'],
						'types'    => array(),
					);
				}

				$services[$row['apiId']]['types'][] = $row['type'];
			}

			foreach($services as $service)
			{
				$uri      = $baseUrl . 'api' . $service['endpoint'];
				$priority = $service['priority'] > 0 ? floatval($service['priority']) : null;

				$this->writer->addService($uri, $service['types'], $priority);
			}

			$this->writer->output();
		}
		catch(\Exception $e)
		{
			$msg = new Message($e->getMessage(), false);

			$this->setResponse($msg);
		}
	}
}

