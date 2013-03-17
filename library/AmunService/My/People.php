<?php
/*
 *  $Id: People.php 635 2012-05-01 19:46:37Z k42b3.x@googlemail.com $
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

namespace AmunService\My;

use AmunService\User\Friend\Record;
use PSX\Data\WriterResult;
use PSX\Data\WriterInterface;

/**
 * Amun_Service_My_People
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Service_My
 * @version    $Revision: 635 $
 */
class People extends Record
{
	public function getName()
	{
		return 'person';
	}

	public function getFields()
	{
		return array(

			'id'           => $this->friendGlobalId,
			'displayName'  => $this->friendName,
			'profileUrl'   => $this->friendProfileUrl,
			'thumbnailUrl' => $this->friendThumbnailUrl,
			'updated'      => $this->friendUpdated,
			'published'    => $this->date,
			'connected'    => $this->status == self::NORMAL,

		);
	}

	public function export(WriterResult $result)
	{
		switch($result->getType())
		{
			case WriterInterface::JSON:
			case WriterInterface::XML:
				return parent::export($result);
				break;

			case WriterInterface::ATOM:
				$entry = $result->getWriter()->createEntry();

				$entry->setTitle($this->friendName);
				$entry->setId('urn:uuid:' . $this->friendGlobalId);
				$entry->setUpdated($this->getDate());
				$entry->addAuthor($this->authorName, $this->authorProfileUrl);
				$entry->addLink($this->friendProfileUrl, 'alternate', 'text/html');
				$entry->setContent($this, 'application/xml');

				return $entry;
				break;

			default:
				throw new Exception('Writer is not supported');
				break;
		}
	}
}
