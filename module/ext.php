<?php
/*
 *  $Id: ext.php 834 2012-08-26 21:16:47Z k42b3.x@googlemail.com $
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
 * ext
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @version    $Revision: 834 $
 */
class ext extends Amun_Module_DefaultAbstract
{
	public function onLoad()
	{
		$mode     = $this->get->mode('string');
		$services = $this->get->services('string');

		try
		{
			switch($mode)
			{
				case 'Amun_Html_Css':

					$provider = new Amun_Ext_Provider_Css($this->registry);
					break;

				case 'Amun_Html_Js':

					$provider = new Amun_Ext_Provider_Js($this->registry);
					break;

				default:

					throw new Amun_Exception('Invalid mode');
					break;
			}

			$ext = new Amun_Ext($this->config, $provider);

			$response = $ext->serve($services);

			// remove caching header
			header('Expires:');
			header('Last-Modified:');
			header('Cache-Control:');
			header('Pragma:');

			// gzip encoding
			$acceptEncoding = PSX_Base::getRequestHeader('Accept-Encoding');

			if($this->config['psx_gzip'] === true && strpos($acceptEncoding, 'gzip') !== false)
			{
				header('Content-Encoding: gzip');

				$response = gzencode($response, 9);
			}

			// caching header
			$etag  = md5($response);
			$match = PSX_Base::getRequestHeader('If-None-Match');
			$match = $match !== false ? trim($match, '"') : '';

			header('Etag: "' . $etag . '"');

			if($match != $etag)
			{
				echo $response;
			}
			else
			{
				PSX_Base::setResponseCode(304);
			}
		}
		catch(Exception $e)
		{
			PSX_Base::setResponseCode(500);
			header('Content-type: text/plain');

			echo $e->getMessage();

			if($this->config['psx_debug'] === true)
			{
				echo "\n\n" . $e->getTraceAsString();
			}
		}
	}
}

