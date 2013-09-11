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

namespace AmunService\Vcshook\Author;

use Amun\Data\FormAbstract;
use Amun\Form as AmunForm;
use Amun\Form\Element;
use AmunService\Vcshook;

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


		$panel = new Element\Panel('author', 'Author');


		$userId = new Element\Reference('userId', 'User ID');
		$userId->setValueField('id');
		$userId->setLabelField('name');
		$userId->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/user/account');

		$panel->add($userId);


		$name = new Element\Input('name', 'Name');
		$name->setType('text');

		$panel->add($name);


		if($this->user->isAnonymous() || $this->user->hasInputExceeded())
		{
			$captcha = new Element\Captcha('captcha', 'Captcha');
			$captcha->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/core/captcha');

			$panel->add($captcha);
		}


		$form->setContainer($panel);


		return $form;
	}

	public function update($id)
	{
		$record = $this->hm->getHandler('AmunService\Vcshook\Author')->getRecord($id);


		$form = new AmunForm('PUT', $this->url);


		$panel = new Element\Panel('author', 'Author');


		$id = new Element\Input('id', 'ID', $record->id);
		$id->setType('hidden');

		$panel->add($id);


		$userId = new Element\Reference('userId', 'User ID', $record->userId);
		$userId->setValueField('id');
		$userId->setLabelField('name');
		$userId->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/user/account');

		$panel->add($userId);


		$name = new Element\Input('name', 'Name', $record->name);
		$name->setType('text');

		$panel->add($name);


		if($this->user->isAnonymous() || $this->user->hasInputExceeded())
		{
			$captcha = new Element\Captcha('captcha', 'Captcha');
			$captcha->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/core/captcha');

			$panel->add($captcha);
		}


		$form->setContainer($panel);


		return $form;
	}

	public function delete($id)
	{
		$record = $this->hm->getHandler('AmunService\Vcshook\Author')->getRecord($id);


		$form = new AmunForm('DELETE', $this->url);


		$panel = new Element\Panel('author', 'Author');


		$id = new Element\Input('id', 'ID', $record->id);
		$id->setType('hidden');

		$panel->add($id);


		$userId = new Element\Reference('userId', 'User ID', $record->userId);
		$userId->setValueField('id');
		$userId->setLabelField('name');
		$userId->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/user/account');
		$userId->setDisabled(true);

		$panel->add($userId);


		$name = new Element\Input('name', 'Name', $record->name);
		$name->setType('text');
		$name->setDisabled(true);

		$panel->add($name);


		if($this->user->isAnonymous() || $this->user->hasInputExceeded())
		{
			$captcha = new Element\Captcha('captcha', 'Captcha');
			$captcha->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/core/captcha');

			$panel->add($captcha);
		}


		$form->setContainer($panel);


		return $form;
	}
}

