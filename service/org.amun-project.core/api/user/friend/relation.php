<?php
/*
 *  $Id: relation.php 683 2012-06-03 11:52:32Z k42b3.x@googlemail.com $
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
 * relation
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @package    admin
 * @subpackage user_friend
 * @version    $Revision: 683 $
 */
class relation extends Amun_Module_ApiAbstract
{
	public function onGet()
	{
		$msg = new PSX_Data_Message('Method not allowed', false);

		$this->setResponse($msg, null, 405);
	}

	public function onPost()
	{
		if($this->user->hasRight('user_friend_add'))
		{
			try
			{
				$relation = new AmunService_Core_User_Friend_Relation();
				$relation->import($this->getRequest(PSX_Data_ReaderInterface::FORM));

				$handler = new AmunService_Core_User_Friend_Handler($this->user);


				// check if anonymous
				if($this->user->isAnonymous())
				{
					throw new PSX_Data_Exception('Please sign in to make a friend request');
				}


				$handler->remote($relation);


				$msg = new PSX_Data_Message('You have successful create a request', true);

				$this->setResponse($msg);
			}
			catch(Exception $e)
			{
				$msg = new PSX_Data_Message($e->getMessage(), false);

				$this->setResponse($msg);
			}
		}
		else
		{
			$msg = new PSX_Data_Message('Access not allowed', false);

			$this->setResponse($msg, null, $this->user->isAnonymous() ? 401 : 403);
		}
	}
}

