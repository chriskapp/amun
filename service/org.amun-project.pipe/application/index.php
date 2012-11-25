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
 * @subpackage pipe
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
		if($this->user->hasRight('pipe_view'))
		{
			// load pipe
			$recordPipe = $this->getPipe();

			$this->template->assign('recordPipe', $recordPipe);


			// check whether user has the media right
			if($recordPipe instanceof Service_Pipe && !$this->user->hasRightId($recordPipe->mediaRightId)) 
			{
				throw new Amun_Exception('Access not allowed');
			}


			// options
			$options = new Amun_Option(__CLASS__, $this->registry, $this->user, $this->page);
			$options->add('pipe_edit', 'Edit', $this->page->url . '/edit' . (!empty($recordPipe) ? '?id=' . $recordPipe->id : ''));
			$options->load(array($this->page));

			$this->template->assign('options', $options);


			// template
			$this->htmlCss->add('pipe');
			$this->htmlJs->add('bootstrap');
			$this->htmlJs->add('prettify');

			$this->template->set(__CLASS__ . '.tpl');
		}
		else
		{
			throw new Amun_Exception('Access not allowed');
		}
	}

	private function getPipe()
	{
		return Amun_Sql_Table_Registry::get('Pipe')
			->select(array('id', 'date'))
			->join(PSX_Sql_Join::INNER, Amun_Sql_Table_Registry::get('Core_Content_Media')
				->select(array('rightId', 'name', 'path', 'mimeType'), 'media')
			)
			->join(PSX_Sql_Join::INNER, Amun_Sql_Table_Registry::get('Core_User_Account')
				->select(array('name', 'profileUrl'), 'author')
			)
			->where('pageId', '=', $this->page->id)
			->getRow(PSX_Sql::FETCH_OBJECT);
	}
}

