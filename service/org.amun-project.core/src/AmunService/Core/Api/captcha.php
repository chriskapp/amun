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

namespace AmunService\Core\Api;

use Amun\Module\ApiAbstract;
use Exception;

/**
 * Captcha
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Captcha extends ApiAbstract
{
	public function onLoad()
	{
		parent::onLoad();

		try
		{
			$captcha = \Amun\Captcha::factory($this->config['amun_captcha']);
			$captcha->serve();

			exit;
		}
		catch(Exception $e)
		{
			header('Content-type: image/png');

			$im        = imagecreatetruecolor(300, 57);
			$textcolor = imagecolorallocate($im, 0, 0, 0);
			$bgcolor   = imagecolorallocate($im, 255, 255, 255);

			imagefill($im, 0, 0, $bgcolor);
			imagestring($im, 3, 4, 4,  $e->getMessage(), $textcolor);
			imagepng($im);
			imagedestroy($im);
		}
	}

	public function onPost()
	{
	}

	public function onPut()
	{
	}

	public function onDelete()
	{
	}
}

