<?php
/*
 *  $Id: connect.php 683 2012-06-03 11:52:32Z k42b3.x@googlemail.com $
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

namespace openid\api;

use Amun_Module_RestAbstract;
use Amun_Sql_Table_Registry;
use PSX_Data_Exception;
use PSX_Data_Message;
use PSX_Data_WriterInterface;
use PSX_Data_WriterResult;
use PSX_Sql_Join;

/**
 * connect
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @package    api
 * @subpackage system_connect
 * @version    $Revision: 683 $
 */
class index extends Amun_Module_RestAbstract
{
	public function onPost()
	{
		$msg = new PSX_Data_Message('Create a connect record is not possible', false);

		$this->setResponse($msg, null, 500);
	}

	public function onPut()
	{
		$msg = new PSX_Data_Message('Update a connect record is not possible', false);

		$this->setResponse($msg, null, 500);
	}

	protected function getSelection()
	{
		return $this->getTable()
			->select(array('id', 'userId', 'status', 'claimedId', 'returnTo', 'date'))
			->join(PSX_Sql_Join::INNER, Amun_Sql_Table_Registry::get('User_Account')
				->select(array('name', 'profileUrl'), 'author')
			);
	}

	protected function setWriterConfig(PSX_Data_WriterResult $writer)
	{
		switch($writer->getType())
		{
			case PSX_Data_WriterInterface::ATOM:

				throw new PSX_Data_Exception('Atom not supported');

				break;
		}
	}
}

