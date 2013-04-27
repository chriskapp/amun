<?php
/*
 *  $Id: Handler.php 880 2012-10-27 13:14:26Z k42b3.x@googlemail.com $
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

namespace AmunService\Vcshook\Type;

use AmunService\Vcshook\TypeAbstract;
use AmunService\Vcshook\Project;
use AmunService\Vcshook\Revision;
use Amun\Exception;
use PSX\Http\GetRequest;
use PSX\Url;
use PSX\Json;

/**
 * Amun_Service_Googleproject_Handler
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Service_Googleproject
 * @version    $Revision: 880 $
 */
class Googleproject extends TypeAbstract
{
	public function getRequest($payload)
	{
		$response = Json::decode($payload);

		if(isset($response['project_name']))
		{
			$project = new Project();
			$project->setName($response['project_name']);
			$project->setUrl('https://code.google.com/p/' . $response['project_name'] . '/');

			if(isset($response['revisions']) && is_array($response['revisions']))
			{
				foreach($response['revisions'] as $commit)
				{
					try
					{
						$revision = new Revision();
						$revision->setMessage($commit['message']);
						$revision->setTimestamp($commit['timestamp']);
						$revision->setUrl($commit['url']);
						$revision->setAuthor($commit['author']);

						$project->addCommit($revision);
					}
					catch(\Exception $e)
					{
					}
				}
			}

			return $project;
		}
		else
		{
			throw new Exception('Invalid request');
		}
	}

	public function hasProject($url)
	{
		$url = new Url($url);

		if($url->getHost() != 'code.google.com')
		{
			return false;
		}

		$parts = explode('/', trim($url->getPath(), '/'));

		if(count($parts) != 2)
		{
			return false;
		}

		$request  = new GetRequest($url);
		$response = $this->http->request($request);

		return $response->getCode() == 200;
	}
}
