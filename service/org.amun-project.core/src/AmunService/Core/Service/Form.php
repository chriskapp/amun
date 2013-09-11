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

namespace AmunService\Core\Service;

use Amun\Data\FormAbstract;
use Amun\Exception;
use Amun\Form as AmunForm;
use Amun\Form\Element\Panel;
use Amun\Form\Element\Reference;
use Amun\Form\Element\Input;
use Amun\Form\Element\TabbedPane;
use Amun\Form\Element\Textarea;
use Amun\Form\Element\Captcha;
use Amun\Form\Element\Select;
use PharData;
use SimpleXMLElement;

/**
 * Form
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Form extends FormAbstract
{
	public function create()
	{
		$form = new AmunForm('POST', $this->url);


		$panel = new Panel('service', 'Service');


		$path = new Select('source', 'Source');
		$path->setOptions($this->getInstallableService());

		$panel->add($path);


		if($this->user->isAnonymous() || $this->user->hasInputExceeded())
		{
			$captcha = new Captcha('captcha', 'Captcha');
			$captcha->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/core/captcha');

			$panel->add($captcha);
		}


		$form->setContainer($panel);


		return $form;
	}

	public function update($id)
	{
		throw new Exception('Update a service record is not possible');
	}

	public function delete($id)
	{
		$record = $this->hm->getHandler('AmunService\Core\Service')->getRecord($id);


		$form = new AmunForm('DELETE', $this->url);


		$panel = new Panel('service', 'Service');


		$id = new Input('id', 'ID', $record->id);
		$id->setType('hidden');

		$panel->add($id);


		$name = new Input('name', 'Name', $record->name);
		$name->setType('text');
		$name->setDisabled(true);

		$panel->add($name);


		$type = new Input('type', 'Type', $record->type);
		$type->setType('text');
		$type->setDisabled(true);

		$panel->add($type);


		$link = new Input('link', 'Link', $record->link);
		$link->setType('text');
		$link->setDisabled(true);

		$panel->add($link);


		$author = new Input('author', 'Author', $record->author);
		$author->setType('text');
		$author->setDisabled(true);

		$panel->add($author);


		$license = new Input('license', 'License', $record->license);
		$license->setType('text');
		$license->setDisabled(true);

		$panel->add($license);


		$version = new Input('version', 'Version', $record->version);
		$version->setType('text');
		$version->setDisabled(true);

		$panel->add($version);


		if($this->user->isAnonymous() || $this->user->hasInputExceeded())
		{
			$captcha = new Captcha('captcha', 'Captcha');
			$captcha->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/core/captcha');

			$panel->add($captcha);
		}


		$form->setContainer($panel);


		return $form;
	}

	private function getInstallableService()
	{
		$services          = array();
		$installedServices = $this->sql->getCol('SELECT source FROM ' . $this->registry['table.core_service']);
		$listedServices    = array();

		$path = $this->config['amun_service_path'];
		$dirs = scandir($path);

		foreach($dirs as $f)
		{
			if($f != '.' && $f != '..')
			{
				$xml  = null;
				$item = $path . '/' . $f;

				if(is_dir($item))
				{
					$config = $item . '/config.xml';

					if(is_file($config))
					{
						$xml  = simplexml_load_file($config);
					}
				}

				if(is_file($item))
				{
					$ext = pathinfo($item, PATHINFO_EXTENSION);

					if($ext == 'tar')
					{
						$phar = new PharData($item);
						$xml  = simplexml_load_string($phar->getMetadata());
					}
				}

				if($xml instanceof SimpleXMLElement)
				{
					if(isset($xml->name))
					{
						$name    = strval($xml->name);
						$version = strval($xml->version);

						if(!in_array($f, $installedServices) && !in_array($f, $listedServices))
						{
							array_push($services, array(

								'label' => $name . ' (' . $version . ')',
								'value' => $f,

							));

							$listedServices[] = $f;
						}
					}
				}
			}
		}

		return $services;
	}
}

