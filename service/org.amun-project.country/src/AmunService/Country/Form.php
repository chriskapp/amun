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

namespace AmunService\Country;

use Amun\DataFactory;
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
		$form = new AmunForm('POST', $this->url);


		$panel = new Panel('country', 'Country');


		$title = new Input('title', 'Title');
		$title->setType('text');

		$panel->add($title);


		$code = new Input('code', 'Code');
		$code->setType('text');

		$panel->add($code);


		$longitude = new Input('longitude', 'Longitude');
		$longitude->setType('text');

		$panel->add($longitude);


		$latitude = new Input('latitude', 'Latitude');
		$latitude->setType('text');

		$panel->add($latitude);


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
		$record = $this->hm->getHandler('Country')->getRecord($id);


		$form = new AmunForm('PUT', $this->url);


		$panel = new Panel('country', 'Country');


		$id = new Input('id', 'ID', $record->id);
		$id->setType('hidden');

		$panel->add($id);


		$title = new Input('title', 'Title', $record->title);
		$title->setType('text');

		$panel->add($title);


		$code = new Input('code', 'Code', $record->code);
		$code->setType('text');

		$panel->add($code);


		$longitude = new Input('longitude', 'Longitude', $record->longitude);
		$longitude->setType('text');

		$panel->add($longitude);


		$latitude = new Input('latitude', 'Latitude', $record->latitude);
		$latitude->setType('text');

		$panel->add($latitude);


		if($this->user->isAnonymous() || $this->user->hasInputExceeded())
		{
			$captcha = new Captcha('captcha', 'Captcha');
			$captcha->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/core/captcha');

			$panel->add($captcha);
		}


		$form->setContainer($panel);


		return $form;
	}

	public function delete($id)
	{
		$record = $this->hm->getHandler('Country')->getRecord($id);


		$form = new AmunForm('DELETE', $this->url);


		$panel = new Panel('country', 'Country');


		$id = new Input('id', 'ID', $record->id);
		$id->setType('hidden');

		$panel->add($id);


		$title = new Input('title', 'Title', $record->title);
		$title->setType('text');
		$title->setDisabled(true);

		$panel->add($title);


		$code = new Input('code', 'Code', $record->code);
		$code->setType('text');
		$code->setDisabled(true);

		$panel->add($code);


		$longitude = new Input('longitude', 'Longitude', $record->longitude);
		$longitude->setType('text');
		$longitude->setDisabled(true);

		$panel->add($longitude);


		$latitude = new Input('latitude', 'Latitude', $record->latitude);
		$latitude->setType('text');
		$latitude->setDisabled(true);

		$panel->add($latitude);


		if($this->user->isAnonymous() || $this->user->hasInputExceeded())
		{
			$captcha = new Captcha('captcha', 'Captcha');
			$captcha->setSrc($this->config['psx_url'] . '/' . $this->config['amun_dispatch'] . 'api/core/captcha');

			$panel->add($captcha);
		}


		$form->setContainer($panel);


		return $form;
	}
}

