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
class template extends Amun_Module_ApiAbstract
{
	public function onLoad()
	{
		if($this->user->hasRight('content_page_view'))
		{
			try
			{
				$totalResults = $this->sql->count($this->registry['table.content_page']);
				$startIndex   = 0;
				$data         = $this->buildTemplates();
				$count        = count($data);


				$resultset = new PSX_Data_ResultSet($totalResults, $startIndex, $count, $data);

				$this->setResponse($resultset);
			}
			catch(Exception $e)
			{
				$msg = new PSX_Data_Message($e->getMessage(), false);

				$this->setResponse($msg);
			}
		}
		else
		{
			$msg = new PSX_Data_Message('Access not allowed', false);

			$this->setResponse($msg, null, $this->user->isAnonymous() ? 401 : 403);
		}
	}

	private function buildTemplates()
	{
		$templates = array();
		$path      = PSX_PATH_TEMPLATE . '/' . $this->config['psx_template_dir'];
		$files     = scandir($path);

		foreach($files as $f)
		{
			if($f[0] != '.' && is_file($path . '/' . $f))
			{
				$name = pathinfo($f, PATHINFO_FILENAME);

				$templates[] = array(

					'title'        => $name,
					'value'        => $f,
					'thumbnailUrl' => $this->config['psx_url'] . '/img/layout/' . $name . '.png',

				);
			}
		}

		return $templates;
	}
}
