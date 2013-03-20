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

namespace AmunService\Core\Approval\Record;

use Amun\DataFactory;
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

/**
 * AmunService_Core_System_Approval_Record_Form
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_System_Approval
 * @version    $Revision: 666 $
 */
class Form extends FormAbstract
{
	public function create()
	{
		throw new Exception('You cant create a approval record');
	}

	public function update($id)
	{
		$record = DataFactory::getTable('Core_Approval_Record')->getRecord($id);


		$form = new AmunForm('PUT', $this->url);


		$panel = new Panel('record', 'Record');


		$id = new Input('id', 'ID', $record->id);
		$id->setType('hidden');

		$panel->add($id);


		$type = new Select('type', 'Type', $record->type);
		$type->setOptions($this->getType());
		$type->setDisabled(true);

		$panel->add($type);


		$table = new Select('table', 'Table', $record->table);
		$table->setOptions($this->getTable());
		$table->setDisabled(true);

		$panel->add($table);


		/*
		$rec = new Input('record', 'Record', $record->record);
		$rec->setType('text');
		$rec->setDisabled(true);

		$panel->add($rec);
		*/


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
		$record = DataFactory::getTable('Core_Approval_Record')->getRecord($id);


		$form = new AmunForm('DELETE', $this->url);


		$panel = new Panel('record', 'Record');


		$id = new Input('id', 'ID', $record->id);
		$id->setType('hidden');

		$panel->add($id);


		$type = new Select('type', 'Type', $record->type);
		$type->setOptions($this->getType());
		$type->setDisabled(true);

		$panel->add($type);


		$table = new Select('table', 'Table', $record->table);
		$table->setOptions($this->getTable());
		$table->setDisabled(true);

		$panel->add($table);


		/*
		$rec = new Input('record', 'Record', $record->record);
		$rec->setType('text');
		$rec->setDisabled(true);

		$panel->add($rec);
		*/


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
		$types  = array();
		$result = Record::getType();

		foreach($result as $k => $v)
		{
			array_push($types, array(

				'label' => $v,
				'value' => $v,

			));
		}

		return $types;
	}

	private function getTable()
	{
		$result = $this->sql->getAll('SHOW TABLES');
		$tables = array();

		foreach($result as $row)
		{
			$name = current($row);

			array_push($tables, array(

				'label' => $name,
				'value' => $name,

			));
		}

		return $tables;
	}
}

