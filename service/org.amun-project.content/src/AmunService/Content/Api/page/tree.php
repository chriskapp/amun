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

namespace content\api\page;

use Amun\Module\ApiAbstract;
use Amun\Exception;
use PSX\Data\Message;
use PSX\Data\ResultSet;
use PSX\Data\Record;
use PSX\Data\RecordAbstract;

/**
 * tree
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class tree extends ApiAbstract
{
	/**
	 * Returns all pages in an tree structure
	 *
	 * @httpMethod GET
	 * @path /
	 * @nickname getTree
	 * @responseClass PSX_Data_Record
	 */
	public function getTree()
	{
		if($this->user->hasRight('content_page_view'))
		{
			try
			{
				$this->setResponse(new Record('tree', array($this->buildTreeArray())));
			}
			catch(Exception $e)
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

	/**
	 * Reorder pages
	 *
	 * @httpMethod PUT
	 * @path /
	 * @nickname reorderPage
	 * @responseClass PSX_Data_Message
	 */
	public function reorderPage()
	{
		if($this->user->hasRight('content_page_edit'))
		{
			try
			{
				$handler  = $this->getHandler('AmunService\Content\Page');
				$result   = $this->getRequest();
				$data     = (array) $result->getData();
				$messages = array();

				if(isset($data['entry']))
				{
					foreach($data['entry'] as $k => $entry)
					{
						$page = $handler->getRecord();
						$page->setId($entry['id']);
						$page->setSort($entry['sort']);

						$handler->update($page);
					}
				}

				$msg = new Message('You have successful reordered pages', true);

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

	private function buildTreeArray()
	{
		$sql = <<<SQL
SELECT

	`page`.`id`,
	`page`.`parentId`,
	`page`.`status`,
	`page`.`sort`,
	`page`.`path`,
	`page`.`title`,
	`page`.`urlTitle`,
	(CHAR_LENGTH(path) - CHAR_LENGTH(REPLACE(path, "/", ""))) AS `depth`

	FROM {$this->registry['table.content_page']} `page`

		ORDER BY `depth`, `page`.`parentId`, `page`.`sort` ASC
SQL;

		$result = $this->getSql()->getAll($sql);
		$tree   = array();

		foreach($result as $row)
		{
			$this->buildPath($tree, $row['path'], $row);
		}

		return $tree;
	}

	private function buildPath(array &$tree, $path, array $row)
	{
		if(empty($path))
		{
			$tree = array(

				'id'       => $row['id'],
				'status'   => $row['status'],
				'sort'     => $row['sort'],
				'path'     => $row['path'],
				'text'     => $row['urlTitle'],
				'children' => array(),

			);
		}
		else
		{
			$path = ltrim($path, '/');
			$pos  = strpos($path, '/');

			if($pos !== false)
			{
				$name = substr($path, 0, $pos);
				$rest = substr($path, $pos);
			}
			else
			{
				$name = $path;
				$rest = null;
			}

			$found = false;
			$sort  = array();

			foreach($tree['children'] as $k => $node)
			{
				$sort[] = $node['sort'];

				if($node['text'] == $name)
				{
					if(empty($rest))
					{
						$node['children'][] = array(

							'id'       => $row['id'],
							'status'   => $row['status'],
							'sort'     => $row['sort'],
							'path'     => $row['path'],
							'text'     => $row['urlTitle'],
							'children' => array(),

						);

						$found = true;

						break;
					}
					else
					{
						$this->buildPath($tree['children'][$k], $rest, $row);
					}
				}
			}

			array_multisort($sort, SORT_ASC, $tree['children']);

			if(empty($rest) && !$found)
			{
				$tree['children'][] = array(

					'id'       => $row['id'],
					'status'   => $row['status'],
					'sort'     => $row['sort'],
					'path'     => $row['path'],
					'text'     => $row['urlTitle'],
					'children' => array(),

				);
			}
		}
	}

	/*
	private function buildTreeArray($id = 0)
	{
		$node = array();
		$sql  = <<<SQL
SELECT

	page.id      AS `pageId`,
	page.status  AS `pageStatus`,
	page.title   AS `pageTitle`

	FROM {$this->registry['table.content_page']} page

		WHERE `page`.`parentId` = ?

		ORDER BY `page`.`sort` ASC
SQL;

		$result = $this->sql->getAll($sql, array($id));

		foreach($result as $row)
		{
			array_push($node, array(

				'id'       => $row['pageId'],
				'status'   => $row['pageStatus'],
				'text'     => $row['pageTitle'],
				'children' => $this->buildTreeArray($row['pageId']),

			));
		}

		return $node;
	}
	*/
}

