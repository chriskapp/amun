<?php
/*
 *  $Id: Form.php 875 2012-09-30 13:51:45Z k42b3.x@googlemail.com $
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
 * Amun_Service_Pipe_Form
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Service_Pipe
 * @version    $Revision: 875 $
 */
class Amun_Service_Pipe_Form extends Amun_Data_FormAbstract
{
	public function create($pageId = 0)
	{
		$form = new Amun_Form('POST', $this->url);


		$panel = new Amun_Form_Element_Panel('page', 'Pipe');


		if(empty($pageId))
		{
			$pageId = new Amun_Form_Element_Reference('pageId', 'Page ID');
			$pageId->setValueField('id');
			$pageId->setLabelField('title');
			$pageId->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/content/page');

			$panel->add($pageId);
		}
		else
		{
			$pageId = new Amun_Form_Element_Input('pageId', 'Page ID', $pageId);
			$pageId->setType('hidden');

			$panel->add($pageId);
		}


		$media = new Amun_Form_Element_Select('mediaId', 'Media');
		$media->setOptions($this->getMedia());

		$panel->add($media);


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
		$record = Amun_Sql_Table_Registry::get('Service_Pipe')->getRecord($id);


		$form = new Amun_Form('PUT', $this->url);


		$panel = new Amun_Form_Element_Panel('page', 'Page');


		$id = new Amun_Form_Element_Input('id', 'ID', $record->id);
		$id->setType('hidden');

		$panel->add($id);


		$media = new Amun_Form_Element_Select('mediaId', 'Media', $record->mediaId);
		$media->setOptions($this->getMedia());

		$panel->add($media);


		if($this->user->isAnonymous() || $this->user->hasInputExceeded())
		{
			$captcha = new Amun_Form_Element_Captcha('captcha', 'Captcha');
			$captcha->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/system/captcha');

			$panel->add($captcha);
		}


		$form->setContainer($panel);


		return $form;
	}

	public function delete($id)
	{
		$record = Amun_Sql_Table_Registry::get('Service_Pipe')->getRecord($id);


		$form = new Amun_Form('PUT', $this->url);


		$panel = new Amun_Form_Element_Panel('page', 'Page');


		$id = new Amun_Form_Element_Input('id', 'ID', $record->id);
		$id->setType('hidden');

		$panel->add($id);


		$media = new Amun_Form_Element_Select('mediaId', 'Media', $record->mediaId);
		$media->setOptions($this->getMedia());
		$media->setDisabled(true);

		$panel->add($media);


		if($this->user->isAnonymous() || $this->user->hasInputExceeded())
		{
			$captcha = new Amun_Form_Element_Captcha('captcha', 'Captcha');
			$captcha->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/system/captcha');

			$panel->add($captcha);
		}


		$form->setContainer($panel);


		return $form;
	}

	private function getMedia()
	{
		$media  = array();
		$result = $this->sql->getAll('SELECT id, path FROM ' . $this->registry['table.content_media'] . ' ORDER BY path ASC');

		foreach($result as $row)
		{
			array_push($media, array(

				'label' => $row['path'],
				'value' => $row['id'],

			));
		}

		return $media;
	}
}
