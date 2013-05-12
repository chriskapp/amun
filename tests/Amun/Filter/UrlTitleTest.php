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

namespace Amun\Filter;

/**
 * UrlTitleTest
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 * @backupStaticAttributes disabled
 */
class UrlTitleTest extends \PHPUnit_Framework_TestCase
{
	public function testApply()
	{
		$filter = new UrlTitle();

		$this->assertEquals('test', $filter->apply('TEST'));
		$this->assertEquals('test-', $filter->apply('test '));
		$this->assertEquals('-test', $filter->apply(' test'));
		$this->assertEquals('tes-t123', $filter->apply('tes t123'));
		$this->assertEquals('test123', $filter->apply('tes!"§$%&/t123'));
		$this->assertEquals(false, $filter->apply(''));
		$this->assertEquals('test123', $filter->apply('test&amp;123'));
	}
}

