<?php
/*
 *  $Id: template.php 683 2012-06-03 11:52:32Z k42b3.x@googlemail.com $
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

namespace content\api\page;

use Amun\Module\ApiAbstract;
use Amun\Filter;
use PSX\Base;
use PSX\Data\Message;
use PSX\Data\ResultSet;
use PSX\Util\Markdown;

/**
 * template
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @package    api
 * @subpackage content_page
 * @version    $Revision: 683 $
 */
class render extends ApiAbstract
{
	/**
	 * Renders the content as html markup
	 *
	 * @httpMethod POST
	 * @path /
	 * @nickname doRender
	 * @responseClass PSX_Data_ResultSet
	 */
	public function doRender()
	{
		if($this->user->hasRight('content_page_preview'))
		{
			try
			{
				$oembed   = $this->get->oembed('boolean');
				$markdown = $this->get->markdown('boolean');
				$input    = Base::getRawInput();
				$filter   = new Filter\Html($this->config, $this->user, $oembed);

				if($markdown)
				{
					$input = Markdown::decode($input);
				}

				header('Content-Type: text/html');
				echo $filter->apply($input);
				exit;
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
}
