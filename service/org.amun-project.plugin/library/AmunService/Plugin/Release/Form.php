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
 * Amun_Service_Plugin_Release_Form
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Service_Plugin
 * @version    $Revision: 666 $
 */
class AmunService_Plugin_Release_Form extends Amun_Data_FormAbstract
{
	public function create($pluginId = 0)
	{
		$form = new Amun_Form('POST', $this->url);


		$panel = new Amun_Form_Element_Panel('release', 'Release');


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


		$status = new Amun_Form_Element_Select('status', 'Status');
		$status->setOptions($this->getStatus());

		$panel->add($status);


		$version = new Amun_Form_Element_Input('version', 'Version');
		$version->setType('text');

		$panel->add($version);


		$href = new Amun_Form_Element_Input('href', 'Href');
		$href->setType('url');

		$panel->add($href);


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
		$record = Amun_Sql_Table_Registry::get('Service_Release')->getRecord($id);


		$form = new Amun_Form('DELETE', $this->url);


		$panel = new Amun_Form_Element_Panel('release', 'Release');


		$id = new Amun_Form_Element_Input('id', 'ID', $record->id);
		$id->setType('hidden');

		$panel->add($id);


		$status = new Amun_Form_Element_Select('status', 'Status', $record->status);
		$status->setOptions($this->getStatus());

		$panel->add($status);


		$version = new Amun_Form_Element_Input('version', 'Version', $record->version);
		$version->setType('text');

		$panel->add($version);


		if($this->user->isAnonymous() || $this->user->hasInputExceeded())
		{
			$captcha = new Amun_Form_Element_Captcha('captcha', 'Captcha');
			$captcha->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/system/captcha');

			$panel->add($captcha);
		}


		$form->setContainer($panel);


		return $form;
	}

	private function getStatus()
	{
		$status = array();
		$result = Amun_Service_Plugin_Release::getStatus();

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

