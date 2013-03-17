<?php
/*
 *  $Id: Form.php 807 2012-07-09 12:40:35Z k42b3.x@googlemail.com $
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

namespace AmunService\Mail;

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
use AmunService\Media;

/**
 * AmunService_Core_Content_Media_Form
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Content_Media
 * @version    $Revision: 807 $
 */
class Form extends FormAbstract
{
	public function create()
	{
		$form = new AmunForm('POST', $this->url, 'multipart/form-data');


		$panel = new Panel('media', 'Media');


		$rightId = new Select('rightId', 'Right ID', 77); # media_view right
		$rightId->setOptions($this->getRight());

		$panel->add($rightId);


		$folder = new Select('folder', 'Folder', '.');
		$folder->setOptions($this->getFolder());

		$panel->add($folder);


		$path = new Input('path', 'File');
		$path->setType('file');

		$panel->add($path);


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
		$record = DataFactory::getTable('Media')->getRecord($id);


		$form = new AmunForm('PUT', $this->url);


		$panel = new Panel('media', 'Media');


		$id = new Input('id', 'Id', $record->id);
		$id->setType('hidden');

		$panel->add($id);


		$rightId = new Select('rightId', 'Right ID', $record->rightId);
		$rightId->setOptions($this->getRight());

		$panel->add($rightId);


		$path = new Input('path', 'Path', $record->path);
		$path->setType('text');
		$path->setDisabled(true);

		$panel->add($path);


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
		$record = DataFactory::getTable('Media')->getRecord($id);


		$form = new AmunForm('DELETE', $this->url);


		$panel = new Panel('media', 'Media');


		$id = new Input('id', 'Id', $record->id);
		$id->setType('hidden');

		$panel->add($id);


		$rightId = new Select('rightId', 'Right ID', $record->rightId);
		$rightId->setOptions($this->getRight());
		$rightId->setDisabled(true);

		$panel->add($rightId);


		$path = new Input('path', 'Path', $record->path);
		$path->setType('text');
		$path->setDisabled(true);

		$panel->add($path);


		if($this->user->isAnonymous() || $this->user->hasInputExceeded())
		{
			$captcha = new Captcha('captcha', 'Captcha');
			$captcha->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/core/captcha');

			$panel->add($captcha);
		}


		$form->setContainer($panel);


		return $form;
	}

	private function getType()
	{
		$status = array();
		$result = Media\Record::getType();

		foreach($result as $k => $v)
		{
			array_push($status, array(

				'label' => $v,
				'value' => $k,

			));
		}

		return $status;
	}

	private function getRight()
	{
		$right  = array();
		$result = $this->sql->getAll('SELECT id, name FROM ' . $this->registry['table.user_right'] . ' ORDER BY name ASC');

		foreach($result as $row)
		{
			array_push($right, array(

				'label' => $row['name'],
				'value' => $row['id'],

			));
		}

		return $right;
	}

	private function getFolder($path = null)
	{
		$folder = array();

		if($path === null)
		{
			$path = $this->registry['media.path'];

			array_push($folder, array(

				'label' => '.',
				'value' => '.',

			));
		}

		$files = scandir($path);

		foreach($files as $file)
		{
			$item = $path . '/' . $file;

			if($file[0] != '.' && is_dir($item))
			{
				$value = substr($item, strlen($this->registry['media.path']) + 1);

				array_push($folder, array(

					'label' => $value,
					'value' => $value,

				));

				$folder = array_merge($folder, $this->getFolder($item));
			}
		}

		return $folder;
	}
}

