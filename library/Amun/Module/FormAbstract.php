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
	protected $method;
	protected $form;

	/**
	 * @httpMethod GET
	 * @path /
	 * @nickname getForm
	 * @responseClass Amun_Form
	 */
	public function getForm()
	{
		if($this->getProvider()->hasViewRight())
		{
			try
			{
				$this->method = $this->get->method('string');
				$this->form   = $this->getProvider()->getForm();

				if($this->form === null)
				{
					throw new Amun_Exception('Form class not available'); 
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
}

