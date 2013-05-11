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

namespace AmunService\Mail;

use Amun\Data\ListenerAbstract;
use Amun\DataFactory;
use AmunService\Core\Service;
use PSX\Log;
use DOMDocument;
use DOMElement;

/**
 * Adds the option to insert an mail template to the service config
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class ConfigListener extends ListenerAbstract
{
	public function notify(Service\Record $record, DOMDocument $config)
	{
		$mail = $config->getElementsByTagName('mail')->item(0);

		if($mail !== null)
		{
			Log::info('Create mail');

			try
			{
				$templates = $mail->childNodes;

				for($i = 0; $i < $templates->length; $i++)
				{
					$template = $templates->item($i);

					if(!($template instanceof DOMElement))
					{
						continue;
					}

					if($template->nodeName == 'template')
					{
						$name    = $template->getAttribute('name');
						$from    = $template->getAttribute('from');
						$subject = $template->getAttribute('subject');
						$values  = $template->getAttribute('values');
						$text    = $template->getElementsByTagName('text')->item(0);
						$html    = $template->getElementsByTagName('html')->item(0);

						if($text instanceof DOMElement && $html instanceof DOMElement)
						{
							$handler = DataFactory::get('Mail', $this->user);

							$record = $handler->getRecord();
							$record->setName($name);
							$record->setFrom($from);
							$record->setSubject($subject);
							$record->setValues($values);
							$record->setText($text->nodeValue);
							$record->setHtml($html->nodeValue);

							$handler->create($record);
						}
					}
				}
			}
			catch(\Exception $e)
			{
				Log::error($e->getMessage());
			}
		}
	}
}
