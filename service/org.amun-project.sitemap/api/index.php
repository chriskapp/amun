<?php
/*
 *  $Id: index.php 875 2012-09-30 13:51:45Z k42b3.x@googlemail.com $
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

namespace sitemap\api;

use Amun_Module_ApiAbstract;
use DateTime;
use Exception;
use PSX_Data_Message;
use XMLWriter;

/**
 * index
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @package    api
 * @subpackage service_phpinfo
 * @version    $Revision: 875 $
 */
class index extends Amun_Module_ApiAbstract
{
	private $writer;

	public function onGet()
	{
		if($this->getProvider()->hasViewRight())
		{
			try
			{
				header('Content-type: text/xml');

				$this->writer = new XMLWriter();
				$this->writer->openURI('php://output');
				$this->writer->setIndent(true);
				$this->writer->startDocument('1.0', 'UTF-8');

				$this->writer->startElement('urlset');
				$this->writer->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
				$this->generateSitemap();
				$this->writer->endElement();

				$this->writer->endElement();
				$this->writer->endDocument();
				$this->writer->flush();
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

	public function onPost()
	{
		$msg = new PSX_Data_Message('Create a sitemap record is not possible', false);

		$this->setResponse($msg, null, 500);
	}

	public function onPut()
	{
		$msg = new PSX_Data_Message('Update a sitemap record is not possible', false);

		$this->setResponse($msg, null, 500);
	}

	public function onDelete()
	{
		$msg = new PSX_Data_Message('Delete a sitemap record is not possible', false);

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

	FROM {$this->registry['table.core_content_page']} `page`

		ORDER BY `page`.`sort` ASC
SQL;

		$result = $this->sql->getAll($sql);

		foreach($result as $row)
		{
			$date = new DateTime($row['date']);
			$url  = empty($row['path']) ? $this->config['psx_url'] : $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . $row['path'] . $this->config['amun_page_delimiter'];

			$this->writer->startElement('url');
			$this->writer->writeElement('loc', $url);
			$this->writer->writeElement('lastmod', $date->format(DateTime::W3C));
			/*
			$this->writer->writeElement('changefreq', '');
			$this->writer->writeElement('priority', '');
			*/
			$this->writer->endElement();
		}
	}
}
