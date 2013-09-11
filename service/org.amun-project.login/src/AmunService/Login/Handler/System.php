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

namespace AmunService\Login\Handler;

use Amun\Security;
use Amun\Exception;
use AmunService\Login\HandlerAbstract;
use AmunService\Login\InvalidPasswordException;
use AmunService\User\Account;

/**
 * System
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class System extends HandlerAbstract
{
	public function isValid($identity)
	{
		return true;
	}

	public function hasPassword()
	{
		return true;
	}

	public function handle($identity, $password)
	{
		// we have given an email address if a password is set we check
		// whether the user exists
		$identity = sha1($this->config['amun_salt'] . $identity);

		if(!empty($password))
		{
			$row = $this->hm->getTable('AmunService\User\Account')
				->select(array('id', 'status', 'pw'))
				->where('identity', '=', $identity)
				->getRow();

			if(!empty($row))
			{
				if($row['pw'] == sha1($this->config['amun_salt'] . $password))
				{
					if(($error = $this->isValidStatus($row['status'])) === true)
					{
						$this->setUserId($row['id']);

						return true;
					}
					else
					{
						throw new Exception($error);
					}
				}
				else
				{
					throw new InvalidPasswordException('Invalid password');
				}
			}
		}
	}

	protected function isValidStatus($status)
	{
		switch($status)
		{
			case Account\Record::NORMAL:
			case Account\Record::ADMINISTRATOR:
				return true;
				break;

			case Account\Record::NOT_ACTIVATED:
				return 'Account is not activated';
				break;

			case Account\Record::BANNED:
				return 'Account is banned';
				break;

			case Account\Record::RECOVER:
				return 'Account is under recovery';
				break;

			default:
				return 'Unknown status';
				break;
		}
	}
}
