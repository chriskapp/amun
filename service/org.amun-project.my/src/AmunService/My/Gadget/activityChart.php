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

namespace AmunService\My\Gadget;

use Amun\Module\GadgetAbstract;
use Amun\DataFactory;
use DateInterval;
use PSX\DateTime;
use PSX\Sql;
use PSX\Sql\Condition;

/**
 * ActivityChart
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class ActivityChart extends GadgetAbstract
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

		$now  = new DateTime('NOW', $this->registry['core.default_timezone']);
		$past = new DateTime('NOW', $this->registry['core.default_timezone']);
		$past->sub(new DateInterval('P' . $count . 'D'));

		$act = array();

		// condition
		$con = new Condition();
		$con->add('scope', '=', 0);
		$con->add('date', '>=', $past->format(DateTime::SQL));

		// get activities
		$handler = $this->hm->getHandler('AmunService\User\Activity');
		$result  = $handler->getAll(array('id', 
			'scope', 
			'summary', 
			'date', 
			'authorId', 
			'authorName', 
			'authorThumbnailUrl'), 0, 64, 'date', Sql::SORT_ASC, $con);

		foreach($result as $row)
		{
			$date     = new DateTime($row['date'], $this->registry['core.default_timezone']);
			$interval = $date->diff($now);
			$key      = $interval->format('%d');

			if(!isset($act[$key]))
			{
				$act[$key] = 1;
			}
			else
			{
				$act[$key]++;
			}
		}

		// build params
		$chd    = array();
		$labels = array();
		$max    = 0;
		$days   = 0;

		for($i = $count - 1; $i >= 0; $i--)
		{
			if(isset($act[$i]))
			{
				if($act[$i] > $max)
				{
					$max = $act[$i];
				}

				$chd[$i] = $act[$i];
			}
			else
			{
				$chd[$i] = 0;
			}

			$labels[] = date('d M', time() - ($i * 3600 * 24));

			$days++;
		}

		$params = array(

			'cht'  => 'ls',
			'chd'  => 't:' . implode(',', $chd),
			'chs'  => '320x100',
			'chco' => '0077CC',
			'chds' => '0,' . $max,

			'chxt' => 'x',
			'chxl' => '0:|' . implode('|', $labels),
			'chxr' => '0,1,' . $days . ',1',

		);


		$this->display($params);
	}

	private function display(array $params)
	{
		$param = '';

		foreach($params as $k => $v)
		{
			$param.= $k . '=' . $v . '&';
		}

		$param = substr($param, 0, -1);

		echo '<img src="http://chart.apis.google.com/chart?' . $param . '" alt="Activity Chart" />';
	}
}


