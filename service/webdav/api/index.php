<?php
/*
 *  $Id: index.php 875 2012-09-30 13:51:45Z k42b3.x@googlemail.com $
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
 * index
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @package    api
 * @subpackage service_webdav
 * @version    $Revision: 875 $
 */
class index extends Amun_Module_DefaultAbstract
{
	public function onLoad()
	{
		$auth = new Sabre_HTTP_BasicAuth();

		list($username, $pw) = $auth->getUserPass();

		$identity = sha1(Amun_Security::getSalt() . $username);

		if(!empty($pw))
		{
			$row = Amun_Sql_Table_Registry::get('User_Account')
				->select(array('id', 'status', 'pw'))
				->where('identity', '=', $identity)
				->getRow();

			if(!empty($row))
			{
				if($row['pw'] == sha1(Amun_Security::getSalt() . $pw))
				{
					if($this->isValidStatus($row['status']) === true)
					{
						$this->base->setUser($row['id']);

						$root = new Amun_Service_Webdav_Page(1);
						$url  = new PSX_Url($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/service/webdav');

						$server = new Sabre_DAV_Server($root);
						$server->setBaseUri($url->getPath());

						$server->exec();

						exit;
					}
				}
			}
		}

		$auth->requireLogin();

		echo 'Authentication required' . "\n";
		exit;
	}

	private function isValidStatus($status)
	{
		switch($status)
		{
			case Amun_User_Account::NORMAL:
			case Amun_User_Account::ADMINISTRATOR:

				return true;

				break;
		}

		return false;
	}
}
