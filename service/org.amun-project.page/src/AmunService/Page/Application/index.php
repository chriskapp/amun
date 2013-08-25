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

namespace AmunService\Page\Application;

use Amun\Exception;
use Amun\Module\ApplicationAbstract;
use AmunService\Page\Record;
use PSX\Sql;

/**
 * Index
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Index extends ApplicationAbstract
{
	/**
	 * @httpMethod GET
	 * @path /
	 */
	public function doIndex()
	{
		if($this->user->hasRight('page_view'))
		{
			// load page
			$recordPage = $this->getHandler()->getOneByPageId($this->page->getId(), array(), Sql::FETCH_OBJECT);

			$this->template->assign('recordPage', $recordPage);

			// options
			if($recordPage instanceof Record)
			{
				$url = $this->service->getApiEndpoint() . '/form?format=json&method=update&id=' . $recordPage->id;
			}
			else
			{
				$url = $this->service->getApiEndpoint() . '/form?format=json&method=create&pageId=' . $this->page->getId();
			}

			$this->setOptions(array(
				array('page_edit', 'Edit', 'javascript:amun.services.page.showForm(\'' . $url . '\')')
			));

			// template
			$this->htmlCss->add('page');
			$this->htmlJs->add('page');
			$this->htmlJs->add('ace-html');
			$this->htmlJs->add('bootstrap');
			$this->htmlJs->add('prettify');
		}
		else
		{
			throw new Exception('Access not allowed');
		}
	}
}
