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

namespace AmunService\Openid;

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
use AmunService\Openid;

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
		throw new Exception('Create a openid record is not possible');
	}

	public function update($id)
	{
		throw new Exception('Update a openid record is not possible');
	}

	public function delete($id)
	{
		$record = $this->hm->getHandler('AmunService\Openid')->getRecord($id);


		$form = new AmunForm('DELETE', $this->url);


		$panel = new Panel('connect', 'Connect');


		$id = new Input('id', 'ID', $record->id);
		$id->setType('hidden');

		$panel->add($id);


		$status = new Select('status', 'Status', $record->status);
		$status->setOptions($this->getStatus());
		$status->setDisabled(true);

		$panel->add($status);


		$claimedId = new Input('claimedId', 'Claimed Id', $record->claimedId);
		$claimedId->setType('url');
		$claimedId->setDisabled(true);

		$panel->add($claimedId);


		$identity = new Input('identity', 'Identity', $record->identity);
		$identity->setType('url');
		$identity->setDisabled(true);

		$panel->add($identity);


		$returnTo = new Input('returnTo', 'Return to', $record->returnTo);
		$returnTo->setType('url');
		$returnTo->setDisabled(true);

		$panel->add($returnTo);


		$expire = new Input('expire', 'Expire', $record->expire);
		$expire->setType('text');
		$expire->setDisabled(true);

		$panel->add($expire);


		$date = new Input('date', 'Date', $record->date);
		$date->setType('text');
		$date->setDisabled(true);

		$panel->add($date);


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
		$result = Openid\Record::getStatus();

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

