<?php
/*
 *  $Id: activityChart.php 875 2012-09-30 13:51:45Z k42b3.x@googlemail.com $
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

namespace my\gadget;

use Amun_Module_GadgetAbstract;

/**
 * activityChart
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @package    gadget
 * @version    $Revision: 875 $
 */
class activityChart extends Amun_Module_GadgetAbstract
{
	/**
	 * onLoad
	 *
	 * @param hours integer
	 */
	public function onLoad()
	{
		$hours = $this->args->get('count', 12);

		// add css
		$this->htmlCss->add('my');

		$now  = new DateTime('NOW', $this->registry['core.default_timezone']);
		$past = new DateTime('NOW', $this->registry['core.default_timezone']);
		$past->sub(new DateInterval('PT' . $hours . 'H'));

		$act = array();


		$result = Amun_Sql_Table_Registry::get('User_Activity')
			->select(array('date'))
			->where('date', '>=', $past->format(PSX_DateTime::SQL))
			->orderBy('date', PSX_Sql::SORT_ASC)
			->getAll();

		foreach($result as $row)
		{
			$date     = new DateTime($row['date'], $this->registry['core.default_timezone']);
			$interval = $date->diff($now);
			$key      = $interval->format('%h');

			if(!isset($act[$key]))
			{
				$act[$key] = 1;
			}
			else
			{
				$act[$key]++;
			}
		}


		$now    = time();
		$past   = $date->getTimestamp();
		$hour   = 3600;

		$chd    = array();
		$labels = array();
		$max    = 0;
		$days   = 0;

		for($i = $hours - 1; $i >= 0; $i--)
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

			$labels[] = date('H', time() - ($i * 3600));

			$days++;
		}


		$params = array(

			'cht'  => 'ls',
			'chd'  => 't:' . implode(',', $chd),
			'chs'  => '260x100',
			'chco' => '0077CC',
			'chds' => '0,' . $max,

			//'chxt' => 'x',
			//'chxl' => '0:|' . implode('|', $labels),
			//'chxr' => '0,1,' . $days . ',1',

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


