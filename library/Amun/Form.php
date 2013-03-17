<?php
/*
 *  $Id: Form.php 692 2012-06-07 15:13:59Z k42b3.x@googlemail.com $
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

namespace Amun;

use Amun\Form\ContainerInterface;
use PSX\Data\RecordAbstract;

/**
 * Amun_Form
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Form
 * @version    $Revision: 692 $
 */
class Form extends RecordAbstract
{
	protected $class;
	protected $ref;
	protected $method;
	protected $action;
	protected $enctype;
	protected $item;

	public function __construct($method, $action, $enctype = 'application/x-www-form-urlencoded')
	{
		$this->class   = 'form';
		$this->method  = $method;
		$this->action  = $action;
		$this->enctype = $enctype;
	}

	public function getName()
	{
		return 'form';
	}

	public function getFields()
	{
		return array(

			'class'   => $this->class,
			'ref'     => substr(md5($this->method . $this->action), 0, 8),
			'method'  => $this->method,
			'action'  => $this->action,
			'enctype' => $this->enctype,
			'item'    => $this->item,

		);
	}

	public function setContainer(ContainerInterface $container)
	{
		$this->item = $container->getFields();
	}

	public function setMethod($method)
	{
		$this->method = $method;
	}

	public function getMethod()
	{
		return $this->method;
	}
}

