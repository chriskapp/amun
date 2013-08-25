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

namespace my\gadget;

use Amun\Module\GadgetAbstract;
use Amun\DataFactory;
use PSX\DateTime;
use PSX\Sql;
use PSX\Sql\Condition;
use PSX\Sql\Join;

/**
 * latestActivity
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class latestActivity extends GadgetAbstract
{
	/**
	 * onLoad
	 *
	 * @param count integer
	 */
	public function onLoad()
	{
		parent::onLoad();

		$count = $this->args->get('count', 8);

		// condition
		$con = new Condition(array('scope', '=', 0));

		// get activities
		$handler = $this->hm->getHandler('AmunService\User\Activity');
		$result  = $handler->getAll(array('id', 
			'scope', 
			'summary', 
			'date', 
			'authorId', 
			'authorName', 
			'authorThumbnailUrl'), 0, $count, 'date', Sql::SORT_DESC, $con);

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

			echo '<li>' . $row['summary'] . '<p class="muted">' . $ago . '</p></li>';
		}

		echo '</ul>';
		echo '<div class="clearfix"></div>';
	}
}






