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
use Amun\DataFactory;
use Amun\Data\HandlerAbstract;
use Amun\Data\RecordAbstract;
use Amun\Exception;
use AmunService\Core\Approval;
use AmunService\Core\Service;
use PSX\DateTime;
use PSX\Data\RecordInterface;
use PSX\Url;
use PSX\Sql;
use PSX\Sql\Condition;
use PSX\Sql\Join;
use PSX\Log;
use PharData;
use DOMDocument;
use DOMElement;
use DOMNodeList;
use DOMNode;
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
	const SECRET = 'd6b0c93c6f9b0a7917fdb402298ac692bf25fab8';

	private $serviceId;
	private $serviceConfig;

	private $ids = array();

	public function create(RecordInterface $record)
	{
		if($record->hasFields('source'))
		{
			// already installed
			if($this->registry->hasService($record->source))
			{
				throw new Exception('Service already installed');
			}


			// set logger if in debug mode
			if($this->config['psx_debug'] === true)
			{
				Log::getLogger()->setHandler(new Log\Handler\File(PSX_PATH_CACHE . '/log.txt'));
				Log::getLogger()->setLevel(Log::ALL);

				Log::info('Start installation of service ' . $record->source);
			}


			// check whether phar or folder installation
			$phar = $this->config['amun_service_path'] . '/' . $record->source;

			if(is_file($phar))
			{
				// parse config
				$phar      = new PharData($phar);
				$configXml = $phar->getMetadata();

				if(!empty($configXml))
				{
					$this->serviceConfig = new DOMDocument();
					$this->serviceConfig->loadXML($configXml, LIBXML_NOBLANKS);

					$this->parseMeta($record);
				}
				else
				{
					throw new Exception('Found no config');
				}

				// copy files
				$this->parsePharFiles($record);
			}
			else
			{
				// parse config
				$configFile = $this->config['amun_service_path'] . '/' . $record->source . '/config.xml';

				if(is_file($configFile))
				{
					$this->serviceConfig = new DOMDocument();
					$this->serviceConfig->load($configFile, LIBXML_NOBLANKS);

					$this->parseMeta($record);
				}
				else
				{
					throw new Exception('Found no config.xml');
				}

				// copy files
				$this->parseFolderFiles($record);
			}


			// check config fields
			if(!$record->hasFields('status', 'name', 'path', 'namespace', 'type', 'link', 'author', 'license', 'version'))
			{
				throw new Exception('Missing fields in config');
			}


			// get provider id
			$record->providerId = $this->getProviderId();


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
			$this->event->notifyListener('core.service_install', array($record, $this->serviceConfig));


			$this->notify(RecordAbstract::INSERT, $record);


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


			// delete options
			$this->sql->delete($this->registry['table.core_service_option'], $con);


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

	private function parseMeta(Service\Record $record)
	{
		$rootElement = $this->serviceConfig->documentElement;


		// validate signature
		$foreignSignature = $rootElement->getAttribute('signature');

		if(empty($foreignSignature))
		{
			throw new Exception('No signature given');
		}

		$rootElement->setAttribute('signature', '');

		$this->serviceConfig->preserveWhiteSpace = false;
		$this->serviceConfig->formatOutput = true;

		$signature = hash_hmac('sha1', $this->serviceConfig->saveXML(), self::SECRET);

		if(strcmp($signature, $foreignSignature) !== 0)
		{
			throw new Exception('Invalid configuration signature');
		}


		// get meta data
		$fields = array('status', 'name', 'path', 'namespace', 'type', 'link', 'author', 'license', 'version');

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


		// check requirements
		$required = $this->serviceConfig->getElementsByTagName('required')->item(0);

		if($required !== null)
		{
			$services = array();

			for($i = 0; $i < $required->childNodes->length; $i++)
			{
				$node = $required->childNodes->item($i);

				if($node instanceof DOMElement && $node->nodeName == 'service')
				{
					$services[] = $node->nodeValue;
				}
			}

			foreach($services as $source)
			{
				if(!$this->registry->hasService($source))
				{
					// try to install required services
					$service = $this->hm->getTable('Core_Service')->getRecord();
					$service->setSource($source);

					$this->create($service);
				}
			}
		}
	}

	private function getProviderId()
	{
		$rootElement = $this->serviceConfig->documentElement;
		$providerId  = 0;

		for($i = 0; $i < $rootElement->childNodes->length; $i++)
		{
			$node = $rootElement->childNodes->item($i);

			if($node instanceof DOMElement && $node->nodeName == 'provider')
			{
				$src = $node->nodeValue;

				Log::info('Found provider ' . $src);

				try
				{
					$url = new Url($src);
					$con = new Condition(array('url', '=', $src));
					$id  = $this->hm->getTable('Core_Service_Provider')->getField('id', $con);

					if(empty($id))
					{
						$this->hm->getTable('Core_Service_Provider')->insert(array(
							'url'  => $src,
							'date' => date(DateTime::SQL),
						));

						$providerId = $this->sql->getLastInsertId();

						Log::info('Added new provider ' . $src . ' (' . $providerId . ')');
					}
					else
					{
						$providerId = $id;
					}

					break;
				}
				catch(\Exception $e)
				{
					Log::error($e->getMessage());
				}
			}
		}

		return $providerId;
	}

	private function parsePharFiles(Service\Record $record)
	{
		// library
		$library = $this->serviceConfig->getElementsByTagName('library');

		if($library->length > 0)
		{
			if(is_dir(PSX_PATH_LIBRARY))
			{
				Log::info('Copy library files');

				$this->copyFiles('phar://' . $this->config['amun_service_path'] . '/' . $record->source . '/library', PSX_PATH_LIBRARY, $library->item(0));
			}
			else
			{
				Log::info('Library path is not an folder');
			}
		}
	}

	private function parseFolderFiles(Service\Record $record)
	{
		// library
		$library = $this->serviceConfig->getElementsByTagName('library');

		if($library->length > 0)
		{
			if(is_dir(PSX_PATH_LIBRARY))
			{
				$srcDir = $this->config['amun_service_path'] . '/' . $record->source . '/library';

				if(is_dir($srcDir))
				{
					Log::info('Copy library files');

					$this->copyFiles($srcDir, PSX_PATH_LIBRARY, $library->item(0));
				}
			}
			else
			{
				Log::info('Library path is not an folder');
			}
		}
	}

	private function parseEvents(Service\Record $record)
	{
		$event = $this->serviceConfig->getElementsByTagName('event')->item(0);

		if($event !== null)
		{
			Log::info('Create events');

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

							Log::info('> Created publisher event "' . $name . '"');
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

								Log::info('> Added event listener "' . $name . '" to event ' . $eventId);
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
					Log::error($e->getMessage());
				}
			}
		}
	}

	private function parseRegistry(Service\Record $record)
	{
		$registry = $this->serviceConfig->getElementsByTagName('registry')->item(0);

		if($registry !== null)
		{
			Log::info('Create registry entries');

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

						$name = $record->namespace . '.' . $name;

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

						Log::info('> Created registry entry "' . $name . '" = "' . $value . '"');
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

						Log::info('> Created registry entry "' . $name . '" = "' . $value . '"');
					}
				}
				catch(\Exception $e)
				{
					Log::error($e->getMessage());
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
			Log::info('Execute sql queries');

			try
			{
				$this->parseQueries($database->childNodes, $record);
			}
			catch(\Exception $e)
			{
				Log::error($e->getMessage());
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

				Log::info('> ' . $sql);

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
					if($this->opHasVersion($query->getAttribute('hasVersion')))
					{
						$this->parseQueries($query->childNodes, $record);
					}
				}

				if($query->hasAttribute('hasMinVersion'))
				{
					if($this->opHasMinVersion($query->getAttribute('hasMinVersion')))
					{
						$this->parseQueries($query->childNodes, $record);
					}
				}

				if($query->hasAttribute('hasMaxVersion'))
				{
					if($this->opHasMaxVersion($query->getAttribute('hasMaxVersion')))
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

	private function opHasVersion($value)
	{
		return $this->parseVersion(Base::getVersion()) == $this->parseVersion($value);
	}

	private function opHasMinVersion($value)
	{
		return $this->parseVersion(Base::getVersion()) >= $this->parseVersion($value);
	}

	private function opHasMaxVersion($value)
	{
		return $this->parseVersion(Base::getVersion()) <= $this->parseVersion($value);
	}

	private function parseVersion($value)
	{
		$value = substr($value, 0, 5); // strip beta etc.
		$parts = explode('.', $value);
		$parts = array_reverse(array_map('intval', $parts));
		$ver   = 0;

		foreach($parts as $k => $v)
		{
			$ver+= $v << (8 * $k);
		}

		return $ver;
	}

	private function copyFiles($src, $dest, DOMNode $el)
	{
		if(!is_dir($src))
		{
			throw new Exception('Invalid source path ' . $src);
		}

		if(!is_dir($dest))
		{
			if(!mkdir($dest, 0755))
			{
				throw new Exception('Could not create folder ' . $dest);
			}

			Log::info('A ' . $dest);
		}

		for($i = 0; $i < $el->childNodes->length; $i++)
		{
			$e = $el->childNodes->item($i);

			if($e instanceof DOMElement)
			{
				if($e->nodeName == 'dir')
				{
					$this->copyFiles($src . '/' . $e->getAttribute('name'), $dest . '/' . $e->getAttribute('name'), $e);
				}

				if($e->nodeName == 'file')
				{
					$srcFile  = $src . '/' . $e->getAttribute('name');
					$destFile = $dest . '/' . $e->getAttribute('name');

					if(!is_file($srcFile))
					{
						throw new Exception('Invalid source file ' . $srcFile);
					}

					if(md5_file($srcFile) != $e->getAttribute('md5'))
					{
						throw new Exception('Invalid md5 hash for file: ' . $srcFile);
					}

					if(!is_file($destFile))
					{
						file_put_contents($destFile, file_get_contents($srcFile));

						Log::info('A ' . $destFile);
					}
					else
					{
						// we dont override files
					}
				}
			}
		}
	}

	public function substituteVars($sql, Service\Record $record)
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

