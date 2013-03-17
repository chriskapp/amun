<?php
/*
 *  $Id: index.php 875 2012-09-30 13:51:45Z k42b3.x@googlemail.com $
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

namespace file\application;

use Amun\Module\ApplicationAbstract;
use Amun\Exception;
use PSX\DateTime;

/**
 * index
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @package    application
 * @subpackage page
 * @version    $Revision: 875 $
 */
class index extends ApplicationAbstract
{
	public function onLoad()
	{
		if($this->user->hasRight('file_view'))
		{
			$file = $this->getHandler()->getByPageId($this->page->id);

			if(!empty($file))
			{
				$date = new DateTime($file['date'], $this->registry['core.default_timezone']);

				header('Content-Type: ' . $file['contentType']);
				header('Last-Modified: ' . $date->format(DateTime::RFC2822));

				echo $file['content'];
				exit;
			}
			else
			{
				throw new Exception('No file set');
			}
		}
		else
		{
			throw new Exception('Access not allowed');
		}
	}
}
