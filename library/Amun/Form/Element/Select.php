<?php
/*
 *  $Id: Select.php 635 2012-05-01 19:46:37Z k42b3.x@googlemail.com $
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
 * Amun_Form_Element_Select
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Form
 * @version    $Revision: 635 $
 */
class Amun_Form_Element_Select extends Amun_Form_ElementAbstract
{
	protected $children = array();
	protected $options;

	public function getName()
	{
		return 'select';
	}

	public function getFields()
	{
		return array_merge(parent::getFields(), array(

			'children' => $this->children,

		));
	}

	public function setOptions(array $options)
	{
		$this->options = $options;

		if(!empty($options))
		{
			if(!isset($options[0]['label']))
			{
				throw new Amun_Exception('Key "label" not set');
			}

			if(!isset($options[0]['value']))
			{
				throw new Amun_Exception('Key "value" not set');
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

