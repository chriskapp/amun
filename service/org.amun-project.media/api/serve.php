<?php
/*
 *  $Id: serve.php 880 2012-10-27 13:14:26Z k42b3.x@googlemail.com $
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

namespace media\api;

use Amun_Module_ApiAbstract;
use Amun_Sql_Table_Registry;
use Exception;
use PSX_Base;
use PSX_Data_Exception;
use PSX_Data_Message;

/**
 * serve
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @package    api
 * @subpackage content_media
 * @version    $Revision: 880 $
 */
class serve extends Amun_Module_ApiAbstract
{
	/**
	 * Outputs the raw media item
	 *
	 * @httpMethod GET
	 * @path /{mediaId}
	 * @nickname doServe
	 * @responseClass PSX_Data_Message
	 */
	public function doServe()
	{
		try
		{
			// get id
			$mediaId = $this->getUriFragments('mediaId');

			if(strlen($mediaId) == 36)
			{
				$media = $this->getHandler()->getByGlobalId($mediaId);
			}
			else
			{
				$media = $this->getHandler()->getById($mediaId);
			}

			// get media item
			if(!empty($media))
			{
				// remove caching header
				header('Expires:');
				header('Last-Modified:');
				header('Cache-Control:');
				header('Pragma:');

				// check right
				if(!empty($media['rightId']) && !$this->user->hasRightId($media['rightId']))
				{
					throw new PSX_Data_Exception('Access not allowed');
				}

				// send header
				switch($media['mimeType'])
				{
					case 'application/octet-stream':
						header('Content-type: ' . $media['mimeType']);
						header('Content-disposition: attachment; filename="' . $media['name'] . '"');
						break;

					default:
						header('Content-type: ' . $media['mimeType']);
						break;
				}

				// read content
				if($media['path'][0] == '/' || $media['path'][1] == ':')
				{
					// absolute path
					$path = $media['path'];
				}
				else
				{
					// relative path
					$path = $this->registry['media.path'] . '/' . $media['path'];
				}

				if(!is_file($path))
				{
					throw new PSX_Data_Exception('File not found', 404);
				}

				$response = file_get_contents($path);

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
					header('HTTP/1.1 304 Not Modified');
				}

				exit;
			}
			else
			{
				throw new PSX_Data_Exception('Invalid media id');
			}
		}
		catch(Exception $e)
		{
			$msg = new PSX_Data_Message($e->getMessage(), false);

			$this->setResponse($msg, null, 404);
		}
	}
}

