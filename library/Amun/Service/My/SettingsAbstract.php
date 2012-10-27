<?php
/*
 *  $Id: SettingsAbstract.php 635 2012-05-01 19:46:37Z k42b3.x@googlemail.com $
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
 * Amun_Service_My_SettingsAbstract
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Service_My
 * @version    $Revision: 635 $
 */
abstract class Amun_Service_My_SettingsAbstract extends Amun_Service_My_MyAbstract
{
	public function onLoad()
	{
		parent::onLoad();


		// options
		$settings = new Amun_Option('settings', $this->registry, $this->user, $this->page);
		$settings->add('service_my_view', 'Account', $this->page->url . '/settings');
		$settings->add('service_my_view', 'Security', $this->page->url . '/settings/security');
		$settings->add('service_my_view', 'Contact', $this->page->url . '/settings/contact');
		$settings->add('service_my_view', 'Notification', $this->page->url . '/settings/notification');
		$settings->add('service_my_view', 'Subscription', $this->page->url . '/settings/subscription');
		$settings->add('service_my_view', 'Connection', $this->page->url . '/settings/connection');
		$settings->add('service_my_view', 'Application', $this->page->url . '/settings/application');
		$settings->load(array($this->page));

		$this->template->assign('optionsSettings', $settings);
	}
}

