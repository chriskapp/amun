<?php
/*
 *  $Id: activate.php 875 2012-09-30 13:51:45Z k42b3.x@googlemail.com $
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

namespace my\application\register;

use Amun\Module\ApplicationAbstract;
use Amun\Exception;
use AmunService\User\Account;
use PSX\DateTime;
use PSX\Filter;
use PSX\Sql\Condition;

/**
 * activate
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @package    application
 * @subpackage my
 * @version    $Revision: 875 $
 */
class activate extends ApplicationAbstract
{
	public function onLoad()
	{
		parent::onLoad();

		// add path
		$this->path->add('Register', $this->page->url . '/register');
		$this->path->add('Activate', $this->page->url . '/register/activate');

		// template
		$this->htmlCss->add('my');
	}

	public function onGet()
	{
		try
		{
			$token = $this->get->token('string', array(new Filter\Length(40, 40), new Filter\Xdigit()));

			if($token !== false)
			{
				$account = $this->getHandler('User_Account')->getNotActivatedByToken($token);

				if($account instanceof Account\Record)
				{
					try
					{
						$expire = 'PT24H'; // expire after 24 hours
						$now    = new DateTime('NOW', $this->registry['core.default_timezone']);

						if($now > $account->getDate()->add(new DateInterval($expire)))
						{
							throw new Exception('Activation is expired');
						}

						if($_SERVER['REMOTE_ADDR'] == $account->ip)
						{
							$account->setStatus(Account\Record::NORMAL);

							$handler = new Account\Handler($this->user);
							$handler->update($account);


							$this->template->assign('success', true);
						}
						else
						{
							throw new Exception('Registration was requested from another IP');
						}
					}
					catch(\Exception $e)
					{
						$con = new Condition();
						$con->add('id', '=', $account->id);
						$con->add('status', '=', Account\Record::NOT_ACTIVATED);

						$this->sql->delete($this->registry['table.user_account'], $con);

						throw $e;
					}
				}
				else
				{
					throw new Exception('Invalid token');
				}
			}
			else
			{
				throw new Exception('Token not set');
			}
		}
		catch(\Exception $e)
		{
			$this->template->assign('error', $e->getMessage());
		}
	}
}
