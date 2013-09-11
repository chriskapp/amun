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

namespace Amun\Oauth\Request;

/**
 * HandlerTest
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 * @backupStaticAttributes disabled
 */
class HandlerTest extends \Amun\HandlerTest
{
	public function testDefaultSelect()
	{
		$handler = $this->getHandler('AmunService\Oauth\Request');
		$actual  = $handler->getOneById(1);
		$expect  = array(
			'id' => '1',
			'date' => '2013-03-20 19:49:26',
			'status' => '3',
			'ip' => '127.0.0.1',
			'callback' => 'oob',
			'expire' => 'P6M',
			'apiId' => '1',
			'apiTitle' => 'System',
			'authorName' => 'test',
			'authorProfileUrl' => 'http://127.0.0.1/index.php/profile/test',
		);

		$this->assertEquals($expect, $actual);
	}
}
