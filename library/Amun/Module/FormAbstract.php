<?php
/*
 * amun
 * A social content managment system based on the psx framework. For
 * the current version and informations visit <http://amun.phpsx.org>
 *
 * Copyright (c) 2010-2013 Christoph Kappestein <k42b3.x@gmail.com>
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

namespace Amun\Module;

use Amun\Module\ApiAbstract;
use Amun\Exception;
use PSX\Data\Message;

/**
 * FormAbstract
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
abstract class FormAbstract extends ApiAbstract
{
	protected $method;
	protected $form;

	/**
	 * Returns an form to create, update or delete an record
	 *
	 * @httpMethod GET
	 * @path /
	 * @nickname doForm
	 * @parameter query method string create|update|delete
	 * @parameter [query id integer]
	 * @responseClass Amun_Form
	 */
	public function doForm()
	{
		if($this->user->hasRight($this->service->getNamespace() . '_view'))
		{
			try
			{
				$this->method = $this->getInputGet()->method('string');
				$this->form   = $this->getForm();

				if($this->form === null)
				{
					throw new Exception('Form class not available'); 
				}

				switch($this->method)
				{
					case 'create':
						$form = $this->getCreateForm();
						break;

					case 'update':
						$form = $this->getUpdateForm();
						break;

					case 'delete':
						$form = $this->getDeleteForm();
						break;

					default:
						throw new Exception('Invalid method');
						break;
				}

				$this->setResponse($form);
			}
			catch(\Exception $e)
			{
				$msg = new Message($e->getMessage(), false);

				$this->setResponse($msg);
			}
		}
		else
		{
			$msg = new Message('Access not allowed', false);

			$this->setResponse($msg, null, $this->user->isAnonymous() ? 401 : 403);
		}
	}

	protected function getCreateForm()
	{
		return $this->form->create();
	}

	protected function getUpdateForm()
	{
		return $this->form->update($this->get->id('integer'));
	}

	protected function getDeleteForm()
	{
		return $this->form->delete($this->get->id('integer'));
	}

	protected function getForm($table = null)
	{
		return $this->dataFactory->getFormInstance($table === null ? $this->service->namespace : $table);
	}
}
