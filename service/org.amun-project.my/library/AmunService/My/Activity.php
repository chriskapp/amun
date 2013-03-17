<?php
/*
 *  $Id: Activity.php 635 2012-05-01 19:46:37Z k42b3.x@googlemail.com $
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

use Amun\Exception;
use Amun\Util;
use AmunService\User\Activity\Record;
use PSX\Data\WriterResult;
use PSX\Data\WriterInterface;
use PSX\ActivityStream\Type;

/**
 * Amun_Service_My_Activity
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Service_My
 * @version    $Revision: 635 $
 */
class Activity extends Record
{
	public function export(WriterResult $result)
	{
		switch($result->getType())
		{
			case WriterInterface::JSON:
			case WriterInterface::XML:

				$person              = new Type\Person();
				$person->displayName = $this->authorName;
				$person->image       = $this->authorThumbnailUrl;
				$person->id          = $this->authorGlobalId;
				$person->url         = $this->authorProfileUrl;

				$data = array();
				$data['published'] = $this->getDate()->format(DateTime::RFC3339);
				$data['actor']     = $person;
				$data['verb']      = $this->verb;
				$data['object']    = $this->getObject();

				return $data;

				break;

			case WriterInterface::ATOM:

				$entry = $result->getWriter()->createEntry();

				$entry->setTitle(Util::stripAndTruncateHtml($this->summary));
				$entry->setId('urn:uuid:' . $this->globalId);
				$entry->setUpdated($this->getDate());
				$entry->setPublished($this->getDate());
				$entry->addAuthor($this->authorName, $this->authorProfileUrl);

				if($this->parentId > 0)
				{
					$writer = $result->getWriter()->writer;
					$parent = $this->_table->select(array('id', 'globalId', 'date'))
						->where('id', '=', $this->parentId)
						->getRow();

					$writer->startElementNS('thr', 'in-reply-to', 'http://purl.org/syndication/thread/1.0');
					$writer->writeAttribute('ref', 'urn:uuid:' . $parent['globalId']);
					$writer->endElement();
				}

				$entry->setContent($this->summary, 'html');

				return $entry;

				break;

			default:
				throw new Exception('Writer is not supported');
				break;
		}
	}
}

