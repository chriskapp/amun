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
 * AmunService_My_LoginHandlerAbstract
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Service_My
 * @version    $Revision: 635 $
 */
abstract class AmunService_My_LoginHandlerAbstract
{
	protected $base;
	protected $config;
	protected $sql;
	protected $session;
	protected $registry;
	protected $event;
	protected $user;

	protected $pageUrl;

	public function __construct()
	{
		$ct = Amun_DataFactory::getContainer();

		$this->base     = $ct->getBase();
		$this->config   = $ct->getConfig();
		$this->sql      = $ct->getSql();
		$this->session  = $ct->getSession();
		$this->registry = $ct->getRegistry();
		$this->event    = $ct->getEvent();
		$this->user     = $ct->getUser();
	}

	/**
	 * Sets the page url to determine the callback url for some handlers
	 *
	 * @param string $pageUrl
	 * @return void
	 */
	public function setPageUrl($pageUrl)
	{
		$this->pageUrl = $pageUrl;
	}

	/**
	 * Sets the user id in the sessions and thus logs the user in to the 
	 * website. This method must be only called if the user is authenticated 
	 *
	 * @param integer $userId
	 * @return void
	 */
	protected function setUserId($userId)
	{
		// set user id
		$this->session->set('amun_id', $userId);
		$this->session->set('amun_t', time());
	}

	/**
	 * We receive an name from an provider the name is handled as an untrsuted 
	 * value because of that we go through each sign and check whether it is 
	 * valid. The method returns an valid user name
	 *
	 * @param string $name
	 * @return string
	 */
	protected function normalizeName($name)
	{
		$norm = '';
		$len  = strlen($name);

		// replace white space with period
		$name = str_replace(' ', '.', $name);

		// name can only contain A-Z a-z 0-9 .
		$period = false;

		for($i = 0; $i < $len; $i++)
		{
			$ascii = ord($name[$i]);

			# alpha (A - Z / a - z / 0 - 9 / .)
			if(($ascii >= 0x41 && $ascii <= 0x5A) || ($ascii >= 0x61 && $ascii <= 0x7A) || ($ascii >= 0x30 && $ascii <= 0x39))
			{
				$norm.= $name[$i];
			}

			if($period === false && $ascii == 0x2E)
			{
				$norm.= $name[$i];

				$period = true;
			}
		}

		return $norm;
	}

	/**
	 * Returns whether the identity has an valid format for the handler. The 
	 * handle method is only called if this method returns true
	 *
	 * @param string $identity
	 * @return boolean
	 */
	abstract public function isValid($identity);

	/**
	 * Indicates whether the handler need an password to authenticate the user
	 *
	 * @return boolean
	 */
	abstract public function hasPassword();

	/**
	 * Returns whether the $identity and $password are valid
	 *
	 * @param string $identity
	 * @param string $password
	 * @return boolean
	 */
	abstract public function handle($identity, $password);
}
