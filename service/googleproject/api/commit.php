<?php
/*
 *  $Id: commit.php 875 2012-09-30 13:51:45Z k42b3.x@googlemail.com $
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
 * commit
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @package    admin
 * @subpackage service_googleproject
 * @version    $Revision: 875 $
 */
class commit extends Amun_Module_RestAbstract
{
	protected function getSelection()
	{
		return $this->getTable()
			->select(array('id', 'globalId', 'revision', 'url', 'message', 'commitDate', 'date'))
			->join(PSX_Sql_Join::INNER, Amun_Sql_Table_Registry::get('Service_Googleproject_Author')
				->select(array('id', 'name'), 'author')
			);
	}

	public function onPost()
	{
		$msg = new PSX_Data_Message('Create a commit record is not possible', false);

		$this->setResponse($msg, null, 500);
	}

	public function onPut()
	{
		$msg = new PSX_Data_Message('Update a commit record is not possible', false);

		$this->setResponse($msg, null, 500);
	}

	public function onDelete()
	{
		$msg = new PSX_Data_Message('Delete a commit record is not possible', false);

		$this->setResponse($msg, null, 500);
	}

	protected function setWriterConfig(PSX_Data_WriterResult $writer)
	{
		switch($writer->getType())
		{
			case PSX_Data_WriterInterface::ATOM:

				$updated = $this->sql->getField('SELECT `date` FROM ' . $this->registry['table.service_googleproject_commit'] . ' ORDER BY `date` DESC LIMIT 1');

				$title   = 'Commit';
				$id      = 'urn:uuid:' . $this->base->getUUID('service:googleproject:commit');
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

