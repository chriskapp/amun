<?php
/*
 *  $Id: account.php 743 2012-06-26 19:31:26Z k42b3.x@googlemail.com $
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
 * account
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @package    api
 * @subpackage user_account
 * @version    $Revision: 743 $
 */
class account extends Amun_Module_RestAbstract
{
	protected function getSelection()
	{
		return $this->getTable()
			->select(array('id', 'globalId', 'groupId', 'status', 'name', 'updated', 'profileUrl'))
			->join(PSX_Sql_Join::INNER, Amun_Sql_Table_Registry::get('User_Group')
				->select(array('title'), 'group')
			)
			->join(PSX_Sql_Join::INNER, Amun_Sql_Table_Registry::get('System_Country')
				->select(array('title'), 'country')
			);
	}

	protected function getRestrictedFields()
	{
		return array('pw', 'email', 'token', 'ip');
	}

	public function onPut()
	{
		if($this->user->hasRight($this->ns . '_edit'))
		{
			try
			{
				$handler = $this->getHandler();

				$record = $this->getTable()->getRecord();
				$record->import($this->getRequest());


				// check owner
				if(!$handler->isOwner($record, 'id'))
				{
					throw new PSX_Data_Exception('You are not the owner of the record');
				}


				// check captcha if anonymous
				if($this->user->isAnonymous())
				{
					$captcha = Amun_Captcha::factory($this->config['amun_captcha']);

					if(!$captcha->verify($record->captcha))
					{
						throw new PSX_Data_Exception('Invalid captcha');
					}
				}


				$handler->update($record);


				$msg = new PSX_Data_Message('You have successful edit a ' . $this->getTable()->getDisplayName(), true);

				$this->setResponse($msg);
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

	protected function setWriterConfig(PSX_Data_WriterResult $writer)
	{
		switch($writer->getType())
		{
			case PSX_Data_WriterInterface::ATOM:

				$updated = $this->sql->getField('SELECT `date` FROM ' . $this->registry['table.user_account'] . ' ORDER BY `date` DESC LIMIT 1');

				$title   = 'News';
				$id      = 'urn:uuid:' . $this->base->getUUID('user:account');
				$updated = new DateTime($updated, $this->registry['core.default_timezone']);


				$writer = $writer->getWriter();

				$writer->setConfig($title, $id, $updated);

				$writer->setGenerator('amun ' . Amun_Base::getVersion());

				if(!empty($this->config['amun_hub']))
				{
					$writer->addLink($this->config['amun_hub'], 'hub');
				}

				break;
		}
	}
}
