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

namespace marketplace\api;

use Amun\Module\ApiAbstract;
use Amun\Exception;
use PSX\Data\Message;
use PSX\Data\ResultSet;
use PSX\Data\Record;
use PSX\Data\RecordAbstract;

/**
 * get
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class get extends ApiAbstract
{
	/**
	 * Returns a specific service as zip
	 *
	 * @httpMethod GET
	 * @path /
	 * @nickname getService
	 * @responseClass PSX_Data_Record
	 */
	public function getService()
	{
		if($this->user->hasRight('marketplace_download'))
		{
			try
			{
				$source = $this->get->source('string');

				$this->buildSource($source);
			}
			catch(\Exception $e)
			{
				$msg = new Message($e->getMessage(), false);

				$this->setResponse($msg);
			}
		}
		else
		{
			$msg = new Message('Access not allowed', false);

			$this->setResponse($msg, null, $this->user->isAnonymous() ? 401 : 403);
		}
	}

	private function buildSource($source)
	{
		// check whether we have this source
		$con    = new Condition(array('source', '=', $source));
		$source = $this->hm->getTable('AmunService\Marketplace')->getField('source', $con);

		if(!empty($source))
		{
			$servicePath = $this->config['amun_service_path'] . '/' . $source;

			$file = $source . '.zip';
			$path = PSX_PATH_CACHE . '/' . $file;

			if(is_dir($servicePath))
			{
				$zip = new ZipArchive();
				$zip->open($path, ZIPARCHIVE::CREATE);

				// add source files
				$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($servicePath));

				while($it->valid())
				{
					if(!$it->isDot())
					{
						$zip->addFile($it->getSubPathName());
					}

					$it->next();
				}

				$zip->close();

				// send to browser
				header('Content-Type: application/octet-stream');
				header('Content-Disposition: attachment; filename=' . $file);
				header('Content-Transfer-Encoding: binary');
				header('Expires: 0');
				header('Cache-Control: must-revalidate');
				header('Pragma: public');
				header('Content-Length: ' . filesize($path));
				header('Content-MD5: ' . md5_file($path));

				ob_clean();
				flush();

				readfile($path);
				exit;
			}
			else
			{
				throw new Exception('Source doesnt exist');
			}
		}
		else
		{
			throw new Exception('Invalid source');
		}
	}
}
