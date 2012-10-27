<?php
/*
 *  $Id: Form.php 696 2012-06-08 16:00:47Z k42b3.x@googlemail.com $
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
 * Amun_Service_Plugin_Maintainer_Form
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Service_Plugin
 * @version    $Revision: 696 $
 */
class Amun_Service_Plugin_Maintainer_Form extends Amun_Data_FormAbstract
{
	public function create($pluginId = 0)
	{
		$form = new Amun_Form('POST', $this->url);


		$panel = new Amun_Form_Element_Panel('maintainer', 'Maintainer');


		if(empty($pluginId))
		{
			$pluginId = new Amun_Form_Element_Reference('pluginId', 'Plugin ID');
			$pluginId->setValueField('id');
			$pluginId->setLabelField('title');
			$pluginId->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/service/plugin');

			$panel->add($pluginId);
		}
		else
		{
			$pluginId = new Amun_Form_Element_Input('pluginId', 'Plugin ID', $pluginId);
			$pluginId->setType('hidden');

			$panel->add($pluginId);
		}


		$userId = new Amun_Form_Element_Reference('userId', 'User ID');
		$userId->setValueField('id');
		$userId->setLabelField('name');
		$userId->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/user/account');

		$panel->add($userId);


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
		throw new PSX_Data_Exception('You cant update a maintainer record');
	}

	public function delete($id)
	{
		$record = Amun_Sql_Table_Registry::get('Service_Plugin')->getRecord($id);


		$form = new Amun_Form('DELETE', $this->url);


		$panel = new Amun_Form_Element_Panel('plugin', 'Plugin');


		$id = new Amun_Form_Element_Input('id', 'ID', $record->id);
		$id->setType('hidden');

		$panel->add($id);


		$pluginId = new Amun_Form_Element_Reference('pluginId', 'Plugin ID', $record->pluginId);
		$pluginId->setValueField('id');
		$pluginId->setLabelField('title');
		$pluginId->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/service/plugin');

		$panel->add($pluginId);


		$userId = new Amun_Form_Element_Reference('userId', 'User ID', $record->userId);
		$userId->setValueField('id');
		$userId->setLabelField('name');
		$userId->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/user/account');

		$panel->add($userId);


		if($this->user->isAnonymous() || $this->user->hasInputExceeded())
		{
			$captcha = new Amun_Form_Element_Captcha('captcha', 'Captcha');
			$captcha->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/system/captcha');

			$panel->add($captcha);
		}


		$form->setContainer($panel);


		return $form;
	}
}

