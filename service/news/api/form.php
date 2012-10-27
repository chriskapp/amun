<?php
/*
 *  $Id: form.php 875 2012-09-30 13:51:45Z k42b3.x@googlemail.com $
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
 * form
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @package    api
 * @subpackage service_page
 * @version    $Revision: 875 $
 */
class form extends Amun_Module_FormAbstract
{
	public function onGet()
	{
		if($this->user->hasRight('service_news_view'))
		{
			try
			{
				$method  = $this->get->method('string');
				$builder = $this->getForm();

				switch($method)
				{
					case 'create':

						$form = $builder->create($this->get->pageId('integer'));

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
}
