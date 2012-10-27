<?php
/*
 *  $Id: MyAbstract.php 684 2012-06-03 12:01:26Z k42b3.x@googlemail.com $
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
 * Amun_Service_My_MyAbstract
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Service_My
 * @version    $Revision: 684 $
 */
abstract class Amun_Service_My_MyAbstract extends Amun_Module_ApplicationAbstract
{
	public function onLoad()
	{
		if($this->user->hasRight('service_my_view'))
		{
			// check status of current user
			if($this->user->isAnonymous())
			{
				throw new Amun_Exception_Forbidden('Anonymous user cant view their profile');
			}
		}
		else
		{
			throw new Amun_Exception('Access not allowed');
		}


		// options
		$options = new Amun_Option('index', $this->registry, $this->user, $this->page);
		$options->add('service_my_view', 'Account', $this->page->url);
		$options->add('service_my_view', 'Friends', $this->page->url . '/friends');
		$options->add('service_my_view', 'Settings', $this->page->url . '/settings');
		$options->load(array($this->page));

		$this->template->assign('options', $options);
	}
}

