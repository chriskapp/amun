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
 * Amun_Service_File_Form
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Service_File
 * @version    $Revision: 666 $
 */
class AmunService_Explorer_Form extends Amun_Data_FormAbstract
{
	public function create($path = '')
	{
		$form = new Amun_Form('POST', $this->url);


		$panel = new Amun_Form_Element_Panel('file', 'File');


		$path = new Amun_Form_Element_Input('path', 'Path', $path);
		$path->setType('text');

		$panel->add($path);


		$content = new Amun_Form_Element_Textarea('content', 'Content');

		$panel->add($content);


		if($this->user->isAnonymous() || $this->user->hasInputExceeded())
		{
			$captcha = new Amun_Form_Element_Captcha('captcha', 'Captcha');
			$captcha->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/core/captcha');

			$panel->add($captcha);
		}


		$form->setContainer($panel);


		return $form;
	}

	public function update($path)
	{
		if(!is_file($path))
		{
			throw new Amun_Exception('Invalid file');
		}

		$record = file_get_contents($path);


		$form = new Amun_Form('PUT', $this->url);


		$panel = new Amun_Form_Element_Panel('file', 'File');


		$path = new Amun_Form_Element_Input('path', 'Path', $path);
		$path->setType('hidden');

		$panel->add($path);


		$content = new Amun_Form_Element_Textarea('content', 'Content', $record);

		$panel->add($content);


		if($this->user->isAnonymous() || $this->user->hasInputExceeded())
		{
			$captcha = new Amun_Form_Element_Captcha('captcha', 'Captcha');
			$captcha->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/core/captcha');

			$panel->add($captcha);
		}


		$form->setContainer($panel);


		return $form;
	}

	public function delete($path)
	{
		if(!is_file($path))
		{
			throw new Amun_Exception('Invalid file');
		}

		$record = file_get_contents($path);


		$form = new Amun_Form('DELETE', $this->url);


		$panel = new Amun_Form_Element_Panel('file', 'File');


		$path = new Amun_Form_Element_Input('path', 'Path', $path);
		$path->setType('hidden');

		$panel->add($path);


		$content = new Amun_Form_Element_Textarea('content', 'Content', $record);
		$content->setDisabled(true);

		$panel->add($content);


		if($this->user->isAnonymous() || $this->user->hasInputExceeded())
		{
			$captcha = new Amun_Form_Element_Captcha('captcha', 'Captcha');
			$captcha->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/core/captcha');

			$panel->add($captcha);
		}


		$form->setContainer($panel);


		return $form;
	}
}

