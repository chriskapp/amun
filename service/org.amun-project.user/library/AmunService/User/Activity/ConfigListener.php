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

namespace AmunService\User\Activity;

use Amun\Data\ListenerAbstract;
use Amun\Exception;
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
		$activity = $config->getElementsByTagName('activity')->item(0);

		if($activity !== null)
		{
			$logger->info('Create user activity template');

			try
			{
				$templates = $activity->childNodes;

				for($i = 0; $i < $templates->length; $i++)
				{
					$template = $templates->item($i);

					if(!($template instanceof DOMElement))
					{
						continue;
					}

					if($template->nodeName == 'template')
					{
						$type    = $template->getAttribute('type');
						$verb    = $template->getAttribute('verb');
						$table   = $template->getAttribute('table');
						$path    = $template->getAttribute('path');
						$summary = $template->nodeValue;

						if(isset($this->registry['table.' . $table]))
						{
							$table = $this->registry['table.' . $table];
						}
						else
						{
							throw new Exception('Invalid table ' . $table);
						}

						if(!empty($type) && !empty($verb) && !empty($table) && !empty($summary))
						{
							$this->sql->insert($this->registry['table.user_activity_template'], array(
								'type'    => $type,
								'verb'    => $verb,
								'table'   => $table,
								'path'    => $path,
								'summary' => $summary,
							));

							$logger->info('> Created user activity template');
							$logger->info($summary);
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

