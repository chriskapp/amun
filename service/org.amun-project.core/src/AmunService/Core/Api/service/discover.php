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

namespace AmunService\Core\Api\Service;

use Amun\Module\ApiAbstract;
use Amun\DataFactory;
use Amun\Exception;
use PSX\Data\Message;
use PSX\Data\Record;
use PSX\Data\ResultSet;
use PSX\Cache;
use PSX\Yadis;
use PSX\Http;
use PSX\Http\GetRequest;
use PSX\Url;
use PSX\Filter;
use PSX\Sql;
use PSX\Sql\Condition;
use PSX\Sql\Join;

/**
 * Discover
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Discover extends ApiAbstract
{
	/**
	 * List all available provider
	 *
	 * @httpMethod GET
	 * @path /
	 * @nickname getProvider
	 * @responseClass PSX_Data_Record
	 */
	public function getProvider()
	{
		if($this->user->hasRight('core_service_view'))
		{
			try
			{
				$provider = $this->get->provider('string', array(new Filter\Length(3, 256), new Filter\Url()));
				$key      = empty($provider) ? 'local-service-discover' : 'remote-service-disover-' . $provider;
				$cache    = new Cache($key);

				if(($content = $cache->load()) === false)
				{
					if(empty($provider))
					{
						$services = $this->discoverLocalProvider();
					}
					else
					{
						$services = $this->discoverRemoteProvider(new Url($provider));
					}

					$cache->write(serialize($services));
				}
				else
				{
					$services = unserialize($content);
				}

				// check wich services are installed
				$installedServices = $this->getSql()->getCol('SELECT `source` FROM ' . $this->registry['table.core_service']);

				foreach($services as $k => $service)
				{
					$services[$k]['installed'] = in_array($service['source'], $installedServices);
				}

				// set response
				$resultSet = new ResultSet(count($services), 0, null, $services);

				$this->setResponse($resultSet);
			}
			catch(\Exception $e)
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

	protected function discoverLocalProvider()
	{
		$services = array();
		$path     = $this->config['amun_service_path'];
		$files    = scandir($path);

		foreach($files as $file)
		{
			$serviceDir = $path . '/' . $file;

			if($file[0] != '.' && is_dir($serviceDir))
			{
				// read config xml
				$serviceConfigFile = $serviceDir . '/config.xml';

				if(is_file($serviceConfigFile))
				{
					$serviceConfig = simplexml_load_file($serviceConfigFile);

					// get meta data
					$fields = array('name', 'description', 'link', 'author', 'license', 'version');
					$config = array_fill_keys($fields, null);

					foreach($serviceConfig->children() as $element)
					{
						if(in_array($element->getName(), $fields))
						{
							$config[$element->getName()] = (string) $element;
						}
					}

					$services[] = array(
						'source'      => $file,
						'name'        => $config['name'],
						'description' => $config['description'],
						'link'        => $config['link'],
						'author'      => $config['author'],
						'license'     => $config['license'],
						'version'     => $config['version'],
					);
				}
			}
		}

		return $services;
	}

	protected function discoverRemoteProvider(Url $url)
	{
		// check whether we have this provider
		$con = new Condition(array('url', '=', $url->__toString()));
		$id  = $this->hm->getTable('AmunService\Core\Service\Provider')->getField('id', $con);

		if(!empty($id))
		{
			$http  = new Http();
			$yadis = new Yadis($http);
			$xrds  = $yadis->discover($url);

			$providerUrl = null;

			foreach($xrds->getService() as $service)
			{
				if(in_array('http://ns.amun-project.org/2011/amun/service/marketplace', $service->getType()))
				{
					$providerUrl = new Url($service->getUri());
				}
			}

			if($providerUrl instanceof Url)
			{
				// add json format
				$providerUrl->addParam('format', 'json');

				$header   = array(
					'Accept' => 'application/json'
				);
				$request  = new GetRequest($providerUrl, $header);
				$response = $http->request($request);

				if($response->getCode() == 200)
				{
					$services = Json::decode($response->getBody());

					if(is_array($services))
					{
						return $services;
					}
					else
					{
						throw new Exception('No services available');
					}
				}
				else
				{
					throw new Exception('Invalid marketplace response');
				}
			}
			else
			{
				throw new Exception('Could not discover marketplace endpoint');
			}
		}
		else
		{
			throw new Exception('Invalid provider url');
		}
	}
}

