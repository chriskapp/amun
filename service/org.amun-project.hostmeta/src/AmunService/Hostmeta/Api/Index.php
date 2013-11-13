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

namespace AmunService\Hostmeta\Api;

use Amun\Module\ApiAbstract;
use PSX\Data\Message;
use PSX\Http;
use PSX\Hostmeta\Jrd;
use PSX\Hostmeta\Xrd;

/**
 * Index
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Index extends ApiAbstract
{
	private $writer;

	/**
	 * Returns hostmeta informations
	 *
	 * @httpMethod GET
	 * @path /
	 * @nickname getHostmeta
	 * @responseClass PSX_Data_ResultSet
	 */
	public function getHostmeta()
	{
		try
		{
			$accept = $this->getHeader('Accept');
			$format = $this->get->format('string');

			if($format == 'xml' || $accept == 'application/xrd+xml')
			{
				header('Content-Type: application/xrd+xml');

				$document = new Xrd();
			}
			else
			{
				header('Content-Type: application/jrd+json');

				$document = new Jrd();
			}

			$document->setSubject($this->config['psx_url']);
			$document->addProperty('http://ns.amun-project.org/2011/meta/title', $this->registry['core.title']);
			$document->addProperty('http://ns.amun-project.org/2011/meta/subTitle', $this->registry['core.sub_title']);
			$document->addProperty('http://ns.amun-project.org/2011/meta/timezone', $this->registry['core.default_timezone']->getName());

			echo $document->export();
		}
		catch(\Exception $e)
		{
			$code = isset(Http::$codes[$e->getCode()]) ? $e->getCode() : 500;
			$msg  = new Message($e->getMessage() . $e->getTraceAsString(), false);

			$this->setResponse($msg, null, $code);
		}
	}
}
