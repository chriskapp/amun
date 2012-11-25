<?php
/*
 *  $Id: Option.php 635 2012-05-01 19:46:37Z k42b3.x@googlemail.com $
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
 * Amun_Option
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Html
 * @version    $Revision: 635 $
 */
class Amun_Option extends RecursiveArrayIterator
{
	private $options;

	private $optionName;
	private $config;
	private $sql;
	private $registry;
	private $user;
	private $page;

	private $objects;

	public function __construct($optionName, Amun_Registry $registry, Amun_User $user, Amun_Page $page)
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
		$sql = <<<SQL
SELECT

	pageOption.rightId,
	pageOption.name,
	pageOption.href,
	page.path

	FROM {$this->registry['table.core_content_page_option']} `pageOption`

		INNER JOIN {$this->registry['table.core_content_service_option']} `serviceOption`

		ON pageOption.optionId = serviceOption.id

			INNER JOIN {$this->registry['table.core_content_page']} `page`

			ON pageOption.destPageId = page.id

				WHERE serviceOption.serviceId = {$this->page->serviceId}

				AND pageOption.srcPageId = {$this->page->id}

					AND serviceOption.name = ?
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

