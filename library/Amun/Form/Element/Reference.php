<?php
/*
 *  $Id: Reference.php 635 2012-05-01 19:46:37Z k42b3.x@googlemail.com $
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

namespace Amun\Form\Element;

use Amun\Form\ElementAbstract;

/**
 * Amun_Form_Element_Reference
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Form
 * @version    $Revision: 635 $
 */
class Reference extends ElementAbstract
{
	protected $valueField;
	protected $labelField;
	protected $src;

	public function getName()
	{
		return 'reference';
	}

	public function getFields()
	{
		return array_merge(parent::getFields(), array(

			'valueField' => $this->valueField,
			'labelField' => $this->labelField,
			'src'        => $this->src,

		));
	}

	public function setValueField($valueField)
	{
		$this->valueField = $valueField;
	}

	public function getValueField()
	{
		return $this->valueField;
	}

	public function setLabelField($labelField)
	{
		$this->labelField = $labelField;
	}

	public function getLabelField()
	{
		return $this->labelField;
	}

	public function setSrc($src)
	{
		$this->src = $src;
	}

	public function getSrc()
	{
		return $this->src;
	}
}

