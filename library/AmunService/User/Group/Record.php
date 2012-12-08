<?php
/*
 *  $Id: Group.php 880 2012-10-27 13:14:26Z k42b3.x@googlemail.com $
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
 * Amun_User_Group
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_User_Group
 * @version    $Revision: 880 $
 */
class AmunService_User_Group_Record extends Amun_Data_RecordAbstract
{
	public function getFields()
	{
		$fields = parent::getFields();

		// add rights field
		$fields['rights'] = isset($this->rights) ? $this->rights : null;

		return $fields;
	}

	public function setId($id)
	{
		$id = $this->_validate->apply($id, 'integer', array(new Amun_Filter_Id($this->_table)), 'id', 'Id');

		if(!$this->_validate->hasError())
		{
			$this->id = $id;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function setTitle($title)
	{
		$title = $this->_validate->apply($title, 'string', array(new PSX_Filter_Length(3, 32), new AmunService_Core_Registry_Filter_Name()));

		if(!$this->_validate->hasError())
		{
			$this->title = $title;
		}
		else
		{
			throw new psx_data_exception($this->_validate->getLastError());
		}
	}

	public function setRights($rights)
	{
		$rights = array_map('intval', explode(',', $rights));
		$con    = new PSX_Sql_Condition(array('id', 'IN', $rights));

		$this->rights = Amun_Sql_Table_Registry::get('User_Right')->getCol('id', $con);
	}

	public function getId()
	{
		return $this->_base->getUrn('user', 'group', $this->id);
	}

	public function getDate()
	{
		$date = new DateTime($this->date, $this->_registry['core.default_timezone']);

		return $date;
	}
}


