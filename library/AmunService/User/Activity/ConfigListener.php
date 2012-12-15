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
class AmunService_User_Activity_ConfigListener extends Amun_Module_ListenerAbstract
{
	public function notify(AmunService_Core_Service_Record $record, DOMDocument $config)
	{
		$activity = $config->getElementsByTagName('activity')->item(0);

		if($activity !== null)
		{
			PSX_Log::info('Create user activity template');

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
							throw new PSX_Exception('Invalid table ' . $table);
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

							PSX_Log::info('> Created user activity template');
							PSX_Log::info($summary);
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

