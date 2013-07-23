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

use DateInterval;
use DateTime;
use AmunService\Content\Page\Record;

/**
 * Page
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Page
{
	protected $id;
	protected $parentId;
	protected $serviceId;
	protected $rightId;
	protected $status;
	protected $load;
	protected $path;
	protected $urlTitle;
	protected $title;
	protected $template;
	protected $description;
	protected $keywords;
	protected $cache;
	protected $expire;
	protected $publishDate;
	protected $date;

	protected $application;
	protected $url;

	protected $config;
	protected $sql;
	protected $registry;
	protected $user;

	public function __construct($pageId, Registry $registry, User $user)
	{
		$this->config   = $registry->getConfig();
		$this->sql      = $registry->getSql();
		$this->registry = $registry;
		$this->user     = $user;

		$status = Record::NORMAL;
		$sql    = <<<SQL
SELECT
	`page`.`id`          AS `pageId`,
	`page`.`parentId`    AS `pageParentId`,
	`page`.`serviceId`   AS `pageServiceId`,
	`page`.`rightId`     AS `pageRightId`,
	`page`.`status`      AS `pageStatus`,
	`page`.`load`        AS `pageLoad`,
	`page`.`path`        AS `pagePath`,
	`page`.`urlTitle`    AS `pageUrlTitle`,
	`page`.`title`       AS `pageTitle`,
	`page`.`template`    AS `pageTemplate`,
	`page`.`description` AS `pageDescription`,
	`page`.`keywords`    AS `pageKeywords`,
	`page`.`cache`       AS `pageCache`,
	`page`.`expire`      AS `pageExpire`,
	`page`.`publishDate` AS `pagePublishDate`,
	`page`.`date`        AS `pageDate`,
	`service`.`source`   AS `serviceSource`
FROM 
	{$this->registry['table.content_page']} `page`
INNER JOIN 
	{$this->registry['table.core_service']} `service`
	ON `page`.`serviceId` = `service`.`id`
WHERE 
	`page`.`id` = ?
SQL;

		$row = $this->sql->getRow($sql, array($pageId));

		if(!empty($row))
		{
			if(!empty($row['pageRightId']) && !$this->user->hasRightId($row['pageRightId']))
			{
				throw new Exception('Access not allowed', 401);
			}

			$this->id          = $row['pageId'];
			$this->parentId    = $row['pageParentId'];
			$this->serviceId   = $row['pageServiceId'];
			$this->rightId     = $row['pageRightId'];
			$this->status      = $row['pageStatus'];
			$this->load        = $row['pageLoad'];
			$this->path        = $row['pagePath'];
			$this->urlTitle    = $row['pageUrlTitle'];
			$this->title       = $row['pageTitle'];
			$this->template    = $row['pageTemplate'];
			$this->description = $row['pageDescription'];
			$this->keywords    = $row['pageKeywords'];
			$this->cache       = $row['pageCache'];
			$this->expire      = $row['pageExpire'];
			$this->publishDate = $row['pagePublishDate'];
			$this->date        = $row['pageDate'];

			$this->application = $row['serviceSource'];
			$this->url         = $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . $row['pagePath'];
		}
		else
		{
			throw new Exception('Invalid page');
		}
	}

	public function getId()
	{
		return $this->id;
	}

	public function getParentId()
	{
		return $this->parentId;
	}

	public function getServiceId()
	{
		return $this->serviceId;
	}

	public function getRightId()
	{
		return $this->rightId;
	}

	public function getStatus()
	{
		return $this->status;
	}

	public function getLoad()
	{
		return (integer) $this->load;
	}

	public function getPath()
	{
		return $this->path;
	}

	public function getUrlTitle()
	{
		return $this->urlTitle;
	}

	public function getTitle()
	{
		return $this->title;
	}

	public function getTemplate()
	{
		return $this->template;
	}

	public function getDescription()
	{
		return $this->description;
	}

	public function getKeywords()
	{
		return $this->keywords;
	}

	public function hasCache()
	{
		return (boolean) $this->cache;
	}

	public function getExpire()
	{
		if(!empty($this->expire))
		{
			return new DateInterval($this->expire);
		}

		return 0;
	}

	public function getPublishDate()
	{
		return $this->publishDate;
	}

	public function getDate()
	{
		return new DateTime($this->date);
	}

	public function getApplication()
	{
		return $this->application;
	}

	public function getUrl()
	{
		return $this->url;
	}

	public function hasNav()
	{
		return $this->getLoad() & Record::NAV;
	}

	public function hasPath()
	{
		return $this->getLoad() & Record::PATH;
	}

	public function hasGadget()
	{
		return $this->getLoad() & Record::GADGET;
	}
}
