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
 * Amun_Service_My_Notify_Form
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Service_My
 * @version    $Revision: 666 $
 */
class AmunService_My_Notify_Form extends Amun_Data_FormAbstract
{
	public function create()
	{
		$form = new Amun_Form('POST', $this->url);


		$panel = new Amun_Form_Element_Panel('notify', 'Notify');


		$type = new Amun_Form_Element_Select('serviceId', 'Service');
		$type->setOptions($this->getService());

		$panel->add($type);


		$type = new Amun_Form_Element_Select('contactId', 'Contact');
		$type->setOptions($this->getContact());

		$panel->add($type);


		if($this->user->isAnonymous() || $this->user->hasInputExceeded())
		{
			$captcha = new Amun_Form_Element_Captcha('captcha', 'Captcha');
			$captcha->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/system/captcha');

			$panel->add($captcha);
		}


		$form->setContainer($panel);


		return $form;
	}

	public function update($id)
	{
		$record = Amun_Sql_Table_Registry::get('Core_User_Account')->getRecord($id);


		$form = new Amun_Form('PUT', $this->url);


		$panel = new Amun_Form_Element_Panel('account', 'Account');


		$id = new Amun_Form_Element_Input('id', 'Id', $record->id);
		$id->setType('hidden');

		$panel->add($id);


		$type = new Amun_Form_Element_Select('type', 'Type', $record->type);
		$type->setOptions($this->getType());

		$panel->add($type);


		$value = new Amun_Form_Element_Input('value', 'Value', $record->value);
		$value->setType('text');

		$panel->add($value);


		if($this->user->isAnonymous() || $this->user->hasInputExceeded())
		{
			$captcha = new Amun_Form_Element_Captcha('captcha', 'Captcha');
			$captcha->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/system/captcha');

			$panel->add($captcha);
		}


		$form->setContainer($panel);


		return $form;
	}

	public function delete($id)
	{
		$record = Amun_Sql_Table_Registry::get('Core_User_Account')->getRecord($id);


		$form = new Amun_Form('DELETE', $this->url);


		$panel = new Amun_Form_Element_Panel('account', 'Account');


		$id = new amun_form_element_input('id', 'Id', $record->id);
		$id->setType('hidden');

		$panel->add($id);


		$type = new Amun_Form_Element_Select('type', 'Type', $record->type);
		$type->setOptions($this->getType());
		$type->setDisabled(true);

		$panel->add($type);


		$value = new Amun_Form_Element_Input('value', 'Value', $record->value);
		$value->setType('text');
		$value->setDisabled(true);

		$panel->add($value);


		if($this->user->isAnonymous() || $this->user->hasInputExceeded())
		{
			$captcha = new Amun_Form_Element_Captcha('captcha', 'Captcha');
			$captcha->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/system/captcha');

			$panel->add($captcha);
		}


		$form->setContainer($panel);


		return $form;
	}

	private function getService()
	{
		$service = array();
		$result  = $this->sql->getAll('SELECT id, title FROM ' . $this->registry['table.my_notify_service']);

		foreach($result as $row)
		{
			array_push($service, array(

				'label' => $row['title'],
				'value' => $row['id'],

			));
		}

		return $service;
	}

	private function getContact()
	{
		$contact = array();
		$result  = $this->sql->getAll('SELECT id, value FROM ' . $this->registry['table.my_contact']);

		foreach($result as $row)
		{
			array_push($contact, array(

				'label' => $row['value'],
				'value' => $row['id'],

			));
		}

		return $contact;
	}
}

