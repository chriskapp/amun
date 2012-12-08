<?php
/*
 *  $Id: Log.php 635 2012-05-01 19:46:37Z k42b3.x@googlemail.com $
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
 * AmunService_Xrds_Listener
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    AmunService_Xrds
 * @version    $Revision: 635 $
 */
class AmunService_Xrds_ConfigListener extends Amun_Module_ListenerAbstract
{
	public function notify(AmunService_Core_Service_Record $record, DOMDocument $config)
	{
		$api = $config->getElementsByTagName('api')->item(0);

		if($api !== null)
		{
			PSX_Log::info('Create api');

			try
			{
				$services = $api->childNodes;

				for($i = 0; $i < $services->length; $i++)
				{
					$service = $services->item($i);

					if(!($service instanceof DOMElement))
					{
						continue;
					}

					if($service->nodeName == 'service')
					{
						$types = $service->getElementsByTagName('type');
						$uri   = $service->getElementsByTagName('uri')->item(0);

						if($uri instanceof DOMElement)
						{
							$endpoint = rtrim($record->path . $uri->nodeValue, '/');

							$this->sql->insert($this->registry['table.xrds'], array(
								'serviceId' => $record->id,
								'priority'  => 0,
								'endpoint'  => $endpoint,
							));

							$apiId = $this->sql->getLastInsertId();

							foreach($types as $type)
							{
								$this->sql->insert($this->registry['table.xrds_type'], array(
									'apiId' => $apiId,
									'type'  => $type->nodeValue,
								));
							}
						}
					}
				}
			}
			catch(Exception $e)
			{
				PSX_Log::error($e->getMessage());
			}
		}
	}
}

