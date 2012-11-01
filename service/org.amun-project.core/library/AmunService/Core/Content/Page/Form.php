<?php
/*
 *  $Id: Form.php 840 2012-09-11 22:19:37Z k42b3.x@googlemail.com $
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
 * AmunService_Core_Content_Page_Form
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Content_Page
 * @version    $Revision: 840 $
 */
class AmunService_Core_Content_Page_Form extends Amun_Data_FormAbstract
{
	public function getUrl()
	{
		return $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/content/page';
	}

	public function create()
	{
		$form = new Amun_Form('POST', $this->url);


		$tabbedPane = new Amun_Form_Element_TabbedPane('page', 'Page');


		$panel = new Amun_Form_Element_Panel('settings', 'Settings');


		$parentId = new Amun_Form_Element_Reference('parentId', 'Parent ID');
		$parentId->setValueField('id');
		$parentId->setLabelField('title');
		$parentId->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/content/page');

		$panel->add($parentId);


		$service = new Amun_Form_Element_Select('serviceId', 'Service');
		$service->setOptions($this->getService());

		$panel->add($service);


		$status = new Amun_Form_Element_Select('status', 'Status');
		$status->setOptions($this->getStatus());

		$panel->add($status);


		$title = new Amun_Form_Element_Input('title', 'Title');
		$title->setType('text');

		$panel->add($title);


		$template = new Amun_Form_Element_Select('template', 'Template');
		$template->setOptions($this->getTemplate());

		$panel->add($template);


		if($this->user->isAnonymous() || $this->user->hasInputExceeded())
		{
			$captcha = new Amun_Form_Element_Captcha('captcha', 'Captcha');
			$captcha->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/system/captcha');

			$panel->add($captcha);
		}


		$tabbedPane->add($panel);


		$panel = new Amun_Form_Element_Panel('extras', 'Extras');


		$right = new Amun_Form_Element_Select('rightId', 'Right');
		$right->setOptions($this->getRights());

		$panel->add($right);


		$sort = new Amun_Form_Element_Input('sort', 'Sort');
		$sort->setType('text');

		$panel->add($sort);


		$load = new Amun_Form_Element_Input('load', 'Load', 6);
		$load->setType('text');

		$panel->add($load);


		$cache = new Amun_Form_Element_Input('cache', 'Cache');
		$cache->setType('text');

		$panel->add($cache);


		$expire = new Amun_Form_Element_Input('expire', 'Expire');
		$expire->setType('text');

		$panel->add($expire);


		$tabbedPane->add($panel);


		$form->setContainer($tabbedPane);


		return $form;
	}

	public function update($id)
	{
		$record = Amun_Sql_Table_Registry::get('Content_Page')->getRecord($id);


		$form = new Amun_Form('PUT', $this->url);


		$tabbedPane = new Amun_Form_Element_TabbedPane('page', 'Page');


		$panel = new Amun_Form_Element_Panel('settings', 'Settings');


		$id = new Amun_Form_Element_Input('id', 'ID', $record->id);
		$id->setType('hidden');

		$panel->add($id);


		$parentId = new Amun_Form_Element_Reference('parentId', 'Parent ID', $record->parentId);
		$parentId->setValueField('id');
		$parentId->setLabelField('title');
		$parentId->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/content/page');

		$panel->add($parentId);


		$service = new Amun_Form_Element_Select('serviceId', 'Service', $record->serviceId);
		$service->setOptions($this->getService());

		$panel->add($service);


		$status = new Amun_Form_Element_Select('status', 'Status', $record->status);
		$status->setOptions($this->getStatus());

		$panel->add($status);


		$title = new Amun_Form_Element_Input('title', 'Title', $record->title);
		$title->setType('text');

		$panel->add($title);


		$template = new Amun_Form_Element_Select('template', 'Template', $record->template);
		$template->setOptions($this->getTemplate());

		$panel->add($template);


		if($this->user->isAnonymous() || $this->user->hasInputExceeded())
		{
			$captcha = new Amun_Form_Element_Captcha('captcha', 'Captcha');
			$captcha->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/system/captcha');

			$panel->add($captcha);
		}


		$tabbedPane->add($panel);


		$panel = new Amun_Form_Element_Panel('extras', 'Extras');


		$right = new Amun_Form_Element_Select('rightId', 'Right', $record->rightId);
		$right->setOptions($this->getRights());

		$panel->add($right);


		$sort = new Amun_Form_Element_Input('sort', 'Sort', $record->sort);
		$sort->setType('text');

		$panel->add($sort);


		$load = new Amun_Form_Element_Input('load', 'Load', $record->load);
		$load->setType('text');

		$panel->add($load);


		$cache = new Amun_Form_Element_Input('cache', 'Cache', $record->cache);
		$cache->setType('text');

		$panel->add($cache);


		$expire = new Amun_Form_Element_Input('expire', 'Expire', $record->expire);
		$expire->setType('text');

		$panel->add($expire);


		$tabbedPane->add($panel);


		$form->setContainer($tabbedPane);


		return $form;
	}

