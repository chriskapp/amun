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

namespace Amun;

use Amun\Data\ListenerAbstract;
use DOMDocument;
use PSX\Data\RecordInterface;
use PSX\Sql;
use PSX\Sql\Condition;

/**
 * SetupAbstract
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
abstract class SetupAbstract
{
	protected $container;
	protected $config;
	protected $sql;
	protected $registry;
	protected $event;
	protected $logger;

	public function __construct($container)
	{
		$this->container = $container;
		$this->config    = $container->get('config');
		$this->sql       = $container->get('sql');
		$this->registry  = $container->get('registry');
		$this->event     = $container->get('event');
		$this->logger    = $container->get('logger');
	}

	/**
	 * Method wich executes for every service wich was installed before $record
	 * a specific core.service_install listener. Note this method can only 
	 * handle core.service_install listener. Should be called if a service 
	 * registeres a new core.service_install listener
	 *
	 * @param PSX\Data\RecordInterface $record
	 * @param Amun\Data\ListenerAbstract $listener
	 */
	protected function notifyInstalledServiceInstallListener(RecordInterface $record, ListenerAbstract $listener)
	{
		$handler = $this->container->get('handlerManager')->getHandler('AmunService\Core\Service');
		$con     = new Condition(array('id', '<', $record->id));
		$fields  = array('id', 'status', 'source', 'config', 'name', 'path', 'namespace', 'type');
		$result  = $handler->getAll($fields, 0, 16, null, null, $con, Sql::FETCH_OBJECT);

		foreach($result as $serviceRecord)
		{
			if(is_file($serviceRecord->config))
			{
				$config = new DOMDocument();
				$config->load($serviceRecord->config, LIBXML_NOBLANKS);

				$listener->notify($serviceRecord, $config, $this->logger);
			}
		}
	}

	abstract public function preInstall(RecordInterface $record);
	abstract public function postInstall(RecordInterface $record);
}

