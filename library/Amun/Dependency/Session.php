<?php
/*
 *  $Id: Default.php 818 2012-08-25 18:52:34Z k42b3.x@googlemail.com $
 *
 * psx
 * A object oriented and modular based PHP framework for developing
 * dynamic web applications. For the current version and informations
 * visit <http://phpsx.org>
 *
 * Copyright (c) 2010-2012 Christoph Kappestein <k42b3.x@gmail.com>
 *
 * This file is part of psx. psx is free software: you can
 * redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or any later version.
 *
 * psx is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with psx. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Amun_Dependency_Default
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://phpsx.org
 * @category   Amun
 * @package    Amun_Dependency
 * @version    $Revision: 818 $
 */
class Amun_Dependency_Session extends Amun_Dependency_Default
{
	protected $sessionName;
	protected $sessionId;
	protected $userId;

	public function __construct(PSX_Config $config, array $params = array())
	{
		$this->sessionName = isset($params['session.name']) ? $params['session.name'] : 'amun_' . md5($config['psx_url']);
		$this->sessionId   = isset($params['session.id']) ? $params['session.id'] : null;
		$this->userId      = isset($params['session.userId']) ? $params['session.userId'] : null;

		parent::__construct($config);
	}

	public function setup()
	{
		parent::setup();

		$this->getSession();
		$this->getUser();
	}

	public function getSession()
	{
		if($this->has('session'))
		{
			return $this->get('session');
		}

		$session = new PSX_Session($this->sessionName);

		if($this->sessionId !== null)
		{
			$session->setId($this->sessionId);
		}

		$session->start();

		return $this->set('session', $session);
	}

	public function getUser()
	{
		if($this->has('user'))
		{
			return $this->get('user');
		}

		if($this->userId === null)
		{
			$this->userId = Amun_User::getId($this->getSession(), $this->getRegistry());
		}

		return $this->set('user', new Amun_User($this->userId, $this->getRegistry()));
	}
}
