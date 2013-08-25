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

namespace login\gadget;

use Amun\Module\GadgetAbstract;
use Amun\DataFactory;
use PSX\Sql;

/**
 * latestUser
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class latestUser extends GadgetAbstract
{
	/**
	 * onLoad
	 *
	 * @param count integer
	 */
	public function onLoad()
	{
		parent::onLoad();

		$count = $this->args->get('count', 10);

		// get latest user
		$handler = $this->hm->getHandler('User_Account');
		$result  = $handler->getAll(array('id', 
			'name', 
			'thumbnailUrl', 
			'profileUrl', 
			'date'), 0, $count, 'id', Sql::SORT_DESC, null, Sql::FETCH_OBJECT);

		$this->display($result);
	}

	private function display(array $result)
	{
		echo '<ul>';

		foreach($result as $row)
		{
			echo '<li title="' . $row->name . '"><a href="' . $row->profileUrl . '"><img src="' . $row->thumbnailUrl . '" width="48" height="48" /></a></li>';
		}

		echo '</ul>';
		echo '<div class="clearfix"></div>';
	}
}


