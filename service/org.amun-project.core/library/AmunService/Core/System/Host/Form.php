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
 * Amun_System_Host_Form
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_System_Host
 * @version    $Revision: 666 $
 */
class AmunService_Core_System_Host_Form extends Amun_Data_FormAbstract
{
	public function create()
	{
		$form = new Amun_Form('POST', $this->url);


		$panel = new Amun_Form_Element_Panel('api', 'API');


		$status = new Amun_Form_Element_Select('status', 'Status');
		$status->setOptions($this->getStatus());

		$panel->add($status);


		$name = new Amun_Form_Element_Input('name', 'Name');
		$name->setType('text');

		$panel->add($name);


		$consumerKey = new Amun_Form_Element_Input('consumerKey', 'Consumer key');
		$consumerKey->setType('text');

		$panel->add($consumerKey);


		$consumerSecret = new Amun_Form_Element_Input('consumerSecret', 'Consumer secret');
		$consumerSecret->setType('text');

		$panel->add($consumerSecret);


		$url = new Amun_Form_Element_Input('url', 'Url');
		$url->setType('url');

		$panel->add($url);


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
		$record = Amun_Sql_Table_Registry::get('Core_System_Host')->getRecord($id);


		$form = new Amun_Form('PUT', $this->url);


		$panel = new Amun_Form_Element_Panel('api', 'API');


		$id = new Amun_Form_Element_Input('id', 'ID', $record->id);
		$id->setType('hidden');

		$panel->add($id);


		$status = new Amun_Form_Element_Select('status', 'Status', $record->status);
		$status->setOptions($this->getStatus());

		$panel->add($status);


		$name = new Amun_Form_Element_Input('name', 'Name', $record->name);
		$name->setType('text');

		$panel->add($name);


		$consumerKey = new Amun_Form_Element_Input('consumerKey', 'Consumer key', $record->consumerKey);
		$consumerKey->setType('text');

		$panel->add($consumerKey);


		$consumerSecret = new Amun_Form_Element_Input('consumerSecret', 'Consumer secret', $record->consumerSecret);
		$consumerSecret->setType('text');

		$panel->add($consumerSecret);


		$url = new Amun_Form_Element_Input('url', 'Url', $record->url);
		$url->setType('url');

		$panel->add($url);


		if($this->user->isAnonymous() || $this->user->hasInputExceeded())
		{
			$captcha = new amun_form_element_captcha('captcha', 'Captcha');
			$captcha->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/system/captcha');

			$panel->add($captcha);
		}


		$form->setContainer($panel);


		return $form;
	}

	public function delete($id)
	{
		$record = Amun_Sql_Table_Registry::get('Core_System_Host')->getRecord($id);


		$form = new Amun_Form('DELETE', $this->url);


		$panel = new Amun_Form_Element_Panel('api', 'API');


		$id = new Amun_Form_Element_Input('id', 'ID', $record->id);
		$id->setType('hidden');

		$panel->add($id);


		$status = new Amun_Form_Element_Select('status', 'Status', $record->status);
		$status->setOptions($this->getStatus());
		$status->setDisabled(true);

		$panel->add($status);


		$name = new Amun_Form_Element_Input('name', 'Name', $record->name);
		$name->setType('text');
		$name->setDisabled(true);

		$panel->add($name);


		$consumerKey = new Amun_Form_Element_Input('consumerKey', 'Consumer key', $record->consumerKey);
		$consumerKey->setType('text');
		$consumerKey->setDisabled(true);

		$panel->add($consumerKey);


		$consumerSecret = new Amun_Form_Element_Input('consumerSecret', 'Consumer secret', $record->consumerSecret);
		$consumerSecret->setType('text');
		$consumerSecret->setDisabled(true);

		$panel->add($consumerSecret);


		$url = new Amun_Form_Element_Input('url', 'Url', $record->url);
		$url->setType('url');
		$url->setDisabled(true);

		$panel->add($url);


		if($this->user->isAnonymous() || $this->user->hasInputExceeded())
		{
			$captcha = new Amun_Form_Element_Captcha('captcha', 'Captcha');
			$captcha->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/system/captcha');

			$panel->add($captcha);
		}


		$form->setContainer($panel);


		return $form;
	}

	private function getStatus()
	{
		$status = array();
		$result = AmunService_Core_System_Host_Record::getStatus();

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

