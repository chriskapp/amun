<?php
/*
 *  $Id: archive.php 744 2012-06-26 19:35:44Z k42b3.x@googlemail.com $
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
 * archive
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @package    gadget
 * @subpackage news
 * @version    $Revision: 744 $
 */
class archive extends Amun_Module_GadgetAbstract
{
	/**
	 * onLoad
	 *
	 * @pageId(integer)
	 */
	public function onLoad(Amun_Gadget_Args $args)
	{
		$pageId = $args->get('pageId', 0);


		$this->htmlCss->add('news');


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

	FROM {$this->registry['table.news']} `news`

		INNER JOIN {$this->registry['table.core_content_page']} `page`

		ON `news`.`pageId` = `page`.`id`

			{$con}

			GROUP BY `newsMonth`

				ORDER BY `news`.`date` DESC
SQL;

		$result = $this->sql->getAll($sql);


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


