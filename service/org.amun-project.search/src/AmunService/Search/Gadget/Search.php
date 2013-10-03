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

namespace AmunService\Search\Gadget;

use Amun\Module\GadgetAbstract;

/**
 * Search
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Search extends GadgetAbstract
{
	/**
	 * onLoad
	 */
	public function onLoad()
	{
		parent::onLoad();

		// get action url
		$url = $this->registry->getUrlByType('http://ns.amun-project.org/2013/amun/service/search');

		$this->display($url);
	}

	private function display($url)
	{
		echo '<form method="get" action="' . $url . '" class="form-inline" role="form">';
		echo '	<div class="form-group">';
		echo '		<label class="sr-only" for="search">Search</label>';
		echo '		<input type="search" class="form-control" name="search" id="search" placeholder="Search ..." />';
		echo '	</div>';
		echo '	<button type="submit" class="btn btn-default">Search</button>';
		echo '</form>';
	}
}
