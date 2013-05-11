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

namespace Amun;

use ArrayObject;

/**
 * Path
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Path extends ArrayObject
{
	private $config;
	private $sql;
	private $registry;
	private $page;

	private $path = array();

	public function __construct(Registry $registry, Page $page)
	{
		parent::__construct($this->path);

		$this->config   = $registry->getConfig();
		$this->sql      = $registry->getSql();
		$this->registry = $registry;
		$this->page     = $page;
	}

	public function load()
	{
		$sql = <<<SQL
SELECT
	`page`.`parentId`,
	`page`.`title`,
	`page`.`urlTitle`
FROM 
	{$this->registry['table.content_page']} `page`
WHERE 
	SUBSTRING('{$this->page->path}', 1, LENGTH(`path`)) LIKE `path`
ORDER BY 
	LENGTH(`path`) ASC
SQL;

		$result = $this->sql->getAll($sql);
		$url    = '';

		foreach($result as $row)
		{
			if($row['parentId'] == 0)
			{
				$this->add($row['title'], $this->config['psx_url']);
			}
			else
			{
				$url.= '/' . $row['urlTitle'];

				$this->add($row['title'], $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . ltrim($url, '/'));
			}
		}
	}

	public function clear()
	{
		$this->exchangeArray($this->path = array());
	}

	public function add($name, $href)
	{
		$this->append(array(

			'name' => $name,
			'href' => $href,

		));
	}
}

