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

namespace AmunService\Content\Page\Option;

use Amun\DataFactory;
use Amun\Data\FormAbstract;
use Amun\Form as AmunForm;
use Amun\Form\Element\Panel;
use Amun\Form\Element\Reference;
use Amun\Form\Element\Input;
use Amun\Form\Element\TabbedPane;
use Amun\Form\Element\Textarea;
use Amun\Form\Element\Captcha;
use Amun\Form\Element\Select;
use AmunService\Core\Service;

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


		$panel = new Panel('option', 'Option');


		$optionId = new Select('optionId', 'Option ID');
		$optionId->setOptions($this->getOption());

		$panel->add($optionId);


		$rightId = new Select('rightId', 'Right ID');
		$rightId->setOptions($this->getRight());

		$panel->add($rightId);


		$srcPageId = new Reference('srcPageId', 'Source Page ID');
		$srcPageId->setValueField('id');
		$srcPageId->setLabelField('title');
		$srcPageId->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/content/page');

		$panel->add($srcPageId);


		$destPageId = new Reference('destPageId', 'Destination Page ID');
		$destPageId->setValueField('id');
		$destPageId->setLabelField('title');
		$destPageId->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/content/page');

		$panel->add($destPageId);


		$name = new Input('name', 'Name');
		$name->setType('text');

		$panel->add($name);


		$href = new Input('href', 'Href');
		$href->setType('text');

		$panel->add($href);


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
		$record = $this->hm->getHandler('Content_Page_Option')->getRecord($id);


		$form = new AmunForm('PUT', $this->url);


		$panel = new Panel('option', 'Option');


		$id = new Input('id', 'ID', $record->id);
		$id->setType('hidden');

		$panel->add($id);


		$optionId = new Select('optionId', 'Option ID', $record->optionId);
		$optionId->setOptions($this->getOption());

		$panel->add($optionId);


		$rightId = new Select('rightId', 'Right ID', $record->rightId);
		$rightId->setOptions($this->getRight());

		$panel->add($rightId);


		$srcPageId = new Reference('srcPageId', 'Source Page ID', $record->srcPageId);
		$srcPageId->setValueField('id');
		$srcPageId->setLabelField('title');
		$srcPageId->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/content/page');

		$panel->add($srcPageId);


		$destPageId = new Reference('destPageId', 'Destination Page ID', $record->destPageId);
		$destPageId->setValueField('id');
		$destPageId->setLabelField('title');
		$destPageId->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/content/page');

		$panel->add($destPageId);


		$name = new Input('name', 'Name', $record->name);
		$name->setType('text');

		$panel->add($name);


		$href = new Input('href', 'Href', $record->href);
		$href->setType('text');

		$panel->add($href);


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
		$record = $this->hm->getHandler('Content_Page_Option')->getRecord($id);


		$form = new AmunForm('DELETE', $this->url);


		$panel = new Panel('option', 'Option');


		$id = new Input('id', 'ID', $record->id);
		$id->setType('hidden');

		$panel->add($id);


		$optionId = new Select('optionId', 'Option ID', $record->optionId);
		$optionId->setOptions($this->getOption());
		$optionId->setDisabled(true);

		$panel->add($optionId);


		$rightId = new Select('rightId', 'Right ID', $record->rightId);
		$rightId->setOptions($this->getRight());
		$rightId->setDisabled(true);

		$panel->add($rightId);


		$srcPageId = new Reference('srcPageId', 'Source Page ID', $record->srcPageId);
		$srcPageId->setValueField('id');
		$srcPageId->setLabelField('title');
		$srcPageId->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/content/page');
		$srcPageId->setDisabled(true);

		$panel->add($srcPageId);


		$destPageId = new Reference('destPageId', 'Destination Page ID', $record->destPageId);
		$destPageId->setValueField('id');
		$destPageId->setLabelField('title');
		$destPageId->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/content/page');
		$destPageId->setDisabled(true);

		$panel->add($destPageId);


		$name = new Input('name', 'Name', $record->name);
		$name->setType('text');
		$name->setDisabled(true);

		$panel->add($name);


		$href = new Input('href', 'Href', $record->href);
		$href->setType('text');
		$href->setDisabled(true);

		$panel->add($href);


		if($this->user->isAnonymous() || $this->user->hasInputExceeded())
		{
			$captcha = new Captcha('captcha', 'Captcha');
			$captcha->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/core/captcha');

			$panel->add($captcha);
		}


		$form->setContainer($panel);


		return $form;
	}

	private function getOption()
	{
		$status = Service\Record::NORMAL;
		$sql    = <<<SQL
SELECT
	`service`.`id`,
	`service`.`name`,
	`option`.`name` AS optionName
FROM 
	{$this->registry['table.core_service']} `service`
INNER JOIN 
	{$this->registry['table.core_service_option']} `option`
	ON `option`.`serviceId` = `service`.`id`
WHERE 
	`service`.`status` = {$status}
ORDER BY 
	`option`.`name` ASC
SQL;

		$option = array();
		$result = $this->sql->getAll($sql);

		foreach($result as $row)
		{
			array_push($option, array(

				'label' => $row['name'] . ' / ' . $row['optionName'],
				'value' => $row['id'],

			));
		}

		return $option;
	}

	private function getRight()
	{
		$right  = array();
		$result = $this->sql->getAll('SELECT id, name FROM ' . $this->registry['table.user_right'] . ' ORDER BY name ASC');

		foreach($result as $row)
		{
			array_push($right, array(

				'label' => $row['name'],
				'value' => $row['id'],

			));
		}

		return $right;
	}
}

