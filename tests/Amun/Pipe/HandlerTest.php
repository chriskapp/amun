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

namespace Amun\Pipe;

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
		$handler = $this->getHandler('AmunService\Pipe');
		$actual  = $handler->getOneById(1);
		$expect  = array(
			'id' => '1',
			'globalId' => '5bc21e8d-137a-5127-b997-79b38865e80e',
			'date' => '2013-04-12 20:54:52',
			'pageId' => '9',
			'pagePath' => 'pipe',
			'authorName' => 'test',
			'authorProfileUrl' => 'http://127.0.0.1/index.php/profile/test',
			'mediaId' => '1',
			'processor' => 'passthru',
			'mediaPath' => 'empty',
		);

		$this->assertEquals($expect, $actual);
	}
}
