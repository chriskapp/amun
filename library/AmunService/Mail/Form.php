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
 * Amun_System_Mail_Form
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_System_Mail
 * @version    $Revision: 666 $
 */
class AmunService_Mail_Form extends Amun_Data_FormAbstract
{
	public function create()
	{
		$form = new Amun_Form('POST', $this->url);


		$panel = new Amun_Form_Element_Panel('mail', 'Mail');


		$name = new Amun_Form_Element_Input('name', 'Name');
		$name->setType('text');

		$panel->add($name);


		$from = new Amun_Form_Element_Input('from', 'From');
		$from->setType('text');

		$panel->add($from);


		$subject = new Amun_Form_Element_Input('subject', 'Subject');
		$subject->setType('text');

		$panel->add($subject);


		$text = new Amun_Form_Element_Textarea('text', 'Text');

		$panel->add($text);


		$html = new Amun_Form_Element_Textarea('html', 'Html');

		$panel->add($html);


		$values = new Amun_Form_Element_Input('values', 'Values');
		$values->setType('text');

		$panel->add($values);


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
		$record = Amun_Sql_Table_Registry::get('Mail')->getRecord($id);


		$form = new Amun_Form('PUT', $this->url);


		$panel = new Amun_Form_Element_Panel('mail', 'Mail');


		$id = new Amun_Form_Element_Input('id', 'Id', $record->id);
		$id->setType('hidden');

		$panel->add($id);


		$name = new Amun_Form_Element_Input('name', 'Name', $record->name);
		$name->setType('text');

		$panel->add($name);


		$from = new Amun_Form_Element_Input('from', 'From', $record->from);
		$from->setType('text');

		$panel->add($from);


		$subject = new Amun_Form_Element_Input('subject', 'Subject', $record->subject);
		$subject->setType('text');

		$panel->add($subject);


		$text = new Amun_Form_Element_Textarea('text', 'Text', $record->text);

		$panel->add($text);


		$html = new Amun_Form_Element_Textarea('html', 'Html', $record->html);

		$panel->add($html);


		$values = new Amun_Form_Element_Input('values', 'Values', $record->values);
		$values->setType('text');

		$panel->add($values);


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
		throw new PSX_Data_Exception('You cant delete a mail record');
	}
}

