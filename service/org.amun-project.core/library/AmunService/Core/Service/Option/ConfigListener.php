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
 * AmunService_Core_Service_Option_ConfigListener
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    AmunService_Xrds
 * @version    $Revision: 635 $
 */
class AmunService_Core_Service_Option_ConfigListener extends Amun_Module_ListenerAbstract
{
	public function notify(AmunService_Core_Service_Record $record, DOMDocument $config)
	{
		$navigation = $config->getElementsByTagName('navigation')->item(0);

		if($navigation !== null)
		{
			PSX_Log::info('Navigation option');

			try
			{
				$options = $navigation->childNodes;

				for($i = 0; $i < $options->length; $i++)
				{
					$option = $options->item($i);

					if(!($option instanceof DOMElement))
					{
						continue;
					}

					if($option->nodeName == 'option')
					{
						$name = $option->getAttribute('name');

						if(!empty($name))
						{
							$this->sql->insert($this->registry['table.core_service_option'], array(
								'serviceId' => $record->id,
								'name'      => $name,
							));

							PSX_Log::info('> Created navigation option "' . $name . '"');
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
