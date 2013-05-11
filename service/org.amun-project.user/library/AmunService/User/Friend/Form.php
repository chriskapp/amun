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

namespace AmunService\User\Friend;

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


		$panel = new Panel('friend', 'Friend');


		$status = new Select('status', 'Status', Record::REQUEST);
		$status->setOptions($this->getStatus());
		$status->setDisabled(true);

		$panel->add($status);


		$userId = new Input('userId', 'User', $this->user->name);
		$userId->setType('text');
		$userId->setDisabled(true);

		$panel->add($userId);


		$friendId = new Reference('friendId', 'Friend ID');
		$friendId->setValueField('id');
		$friendId->setLabelField('name');
		$friendId->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/user/account');

		$panel->add($friendId);


		if($this->user->isAnonymous() || $this->user->hasInputExceeded())
		{
			$captcha = new Captcha('captcha', 'Captcha');
			$captcha->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/core/captcha');

			$panel->add($captcha);
		}


		$form->setContainer($panel);


		return $form;
	}

	public function update()
	{
		throw new Exception('You cant update a friend record');
	}

	public function delete($id)
	{
		$record = DataFactory::get('User_Friend')->getRecord($id);


		$form = new AmunForm('DELETE', $this->url);


		$panel = new Panel('friend', 'Friend');


		$id = new Input('id', 'Id', $record->id);
		$id->setType('hidden');

		$panel->add($id);


		$status = new Select('status', 'Status', $record->status);
		$status->setOptions($this->getStatus());
		$status->setDisabled(true);

		$panel->add($status);


		$userId = new Input('userId', 'User', $record->getUser()->name);
		$userId->setType('text');
		$userId->setDisabled(true);

		$panel->add($userId);


		$friendId = new Input('friendId', 'Friend ID', $record->getFriend()->name);
		$friendId->setType('text');
		$friendId->setDisabled(true);

		$panel->add($friendId);


		if($this->user->isAnonymous() || $this->user->hasInputExceeded())
		{
			$captcha = new Captcha('captcha', 'Captcha');
			$captcha->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/core/captcha');

			$panel->add($captcha);
		}


		$form->setContainer($panel);


		return $form;
	}

	public function getStatus()
	{
		$status = array();
		$result = Record::getStatus();

		foreach($result as $k => $v)
		{
			array_push($status, array(

				'label' => $v,
				'value' => $k,

			));
		}

		return $status;
	}
}


