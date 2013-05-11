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

namespace AmunService\Comment;

use Amun\DataFactory;
use Amun\Data\StreamAbstract;
use PSX\ActivityStream\Type;
use PSX\DateTime;
use PSX\Sql\Join;

/**
 * Stream
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Stream extends StreamAbstract
{
	public function getObject($id)
	{
		$row = $this->table->select(array('globalId', 'pageId', 'userId', 'refId', 'text', 'date'))
			->join(Join::INNER, DataFactory::getTable('User_Account')
				->select(array('globalId', 'name', 'profileUrl', 'thumbnailUrl', 'updated', 'date'), 'author')
			)
			->where('id', '=', $id)
			->getRow();

		if(!empty($row))
		{
			// person
			$updated = new DateTime($row['authorUpdated']);
			$date    = new DateTime($row['authorDate']);

			$person              = new Type\Person();
			$person->displayName = $row['authorName'];
			$person->image       = $row['authorThumbnailUrl'];
			$person->id          = $row['authorGlobalId'];
			$person->published   = $date->format(DateTime::RFC3339);
			$person->updated     = $updated->format(DateTime::RFC3339);
			$person->url         = $row['authorProfileUrl'];

			// comment
			$published = new DateTime($row['date']);

			$comment             = new Type\Comment();
			$comment->author     = $person;
			$comment->content    = $row['text'];
			$comment->id         = $row['globalId'];
			$comment->published  = $published->format(DateTime::RFC3339);

			return $comment;
		}
	}
}
