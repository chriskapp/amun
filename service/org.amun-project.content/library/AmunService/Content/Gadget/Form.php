<?php
/*
 *  $Id: Form.php 838 2012-08-27 20:20:36Z k42b3.x@googlemail.com $
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
 * AmunService_Core_Content_Gadget_Form
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Content_Gadget
 * @version    $Revision: 838 $
 */
class AmunService_Content_Gadget_Form extends Amun_Data_FormAbstract
{
	public function create()
	{
		$form = new Amun_Form('POST', $this->url);


		$panel = new Amun_Form_Element_Panel('gadget', 'Gadget');


		$name = new Amun_Form_Element_Input('name', 'Name');
		$name->setType('text');

		$panel->add($name);


		$title = new Amun_Form_Element_Input('title', 'Title');
		$title->setType('text');

		$panel->add($title);


		$path = new Amun_Form_Element_Select('path', 'Path');
		$path->setOptions($this->listGadget());

		$panel->add($path);


		$cache = new Amun_Form_Element_Input('cache', 'Cache');
		$cache->setType('checkbox');

		$panel->add($cache);


		$expire = new Amun_Form_Element_Input('expire', 'Expire');
		$expire->setType('text');

		$panel->add($expire);


		$param = new Amun_Form_Element_Input('param', 'Param');
		$param->setType('text');

		$panel->add($param);


		if($this->user->isAnonymous() || $this->user->hasInputExceeded())
		{
			$captcha = new Amun_Form_Element_Captcha('captcha', 'Captcha');
			$captcha->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/core/captcha');

			$panel->add($captcha);
		}


		$form->setContainer($panel);


		return $form;
	}

	public function update($id)
	{
		$record = Amun_Sql_Table_Registry::get('Content_Gadget')->getRecord($id);


		$form = new Amun_Form('PUT', $this->url);


		$panel = new Amun_Form_Element_Panel('gadget', 'Gadget');


		$id = new Amun_Form_Element_Input('id', 'Id', $record->id);
		$id->setType('hidden');

		$panel->add($id);


		$name = new Amun_Form_Element_Input('name', 'Name', $record->name);
		$name->setType('text');

		$panel->add($name);


		$title = new Amun_Form_Element_Input('title', 'Title', $record->title);
		$title->setType('text');

		$panel->add($title);


		$path = new Amun_Form_Element_Select('path', 'Path', $record->path);
		$path->setOptions($this->listGadget());

		$panel->add($path);


		$cache = new Amun_Form_Element_Input('cache', 'Cache', $record->cache);
		$cache->setType('checkbox');

		$panel->add($cache);


		$expire = new Amun_Form_Element_Input('expire', 'Expire', $record->expire);
		$expire->setType('text');

		$panel->add($expire);


		$param = new Amun_Form_Element_Input('param', 'Param', $record->getParam());
		$param->setType('text');

		$panel->add($param);


		if($this->user->isAnonymous() || $this->user->hasInputExceeded())
		{
			$captcha = new Amun_Form_Element_Captcha('captcha', 'Captcha');
			$captcha->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/core/captcha');

			$panel->add($captcha);
		}


		$form->setContainer($panel);


		return $form;
	}

	public function delete($id)
	{
		$record = Amun_Sql_Table_Registry::get('Content_Gadget')->getRecord($id);


		$form = new Amun_Form('DELETE', $this->url);


		$panel = new Amun_Form_Element_Panel('gadget', 'Gadget');


		$id = new Amun_Form_Element_Input('id', 'Id', $record->id);
		$id->setType('hidden');

		$panel->add($id);


		$name = new Amun_Form_Element_Input('name', 'Name', $record->name);
		$name->setType('text');
		$name->setDisabled(true);

		$panel->add($name);


		$title = new Amun_Form_Element_Input('title', 'Title', $record->title);
		$title->setType('text');
		$title->setDisabled(true);

		$panel->add($title);


		$path = new Amun_Form_Element_Select('path', 'Path', $record->path);
		$path->setOptions($this->listGadget());
		$path->setDisabled(true);

		$panel->add($path);


		$cache = new Amun_Form_Element_Input('cache', 'Cache', $record->cache);
		$cache->setType('checkbox');
		$cache->setDisabled(true);

		$panel->add($cache);


		$expire = new Amun_Form_Element_Input('expire', 'Expire', $record->expire);
		$expire->setType('text');
		$expire->setDisabled(true);

		$panel->add($expire);


		$param = new Amun_Form_Element_Input('param', 'Param', $record->getParam());
		$param->setType('text');
		$param->setDisabled(true);

		$panel->add($param);


		if($this->user->isAnonymous() || $this->user->hasInputExceeded())
		{
			$captcha = new Amun_Form_Element_Captcha('captcha', 'Captcha');
			$captcha->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/core/captcha');

			$panel->add($captcha);
		}


		$form->setContainer($panel);


		return $form;
	}

	private function listGadget()
	{
		$path   = PSX_PATH_MODULE . '/gadget';
		$gadget = array();

		// service gadgets
		$result = $this->sql->getAll('SELECT id, source, name FROM ' . $this->registry['table.core_service'] . ' WHERE status = ? ORDER BY name ASC', array(AmunService_Core_Service_Record::NORMAL));

		foreach($result as $row)
		{
			$this->scanDir($gadget, $row['name'], $this->config['amun_service_path'] . '/' . $row['source'] . '/gadget');
		}

		return $gadget;
	}

	private function scanDir(&$gadget, $name, $path)
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
						'value' => $name . '/gadget/' . $d,

					);
				}
			}
		}
	}
}