	public function delete($id)
	{
		$record = Amun_Sql_Table_Registry::get('Content_Page')->getRecord($id);


		$form = new Amun_Form('DELETE', $this->url);


		$tabbedPane = new Amun_Form_Element_TabbedPane('page', 'Page');


		$panel = new Amun_Form_Element_Panel('settings', 'Settings');


		// settings
		$id = new Amun_Form_Element_Input('id', 'ID', $record->id);
		$id->setType('hidden');

		$panel->add($id);


		$parentId = new Amun_Form_Element_Reference('parentId', 'Parent ID', $record->parentId);
		$parentId->setValueField('id');
		$parentId->setLabelField('title');
		$parentId->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/content/page');
		$parentId->setDisabled(true);

		$panel->add($parentId);


		$service = new Amun_Form_Element_Select('serviceId', 'Service', $record->serviceId);
		$service->setOptions($this->getService());
		$service->setDisabled(true);

		$panel->add($service);


		$status = new Amun_Form_Element_Select('status', 'Status', $record->status);
		$status->setOptions($this->getStatus());
		$status->setDisabled(true);

		$panel->add($status);


		$title = new Amun_Form_Element_Input('title', 'Title', $record->title);
		$title->setType('text');
		$title->setDisabled(true);

		$panel->add($title);


		$template = new Amun_Form_Element_Select('template', 'Template', $record->template);
		$template->setOptions($this->getTemplate());
		$template->setDisabled(true);

		$panel->add($template);


		if($this->user->isAnonymous() || $this->user->hasInputExceeded())
		{
			$captcha = new Amun_Form_Element_Captcha('captcha', 'Captcha');
			$captcha->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/system/captcha');

			$panel->add($captcha);
		}


		$tabbedPane->add($panel);


		// extras
		$panel = new Amun_Form_Element_Panel('extras', 'Extras');


		$right = new Amun_Form_Element_Select('rightId', 'Right', $record->rightId);
		$right->setOptions($this->getRights());
		$right->setDisabled(true);

		$panel->add($right);


		$sort = new Amun_Form_Element_Input('sort', 'Sort', $record->sort);
		$sort->setType('text');
		$sort->setDisabled(true);

		$panel->add($sort);


		$load = new Amun_Form_Element_Input('load', 'Load', $record->load);
		$load->setType('text');
		$load->setDisabled(true);

		$panel->add($load);


		$cache = new Amun_Form_Element_Input('cache', 'Cache', $record->cache);
		$cache->setType('text');
		$cache->setDisabled(true);

		$panel->add($cache);


		$expire = new Amun_Form_Element_Input('expire', 'Expire', $record->expire);
		$expire->setType('text');
		$expire->setDisabled(true);

		$panel->add($expire);


		$tabbedPane->add($panel);


		$form->setContainer($tabbedPane);


		return $form;
	}

	private function getStatus()
	{
		$status = array();
		$result = Amun_Content_Page::getStatus();

		foreach($result as $k => $v)
		{
			array_push($status, array(

				'label' => $v,
				'value' => $k,

			));
		}

		return $status;
	}

	private function getService()
	{
		$service = array();
		$result  = $this->sql->getAll('SELECT id, name FROM ' . $this->registry['table.core_content_service'] . ' WHERE status = ' . intval(Amun_Content_Service::NORMAL) . ' ORDER BY name ASC');

		foreach($result as $row)
		{
			array_push($service, array(

				'label' => $row['name'],
				'value' => $row['id'],

			));
		}

		return $service;
	}

	public function getTemplate()
	{
		$path     = PSX_PATH_TEMPLATE . '/' . $this->config['psx_template_dir'];
		$template = array();
		$result   = scandir($path);

		foreach($result as $file)
		{
			$t = $path . '/' . $file;

			if($file[0] != '.' && is_file($t) && pathinfo($t, PATHINFO_EXTENSION) == 'tpl')
			{
				array_push($template, array(

					'label' => $file,
					'value' => $file,

				));
			}
		}

		return $template;
	}

	private function getRights()
	{
		$rights = array();

		array_push($rights, array(

			'label' => '-',
			'value' => 0,

		));

		$result = $this->sql->getAll('SELECT id, description FROM ' . $this->registry['table.core_user_right'] . ' ORDER BY name ASC');

		foreach($result as $row)
		{
			array_push($rights, array(

				'label' => $row['description'],
				'value' => $row['id'],

			));
		}

		return $rights;
	}
}

