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

/**
 * index
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @package    application
 * @subpackage news
 * @version    $Revision: 875 $
 */
class index extends Amun_Module_ApplicationAbstract
{
	private $project;

	/**
	 * @httpMethod GET
	 * @path /
	 */
	public function doIndex()
	{
		if($this->getProvider()->hasViewRight())
		{
			// load page
			$this->project = $this->getProject();

			$this->template->assign('recordProject', $this->project);

			// load commits
			if($this->project instanceof AmunService_Googleproject_Record)
			{
				$resultCommit = $this->getCommits();

				$this->template->assign('resultCommit', $resultCommit);
			}

			// options
			$this->setOptions(array(
				array('googleproject_edit', 'Edit', $this->page->url . '/edit' . (!empty($this->project) ? '?id=' . $this->project->id : ''))
			));

			// template
			$this->htmlCss->add('googleproject');
			$this->htmlContent->add(Amun_Html_Content::META, PSX_Data_Writer_Atom::link($this->page->title, $this->service->getApiEndpoint() . '/commit?format=atom&filterBy=pageId&filterOp=equals&filterValue=' . $this->page->id));

			$this->template->set(__CLASS__ . '.tpl');
		}
		else
		{
			throw new Amun_Exception('Access not allowed');
		}
	}

	private function getProject()
	{
		return Amun_Sql_Table_Registry::get('Googleproject')
			->select(array('id', 'name', 'repositoryPath', 'lastCommitDate', 'date'))
			->join(PSX_Sql_Join::INNER, Amun_Sql_Table_Registry::get('User_Account')
				->select(array('name', 'profileUrl'), 'author')
			)
			->where('pageId', '=', $this->page->id)
			->getRow(PSX_Sql::FETCH_OBJECT);
	}

	private function getCommits()
	{
		$select = Amun_Sql_Table_Registry::get('Googleproject_Commit')
			->select(array('id', 'revision', 'url', 'message', 'commitDate', 'date'))
			->join(PSX_Sql_Join::INNER, Amun_Sql_Table_Registry::get('Googleproject_Author')
				->select(array('name'), 'user')
				->join(PSX_Sql_Join::INNER, Amun_Sql_Table_Registry::get('User_Account')
					->select(array('name', 'profileUrl'), 'author')
				)
			)
			->where('projectId', '=', $this->project->id)
			->orderBy('commitDate', PSX_Sql::SORT_DESC);


		$fragments = $this->getUriFragments();

		if(isset($fragments[0]) && $fragments[0] == 'archive')
		{
			$rawDate = isset($fragments[1]) && strlen($fragments[1]) == 6 ? $fragments[1] : date('Ym');

			$year  = intval(substr($rawDate, 0, 4));
			$month = intval(substr($rawDate, 4));

			// i think this software will not be used after the year 3000
			// if so please travel back in time and slap me in the face
			// ... nothing happens ;D
			if(($year > 2010 && $year < 3000) && ($month > 0 && $month < 13))
			{
				$date = new DateTime($year . '-' . ($month < 10 ? '0' : '') . $month . '-01', $this->registry['core.default_timezone']);

				$select->where('date', '>=', $date->format(PSX_DateTime::SQL));
				$select->where('date', '<', $date->add(new DateInterval('P1M'))->format(PSX_DateTime::SQL));
			}
		}


		$url    = new PSX_Url($this->base->getSelf());
		$count  = $url->getParam('count') > 0 ? $url->getParam('count') : 8;

		$result = $select->getResultSet($url->getParam('startIndex'), $count, $url->getParam('sortBy'), $url->getParam('sortOrder'), $url->getParam('filterBy'), $url->getParam('filterOp'), $url->getParam('filterValue'), $url->getParam('updatedSince'), PSX_SQL::FETCH_OBJECT);


		$paging = new PSX_Html_Paging($url, $result);

		$this->template->assign('pagingCommits', $paging, 0);


		return $result;
	}
}

