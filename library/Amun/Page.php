<?php
/*
 *  $Id: Page.php 840 2012-09-11 22:19:37Z k42b3.x@googlemail.com $
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
 * Amun_Page
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Page
 * @version    $Revision: 840 $
 */
class Amun_Page
{
	public $id;
	public $parentId;
	public $serviceId;
	public $rightId;
	public $status;
	public $load;
	public $application;
	public $path;
	public $urlTitle;
	public $title;
	public $template;
	public $description;
	public $keywords;
	public $cache;
	public $expire;
	public $module;
	public $publishDate;
	public $date;

	public $applicationPath;
	public $url;

	private $config;
	private $sql;
	private $registry;
	private $user;

	public function __construct($pageId, Amun_Registry $registry, Amun_User $user)
	{
		$this->config   = $registry->getConfig();
		$this->sql      = $registry->getSql();
		$this->registry = $registry;
		$this->user     = $user;


		$status = AmunService_Content_Page_Record::NORMAL;
		$sql    = <<<SQL
SELECT

	page.id          AS `pageId`,
	page.parentId    AS `pageParentId`,
	page.serviceId   AS `pageServiceId`,
	page.rightId     AS `pageRightId`,
	page.status      AS `pageStatus`,
	page.load        AS `pageLoad`,
	page.path        AS `pagePath`,
	page.urlTitle    AS `pageUrlTitle`,
	page.title       AS `pageTitle`,
	page.template    AS `pageTemplate`,
	page.description AS `pageDescription`,
	page.keywords    AS `pageKeywords`,
	page.cache       AS `pageCache`,
	page.expire      AS `pageExpire`,
	page.publishDate AS `pagePublishDate`,
	page.date        AS `pageDate`,
	service.source   AS `serviceSource`

	FROM {$this->registry['table.content_page']} `page`

		INNER JOIN {$this->registry['table.core_service']} `service`

		ON `page`.`serviceId` = `service`.`id`

			WHERE `page`.`id` = ?
SQL;

		$row = $this->sql->getRow($sql, array($pageId));

		if(!empty($row))
		{
			if(!empty($row['pageRightId']) && !$this->user->hasRightId($row['pageRightId']))
			{
				throw new Amun_Exception('Access not allowed', 401);
			}

			$this->id          = $row['pageId'];
			$this->parentId    = $row['pageParentId'];
			$this->serviceId   = $row['pageServiceId'];
			$this->rightId     = $row['pageRightId'];
			$this->status      = $row['pageStatus'];
			$this->load        = (integer) $row['pageLoad'];
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
			throw new Amun_Exception('Invalid page');
		}
	}

	public function getServiceId()
	{
		return $this->serviceId;
	}

	public function hasNav()
	{
		return $this->load & AmunService_Content_Page_Record::NAV;
	}

	public function hasPath()
	{
		return $this->load & AmunService_Content_Page_Record::PATH;
	}

	public function hasGadget()
	{
		return $this->load & AmunService_Content_Page_Record::GADGET;
	}

	public static function getUrl(Amun_Registry $registry, $pageId)
	{
		$id   = intval($pageId);
		$stmt = <<<SQL
SELECT

	page.path

	FROM {$registry['table.content_page']} `page`

		WHERE `page`.`id` = {$id}
SQL;

		$row = $registry->getSql()->getRow($stmt);

		if(!empty($row))
		{
			$config = $registry->getConfig();
			$url    = $config['psx_url'] . '/' . $config['psx_dispatch'] . $row['path'];
		}
		else
		{
			throw new Amun_Page_Exception('Invalid page id');
		}

		return $url;
	}
}
