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

namespace AmunService\User\Right;

use Amun\Data\ListenerAbstract;
use AmunService\Core\Service;
use DOMDocument;
use DOMElement;
use Monolog\Logger;
use PSX\Log;

/**
 * ConfigListener
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class ConfigListener extends ListenerAbstract
{
	public function notify(Service\Record $record, DOMDocument $config, Logger $logger)
	{
		$permissions = $config->getElementsByTagName('permissions')->item(0);

		if($permissions !== null)
		{
			$logger->info('Create user permissions');

			try
			{
				$namespace = strtolower($record->namespace);
				$perms     = $permissions->childNodes;

				for($i = 0; $i < $perms->length; $i++)
				{
					$perm = $perms->item($i);

					if(!($perm instanceof DOMElement))
					{
						continue;
					}

					if($perm->nodeName == 'perm')
					{
						$name = $perm->getAttribute('name');
						$desc = $perm->getAttribute('description');

						if(!empty($name) && !empty($desc))
						{
							$name = $namespace . '_' . $name;

							$this->sql->insert($this->registry['table.user_right'], array(
								'serviceId'   => $record->id,
								'name'        => $name,
								'description' => $desc,
							));

							$logger->info('> Created permission "' . $name . '"');
						}
					}
				}
			}
			catch(\Exception $e)
			{
				$logger->error($e->getMessage());
			}
		}
	}
}

