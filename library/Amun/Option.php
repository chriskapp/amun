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

use RecursiveArrayIterator;

/**
 * Option
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Option extends RecursiveArrayIterator
{
	protected $options;

	protected $optionName;
	protected $config;
	protected $sql;
	protected $registry;
	protected $user;
	protected $page;

	public function __construct($optionName, Registry $registry, User $user, Page $page)
	{
		$this->options = array();

		parent::__construct($this->options);


		$this->optionName = $optionName;
		$this->config     = $registry->getConfig();
		$this->sql        = $registry->getSql();
		$this->registry   = $registry;
		$this->user       = $user;
		$this->page       = $page;
	}

	public function add($right, $name, $href)
	{
		if($this->user->hasRight($right))
		{
			$this->append(array(

				'name' => $name,
				'href' => $href,

			));
		}
	}

	public function load(array $objects = array())
	{
		/*
		$sql = <<<SQL
SELECT
	`pageOption`.`rightId`,
	`pageOption`.`name`,
	`pageOption`.`href`,
	`page`.`path`
FROM 
	{$this->registry['table.content_page_option']} `pageOption`
INNER JOIN 
	{$this->registry['table.core_service_option']} `serviceOption`
	ON `pageOption`.`optionId` = `serviceOption`.`id`
INNER JOIN 
	{$this->registry['table.content_page']} `page`
	ON `pageOption`.`destPageId` = `page`.`id`
WHERE 
	`serviceOption`.`serviceId` = {$this->page->getServiceId()}
AND
	`pageOption`.`srcPageId` = {$this->page->getId()}
AND 
	`serviceOption`.`name` = ?
SQL;

		$result = $this->sql->getAll($sql, array($this->optionName));

		foreach($result as $row)
		{
			if($this->user->hasRightId($row['rightId']))
			{
				$href = $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . $row['path'] . $this->substitueVars($objects, $row['href']);

				$this->append(array(

					'name' => $row['name'],
					'href' => $href,

				));
			}
		}
		*/
	}

	private function substitueVars(array $objects, $href)
	{
		foreach($objects as $obj)
		{
			$name = get_class($obj);

			if(strpos($href, $name) !== false)
			{
				$vars = get_class_vars($name);

				foreach($vars as $k => $v)
				{
					$search  = '{' . $name . '.' . $k . '}';
					$replace = $obj->$k;

					if(is_scalar($replace))
					{
						$href = str_replace($search, $replace, $href);
					}
				}
			}
		}

		return $href;
	}
}

