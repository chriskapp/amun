<?php
/*
 *  $Id: FriendsAbstract.php 635 2012-05-01 19:46:37Z k42b3.x@googlemail.com $
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
 * AmunService_My_LoginHandler_System
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Service_My
 * @version    $Revision: 635 $
 */
class AmunService_My_LoginHandler_System extends AmunService_My_LoginHandlerAbstract
{
	public function isValid($identity)
	{
		return true;
	}

	public function handle($identity, $password)
	{
		// we have given an email address if a password is set we check
		// whether the user exists
		$identity = sha1(Amun_Security::getSalt() . $identity);

		if(!empty($password))
		{
			$row = Amun_Sql_Table_Registry::get('User_Account')
				->select(array('id', 'status', 'pw'))
				->where('identity', '=', $identity)
				->getRow();

			if(!empty($row))
			{
				if($row['pw'] == sha1(Amun_Security::getSalt() . $password))
				{
					if(($error = $this->isValidStatus($row['status'])) === true)
					{
						$this->setUserId($row['id']);

						return true;
					}
					else
					{
						throw new Amun_Exception($error);
					}
				}
				else
				{
					throw new AmunService_My_Login_InvalidPasswordException('Invalid password');
				}
			}
		}
	}

	protected function isValidStatus($status)
	{
		switch($status)
		{
			case AmunService_User_Account_Record::NORMAL:
			case AmunService_User_Account_Record::ADMINISTRATOR:
				return true;
				break;

			case AmunService_User_Account_Record::NOT_ACTIVATED:
				return 'Account is not activated';
				break;

			case AmunService_User_Account_Record::BANNED:
				return 'Account is banned';
				break;

			case AmunService_User_Account_Record::RECOVER:
				return 'Account is under recovery';
				break;

			default:
				return 'Unknown status';
				break;
		}
	}
}
