<?php
/*
 *  $Id: ApplicationAbstract.php 712 2012-06-18 22:02:46Z k42b3.x@googlemail.com $
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
 * Amun_Module_ApplicationAbstract
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Captcha
 * @version    $Revision: 712 $
 */
abstract class Amun_Module_ApplicationAbstract extends PSX_Module_ViewAbstract
{
	public function getDependencies()
	{
		return new Amun_Dependency_Application();
	}

	/**
	 * Helper method to build the options for an application. Using the option
	 * class has the advantage that other services can easily extend the service
	 * by injecting links into the option menu
	 *
	 * @param array $data
	 * @return void
	 */
	protected function setOptions(array $data)
	{
		$options = new Amun_Option(__CLASS__, $this->registry, $this->user, $this->page);

		foreach($data as $row)
		{
			list($rightName, $title, $url) = $row;

			$options->add($rightName, $title, $url);
		}

		$options->load(array($this->page));

		$this->template->assign('options', $options);
	} 
}

