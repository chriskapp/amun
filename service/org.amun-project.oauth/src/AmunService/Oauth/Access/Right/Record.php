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

namespace AmunService\Oauth\Access\Right;

use Amun\Data\RecordAbstract;
use Amun\DataFactory;
use Amun\Filter;
use Amun\Exception;

/**
 * Record
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Record extends RecordAbstract
{
	protected $_access;
	protected $_right;

	public function setId($id)
	{
		$id = $this->_validate->apply($id, 'integer', array(new Filter\Id($this->_table)), 'id', 'Id');

		if(!$this->_validate->hasError())
		{
			$this->id = $id;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setAccessId($accessId)
	{
		$accessId = $this->_validate->apply($accessId, 'integer', array(new Filter\Id($this->_hm->getTable('AmunService\Oauth\Access'))), 'accessId', 'Access Id');

		if(!$this->_validate->hasError())
		{
			$this->accessId = $accessId;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setRightId($rightId)
	{
		$rightId = $this->_validate->apply($rightId, 'integer', array(new Filter\Id($this->_hm->getTable('AmunService\User\Right'))), 'rightId', 'Right Id');

		if(!$this->_validate->hasError())
		{
			$this->rightId = $rightId;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function getAccess()
	{
		if($this->_access === null)
		{
			$this->_access = $this->_hm->getHandler('AmunService\Oauth\Access')->getRecord($this->accessId);
		}

		return $this->_access;
	}

	public function getRight()
	{
		if($this->_right === null)
		{
			$this->_right = $this->_hm->getHandler('AmunService\User\Right')->getRecord($this->rightId);
		}

		return $this->_right;
	}
}


