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

namespace AmunService\Content\Gadget;

use Amun\DataFactory;
use Amun\Data\FormAbstract;
use Amun\Form as AmunForm;
use Amun\Form\Element\Panel;
use Amun\Form\Element\Reference;
use Amun\Form\Element\Input;
use Amun\Form\Element\Textarea;
use Amun\Form\Element\Captcha;
use Amun\Form\Element\Select;
use AmunService\Content\Gadget;

/**
 * Form
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Form extends FormAbstract
{
	public function create()
	{
		$form = new AmunForm('POST', $this->url);


		$panel = new Panel('gadget', 'Gadget');


		$name = new Input('name', 'Name');
		$name->setType('text');

		$panel->add($name);


		$title = new Input('title', 'Title');
		$title->setType('text');

		$panel->add($title);


		$path = new Select('path', 'Path');
		$path->setOptions($this->getGadget());

		$panel->add($path);


		$type = new Select('type', 'Type', 'ajax');
		$type->setOptions($this->getType());

		$panel->add($type);


		$right = new Select('rightId', 'Right');
		$right->setOptions($this->getRights());

		$panel->add($right);


		$cache = new Input('cache', 'Cache');
		$cache->setType('checkbox');

		$panel->add($cache);


		$expire = new Input('expire', 'Expire');
		$expire->setType('text');

		$panel->add($expire);


		$param = new Input('param', 'Param');
		$param->setType('text');

		$panel->add($param);


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
		$record = $this->hm->getHandler('Content_Gadget')->getRecord($id);


		$form = new AmunForm('PUT', $this->url);


		$panel = new Panel('gadget', 'Gadget');


		$id = new Input('id', 'Id', $record->id);
		$id->setType('hidden');

		$panel->add($id);


		$name = new Input('name', 'Name', $record->name);
		$name->setType('text');

		$panel->add($name);


		$title = new Input('title', 'Title', $record->title);
		$title->setType('text');

		$panel->add($title);


		$path = new Select('path', 'Path', $record->getPath());
		$path->setOptions($this->getGadget());

		$panel->add($path);


		$type = new Select('type', 'Type', $record->type);
		$type->setOptions($this->getType());

		$panel->add($type);


		$right = new Select('rightId', 'Right', $record->rightId);
		$right->setOptions($this->getRights());

		$panel->add($right);


		$cache = new Input('cache', 'Cache', $record->cache);
		$cache->setType('checkbox');

		$panel->add($cache);


		$expire = new Input('expire', 'Expire', $record->expire);
		$expire->setType('text');

		$panel->add($expire);


		$param = new Input('param', 'Param', $record->getParam());
		$param->setType('text');

		$panel->add($param);


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
		$record = $this->hm->getHandler('Content_Gadget')->getRecord($id);


		$form = new AmunForm('DELETE', $this->url);


		$panel = new Panel('gadget', 'Gadget');


		$id = new Input('id', 'Id', $record->id);
		$id->setType('hidden');

		$panel->add($id);


		$name = new Input('name', 'Name', $record->name);
		$name->setType('text');
		$name->setDisabled(true);

		$panel->add($name);


		$title = new Input('title', 'Title', $record->title);
		$title->setType('text');
		$title->setDisabled(true);

		$panel->add($title);


		$path = new Select('path', 'Path', $record->getPath());
		$path->setOptions($this->getGadget());
		$path->setDisabled(true);

		$panel->add($path);


		$type = new Select('type', 'Type', $record->type);
		$type->setOptions($this->getType());

		$panel->add($type);


		$right = new Select('rightId', 'Right', $record->rightId);
		$right->setOptions($this->getRights());

		$panel->add($right);


		$cache = new Input('cache', 'Cache', $record->cache);
		$cache->setType('checkbox');
		$cache->setDisabled(true);

		$panel->add($cache);


		$expire = new Input('expire', 'Expire', $record->expire);
		$expire->setType('text');
		$expire->setDisabled(true);

		$panel->add($expire);


		$param = new Input('param', 'Param', $record->getParam());
		$param->setType('text');
		$param->setDisabled(true);

		$panel->add($param);


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
		$type   = array();
		$result = Gadget\Record::getType();

		foreach($result as $k => $v)
		{
			array_push($type, array(

				'label' => $v,
				'value' => $k,

			));
		}

		return $type;
	}

	private function getGadget()
	{
		$path   = PSX_PATH_MODULE . '/gadget';
		$gadget = array();

		// service gadgets
		$result = $this->sql->getAll('SELECT id, source, name FROM ' . $this->registry['table.core_service'] . ' ORDER BY name ASC');

		foreach($result as $row)
		{
			$this->scanDir($gadget, $row['name'], $row['id'], $this->config['amun_service_path'] . '/' . $row['source'] . '/gadget');
		}

		return $gadget;
	}

	private function getRights()
	{
		$rights = array();

		array_push($rights, array(

			'label' => '-',
			'value' => 0,

		));

		$result = $this->sql->getAll('SELECT id, description FROM ' . $this->registry['table.user_right'] . ' ORDER BY name ASC');

		foreach($result as $row)
		{
			array_push($rights, array(

				'label' => $row['description'],
				'value' => $row['id'],

			));
		}

		return $rights;
	}

	private function scanDir(&$gadget, $name, $id, $path)
	{
		if(!is_dir($path))
		{
			return;
		}

		$dirs = scandir($path);

		foreach($dirs as $d)
		{
			if($d[0] != '.')
			{
				$item = $path . '/' . $d;
				$ext  = pathinfo($item, PATHINFO_EXTENSION);

				if(is_file($item) && $ext == 'php')
				{
					$gadget[] = array(

						'label' => ucfirst($name) . ' -> ' . $d,
						'value' => $id . ':' . $d,

					);
				}
			}
		}
	}
}

