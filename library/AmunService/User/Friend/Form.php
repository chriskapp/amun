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
 * Amun_User_Friend_Form
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_User_Friend
 * @version    $Revision: 666 $
 */
class AmunService_User_Friend_Form extends Amun_Data_FormAbstract
{
	public function create()
	{
		$form = new Amun_Form('POST', $this->url);


		$panel = new Amun_Form_Element_Panel('friend', 'Friend');


		$status = new Amun_Form_Element_Select('status', 'Status', AmunService_User_Friend_Record::REQUEST);
		$status->setOptions($this->getStatus());
		$status->setDisabled(true);

		$panel->add($status);


		$userId = new Amun_Form_Element_Input('userId', 'User', $this->user->name);
		$userId->setType('text');
		$userId->setDisabled(true);

		$panel->add($userId);


		$friendId = new Amun_Form_Element_Reference('friendId', 'Friend ID');
		$friendId->setValueField('id');
		$friendId->setLabelField('name');
		$friendId->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/user/account');

		$panel->add($friendId);


		if($this->user->isAnonymous() || $this->user->hasInputExceeded())
		{
			$captcha = new Amun_Form_Element_Captcha('captcha', 'Captcha');
			$captcha->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/system/captcha');

			$panel->add($captcha);
		}


		$form->setContainer($panel);


		return $form;
	}

	public function update()
	{
		throw new PSX_Data_Exception('You cant update a friend record');
	}

	public function delete($id)
	{
		$record = Amun_Sql_Table_Registry::get('User_Friend')->getRecord($id);


		$form = new Amun_Form('DELETE', $this->url);


		$panel = new Amun_Form_Element_Panel('friend', 'Friend');


		$id = new Amun_Form_Element_Input('id', 'Id', $record->id);
		$id->setType('hidden');

		$panel->add($id);


		$status = new Amun_Form_Element_Select('status', 'Status', $record->status);
		$status->setOptions($this->getStatus());
		$status->setDisabled(true);

		$panel->add($status);


		$userId = new Amun_Form_Element_Input('userId', 'User', $record->getUser()->name);
		$userId->setType('text');
		$userId->setDisabled(true);

		$panel->add($userId);


		$friendId = new Amun_Form_Element_Input('friendId', 'Friend ID', $record->getFriend()->name);
		$friendId->setType('text');
		$friendId->setDisabled(true);

		$panel->add($friendId);


		if($this->user->isAnonymous() || $this->user->hasInputExceeded())
		{
			$captcha = new Amun_Form_Element_Captcha('captcha', 'Captcha');
			$captcha->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/system/captcha');

			$panel->add($captcha);
		}


		$form->setContainer($panel);


		return $form;
	}

	public function getStatus()
	{
		$status = array();
		$result = AmunService_User_Friend_Record::getStatus();

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


