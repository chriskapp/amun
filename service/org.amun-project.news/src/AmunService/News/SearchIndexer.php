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

namespace AmunService\News;

use Amun\Data\RecordAbstract;
use Amun\Sql\TableInterface;
use Elastica\Client;
use Elastica\Document;
use Elastica\Exception\NotFoundException;
use PSX\Data\RecordInterface;

/**
 * SearchIndexer
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class SearchIndexer extends IndexerAbstract
{
	public function publish(Client $client, $actionType, TableInterface $table, RecordInterface $record)
	{
		$con   = new Condition(array('id', '=', $record->id));
		$row   = $table->getRow(array('id', 'pageId', 'userId', 'urlTitle', 'title', 'text'), $con);

		$index = $client->getIndex('amun');
		$type  = $index->getType('page');
		$id    = $row['pageId'] . '-' . $record->id;

		try
		{
			$document = $type->getDocument($id);

			if($actionType == RecordAbstract::INSERT || $actionType == RecordAbstract::UPDATE)
			{
				// get referring page
				$handler = $this->hm->getHandler('AmunService\Content\Page');
				$page    = $handler->get($row['pageId'], array('id', 'path', 'urlTitle', 'title'));

				$data = array(
					'id'      => $id,
					'userId'  => $row['userId'],
					'path'    => $page['path'] . '/view/' . $row['id'] . '/' . $row['urlTitle'],
					'title'   => $row['title'],
					'content' => $row['text'],
					'date'    => time(),
				);

				$type->updateDocument(new Document($id, $data));
			}
			else if($actionType == RecordAbstract::DELETE)
			{
				$type->deleteDocument($document);
			}
		}
		catch(NotFoundException $e)
		{
			if($actionType == RecordAbstract::INSERT || $actionType == RecordAbstract::UPDATE)
			{
				// get referring page
				$handler = $this->hm->getHandler('AmunService\Content\Page');
				$page    = $handler->get($row['pageId'], array('id', 'path', 'urlTitle', 'title'));

				$data = array(
					'id'      => $id,
					'userId'  => $row['userId'],
					'path'    => $page['path'] . '/view/' . $row['id'] . '/' . $row['urlTitle'],
					'title'   => $row['title'],
					'content' => $row['text'],
					'date'    => time(),
				);

				$type->addDocument(new Document($record->globalId, $data));
			}
			else if($actionType == RecordAbstract::DELETE)
			{
				// is already deleted
			}
		}

		$type->getIndex()->refresh();
	}
}
