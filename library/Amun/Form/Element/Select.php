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

namespace Amun\Form\Element;

use Amun\Form\ElementAbstract;
use Amun\Exception;
use PSX\Data\RecordInfo;

/**
 * Select
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Select extends ElementAbstract
{
	protected $children = array();
	protected $options;

	public function getRecordInfo()
	{
		return new RecordInfo('select', array(
			'children' => $this->children,
		), parent::getRecordInfo());
	}

	public function setOptions(array $options)
	{
		$this->options = $options;

		if(!empty($options))
		{
			if(!isset($options[0]['label']))
			{
				throw new Exception('Key "label" not set');
			}

			if(!isset($options[0]['value']))
			{
				throw new Exception('Key "value" not set');
			}

			foreach($this->options as $option)
			{
				$this->children['item'][] = array(

					'class' => 'option',
					'label' => $option['label'],
					'value' => $option['value'],

				);
			}
		}
	}

	public function getOptions()
	{
		return $this->options;
	}
}

