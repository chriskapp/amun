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
class AmunService_User_Right_ConfigListener extends Amun_Module_ListenerAbstract
{
	public function notify(AmunService_Core_Service_Record $record, DOMDocument $config)
	{
		$permissions = $config->getElementsByTagName('permissions')->item(0);

		if($permissions !== null)
		{
			PSX_Log::info('Create user permissions');

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
								'name'        => $name,
								'description' => $desc,
							));

							PSX_Log::info('> Created permission "' . $name . '"');
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

