<?php
/*
 *  $Id: latestActivity.php 875 2012-09-30 13:51:45Z k42b3.x@googlemail.com $
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
 * latestActivity
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @package    gadget
 * @version    $Revision: 875 $
 */
class latestActivity extends Amun_Module_GadgetAbstract
{
	/**
	 * onLoad
	 *
	 * @count(integer)
	 */
	public function onLoad(Amun_Gadget_Args $args)
	{
		$count = $args->get('count', 8);

		// add css
		$this->htmlCss->add('my');

		// get activities
		$result = Amun_Sql_Table_Registry::get('User_Activity')
			->select(array('id', 'scope', 'summary', 'date'))
			->join(PSX_Sql_Join::INNER, Amun_Sql_Table_Registry::get('User_Account')
				->select(array('id', 'name', 'thumbnailUrl'), 'author')
			)
			->where('scope', '=', 0)
			->orderBy('date', PSX_Sql::SORT_DESC)
			->limit($count)
			->getAll();

		$this->display($result);
	}

	private function display(array $result)
	{
		$now = new DateTime('NOW', $this->registry['core.default_timezone']);

		echo '<ul>';

		foreach($result as $row)
		{
			$date     = new DateTime($row['date'], $this->registry['core.default_timezone']);
			$interval = $now->diff($date);

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
				$ago = 'on ' . $date->format($this->registry['core.format_datetime']);
			}

			echo '<li><img src="' . $row['authorThumbnailUrl'] . '" /><p>' . $row['summary'] . '</p><p class="muted">' . $ago . '</p></li>';
		}

		echo '</ul>';
		echo '<div class="clearfix"></div>';
	}
}






