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

namespace AmunService\Vcshook;

use Amun\Data\FormAbstract;
use Amun\DataFactory;
use Amun\Form as AmunForm;
use Amun\Form\Element;
use AmunService\Vcshook;

/**
 * Amun_Service_Vcshook_Form
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Service_Vcshook
 * @version    $Revision: 666 $
 */
class Form extends FormAbstract
{
	public function create()
	{
		$form = new AmunForm('POST', $this->url);


		$panel = new Element\Panel('vcshook', 'Vcshook');


		$type = new Element\Select('type', 'Type');
		$type->setOptions($this->getType());

		$panel->add($type);


		$url = new Element\Input('url', 'Url');
		$url->setType('url');

		$panel->add($url);


		if($this->user->isAnonymous() || $this->user->hasInputExceeded())
		{
			$captcha = new Element\Captcha('captcha', 'Captcha');
			$captcha->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/system/captcha');

			$panel->add($captcha);
		}


		$form->setContainer($panel);


		return $form;
	}

	public function update($id)
	{
		$record = DataFactory::get('Vcshook')->getRecord($id);


		$form = new AmunForm('PUT', $this->url);


		$panel = new Element\Panel('vcshook', 'Vcshook');


		$id = new Element\Input('id', 'ID', $record->id);
		$id->setType('hidden');

		$panel->add($id);


		$type = new Element\Select('type', 'Type', $record->type);
		$type->setOptions($this->getType());

		$panel->add($type);


		$url = new Element\Input('url', 'Url', $record->url);
		$url->setType('url');

		$panel->add($url);


		$url = $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/vcshook/callback/' . $record->type . '/' . $record->secret;

		$callback = new Element\Input('callback', 'Callback', $url);
		$callback->setType('text');

		$panel->add($callback);


		if($this->user->isAnonymous() || $this->user->hasInputExceeded())
		{
			$captcha = new Element\Captcha('captcha', 'Captcha');
			$captcha->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/system/captcha');

			$panel->add($captcha);
		}


		$form->setContainer($panel);


		return $form;
	}

	public function delete($id)
	{
		$record = DataFactory::get('Vcshook')->getRecord($id);


		$form = new AmunForm('DELETE', $this->url);


		$panel = new Element\Panel('vcshook', 'Vcshook');


		$id = new Element\Input('id', 'ID', $record->id);
		$id->setType('hidden');

		$panel->add($id);


		$type = new Element\Select('type', 'Type', $record->type);
		$type->setOptions($this->getType());
		$type->setDisabled(true);

		$panel->add($type);


		$url = new Element\Input('url', 'Url', $record->url);
		$url->setType('url');
		$url->setDisabled(true);

		$panel->add($url);


		if($this->user->isAnonymous() || $this->user->hasInputExceeded())
		{
			$captcha = new Element\Captcha('captcha', 'Captcha');
			$captcha->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/system/captcha');

			$panel->add($captcha);
		}


		$form->setContainer($panel);


		return $form;
	}

	private function getType()
	{
		$type   = array();
		$result = Vcshook\Record::getType();

		foreach($result as $k => $v)
		{
			array_push($type, array(

				'label' => $v,
				'value' => $k,

			));
		}

		return $type;
	}
}

