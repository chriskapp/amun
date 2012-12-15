<?php
/*
 *  $Id: Form.php 666 2012-05-12 22:10:25Z k42b3.x@googlemail.com $
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
 * Amun_User_Group_Right_Form
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_User_Group
 * @version    $Revision: 666 $
 */
class AmunService_User_Group_Right_Form extends Amun_Data_FormAbstract
{
	public function create()
	{
		$form = new Amun_Form('POST', $this->url);


		$panel = new Amun_Form_Element_Panel('right', 'Right');


		$groupId = new Amun_Form_Element_Select('groupId', 'Group ID');
		$groupId->setOptions($this->getGroup());

		$panel->add($groupId);


		$rightId = new Amun_Form_Element_Select('rightId', 'Right ID');
		$rightId->setOptions($this->getRight());

		$panel->add($rightId);


		if($this->user->isAnonymous() || $this->user->hasInputExceeded())
		{
			$captcha = new Amun_Form_Element_Captcha('captcha', 'Captcha');
			$captcha->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/core/captcha');

			$panel->add($captcha);
		}


		$form->setContainer($panel);


		return $form;
	}

	public function update($id)
	{
		$record = Amun_Sql_Table_Registry::get('User_Group_Right')->getRecord($id);


		$form = new Amun_Form('PUT', $this->url);


		$panel = new Amun_Form_Element_Panel('group', 'Group');


		$id = new Amun_Form_Element_Input('id', 'Id', $record->id);
		$id->setType('hidden');

		$panel->add($id);


		$groupId = new Amun_Form_Element_Select('groupId', 'Group ID', $record->rightId);
		$groupId->setOptions($this->getGroup());

		$panel->add($groupId);


		$rightId = new Amun_Form_Element_Select('rightId', 'Right ID', $record->groupId);
		$rightId->setOptions($this->getRight());

		$panel->add($rightId);


		if($this->user->isAnonymous() || $this->user->hasInputExceeded())
		{
			$captcha = new Amun_Form_Element_Captcha('captcha', 'Captcha');
			$captcha->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/core/captcha');

			$panel->add($captcha);
		}


		$form->setContainer($panel);


		return $form;
	}

	public function delete($id)
	{
		$record = Amun_Sql_Table_Registry::get('User_Group_Right')->getRecord($id);


		$form = new Amun_Form('DELETE', $this->url);


		$panel = new Amun_Form_Element_Panel('group', 'Group');


		$id = new Amun_Form_Element_Input('id', 'Id', $record->id);
		$id->setType('hidden');

		$panel->add($id);


		$groupId = new Amun_Form_Element_Select('groupId', 'Group ID', $record->rightId);
		$groupId->setOptions($this->getGroup());
		$groupId->setDisabled(true);

		$panel->add($groupId);


		$rightId = new Amun_Form_Element_Select('rightId', 'Right ID', $record->groupId);
		$rightId->setOptions($this->getRight());
		$rightId->setDisabled(true);

		$panel->add($rightId);


		if($this->user->isAnonymous() || $this->user->hasInputExceeded())
		{
			$captcha = new Amun_Form_Element_Captcha('captcha', 'Captcha');
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

