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

namespace Amun\Page;

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
		$handler = $this->getHandler('AmunService\Page');
		$actual  = $handler->getOneById(1);
		$expect  = array(
			'id' => '1',
			'globalId' => 'f6a9fc77-27a4-5a48-9228-8dd911485427',
			'date' => '2013-04-12 20:51:10',
			'pageId' => '1',
			'pagePath' => '',
			'content' => '<h1>It works!</h1>' . "\n" . '<p>This is the default web page for this server.</p>' . "\n" . '<p>The web server software is running but no content has been added, yet.</p>',
			'authorName' => 'test',
			'authorProfileUrl' => 'http://127.0.0.1/index.php/profile/test',
		);

		$this->assertEquals($expect, $actual);
	}
}
