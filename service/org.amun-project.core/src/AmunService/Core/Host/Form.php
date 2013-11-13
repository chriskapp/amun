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

namespace AmunService\Core\Host;

use Amun\Data\FormAbstract;
use Amun\Form as AmunForm;
use Amun\Form\Element\Panel;
use Amun\Form\Element\Reference;
use Amun\Form\Element\Input;
use Amun\Form\Element\TabbedPane;
use Amun\Form\Element\Textarea;
use Amun\Form\Element\Captcha;
use Amun\Form\Element\Select;
use AmunService\Core\Host;

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


		$panel = new Panel('api', 'API');


		$status = new Select('status', 'Status');
		$status->setOptions($this->getStatus());

		$panel->add($status);


		$name = new Input('name', 'Name');
		$name->setType('text');

		$panel->add($name);


		$consumerKey = new Input('consumerKey', 'Consumer key');
		$consumerKey->setType('text');

		$panel->add($consumerKey);


		$consumerSecret = new Input('consumerSecret', 'Consumer secret');
		$consumerSecret->setType('text');

		$panel->add($consumerSecret);


		$url = new Input('url', 'Url');
		$url->setType('url');

		$panel->add($url);


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
		$record = $this->hm->getHandler('AmunService\Core\Host')->getRecord($id);


		$form = new AmunForm('PUT', $this->url);


		$panel = new Panel('api', 'API');


		$id = new Input('id', 'ID', $record->id);
		$id->setType('hidden');

		$panel->add($id);


		$status = new Select('status', 'Status', $record->status);
		$status->setOptions($this->getStatus());

		$panel->add($status);


		$name = new Input('name', 'Name', $record->name);
		$name->setType('text');

		$panel->add($name);


		$consumerKey = new Input('consumerKey', 'Consumer key', $record->consumerKey);
		$consumerKey->setType('text');

		$panel->add($consumerKey);


		$consumerSecret = new Input('consumerSecret', 'Consumer secret', $record->consumerSecret);
		$consumerSecret->setType('text');

		$panel->add($consumerSecret);


		$url = new Input('url', 'Url', $record->url);
		$url->setType('url');

		$panel->add($url);


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
		$record = $this->hm->getHandler('AmunService\Core\Host')->getRecord($id);


		$form = new AmunForm('DELETE', $this->url);


		$panel = new Panel('api', 'API');


		$id = new Input('id', 'ID', $record->id);
		$id->setType('hidden');

		$panel->add($id);


		$status = new Select('status', 'Status', $record->status);
		$status->setOptions($this->getStatus());
		$status->setDisabled(true);

		$panel->add($status);


		$name = new Input('name', 'Name', $record->name);
		$name->setType('text');
		$name->setDisabled(true);

		$panel->add($name);


		$consumerKey = new Input('consumerKey', 'Consumer key', $record->consumerKey);
		$consumerKey->setType('text');
		$consumerKey->setDisabled(true);

		$panel->add($consumerKey);


		$consumerSecret = new Input('consumerSecret', 'Consumer secret', $record->consumerSecret);
		$consumerSecret->setType('text');
		$consumerSecret->setDisabled(true);

		$panel->add($consumerSecret);


		$url = new Input('url', 'Url', $record->url);
		$url->setType('url');
		$url->setDisabled(true);

		$panel->add($url);


		if($this->user->isAnonymous() || $this->user->hasInputExceeded())
		{
			$captcha = new Captcha('captcha', 'Captcha');
			$captcha->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/core/captcha');

			$panel->add($captcha);
		}


		$form->setContainer($panel);


		return $form;
	}

	private function getStatus()
	{
		$status = array();
		$result = Host\Record::getStatus();

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

