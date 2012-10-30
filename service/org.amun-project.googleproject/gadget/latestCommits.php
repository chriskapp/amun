<?php
/*
 *  $Id: latestCommits.php 744 2012-06-26 19:35:44Z k42b3.x@googlemail.com $
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
 * latestCommits
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @package    gadget
 * @subpackage googleproject
 * @version    $Revision: 744 $
 */
class latestCommits extends Amun_Module_GadgetAbstract
{
	/**
	 * onLoad
	 *
	 * @count(integer)
	 */
	public function onLoad(Amun_Gadget_Args $args)
	{
		$count  = $args->get('count', 8);


		$this->htmlCss->add('googleproject');


		$result = Amun_Sql_Table_Registry::get('Service_Googleproject_Commit')
			->select(array('id', 'revision', 'url', 'message', 'commitDate', 'date'))
			->join(PSX_Sql_Join::INNER, Amun_Sql_Table_Registry::get('Service_Googleproject_Author')
				->select(array('name'), 'user')
				->join(PSX_Sql_Join::INNER, Amun_Sql_Table_Registry::get('User_Account')
					->select(array('name', 'profileUrl'), 'author')
				)
			)
			->orderBy('date', PSX_Sql::SORT_DESC)
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
			$title = '<a href="' . $row->url . '">' . $row->message . '</a>';

			echo '<li>' . $title . '<br /><small style="display:block;white-space:nowrap;overflow:hidden;">by ' . $user . ' ' . $ago . '</small></li>';
		}

		echo '</ul>';
	}
}


