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
class Amun_Dependency_Default extends PSX_DependencyAbstract
{
	protected function setup()
	{
		parent::setup();

		// sql
		if(!$this->registry->offsetExists('sql'))
		{
			$this->registry->offsetSet('sql', $this->base->getSql());
		}

		// registry
		if(!$this->registry->offsetExists('registry'))
		{
			$this->registry->offsetSet('registry', $this->base->getRegistry());
		}

		// table registry
		if(!$this->registry->offsetExists('tableRegistry'))
		{
			$this->registry->offsetSet('tableRegistry', $this->base->getTableRegistry());
		}

		// validate
		if(!$this->registry->offsetExists('validate'))
		{
			$validate = new PSX_Validate();
			$this->registry->offsetSet('validate', $validate);
		}

		// get
		if(!$this->registry->offsetExists('get'))
		{
			$get = new PSX_Input_Get($this->registry->offsetGet('validate'));
			$this->registry->offsetSet('get', $get);
		}

		// post
		if(!$this->registry->offsetExists('post'))
		{
			$post = new PSX_Input_Post($this->registry->offsetGet('validate'));
			$this->registry->offsetSet('post', $post);
		}
	}

	public function getParameters()
	{
		return array_merge(parent::getParameters(), array(
			'sql' => $this->registry->offsetGet('sql'),
			'registry' => $this->registry->offsetGet('registry'),
			'tableRegistry' => $this->registry->offsetGet('tableRegistry'),
			'validate' => $this->registry->offsetGet('validate'),
			'get' => $this->registry->offsetGet('get'),
			'post' => $this->registry->offsetGet('post'),
		));
	}
}

