<?php
/*
 *  $Id: FormAbstract.php 835 2012-08-26 21:37:35Z k42b3.x@googlemail.com $
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
 * Amun_Module_FormAbstract
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Module
 * @version    $Revision: 835 $
 */
abstract class Amun_Module_FormAbstract extends Amun_Module_ApiAbstract
{
	public function onLoad()
	{
		// determine namespace
		$this->ns = strtolower(str_replace('/', '_', substr($this->basePath, 4, -5)));
	}

	public function onGet()
	{
		if($this->user->hasRight($this->ns . '_view'))
		{
			try
			{
				$method  = $this->get->method('string');
				$builder = $this->getForm();

				switch($method)
				{
					case 'create':

						$form = $builder->create();

						break;

					case 'update':

						$form = $builder->update($this->get->id('integer'));

						break;

					case 'delete':

						$form = $builder->delete($this->get->id('integer'));

						break;

					default:

						throw new Amun_Exception('Invalid method');

						break;
				}

				$this->setResponse($form);
			}
			catch(Exception $e)
			{
				$msg = new PSX_Data_Message($e->getMessage(), false);

				$this->setResponse($msg);
			}
		}
		else
		{
			$msg = new PSX_Data_Message('Access not allowed', false);

			$this->setResponse($msg, null, $this->user->isAnonymous() ? 401 : 403);
		}
	}

	protected function getForm()
	{
		$name = $this->registry->getTableName($this->ns);

		if($name !== false)
		{
			$class = Amun_Registry::getClassName($name . '_Form');

			if(class_exists($class))
			{
				$url = $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . substr($this->basePath, 0, -5);

				return new $class($url);
			}
			else
			{
				throw new PSX_Data_Exception('Form class "' . $class . '" does not exist');
			}
		}
		else
		{
			throw new PSX_Data_Exception('Invalid "' . $this->ns . '" form');
		}
	}
}

