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

namespace AmunService\My\LoginHandler;

use Amun\Exception;
use PSX\OpenId;
use PSX\Webfinger;
use PSX\Url;

/**
 * AmunService_My_LoginHandler_Webfinger
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Service_My
 * @version    $Revision: 635 $
 */
class Webfinger extends Openid
{
	public function isValid($identity)
	{
		return filter_var($identity, FILTER_VALIDATE_EMAIL) !== false;
	}

	protected function getOpenidProvider($identity)
	{
		$pos      = strpos($identity, '@');
		$provider = substr($identity, $pos + 1);

		// we check whether the email provider is an known openid porivder
		// make webfinger request

		// @todo we should probably add here an request cache for 
		// the lrdd template
		$webfinger = new Webfinger($this->http);
		$url       = new Url('http://' . $provider);
		$template  = $webfinger->getLrddTemplate($url);

		// get acct xrd
		$acct      = 'acct:' . $identity;
		$xrd       = $webfinger->getLrdd($acct, $template);

		// check subject
		if(strcmp($xrd->getSubject(), $acct) !== 0)
		{
			throw new Exception('Invalid subject');
		}

		// find openid profile url
		$profileUrl = $xrd->getLinkHref('http://specs.openid.net/auth/2.0/provider');

		if(!empty($profileUrl))
		{
			// initalize openid
			$openid = new OpenId($this->http, $this->config['psx_url'], $this->store);
			$openid->initialize($profileUrl, $callback);

			return $openid;
		}

		return false;
	}
}
