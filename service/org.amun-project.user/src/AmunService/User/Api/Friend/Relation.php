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

namespace AmunService\User\Api\Friend;

use AmunService\User\Friend;
use Amun\Module\ApiAbstract;
use Amun\Exception;
use PSX\Data\Message;
use PSX\Data\ReaderInterface;

/**
 * Relation
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Relation extends ApiAbstract
{
	public function onGet()
	{
		$msg = new Message('Method not allowed', false);

		$this->setResponse($msg, null, 405);
	}

	public function onPost()
	{
		if($this->user->hasRight('user_friend_add'))
		{
			try
			{
				$relation = new Friend\Relation();
				$relation->import($this->getRequest(ReaderInterface::FORM));

				$handler = $this->getHandler('AmunService\User\Friend');


				// check if anonymous
				if($this->user->isAnonymous())
				{
					throw new Exception('Please sign in to make a friend request');
				}


				$handler->remote($relation);


				$msg = new Message('You have successful create a request', true);

				$this->setResponse($msg);
			}
			catch(\Exception $e)
			{
				$msg = new Message($e->getMessage(), false);

				$this->setResponse($msg);
			}
		}
		else
		{
			$msg = new Message('Access not allowed', false);

			$this->setResponse($msg, null, $this->user->isAnonymous() ? 401 : 403);
		}
	}
}

