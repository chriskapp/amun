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
 * @subpackage tracker
 * @version    $Revision: 875 $
 */
class index extends Amun_Module_ApplicationAbstract
{
	/**
	 * @httpMethod GET
	 * @path /
	 */
	public function doIndex()
	{
		if($this->user->hasRight('tracker_view'))
		{
			// load tracker
			$resultTracker = $this->getTracker();

			$this->template->assign('resultTracker', $resultTracker);


			// options
			$options = new Amun_Option(__CLASS__, $this->registry, $this->user, $this->page);
			$options->add('tracker_add', 'Add', $this->page->url . '/add');
			$options->load(array($this->page));

			$this->template->assign('options', $options);


			// template
			$this->htmlCss->add('tracker');
			$this->htmlJs->add('prettify');
			$this->htmlContent->add(Amun_Html_Content::META, PSX_Data_Writer_Atom::link($this->page->title, $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/tracker?format=atom&filterBy=pageId&filterOp=equals&filterValue=' . $this->page->id));

			$this->template->set(__CLASS__ . '.tpl');
		}
		else
		{
			throw new Amun_Exception('Access not allowed');
		}
	}

	private function getTracker()
	{
		$select = Amun_Sql_Table_Registry::get('Tracker')
			->select(array('id', 'urlTitle', 'title', 'seeder', 'leecher', 'date'))
			->join(PSX_Sql_Join::INNER, Amun_Sql_Table_Registry::get('User_Account')
				->select(array('name', 'profileUrl'), 'author')
			)
			->join(PSX_Sql_Join::INNER, Amun_Sql_Table_Registry::get('Content_Page')
				->select(array('path'), 'page')
			)
			->where('pageId', '=', $this->page->id)
			->orderBy('date', PSX_Sql::SORT_DESC);


		$fragments = $this->getUriFragments();


		$url    = new PSX_Url($this->base->getSelf());
		$count  = $url->getParam('count') > 0 ? $url->getParam('count') : 8;

		$result = $select->getResultSet($url->getParam('startIndex'), $count, $url->getParam('sortBy'), $url->getParam('sortOrder'), $url->getParam('filterBy'), $url->getParam('filterOp'), $url->getParam('filterValue'), $url->getParam('updatedSince'), PSX_SQL::FETCH_OBJECT);


		$paging = new PSX_Html_Paging($url, $result);

		$this->template->assign('pagingTracker', $paging, 0);


		return $result;
	}
}

