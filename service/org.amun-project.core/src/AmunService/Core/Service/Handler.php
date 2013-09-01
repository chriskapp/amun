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

namespace AmunService\Core\Service;

use Amun\Base;
use Amun\Composer\LoggerIO;
use Amun\Composer\XmlFile;
use Amun\Data\HandlerAbstract;
use Amun\Data\RecordAbstract;
use Amun\Exception;
use Amun\SetupAbstract;
use Amun\Setup\VoidSetup;
use AmunService\Core\Service;
use Composer\Factory;
use Composer\Config;
use Composer\Installer;
use Composer\Json\JsonFile;
use Composer\Package\Version\VersionParser;
use DOMDocument;
use DOMElement;
use DOMNodeList;
use DOMNode;
use PharData;
use PSX\DateTime;
use PSX\Data\RecordInterface;
use PSX\Url;
use PSX\Sql;
use PSX\Sql\Condition;
use PSX\Sql\Join;
use ReflectionClass;

/**
 * Handler
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Handler extends HandlerAbstract
{
	protected $logger;
	protected $serviceId;
	protected $serviceConfig;

	protected $ids = array();

	/**
	 * Installs a new service. The config value of the record must be a path to
	 * an config.xml. This method is also called by composer when install or 
	 * update a new service
	 *
	 * @param PSX\Data\RecordInterface $record
	 */
	public function create(RecordInterface $record)
	{
		if($record->hasFields('source', 'config', 'name'))
		{
			// already installed
			if($this->registry->hasService($record->name))
			{
				throw new Exception('Service already installed');
			}

			// set logger
			$this->logger = $this->container->get('logger');
			$this->logger->info('Start installation of service ' . $record->source);

			// check extension
			if(is_file($record->config))
			{
				$this->serviceConfig = new DOMDocument();
				$this->serviceConfig->load($record->config, LIBXML_NOBLANKS);

				$this->parseMeta($record);
			}
			else
			{
				throw new Exception('Invalid config');
			}


			// get setup class
			$setup = $this->getSetup($record);

			// call pre install class
			try
			{
				$setup->preInstall($record);
			}
			catch(\Exception $e)
			{
				$this->logger->error($e->getMessage());
			}


			$date = new DateTime('NOW', $this->registry['core.default_timezone']);

			$record->date = $date->format(DateTime::SQL);


			$this->table->insert($record->getData());


			$record->id = $this->sql->getLastInsertId();


			// insert events
			$this->parseEvents($record);

			// parse registry
			$this->parseRegistry($record);

			// execute queries
			$this->parseDatabase($record);

			// notify listener
			$this->event->notifyListener('core.service_install', array($record, $this->serviceConfig, $this->logger));


			$this->notify(RecordAbstract::INSERT, $record);


			// call post install class
			try
			{
				$setup->postInstall($record);
			}
			catch(\Exception $e)
			{
				$this->logger->error($e->getMessage());
			}


			return $record;
		}
		else
		{
			throw new Exception('Missing field in record');
		}
	}

	public function update(RecordInterface $record)
	{
		throw new Exception('Update a service record is not possible');
	}

	public function delete(RecordInterface $record)
	{
		if($record->hasFields('id'))
		{
			$con = new Condition(array('serviceId', '=', $record->id));


			// check whether page exists wich uses this service
			if($this->sql->count($this->registry['table.content_page'], $con) > 0)
			{
				throw new Exception('Page exists wich uses this service');
			}


			// check whether services exist wich depend on this service
			// @todo


			$con = new Condition(array('id', '=', $record->id));

			$this->table->delete($con);


			$this->notify(RecordAbstract::DELETE, $record);


			return $record;
		}
		else
		{
			throw new Exception('Missing field in record');
		}
	}

	protected function getDefaultSelect()
	{
		return $this->table
			->select(array('id', 'status', 'name', 'type', 'link', 'author', 'license', 'version', 'date'));
	}

	private function getSetup(Service\Record $record)
	{
		$className = '\\' . $record->namespace . '\Setup';

		if(class_exists($className))
		{
			$setup = new $className($this->container);

			if($setup instanceof SetupAbstract)
			{
				return $setup;
			}
		}

		return new VoidSetup($this->container);
	}

	private function parseMeta(Service\Record $record)
	{
		$rootElement = $this->serviceConfig->documentElement;

		// get meta data
		$fields = array('status', 'path', 'namespace', 'type');

		for($i = 0; $i < $rootElement->childNodes->length; $i++)
		{
			$node = $rootElement->childNodes->item($i);

			if($node instanceof DOMElement)
			{
				if(in_array($node->nodeName, $fields))
				{
					$method = 'set' . ucfirst($node->nodeName);

					$record->$method($node->nodeValue);
				}
			}
		}

		$diff = array_diff($fields, array_keys($record->getData()));

		if(count($diff) > 0)
		{
			throw new Exception('Missing fields: ' . implode(', ', $diff));
		}
	}

	private function parseEvents(Service\Record $record)
	{
		$event = $this->serviceConfig->getElementsByTagName('event')->item(0);

		if($event !== null)
		{
			$this->logger->info('Create events');

			$events = $event->childNodes;

			for($i = 0; $i < $events->length; $i++)
			{
				try
				{
					$event = $events->item($i);

					if(!($event instanceof DOMElement))
					{
						continue;
					}

					if($event->nodeName == 'publisher')
					{
						$name        = $event->getAttribute('name');
						$interface   = $event->getAttribute('interface');
						$description = $event->getAttribute('description');

						if(!empty($name))
						{
							$interface = empty($interface) ? null : $interface;

							$this->sql->insert($this->registry['table.core_event'], array(
								'name'        => $name,
								'interface'   => $interface,
								'description' => $description,
							));

							$this->logger->info('> Created publisher event "' . $name . '"');
						}
					}

					if($event->nodeName == 'listener')
					{
						$name     = $event->getAttribute('name');
						$priority = (integer) $event->getAttribute('priority');
						$class    = $event->getAttribute('class');

						if(!empty($class))
						{
							$class = new ReflectionClass($class);
						}
						else
						{
							throw new Exception('Empty listener event class');
						}

						if(!empty($name))
						{
							$con     = new Condition(array('name', '=', $name));
							$eventId = $this->sql->select($this->registry['table.core_event'], array('id'), $con, Sql::SELECT_FIELD);

							if(!empty($eventId))
							{
								$this->sql->insert($this->registry['table.core_event_listener'], array(
									'eventId'  => $eventId,
									'priority' => $priority,
									'class'    => $class->getName(),
								));

								$this->logger->info('> Added event listener "' . $name . '" to event ' . $eventId);
							}
							else
							{
								throw new Exception('Unknown listener event name');
							}
						}
						else
						{
							throw new Exception('Empty listener event name');
						}
					}
				}
				catch(\Exception $e)
				{
					$this->logger->error($e->getMessage());
				}
			}
		}
	}

	private function parseRegistry(Service\Record $record)
	{
		$registry = $this->serviceConfig->getElementsByTagName('registry')->item(0);

		if($registry !== null)
		{
			$this->logger->info('Create registry entries');

			$params = $registry->childNodes;

			for($i = 0; $i < $params->length; $i++)
			{
				try
				{
					$param = $params->item($i);

					if(!($param instanceof DOMElement))
					{
						continue;
					}

					if($param->nodeName == 'param')
					{
						$name  = $param->getAttribute('name');
						$value = $param->getAttribute('value');
						$type  = $param->getAttribute('type');
						$class = $param->getAttribute('class');

						if(empty($name))
						{
							throw new Exception('Empty param name');
						}

						$name = $record->getShortName() . '.' . $name;

						if(empty($type))
						{
							$type = 'STRING';
						}

						if(empty($class))
						{
							$class = null;
						}

						$this->sql->insert($this->registry['table.core_registry'], array(
							'name'  => $name,
							'value' => $value,
							'type'  => $type,
							'class' => $class,
						));

						$this->logger->info('> Created registry entry "' . $name . '" = "' . $value . '"');
					}
					else if($param->nodeName == 'table')
					{
						$name  = $param->getAttribute('name');
						$value = $param->getAttribute('value');

						if(empty($name))
						{
							throw new Exception('Empty table name');
						}

						$value = empty($value) ? $name : $value;
						$value = $this->config['amun_table_prefix'] . $value;
						$name  = 'table.' . $name;

						$this->sql->insert($this->registry['table.core_registry'], array(
							'name'  => $name,
							'value' => $value,
							'type'  => 'STRING',
						));

						$this->logger->info('> Created registry entry "' . $name . '" = "' . $value . '"');
					}
				}
				catch(\Exception $e)
				{
					$this->logger->error($e->getMessage());
				}
			}

			// reload registry
			$this->registry->load();
		}
	}

	private function parseDatabase(Service\Record $record)
	{
		$database = $this->serviceConfig->getElementsByTagName('database')->item(0);

		if($database !== null)
		{
			$this->logger->info('Execute sql queries');

			try
			{
				$this->parseQueries($database->childNodes, $record);
			}
			catch(\Exception $e)
			{
				$this->logger->error($e->getMessage());
			}
		}
	}

	private function parseQueries(DOMNodeList $queries, Service\Record $record)
	{
		for($i = 0; $i < $queries->length; $i++)
		{
			$query = $queries->item($i);

			if(!($query instanceof DOMElement))
			{
				continue;
			}

			if($query->nodeName == 'query')
			{
				$sql = $this->substituteVars($query->nodeValue, $record);

				$this->logger->info('> ' . $sql);

				$this->sql->query($sql);

				if($query->hasAttribute('storeID'))
				{
					$this->ids[$query->getAttribute('storeID')] = $this->sql->getLastInsertId();
				}
			}
			else if($query->nodeName == 'if')
			{
				if($query->hasAttribute('hasService'))
				{
					if($this->opHasService($query->getAttribute('hasService')))
					{
						$this->parseQueries($query->childNodes, $record);
					}
				}

				if($query->hasAttribute('hasVersion'))
				{
					if($this->isVersion($query->getAttribute('hasVersion')))
					{
						$this->parseQueries($query->childNodes, $record);
					}
				}

				if($query->hasAttribute('hasMinVersion'))
				{
					if($this->isMinVersion($query->getAttribute('hasMinVersion')))
					{
						$this->parseQueries($query->childNodes, $record);
					}
				}

				if($query->hasAttribute('hasMaxVersion'))
				{
					if($this->isMaxVersion($query->getAttribute('hasMaxVersion')))
					{
						$this->parseQueries($query->childNodes, $record);
					}
				}
			}
		}
	}

	private function opHasService($value)
	{
		$services = explode(',', $value);

		foreach($services as $service)
		{
			if(!$this->registry->hasService($service))
			{
				return false;
			}
		}

		return true;
	}

	private function isVersion($value)
	{
		$a = VersionParser::normalize(Base::getVersion());
		$b = VersionParser::normalize($value);

		return version_compare($a, $b, '==');
	}

	private function isMinVersion($value)
	{
		$a = VersionParser::normalize(Base::getVersion());
		$b = VersionParser::normalize($value);

		return version_compare($a, $b, '>=');
	}

	private function isMaxVersion($value)
	{
		$a = VersionParser::normalize(Base::getVersion());
		$b = VersionParser::normalize($value);

		return version_compare($a, $b, '<=');
	}

	private function substituteVars($sql, Service\Record $record)
	{
		// tables
		$result = $this->sql->getAll('SELECT name, value FROM ' . $this->registry['table.core_registry'] . ' WHERE name LIKE "table.%"');

		foreach($result as $row)
		{
			$sql = str_replace('{' . $row['name'] . '}', $row['value'], $sql);
		}

		// service
		$data = $record->getData();

		foreach($data as $k => $v)
		{
			$sql = str_replace('{service.' . $k . '}', $v, $sql);
		}

		// ids
		foreach($this->ids as $k => $v)
		{
			$sql = str_replace('{id.' . $k . '}', $v, $sql);
		}

		// config
		$data = array(
			'host'         => $this->base->getHost(),
			'table_prefix' => $this->config['amun_table_prefix'],
		);

		foreach($data as $k => $v)
		{
			$sql = str_replace('{config.' . $k . '}', $v, $sql);
		}

		return $sql;
	}
}

