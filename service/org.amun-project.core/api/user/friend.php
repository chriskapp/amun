<?php
/*
 *  $Id: friend.php 743 2012-06-26 19:31:26Z k42b3.x@googlemail.com $
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

namespace core\api\user;

use AmunService_Core_User_Friend_Record;
use Amun_Base;
use Amun_Module_RestAbstract;
use Amun_Sql_Table_Registry;
use DateTime;
use PSX_Data_WriterInterface;
use PSX_Data_WriterResult;
use PSX_Sql_Join;

/**
 * friend
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @package    admin
 * @subpackage user_friend
 * @version    $Revision: 743 $
 */
class friend extends Amun_Module_RestAbstract
{
	protected function getSelection()
	{
		return $this->getTable()
			->select(array('id', 'status', 'date'))
			->join(PSX_Sql_Join::INNER, Amun_Sql_Table_Registry::get('Core_User_Account')
				->select(array('id', 'globalId', 'name', 'profileUrl'), 'author'),
				'n:1',
				'userId'
			)
			->join(PSX_Sql_Join::INNER, Amun_Sql_Table_Registry::get('Core_User_Account')
				->select(array('id', 'globalId', 'name', 'profileUrl', 'thumbnailUrl'), 'friend'),
				'n:1',
				'friendId'
			)
			->where('status', '=', AmunService_Core_User_Friend_Record::NORMAL);
	}

	protected function getProvider()
	{
		return $this->getDataProvider('Core_User_Friend');
	}

	protected function setWriterConfig(PSX_Data_WriterResult $writer)
	{
		switch($writer->getType())
		{
			case PSX_Data_WriterInterface::ATOM:

				$updated = $this->sql->getField('SELECT `date` FROM ' . $this->registry['table.core_user_friend'] . ' ORDER BY `date` DESC LIMIT 1');

				$title   = 'Friend';
				$id      = 'urn:uuid:' . $this->base->getUUID('user:friend');
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
