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
 * AmunService_Country_Form
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    AmunService_Country
 * @version    $Revision: 666 $
 */
class AmunService_Country_Form extends Amun_Data_FormAbstract
{
	public function create()
	{
		$form = new Amun_Form('POST', $this->url);


		$panel = new Amun_Form_Element_Panel('country', 'Country');


		$title = new Amun_Form_Element_Input('title', 'Title');
		$title->setType('text');

		$panel->add($title);


		$code = new Amun_Form_Element_Input('code', 'Code');
		$code->setType('text');

		$panel->add($code);


		$longitude = new Amun_Form_Element_Input('longitude', 'Longitude');
		$longitude->setType('text');

		$panel->add($longitude);


		$latitude = new Amun_Form_Element_Input('latitude', 'Latitude');
		$latitude->setType('text');

		$panel->add($latitude);


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
		$record = Amun_Sql_Table_Registry::get('Country')->getRecord($id);


		$form = new Amun_Form('PUT', $this->url);


		$panel = new Amun_Form_Element_Panel('country', 'Country');


		$id = new Amun_Form_Element_Input('id', 'ID', $record->id);
		$id->setType('hidden');

		$panel->add($id);


		$title = new Amun_Form_Element_Input('title', 'Title', $record->title);
		$title->setType('text');

		$panel->add($title);


		$code = new Amun_Form_Element_Input('code', 'Code', $record->code);
		$code->setType('text');

		$panel->add($code);


		$longitude = new Amun_Form_Element_Input('longitude', 'Longitude', $record->longitude);
		$longitude->setType('text');

		$panel->add($longitude);


		$latitude = new Amun_Form_Element_Input('latitude', 'Latitude', $record->latitude);
		$latitude->setType('text');

		$panel->add($latitude);


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
		$record = Amun_Sql_Table_Registry::get('Country')->getRecord($id);


		$form = new Amun_Form('DELETE', $this->url);


		$panel = new Amun_Form_Element_Panel('country', 'Country');


		$id = new Amun_Form_Element_Input('id', 'ID', $record->id);
		$id->setType('hidden');

		$panel->add($id);


		$title = new Amun_Form_Element_Input('title', 'Title', $record->title);
		$title->setType('text');
		$title->setDisabled(true);

		$panel->add($title);


		$code = new Amun_Form_Element_Input('code', 'Code', $record->code);
		$code->setType('text');
		$code->setDisabled(true);

		$panel->add($code);


		$longitude = new Amun_Form_Element_Input('longitude', 'Longitude', $record->longitude);
		$longitude->setType('text');
		$longitude->setDisabled(true);

		$panel->add($longitude);


		$latitude = new Amun_Form_Element_Input('latitude', 'Latitude', $record->latitude);
		$latitude->setType('text');
		$latitude->setDisabled(true);

		$panel->add($latitude);


		if($this->user->isAnonymous() || $this->user->hasInputExceeded())
		{
			$captcha = new Amun_Form_Element_Captcha('captcha', 'Captcha');
			$captcha->setSrc($this->config['psx_url'] . '/' . $this->config['amun_dispatch'] . 'api/system/captcha');

			$panel->add($captcha);
		}


		$form->setContainer($panel);


		return $form;
	}
}

