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

namespace content\gadget;

use AmunService\Content\Page;
use Amun\DataFactory;
use Amun\Module\GadgetAbstract;
use PSX\Sql;

/**
 * navigation
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class navigation extends GadgetAbstract
{
	/**
	 * onLoad
	 *
	 * @param pageId integer
	 * @param tabs boolean
	 */
	public function onLoad()
	{
		$pageId = $this->args->get('pageId', 1);
		$tabs   = $this->args->get('tabs', false);

		// get pages
		$result = $this->hm->getTable('Content_Page')
			->select(array('id', 'rightId', 'urlTitle', 'title', 'path'))
			->where('parentId', '=', $pageId)
			->where('status', '=', Page\Record::NORMAL)
			->orderBy('sort', Sql::SORT_ASC)
			->getAll();


		$this->display($result, $tabs);
	}

	private function display(array $result, $tabs)
	{
		$path = trim($this->config['psx_module_input'], '/');

		echo '<ul class="nav ' . ($tabs ? 'nav-tabs' : 'nav-list') . '">';

		foreach($result as $i => $row)
		{
			$selected = substr($path, 0, strlen($row['path'])) == $row['path'];

			if(empty($row['rightId']) || $this->user->hasRightId($row['rightId']))
			{
				if($selected)
				{
					echo '<li class="active"><a href="' . $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . $row['path'] . '">' . $row['title'] . '</a></li>' . "\n";
				}
				else
				{
					echo '<li><a href="' . $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . $row['path'] . '">' . $row['title'] . '</a></li>' . "\n";
				}
			}
		}

		echo '</ul>';
	}
}


