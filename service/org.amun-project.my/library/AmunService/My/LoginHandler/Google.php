<?php
/*
 *  $Id: FriendsAbstract.php 635 2012-05-01 19:46:37Z k42b3.x@googlemail.com $
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
 * AmunService_My_LoginHandler_Google
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Service_My
 * @version    $Revision: 635 $
 */
class AmunService_My_LoginHandler_Google extends AmunService_My_LoginHandler_Openid
{
	public function isValid($identity)
	{
		return filter_var($identity, FILTER_VALIDATE_EMAIL) !== false && (strpos($identity, '@googlemail.com') !== false || strpos($identity, '@gmail.com') !== false);
	}

	protected function getOpenidProvider($identity)
	{
		// build callback
		$callback = $this->pageUrl . '/login/callback/openid';

		$openid = new PSX_OpenId_Op_Google($this->http, $this->config['psx_url'], $this->store);
		$openid->initialize($identity, $callback);

		return $openid;
	}
}
