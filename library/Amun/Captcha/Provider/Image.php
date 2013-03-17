<?php
/*
 *  $Id: Default.php 635 2012-05-01 19:46:37Z k42b3.x@googlemail.com $
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

namespace Amun\Captcha\Provider;

use Amun\Captcha\ProviderInterface;
use Amun\Exception;

/**
 * Amun_Captcha_Provider_Default
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Captcha
 * @version    $Revision: 635 $
 */
class Image implements ProviderInterface
{
	private $width  = 200; # width
	private $height = 75;  # height
	private $lines  = 3;   # lines that cross the number
	private $space  = 25;  # space between numbers
	private $noise  = 10;  # additional noise

	public function verify($result)
	{
		if(isset($_SESSION['amun_captcha_result']))
		{
			$captchaResult = $_SESSION['amun_captcha_result'];
		}
		else
		{
			throw new Exception('Result not set');
		}

		if(!empty($captchaResult) && !empty($result))
		{
			return strcmp($captchaResult, $result) == 0;
		}
		else
		{
			return false;
		}
	}

	public function serve()
	{
		header('Content-type: image/png');

		$handle = imagecreatetruecolor($this->width, $this->height);

		$black = imagecolorallocate($handle, 0,   0,   0  );
		$white = imagecolorallocate($handle, 255, 255, 255);

		imagefill($handle, 0, 0, $white);

		$rand   = rand(10000, 99999);
		$rand   = strval($rand);
		$x      = rand(10, $this->width - 130);
		$y      = rand(30, $this->height - 10);
		$length = strlen($rand);


		for($i = 0; $i < $length; $i++)
		{
			$font = PSX_PATH_LIBRARY . '/Amun/Captcha/chopin.ttf';

			imagettftext($handle, 35, 0, $x, $y, $black, $font, $rand[$i]);
			//imagestring($handle, 5, $x, $y, $rand[$i], $black);

			$x = $x + $this->space;
		}

		# horizontal lines
		for($i = 0; $i < $this->lines; $i++)
		{
			$x1 = rand(0, $this->width);
			$y1 = 0;
			$x2 = rand(0, $this->width);
			$y2 = $this->height;

			imageline($handle, $x1, $y1, $x2, $y2, $black);
		}

		# vertical lines
		for($i = 0; $i < $this->lines; $i++)
		{
			$x1 = 0;
			$y1 = rand(0, $this->height);
			$x2 = $this->width;
			$y2 = rand(0, $this->height);

			imageline($handle, $x1, $y1, $x2, $y2, $black);
		}

		# add noise
		$noise = ($this->noise * ($this->width * $this->height)) / 100;

		for($i = 0; $i < $noise; $i++)
		{
			$x = rand(0, $this->width);
			$y = rand(0, $this->height);

			imagesetpixel($handle, $x, $y, $black);
		}


		imagepng($handle);

		imagedestroy($handle);


		$_SESSION['amun_captcha_result'] = $rand;
	}
}

