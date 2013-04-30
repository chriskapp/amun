<?php
/*
 *  $Id: Form.php 683 2012-06-03 11:52:32Z k42b3.x@googlemail.com $
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

namespace AmunService\User\Account;

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
use DateTimeZone;

/**
 * Amun_User_Account_Form
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_User_Account
 * @version    $Revision: 683 $
 */
class Form extends FormAbstract
{
	public function create()
	{
		$form = new AmunForm('POST', $this->url);


		$panel = new Panel('account', 'Account');


		if($this->user->isAdministrator())
		{
			$groupId = new Select('groupId', 'Group');
			$groupId->setOptions($this->getGroup());

			$panel->add($groupId);


			$status = new Select('status', 'Status');
			$status->setOptions($this->getStatus());

			$panel->add($status);
		}


		$identity = new Input('identity', 'Identity');
		$identity->setType('text');

		$panel->add($identity);


		$name = new Input('name', 'Name');
		$name->setType('text');

		$panel->add($name);


		$pw = new Input('pw', 'Password');
		$pw->setType('password');

		$panel->add($pw);


		$email = new Input('email', 'Email');
		$email->setType('email');

		$panel->add($email);


		$country = new Select('countryId', 'Country');
		$country->setOptions($this->getCountry());

		$panel->add($country);


		$gender = new Select('gender', 'Gender');
		$gender->setOptions($this->getGender());

		$panel->add($gender);


		$timezone = new Select('timezone', 'Timezone', 'UTC');
		$timezone->setOptions($this->getTimezone());

		$panel->add($timezone);


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
		$record = DataFactory::get('User_Account')->getRecord($id);


		$form = new AmunForm('PUT', $this->url);


		$panel = new Panel('account', 'Account');


		$id = new Input('id', 'Id', $record->id);
		$id->setType('hidden');

		$panel->add($id);


		if($this->user->isAdministrator())
		{
			$groupId = new Select('groupId', 'Group', $record->groupId);
			$groupId->setOptions($this->getGroup());

			$panel->add($groupId);


			$status = new Select('status', 'Status', $record->status);
			$status->setOptions($this->getStatus());

			$panel->add($status);
		}


		$name = new Input('name', 'Name', $record->name);
		$name->setType('text');
		$name->setDisabled(true);

		$panel->add($name);


		$email = new Input('email', 'Email', $record->email);
		$email->setType('email');

		$panel->add($email);


		$country = new Select('countryId', 'Country', $record->countryId);
		$country->setOptions($this->getCountry());

		$panel->add($country);


		$gender = new Select('gender', 'Gender', $record->gender);
		$gender->setOptions($this->getGender());

		$panel->add($gender);


		$timezone = new Select('timezone', 'Timezone', $record->timezone);
		$timezone->setOptions($this->getTimezone());

		$panel->add($timezone);


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
		$record = DataFactory::get('User_Account')->getRecord($id);


		$form = new AmunForm('DELETE', $this->url);


		$panel = new Panel('account', 'Account');


		$id = new Input('id', 'Id', $record->id);
		$id->setType('hidden');

		$panel->add($id);


		if($this->user->isAdministrator())
		{
			$groupId = new Select('groupId', 'Group', $record->groupId);
			$groupId->setOptions($this->getGroup());
			$groupId->setDisabled(true);

			$panel->add($groupId);


			$status = new Select('status', 'Status', $record->status);
			$status->setOptions($this->getStatus());
			$status->setDisabled(true);

			$panel->add($status);
		}


		$name = new Input('name', 'Name', $record->name);
		$name->setType('text');
		$name->setDisabled(true);

		$panel->add($name);


		$email = new Input('email', 'Email', $record->email);
		$email->setType('email');
		$email->setDisabled(true);

		$panel->add($email);


		$country = new Select('countryId', 'Country', $record->countryId);
		$country->setOptions($this->getCountry());
		$country->setDisabled(true);

		$panel->add($country);


		$gender = new Select('gender', 'Gender', $record->gender);
		$gender->setOptions($this->getGender());
		$gender->setDisabled(true);

		$panel->add($gender);


		$timezone = new Select('timezone', 'Timezone', $record->timezone);
		$timezone->setOptions($this->getTimezone());
		$timezone->setDisabled(true);

		$panel->add($timezone);


		if($this->user->isAnonymous() || $this->user->hasInputExceeded())
		{
			$captcha = new Captcha('captcha', 'Captcha');
			$captcha->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/core/captcha');

			$panel->add($captcha);
		}


		$form->setContainer($panel);


		return $form;
	}

	private function getGroup()
	{
		$group  = array();
		$result = $this->sql->getAll('SELECT id, title FROM ' . $this->registry['table.user_group']);

		foreach($result as $row)
		{
			array_push($group, array(

				'label' => $row['title'],
				'value' => $row['id'],

			));
		}

		return $group;
	}

	private function getHost()
	{
		$host   = array();
		$result = $this->sql->getAll('SELECT id, name FROM ' . $this->registry['table.core_host']);

		array_push($host, array(

			'label' => $this->base->getHost(),
			'value' => 0,

		));

		foreach($result as $row)
		{
			array_push($host, array(

				'label' => $row['name'],
				'value' => $row['id'],

			));
		}

		return $host;
	}

	private function getCountry()
	{
		$country = array();
		$result  = $this->sql->getAll('SELECT id, title FROM ' . $this->registry['table.country']);

		foreach($result as $row)
		{
			array_push($country, array(

				'label' => $row['title'],
				'value' => $row['id'],

			));
		}

		return $country;
	}

	private function getStatus()
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

	private function getGender()
	{
		$gender = array();
		$result = Record::getGender();

		foreach($result as $k => $v)
		{
			array_push($gender, array(

				'label' => $v,
				'value' => $k,

			));
		}

		return $gender;
	}

	public function getTimezone()
	{
		$timezones = array();
		$result    = DateTimeZone::listIdentifiers();

		foreach($result as $tz)
		{
			array_push($timezones, array(

				'label' => $tz,
				'value' => $tz,

			));
		}

		return $timezones;
	}
}

