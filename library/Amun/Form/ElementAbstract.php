<?php
/*
 *  $Id: ElementAbstract.php 635 2012-05-01 19:46:37Z k42b3.x@googlemail.com $
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

namespace Amun\Form;

use PSX\Data\RecordAbstract;

/**
 * Amun_Form_ElementAbstract
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Form
 * @version    $Revision: 635 $
 */
abstract class ElementAbstract extends RecordAbstract
{
	protected $class;
	protected $ref;
	protected $label;
	protected $value;
	protected $disabled;

	public function __construct($ref, $label, $value = null)
	{
		$this->class = $this->getName();
		$this->ref   = $ref;
		$this->label = $label;
		$this->value = $value;
	}

	public function getFields()
	{
		return array(

			'class'    => $this->class,
			'ref'      => $this->ref,
			'label'    => $this->label,
			'value'    => $this->value,
			'disabled' => $this->disabled,

		);
	}

	public function setClass($class)
	{
		$this->class = $class;
	}

	public function setRef($ref)
	{
		$this->ref = $ref;
	}

	public function setLabel($label)
	{
		$this->label = $label;
	}

	public function setValue($value)
	{
		$this->value = $value;
	}

	public function getClass()
	{
		return $this->class;
	}

	public function getRef()
	{
		return $this->ref;
	}

	public function getLabel()
	{
		return $this->label;
	}

	public function getValue()
	{
		return $this->value;
	}

	public function setDisabled($disabled)
	{
		$this->disabled = (boolean) $disabled;
	}
}

