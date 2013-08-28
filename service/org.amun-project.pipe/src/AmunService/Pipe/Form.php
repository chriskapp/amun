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

namespace AmunService\Pipe;

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

/**
 * Form
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Form extends FormAbstract
{
	public function create($pageId = 0)
	{
		$form = new AmunForm('POST', $this->url);


		$panel = new Panel('page', 'Pipe');


		if(empty($pageId))
		{
			$pageId = new Reference('pageId', 'Page ID');
			$pageId->setValueField('id');
			$pageId->setLabelField('title');
			$pageId->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/content/page');

			$panel->add($pageId);
		}
		else
		{
			$pageId = new Input('pageId', 'Page ID', $pageId);
			$pageId->setType('hidden');

			$panel->add($pageId);
		}


		$media = new Select('mediaId', 'Media');
		$media->setOptions($this->getMedia());

		$panel->add($media);


		$processor = new Select('processor', 'Processor', 'passthru');
		$processor->setOptions($this->getProcessor());

		$panel->add($processor);


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
		$record = $this->hm->getHandler('AmunService\Pipe')->getRecord($id);


		$form = new AmunForm('PUT', $this->url);


		$panel = new Panel('page', 'Page');


		$id = new Input('id', 'ID', $record->id);
		$id->setType('hidden');

		$panel->add($id);


		$media = new Select('mediaId', 'Media', $record->mediaId);
		$media->setOptions($this->getMedia());

		$panel->add($media);


		$processor = new Select('processor', 'Processor', $record->processor);
		$processor->setOptions($this->getProcessor());

		$panel->add($processor);


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
		$record = $this->hm->getHandler('AmunService\Pipe')->getRecord($id);


		$form = new AmunForm('PUT', $this->url);


		$panel = new Panel('page', 'Page');


		$id = new Input('id', 'ID', $record->id);
		$id->setType('hidden');

		$panel->add($id);


		$media = new Select('mediaId', 'Media', $record->mediaId);
		$media->setOptions($this->getMedia());
		$media->setDisabled(true);

		$panel->add($media);


		$processor = new Select('processor', 'Processor', $record->processor);
		$processor->setOptions($this->getProcessor());
		$processor->setDisabled(true);

		$panel->add($processor);


		if($this->user->isAnonymous() || $this->user->hasInputExceeded())
		{
			$captcha = new Captcha('captcha', 'Captcha');
			$captcha->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/core/captcha');

			$panel->add($captcha);
		}


		$form->setContainer($panel);


		return $form;
	}

	private function getMedia()
	{
		$media  = array();
		$result = $this->sql->getAll('SELECT id, path FROM ' . $this->registry['table.media'] . ' ORDER BY path ASC');

		foreach($result as $row)
		{
			array_push($media, array(

				'label' => $row['path'],
				'value' => $row['id'],

			));
		}

		return $media;
	}

	private function getProcessor()
	{
		$procs  = array();
		$path   = '../vendor/amun/pipe/src/AmunService/Pipe/Processor';
		$files  = scandir($path);

		foreach($files as $file)
		{
			$proc = $path . '/' . $file;

			if($file[0] != '.' && is_file($proc))
			{
				$className = pathinfo($proc, PATHINFO_FILENAME);

				array_push($procs, array(

					'label' => $className,
					'value' => strtolower($className),

				));
			}
		}

		return $procs;
	}
}

