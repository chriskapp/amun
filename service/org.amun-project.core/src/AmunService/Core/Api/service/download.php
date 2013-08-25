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

namespace core\api\service;

use Amun\Module\ApiAbstract;
use Amun\DataFactory;
use PSX\Data\Message;
use PSX\Data\Record;
use PSX\Yadis;
use PSX\Http;
use PSX\Http\GetRequest;
use PSX\Sql;
use PSX\Sql\Join;
use ZipArchive;

/**
 * download
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class download extends ApiAbstract
{
	/**
	 * Downloads an given service from an remote provider
	 *
	 * @httpMethod POST
	 * @path /
	 * @nickname downloadService
	 * @responseClass PSX_Data_Record
	 */
	public function downloadService()
	{
		if($this->user->hasRight('core_service_add'))
		{
			try
			{
				$source   = $this->post->source('string');
				$provider = $this->post->provider('string', array(new Filter\Length(3, 256), new Filter\Url()));

				if(empty($provider) || empty($source))
				{
					throw new Exception('Invalid arguments');
				}

				// check whether we can write to the service directory
				if(!is_writable($this->config['amun_service_path']))
				{
					throw new Exception('Service folder not writeable');
				}

				// check whether we have this provider
				$url = new Url($provider);
				$con = new Condition(array('url', '=', $url->__toString()));
				$id  = $this->hm->getTable('AmunService\Core\Service\Provider')->getField('id', $con);

				if(!empty($id))
				{
					$this->downloadFromProvider($url, $source);

					// successful response
					$msg = new Message('Download successful', true);

					$this->setResponse($msg);
				}
				else
				{
					throw new Exception('Invalid provider');
				}
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

	protected function downloadFromProvider(Url $url, $source)
	{
		$http  = new Http();
		$yadis = new Yadis($http);
		$xrds  = $yadis->discover($url);

		$providerUrl = null;

		foreach($xrds->getService() as $service)
		{
			if(in_array('http://ns.amun-project.org/2011/amun/service/marketplace/get', $service->getType()))
			{
				$providerUrl = new Url($service->getUri());
			}
		}

		if($providerUrl instanceof Url)
		{
			// add json format
			$providerUrl->addParam('source', $source);

			$request  = new GetRequest($providerUrl);
			$response = $http->request($request);

			if($response->getCode() == 200)
			{
				$path  = $this->config['amun_service_path'] . '/' . md5($source) . '.zip';
				$bytes = file_put_contents($path, $response->getBody());

				if(is_file($path) && md5_file($path) == $response->getHeader('Content-MD5'))
				{
					// extract zip archive
					$zip = new ZipArchive();

					if($zip->open($path))
					{
						$zip->extractTo($this->config['amun_service_path']);
						$zip->close();

						unlink($path);

						return true;
					}
					else
					{
						throw new Exception('Could not open zip file');
					}
				}
				else
				{
					unlink($path);

					throw new Exception('Corrupted file');
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
}

