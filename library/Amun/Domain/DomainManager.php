<?php
/*
 * psx
 * A object oriented and modular based PHP framework for developing
 * dynamic web applications. For the current version and informations
 * visit <http://phpsx.org>
 *
 * Copyright (c) 2010-2013 Christoph Kappestein <k42b3.x@gmail.com>
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

namespace Amun\Domain;

use Amun\Event\ListenerInterface;
use Amun\Event\DispatchableInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * DomainManager
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://phpsx.org
 */
class DomainManager implements DomainManagerInterface
{
	/**
	 * @var Symfony\Component\DependencyInjection\ContainerInterface
	 */
	protected $container;

	protected $_container;

	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}

	public function getDomain($className)
	{
		if(!isset($this->_container[$className]))
		{
			$this->_container[$className] = new $className();

			if($this->_container[$className] instanceof ContainerAwareInterface)
			{
				$this->_container[$className]->setContainer($this->container);
			}

			// if the domain can dispatch events add listener which listen to
			// these events
			if($this->_container[$className] instanceof DispatchableInterface)
			{
				$listeners = $this->getListener($this->_container[$className]);

				foreach($listeners as $listener)
				{
					list($eventName, $callable, $priority) = $listener;

					$this->_container[$className]->addListener($eventName, $callable, $priority);
				}
			}
		}

		return $this->_container[$className];
	}

	/**
	 * Returns an array of all listener classes
	 *
	 * @return array<array<eventName, callable, priority>>
	 */
	public function getListener(DispatchableInterface $domain)
	{
		$listeners = array();
		$events    = $domain->getDispatchableEvents()

		if(!empty($events))
		{
			$con = implode(', ', array_fill(0, count($events), '?'));
			$sql = <<<SQL
SELECT
	`event`.`name`,
	`listener`.`class`,
	`listener`.`priority`
FROM 
	{$this->registry['table.core_event_listener']} `listener`
INNER JOIN 
	{$this->registry['table.core_event']} `event`
	ON `listener`.`eventId` = `event`.`id`
WHERE 
	`event`.`name` IN ({$con})
ORDER BY 
	`listener`.`priority` DESC
SQL;

			$result = $this->sql->getAll($sql, $events);

			foreach($result as $row)
			{
				$listener = $this->getDomain($row['class']);

				if($listener instanceof ListenerInterface)
				{
					$listeners[] = array($row['name'], array($listener, 'notify'), $row['priority']);
				}
			}
		}

		return $listeners;
	}
}
