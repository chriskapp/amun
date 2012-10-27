<?php
/*
 *  $Id: Stream.php 635 2012-05-01 19:46:37Z k42b3.x@googlemail.com $
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
 * Amun_User_Activity_Stream
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_User_Activity
 * @version    $Revision: 635 $
 */
class Amun_User_Activity_Stream extends Amun_User_Activity_StreamAbstract
{
	public function getObject()
	{
		$row = $this->table->select(array('globalId', 'userId', 'summary', 'date'))
			->join(PSX_Sql_Join::INNER, Amun_Sql_Table_Registry::get('User_Account')
				->select(array('globalId', 'name', 'profileUrl', 'thumbnailUrl', 'updated', 'date'), 'author')
			)
			->where('id', '=', $this->refId)
			->getRow();

		if(!empty($row))
		{
			// person
			$updated = new DateTime($row['authorUpdated']);
			$date    = new DateTime($row['authorDate']);

			$person               = new PSX_ActivityStream_Type_Person();
			$person->displayName  = $row['authorName'];
			$person->image        = $row['authorThumbnailUrl'];
			$person->id           = $row['authorGlobalId'];
			$person->published    = $date->format(DateTime::RFC3339);
			$person->updated      = $updated->format(DateTime::RFC3339);
			$person->url          = $row['authorProfileUrl'];

			// comment
			$published = new DateTime($row['date']);

			$note              = new PSX_ActivityStream_Type_Note();
			$note->author      = $person;
			$note->content     = $row['summary'];
			$note->id          = $row['globalId'];
			$note->published   = $published->format(DateTime::RFC3339);

			return $note;
		}
	}
}
