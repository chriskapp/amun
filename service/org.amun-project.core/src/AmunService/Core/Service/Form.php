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
		throw new Exception('Create a service record is not possible');
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
}

