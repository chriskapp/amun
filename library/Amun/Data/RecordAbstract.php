<?php
/*
 *  $Id: RecordAbstract.php 762 2012-07-01 17:07:10Z k42b3.x@googlemail.com $
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

namespace Amun\Data;

use Amun\DataFactory;
use Amun\Sql\TableInterface;
use PSX\Data\Record\TableAbstract;

/**
 * Amun_Data_RecordAbstract
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Data
 * @version    $Revision: 762 $
 */
abstract class RecordAbstract extends TableAbstract
{
	const INSERT = 0x1;
	const UPDATE = 0x2;
	const DELETE = 0x3;

	protected $_base;
	protected $_config;
	protected $_sql;
	protected $_registry;
	protected $_validate;
	protected $_table;

	public $captcha;

	public function __construct(TableInterface $table)
	{
		$ct = DataFactory::getInstance()->getContainer();

		$this->_base     = $ct->getBase();
		$this->_config   = $ct->getConfig();
		$this->_sql      = $ct->getSql();
		$this->_registry = $ct->getRegistry();
		$this->_validate = $ct->getValidate();
		$this->_user     = $ct->getUser();
		$this->_table    = $table;
	}

	public function setCaptcha($captcha)
	{
		$this->captcha = (integer) $captcha;
	}

	public function getFields()
	{
		$fields = parent::getFields();

		// add captcha field
		$fields['captcha'] = isset($this->captcha) ? $this->captcha : null;

		return $fields;
	}

	public function _getTable()
	{
		return $this->_table;
	}

	public static function getType($status = false)
	{
		$s = array(

			self::INSERT => 'INSERT',
			self::UPDATE => 'UPDATE',
			self::DELETE => 'DELETE',

		);

		if($status !== false)
		{
			$status = intval($status);

			if(array_key_exists($status, $s))
			{
				return $s[$status];
			}
			else
			{
				return false;
			}
		}
		else
		{
			return $s;
		}
	}
}

