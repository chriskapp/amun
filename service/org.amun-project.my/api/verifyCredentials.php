<?php
/*
 *  $Id: verifyCredentials.php 875 2012-09-30 13:51:45Z k42b3.x@googlemail.com $
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

namespace my\api;

use Amun_Module_ApiAbstract;
use Amun_Sql_Table_Registry;
use Exception;
use PSX_Data_Message;
use PSX_Sql;

/**
 * verifyCredentials
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @package    api
 * @subpackage service_my
 * @version    $Revision: 875 $
 */
class verifyCredentials extends Amun_Module_ApiAbstract
{
	/**
	 * Returns informations about the current loggedin user
	 *
	 * @httpMethod GET
	 * @path /
	 * @nickname getCredentials
	 * @responseClass AmunService_My_Credentials
	 */
	public function getCredentials()
	{
		try
		{
			$select = Amun_Sql_Table_Registry::get('User_Account')
				->select(array('id', 'groupId', 'status', 'name', 'gender', 'profileUrl', 'thumbnailUrl', 'timezone', 'updated', 'date'))
				->where('id', '=', $this->user->id);

			$account = $select->getRow(PSX_Sql::FETCH_OBJECT, 'AmunService_My_Credentials', array($select->getTable(), $this->user));

			$this->setResponse($account);
		}
		catch(Exception $e)
		{
			$msg = new PSX_Data_Message($e->getMessage(), false);

			$this->setResponse($msg);
		}
	}
}



