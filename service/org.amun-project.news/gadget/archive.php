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

namespace news\gadget;

use Amun\Module\GadgetAbstract;

/**
 * archive
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class archive extends GadgetAbstract
{
	/**
	 * onLoad
	 *
	 * @param pageId integer
	 */
	public function onLoad()
	{
		parent::onLoad();

		$pageId = $this->args->get('pageId', 0);

		// get news archive
		$con = '';

		if(!empty($pageId))
		{
			$con = 'WHERE `page`.`id` = ' . $pageId;
		}

		$sql = <<<SQL
SELECT
	`news`.`id`                      AS `newsId`,
	COUNT(`news`.`id`)               AS `newsCount`,
	MONTHNAME(`news`.`date`)         AS `newsMonthname`,
	DATE_FORMAT(`news`.`date`, '%m') AS `newsMonth`,
	YEAR(`news`.`date`)              AS `newsYear`,
	`page`.`path`                    AS `pagePath`
FROM 
	{$this->registry['table.news']} `news`
INNER JOIN 
	{$this->registry['table.content_page']} `page`
	ON `news`.`pageId` = `page`.`id`
{$con}
GROUP BY 
	`newsMonth`
ORDER BY 
	`news`.`date` DESC
SQL;

		$result = $this->getSql()->getAll($sql);

		$this->display($result);
	}

	private function display(array $result)
	{
		echo '<ul>';

		foreach($result as $row)
		{
			echo '<li><a href="' . $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . $row['pagePath'] . '/archive/' . $row['newsYear'] . $row['newsMonth'] . '">' . $row['newsMonthname'] . ' ' . $row['newsYear'] . '</a> (' . $row['newsCount'] . ')</li>';
		}

		echo '</ul>';
	}
}


