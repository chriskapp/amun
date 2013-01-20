<?php
/*
 *  $Id: Nav.php 840 2012-09-11 22:19:37Z k42b3.x@googlemail.com $
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
 * Amun_Nav
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Nav
 * @version    $Revision: 840 $
 */
class Amun_Nav extends ArrayObject
{
	private $config;
	private $sql;
	private $registry;
	private $user;
	private $page;

	private $nav = array();

	public function __construct(Amun_Registry $registry, Amun_User $user, Amun_Page $page)
	{
		parent::__construct($this->nav);

		$this->config   = $registry->getConfig();
		$this->sql      = $registry->getSql();
		$this->registry = $registry;
		$this->user     = $user;
		$this->page     = $page;
	}

	public function load()
	{
		$status = AmunService_Content_Page_Record::NORMAL;
		$sql    = <<<EOD
SELECT

	page.id          AS `pageId`,
	page.rightId     AS `pageRightId`,
	page.path        AS `pagePath`,
	page.title       AS `pageTitle`,
	page.urlTitle    AS `pageUrlTitle`,
	service.name     AS `serviceName`

	FROM {$this->registry['table.content_page']} `page`

		INNER JOIN {$this->registry['table.core_service']} `service`

		ON page.serviceId = service.id

			WHERE `page`.`parentId` = 1

			AND `page`.`status` = {$status}

				AND (`page`.`publishDate` = '0000-00-00 00:00:00' OR `page`.`publishDate` < NOW())

				ORDER BY `page`.`sort` ASC
EOD;

		$result = $this->sql->getAll($sql);

		foreach($result as $row)
		{
			$name     = $row['pageTitle'];
			$href     = $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . $row['pagePath'];
			$selected = strpos($_SERVER['REQUEST_URI'], $this->config['psx_dispatch'] . $row['pagePath']) !== false;

			if(!empty($row['pageRightId']))
			{
				if($this->user->hasRightId($row['pageRightId']))
				{
					$this->append(array(

						'title'    => $name,
						'href'     => $href,
						'selected' => $selected,

					));
				}
			}
			else
			{
				$this->append(array(

					'title'    => $name,
					'href'     => $href,
					'selected' => $selected,

				));
			}
		}
	}
}
