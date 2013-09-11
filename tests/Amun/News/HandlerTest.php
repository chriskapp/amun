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

namespace Amun\News;

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
		$handler = $this->getHandler('AmunService\News');
		$actual  = $handler->getOneById(1);
		$expect  = array(
			'id' => '1',
			'globalId' => 'f46f108a-3c2b-5cda-855f-f63ea190b9e6',
			'date' => '2013-04-12 20:54:19',
			'pageId' => '7',
			'userId' => '1',
			'urlTitle' => 'foobar',
			'title' => 'foobar',
			'text' => '<p>content </p>' . "\n",
			'authorName' => 'test',
			'authorProfileUrl' => 'http://127.0.0.1/index.php/profile/test',
			'authorThumbnailUrl' => 'http://www.gravatar.com/avatar/b642b4217b34b1e8d3bd915fc65c4452?d=http%3A%2F%2F127.0.0.1%2Fprojects%2Famun%2Fpublic%2Fimg%2Favatar%2Fno_image.png&s=48',
			'pagePath' => 'news',
		);

		$this->assertEquals($expect, $actual);
	}
}
