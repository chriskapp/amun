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

namespace AmunService\User\Group\Right;

use Amun\Data\FormAbstract;
use Amun\DataFactory;
use Amun\Form as AmunForm;
use Amun\Form\Element\Panel;
use Amun\Form\Element\Input;
use Amun\Form\Element\Select;
use Amun\Form\Element\Captcha;

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


		$panel = new Panel('right', 'Right');


		$groupId = new Select('groupId', 'Group ID');
		$groupId->setOptions($this->getGroup());

		$panel->add($groupId);


		$rightId = new Select('rightId', 'Right ID');
		$rightId->setOptions($this->getRight());

		$panel->add($rightId);


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
		$record = DataFactory::get('User_Group_Right')->getRecord($id);


		$form = new AmunForm('PUT', $this->url);


		$panel = new Panel('group', 'Group');


		$id = new Input('id', 'Id', $record->id);
		$id->setType('hidden');

		$panel->add($id);


		$groupId = new Select('groupId', 'Group ID', $record->rightId);
		$groupId->setOptions($this->getGroup());

		$panel->add($groupId);


		$rightId = new Select('rightId', 'Right ID', $record->groupId);
		$rightId->setOptions($this->getRight());

		$panel->add($rightId);


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
		$record = DataFactory::get('User_Group_Right')->getRecord($id);


		$form = new AmunForm('DELETE', $this->url);


		$panel = new Panel('group', 'Group');


		$id = new Input('id', 'Id', $record->id);
		$id->setType('hidden');

		$panel->add($id);


		$groupId = new Select('groupId', 'Group ID', $record->rightId);
		$groupId->setOptions($this->getGroup());
		$groupId->setDisabled(true);

		$panel->add($groupId);


		$rightId = new Select('rightId', 'Right ID', $record->groupId);
		$rightId->setOptions($this->getRight());
		$rightId->setDisabled(true);

		$panel->add($rightId);


		if($this->user->isAnonymous() || $this->user->hasInputExceeded())
		{
			$captcha = new Captcha('captcha', 'Captcha');
			$captcha->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/core/captcha');

			$panel->add($captcha);
		}


		$form->setContainer($panel);


		return $form;
	}

	public function getGroup()
	{
		$group  = array();
		$result = $this->sql->getAll('SELECT id, title FROM ' . $this->registry['table.user_group'] . ' ORDER BY title ASC');

		foreach($result as $row)
		{
			array_push($group, array(

				'label' => $row['title'],
				'value' => $row['id'],

			));
		}

		return $group;
	}

	public function getRight()
	{
		$right  = array();
		$result = $this->sql->getAll('SELECT id, description FROM ' . $this->registry['table.user_right'] . ' ORDER BY description ASC');

		foreach($result as $row)
		{
			array_push($right, array(

				'label' => $row['description'],
				'value' => $row['id'],

			));
		}

		return $right;
	}
}

