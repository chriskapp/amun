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

namespace AmunService\Search\Application;

use Amun\Module\ApplicationAbstract;
use Amun\Exception;
use DateTime;
use Elastica\Client;
use Elastica\Query;
use Elastica\Query\QueryString;
use PSX\Data\ResultSet;
use PSX\Html\Paging;
use PSX\Url;

/**
 * index
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Index extends ApplicationAbstract
{
	public function onLoad()
	{
		parent::onLoad();

		if($this->user->hasRight('search_view'))
		{
			// template
			$this->htmlCss->add('search');
			$this->htmlJs->add('search');
			$this->htmlJs->add('ace-html');
			$this->htmlJs->add('bootstrap');
			$this->htmlJs->add('prettify');
		}
		else
		{
			throw new Exception('Access not allowed');
		}
	}

	/**
	 * @httpMethod GET
	 * @path /
	 */
	public function doIndex()
	{
	}

	/**
	 * @httpMethod POST
	 * @path /
	 */
	public function doSearch()
	{
		$url   = new Url($this->base->getSelf());
		$count = $url->getParam('count') > 0 ? $url->getParam('count') : 8;
		$count = $count > 16 ? 16 : $count;

		$search = $this->post->search('string');

		if(!empty($search))
		{
			$search = strlen($search) > 64 ? substr($search, 0, 64) : $search;

			$queryString = new QueryString();
			//$queryString->setDefaultOperator('AND');
			$queryString->setQuery($search);

			$query = new Query();
			$query->setQuery($queryString);
			//$query->setFrom($url->getParam('startIndex'));
			//$query->setLimit($count);

			// get elasticsearch client
			$client  = new Client(array(
				'host' => $this->registry['search.host'],
				'port' => $this->registry['search.port'],
			));

			$index        = $client->getIndex('amun');
			$searchResult = $index->search($query);
			$result       = new ResultSet($searchResult->getTotalHits(), $url->getParam('startIndex'), $count);

			foreach($searchResult as $row)
			{
				$data = $row->getData();
				$data['url']  = $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . $data['path'];
				$data['date'] = new DateTime('@' . $data['date']);

				$result->addData($data);
			}

			$this->template->assign('resultSearch', $result);


			$paging = new Paging($url, $result);

			$this->template->assign('pagingSearch', $paging, 0);


			return $result;
		}
	}
}
