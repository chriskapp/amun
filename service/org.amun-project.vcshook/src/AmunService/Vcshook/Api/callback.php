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

namespace vcshook\api;

use Amun\Module\ApiAbstract;
use Amun\DataFactory;
use AmunService\Vcshook\TypeAbstract;
use Exception;
use PSX\Base;
use PSX\Data\Message;
use PSX\Data\ReaderInterface;
use PSX\Data\ReaderResult;
use PSX\Json;
use PSX\Sql;
use PSX\Sql\Condition;

/**
 * callback
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class callback extends ApiAbstract
{
	public function onGet()
	{
		header('Allow: POST');

		$msg = new Message('Invalid request method', false);

		$this->setResponse($msg, null, 405);
	}

	/**
	 * @httpMethod POST
	 * @path /{type}/{secret}
	 */
	public function insertCommit()
	{
		try
		{
			$type    = $this->getUriFragments('type');
			$secret  = $this->getUriFragments('secret');

			// parse request
			$type    = TypeAbstract::factory($type);
			$project = $type->getRequest(Base::getRawInput());

			// get project
			$con = new Condition(array('secret', '=', $secret));
			$id  = $this->sql->select($this->registry['table.vcshook'], array('id'), $con, Sql::SELECT_FIELD);

			if(!empty($id) && $project->hasCommits())
			{
				$count = 0;

				foreach($project->getCommits() as $commit)
				{
					try
					{
						$record = $this->hm->getHandler('AmunService\Vcshook\Commit')->getRecord();
						$record->setProjectId($id);
						$record->setAuthor($commit->getAuthor());
						$record->setUrl($commit->getUrl());
						$record->setMessage($commit->getMessage());
						$record->setTimestamp($commit->getTimestamp());

						// notify listener
						$this->event->notifyListener('vcshook.commit', array($record));

						$count++;
					}
					catch(\Exception $e)
					{
						// import fails we go the next commit and ignore the 
						// error
					}
				}

				if($count == 0)
				{
					throw new Exception('No commits inserted');
				}
			}
			else
			{
				throw new Exception('Invalid project or no commits available');
			}


			$msg = new Message('Inserted ' . $count . ' commits', true);

			$this->setResponse($msg);
		}
		catch(\Exception $e)
		{
			$msg = new Message($e->getMessage(), false);

			$this->setResponse($msg, null, 500);
		}
	}
}

