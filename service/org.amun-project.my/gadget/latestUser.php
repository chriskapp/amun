<?php
/*
 *  $Id: latestUser.php 875 2012-09-30 13:51:45Z k42b3.x@googlemail.com $
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
 * latestUser
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @package    gadget
 * @version    $Revision: 875 $
 */
class latestUser extends Amun_Module_GadgetAbstract
{
	/**
	 * onLoad
	 *
	 * @count(integer)
	 */
	public function onLoad(Amun_Gadget_Args $args)
	{
		$count = $args->get('count', 10);

		// add css
		$this->htmlCss->add('my');

		// get latest user
		$result = Amun_Sql_Table_Registry::get('Core_User_Account')
			->select(array('id', 'name', 'thumbnailUrl', 'profileUrl', 'date'))
			->orderBy('date', PSX_Sql::SORT_DESC)
			->limit($count)
			->getAll(PSX_Sql::FETCH_OBJECT);


		$this->display($result);
	}

	private function display(array $result)
	{
		echo '<ul>';

		foreach($result as $row)
		{
			echo '<li title="' . $row->name . '"><a href="' . $row->profileUrl . '"><img src="' . $row->thumbnailUrl . '" /></a></li>';
		}

		echo '</ul>';
		echo '<div class="clearfix"></div>';
	}
}


