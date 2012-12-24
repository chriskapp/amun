<?php
/*
 *  $Id: latestNews.php 845 2012-09-16 17:50:03Z k42b3.x@googlemail.com $
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
 * latestNews
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @package    gadget
 * @subpackage news
 * @version    $Revision: 845 $
 */
class latestNews extends Amun_Data_GadgetAbstract
{
	/**
	 * onLoad
	 *
	 * @pageId(integer)
	 * @count(integer)
	 */
	public function onLoad(Amun_Gadget_Args $args)
	{
		$pageId = $args->get('pageId', 0);
		$count  = $args->get('count', 8);

		// add css
		$this->htmlCss->add('news');

		// get latest news
		$select = Amun_Sql_Table_Registry::get('News')
			->select(array('id', 'urlTitle', 'title', 'date'))
			->join(PSX_Sql_Join::INNER, Amun_Sql_Table_Registry::get('User_Account')
				->select(array('id', 'name', 'profileUrl'), 'author')
			)
			->join(PSX_Sql_Join::INNER, Amun_Sql_Table_Registry::get('Content_Page')
				->select(array('path'), 'page')
			);

		if(!empty($pageId))
		{
			$select->where('pageId', '=', $pageId);
		}

		$result = $select->orderBy('date', PSX_Sql::SORT_DESC)
			->limit($count)
			->getAll(PSX_Sql::FETCH_OBJECT);


		$this->display($result);
	}

	private function display(array $result)
	{
		$now = new DateTime('NOW', $this->registry['core.default_timezone']);

		echo '<ul>';

		foreach($result as $row)
		{
			$interval = $row->getDate()->diff($now);

			if($interval->format('%d') == 0)
			{
				if($interval->format('%h') == 0)
				{
					$ago = 'ago ' . $interval->format('%i minutes');
				}
				else
				{
					$ago = 'ago ' . $interval->format('%h hours');
				}
			}
			else
			{
				$ago = 'on ' . $row->getDate()->format($this->registry['core.format_date']);
			}

			$user  = '<a href="' . $row->authorProfileUrl . '">' . $row->authorName . '</a>';
			$title = '<a href="' . $row->getUrl() . '">' . $row->title . '</a>';

			echo '<li><span>' . $title . '</span><p class="muted">by ' . $user . ' ' . $ago . '</p></li>';
		}

		echo '</ul>';
	}
}


