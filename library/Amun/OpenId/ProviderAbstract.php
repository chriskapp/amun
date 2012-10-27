<?php
/*
 *  $Id: ProviderAbstract.php 818 2012-08-25 18:52:34Z k42b3.x@googlemail.com $
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
 * Amun_OpenId_ProviderAbstract
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Captcha
 * @version    $Revision: 818 $
 */
abstract class Amun_OpenId_ProviderAbstract extends PSX_OpenId_ProviderAbstract
{
	protected $session;
	protected $user;

	public function __construct(PSX_Base $base, $basePath, array $uriFragments)
	{
		parent::__construct($base, $basePath, $uriFragments);

		$this->session = new PSX_Session('amun_' . md5($this->config['psx_url']));
		$this->session->start();

		$this->user = new Amun_User(Amun_User::getId($this->session, $this->registry), $this->registry);
	}

	public function getDependencies()
	{
		return new Amun_Dependency_Default();
	}
}
