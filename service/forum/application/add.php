<?php
/*
 *  $Id: add.php 875 2012-09-30 13:51:45Z k42b3.x@googlemail.com $
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
 * add
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @package    application
 * @subpackage forum
 * @version    $Revision: 875 $
 */
class add extends Amun_Module_ApplicationAbstract
{
	public function onLoad()
	{
		if($this->user->hasRight('service_forum_add'))
		{
			// form url
			$url = $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/service/forum/form?format=json&method=create&pageId=' . $this->page->id;


			// add path
			$this->path->add('Add', $this->page->url . '/add');


			// template
			$this->htmlJs->add('amun');
			$this->htmlJs->add('forum');
			$this->htmlJs->add('ace');


			$helpUrl = $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'help.htm';

			echo <<<HTML
<div id="response"></div>

<div id="form"></div>

<p><span class="small">Please read the <a href="{$helpUrl}">help</a> howto properly format your content before submitting.</small></p>

<script type="text/javascript">
amun.services.forum.loadForm("form", "{$url}");
</script>
HTML;
		}
		else
		{
			throw new Amun_Exception('Access not allowed');
		}
	}
}