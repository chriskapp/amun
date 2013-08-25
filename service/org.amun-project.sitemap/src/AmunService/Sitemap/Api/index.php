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

namespace AmunService\Sitemap\Api;

use Amun\Module\ApiAbstract;
use Amun\Exception;
use PSX\DateTime;
use PSX\Data\Message;
use PSX\Url;
use PSX\Sitemap\Writer;

/**
 * Index
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Index extends ApiAbstract
{
	private $writer;

	/**
	 * Returns all pages as XML sitemap format
	 *
	 * @httpMethod GET
	 * @path /
	 * @nickname getSitemap
	 * @responseClass PSX_Data_ResultSet
	 */
	public function getSitemap()
	{
		if($this->user->hasRight('sitemap_view'))
		{
			try
			{
				header('Content-type: text/xml');

				$this->writer = new Writer();

				$this->generateSitemap();

				$this->writer->output();
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

	public function onPost()
	{
		$msg = new Message('Create a sitemap record is not possible', false);

		$this->setResponse($msg, null, 500);
	}

	public function onPut()
	{
		$msg = new Message('Update a sitemap record is not possible', false);

		$this->setResponse($msg, null, 500);
	}

	public function onDelete()
	{
		$msg = new Message('Delete a sitemap record is not possible', false);

		$this->setResponse($msg, null, 500);
	}

	private function generateSitemap()
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
	`page`.`date`

	FROM {$this->registry['table.content_page']} `page`

		ORDER BY `page`.`sort` ASC
SQL;

		$result = $this->getSql()->getAll($sql);

		foreach($result as $row)
		{
			$date = new DateTime($row['date']);
			$url  = empty($row['path']) ? $this->config['psx_url'] : $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . $row['path'];
			$url  = new Url($url);

			$this->writer->addUrl($url, $date);
		}
	}
}
