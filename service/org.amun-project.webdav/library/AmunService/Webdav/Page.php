<?php
/*
 *  $Id: Page.php 787 2012-07-04 20:10:48Z k42b3.x@googlemail.com $
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
 * Amun_Service_Webdav_Page
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Service_Webdav
 * @version    $Revision: 787 $
 */
class AmunService_Webdav_Page extends AmunService_Webdav_CollectionAbstract
{
	protected $base;
	protected $config;
	protected $sql;
	protected $registry;
	protected $user;

	protected $page;

	private $children;

	public function __construct($id)
	{
		parent::__construct();

		$sql = <<<SQL
SELECT

	`page`.`id`       AS `id`,
	`page`.`urlTitle` AS `urlTitle`,
	`page`.`rightId`  AS `rightId`,
	`page`.`date`     AS `date`,
	`service`.`id`    AS `serviceId`,
	`service`.`name`  AS `serviceName`

	FROM {$this->registry['table.content_page']} `page`

		INNER JOIN {$this->registry['table.core_service']} `service`

		ON `page`.`serviceId` = `service`.`id`

			WHERE `page`.`id` = ?
SQL;

		$this->page = $this->sql->getRow($sql, array($id));

		if(empty($this->page))
		{
			throw new Sabre_DAV_Exception_FileNotFound('Page doesnt exist');
		}

		if(!$this->user->hasRightId($this->page['rightId']))
		{
			throw new Sabre_DAV_Exception_Forbidden('Access not allowed');
		}
	}

	public function getName()
	{
		return $this->page['urlTitle'];
	}

	public function getLastModified()
	{
		return strtotime($this->page['date']);
	}

	public function getChildren()
	{
		if($this->children === null)
		{
			$this->children = array();

			// load page nodes
			$sql = <<<SQL
SELECT

	`page`.`id`           AS `id`,
	`service`.`name`      AS `serviceName`
	`service`.`namespace` AS `serviceNamespace`

	FROM {$this->registry['table.content_page']} `page`

		INNER JOIN {$this->registry['table.core_service']} `service`

		ON `page`.`serviceId` = `service`.`id`

			WHERE parentId = ?
SQL;

			$result = $this->sql->getAll($sql, array($this->page['id']));

			foreach($result as $row)
			{
				if($this->user->hasRight('service_' . $row['serviceName'] . '_view'))
				{
					$this->children[] = new AmunService_Webdav_Page($row['id']);
				}
			}

			// load service nodes
			$class = 'AmunService_' . $this->page['serviceNamespace'] . '_Node';

			if(class_exists($class))
			{
				$node = new $class();

				if($node instanceof AmunService_Webdav_NodeAbstract)
				{
					$children = $node->getChildren($row['id']);

					if(is_array($children))
					{
						foreach($children as $child)
						{
							$this->children[] = $child;
						}
					}
				}
			}
		}

		return $this->children;
	}

	public function childExists($urlTitle)
	{
		$con = new PSX_Sql_Condition();
		$con->add('parentId', '=', $this->page['id']);
		$con->add('urlTitle', '=', $urlTitle);

		return $this->sql->count($this->registry['table.content_page'], $con) > 0;
	}
}

