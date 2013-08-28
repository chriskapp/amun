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

namespace AmunService\Content;

use Amun\Data\ListenerAbstract;
use Amun\DataFactory;
use AmunService\Content\Page;
use PSX\DateTime;
use PSX\Filter;
use XMLWriter;

/**
 * LrddListener
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class LrddListener extends ListenerAbstract
{
	public function notify(XMLWriter $writer, $uri)
	{
		$page = $this->getPage($uri);

		if($page instanceof Page\Record)
		{
			// subject
			$subject = $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . $page->path;

			$writer->writeElement('Subject', $subject);

			// id
			$writer->startElement('Property');
			$writer->writeAttribute('type', 'http://ns.amun-project.org/2011/meta/id');
			$writer->text($page->globalId);
			$writer->endElement();

			// title
			$writer->startElement('Property');
			$writer->writeAttribute('type', 'http://ns.amun-project.org/2011/meta/title');
			$writer->text($page->title);
			$writer->endElement();

			// date
			$writer->startElement('Property');
			$writer->writeAttribute('type', 'http://ns.amun-project.org/2011/meta/date');
			$writer->text($page->getDate()->format(DateTime::ATOM));
			$writer->endElement();
		}
	}

	protected function getPage($uri)
	{
		$filter = new Filter\Url();

		if($filter->apply($uri) === true && (substr($uri, 0, 7) == 'http://' || substr($uri, 0, 8) == 'https://'))
		{
			// remove base url
			$uri = substr($uri, strlen($this->config['psx_url'] . '/' . $this->config['psx_dispatch']));
			$uri = trim($uri, '/');

			// get page
			$handler = $this->hm->getHandler('AmunService\Content\Page');
			return $handler->getOneByPath($uri);
		}
	}
}