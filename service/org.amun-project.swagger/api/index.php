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

namespace swagger\api;

use Amun\Base;
use Amun\Module\ApiAbstract;
use Amun\DataFactory;
use Amun\Exception;
use PSX\Cache;
use PSX\Data\Message;
use PSX\Sql;
use PSX\Swagger\Api;
use PSX\Swagger\Declaration;
use PSX\Swagger\Operation;
use PSX\Swagger\ParameterAbstract;
use PSX\Swagger\Parameter;
use PSX\Util\Annotation;
use PSX\Util\Annotation\DocBlock;
use ReflectionClass;

/**
 * index
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class index extends ApiAbstract
{
	/**
	 * @httpMethod GET
	 * @path /
	 * @nickname getApiIndex
	 * @responseClass Declaration
	 */
	public function getApiIndex()
	{
		try
		{
			$basePath = $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api';
			$cache    = new Cache('swagger-api-index');

			if(($declaration = $cache->load()) === false)
			{
				$declaration = new Declaration(Base::getVersion(), $basePath, null);

				$this->buildApiIndex($declaration);

				$cache->write(serialize($declaration));
			}
			else
			{
				$declaration = unserialize($declaration);
			}

			$this->setResponse($declaration);
		}
		catch(\Exception $e)
		{
			$msg = new Message($e->getMessage(), false);

			$this->setResponse($msg);
		}
	}

	/**
	 * @httpMethod GET
	 * @path /{service}
	 * @nickname getApiDetails
	 * @responseClass Declaration
	 */
	public function getApiDetails()
	{
		try
		{
			$basePath    = $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api';
			$serviceName = $this->getUriFragments('service');
			$cache       = new Cache('swagger-api-detail-' . $serviceName);

			if(($declaration = $cache->load()) === false)
			{
				$declaration = new Declaration(Base::getVersion(), $basePath, null);

				$this->buildApiDetails($declaration, $serviceName);

				$cache->write(serialize($declaration));
			}
			else
			{
				$declaration = unserialize($declaration);
			}

			$this->setResponse($declaration);
		}
		catch(\Exception $e)
		{
			$msg = new Message($e->getMessage(), false);

			$this->setResponse($msg);
		}
	}

	private function buildApiIndex(Declaration $declaration)
	{
		$result = $this->hm->getTable('Core_Service')
			->select(array('name', 'path'))
			->orderBy('name', Sql::SORT_ASC)
			->getAll();

		foreach($result as $row)
		{
			// add api
			$desc = '-';
			$api  = new Api('/swagger/' . $row['name'], $desc);

			$declaration->addApi($api);
		}
	}

	private function buildApiDetails(Declaration $declaration, $serviceName)
	{
		$declaration->setResourcePath('/swagger/' . $serviceName);

		$result = $this->hm->getTable('Core_Service')
			->select(array('source', 'name', 'namespace', 'path'))
			->orderBy('id', Sql::SORT_ASC)
			->where('name', '=', $serviceName)
			->getAll();

		foreach($result as $row)
		{
			try
			{
				// get the api class
				$apiPath = $this->config['amun_service_path'] . '/' . $row['source'] . '/api';

				if(is_dir($apiPath))
				{
					$models  = array();
					$classes = $this->findApiClasses($apiPath, $row['namespace'], $row['source'], $row['path']);

					foreach($classes as $endpoint => $class)
					{
						$this->scanMethods($declaration, $class, $endpoint, $models);
					}

					// add models
					$models = array_unique($models);

					if(!empty($models))
					{
						/*
						foreach($models as $model)
						{
							try
							{
								$record = new ReflectionClass($model);

								$declaration->addModel($record->newInstance($provider->getTable()));
							}
							catch(Exception $e)
							{
							}
						}
						*/
					}
				}
			}
			catch(\Exception $e)
			{
			}
		}
	}

	private function findApiClasses($path, $ns, $src, $basePath)
	{
		$files   = scandir($path);
		$classes = array();

		foreach($files as $f)
		{
			if($f[0] != '.')
			{
				$item = $path . '/' . $f;

				if(is_dir($item))
				{
					$classes = array_merge($classes, $this->findApiClasses($item, $ns, $src, $basePath));
				}

				if(is_file($item))
				{
					$file     = substr($item, strlen($this->config['amun_service_path'] . '/' . $src) + 1);
					$endpoint = $basePath . '/' . substr($file, 4, -4);

					if(substr($endpoint, -5) == 'index')
					{
						$endpoint = substr($endpoint, 0, -5);
					}

					$classes[$endpoint] = $this->getClass($file, $ns, $src);
				}
			}
		}

		return $classes;
	}

	private function getClass($file, $ns, $src)
	{				
		$path  = $this->config['amun_service_path'] . '/' . $src . '/' . $file;
		$class = pathinfo($path, PATHINFO_FILENAME);
		$subNs = str_replace('/', '\\', pathinfo($file, PATHINFO_DIRNAME));

		if(!empty($subNs))
		{
			$ns.= '\\' . $subNs;
		}

		require_once($path);

		return new ReflectionClass($ns . '\\' . $class);
	}

	private function scanMethods(Declaration $declaration, ReflectionClass $class, $endpoint, array &$models)
	{
		$methods  = $class->getMethods();
		$endpoint = trim($endpoint, '/');

		foreach($methods as $method)
		{
			if($method->isPublic())
			{
				$doc = $this->getAnnotations($class, $method->getName());

				$httpMethod = $doc->getFirstAnnotation('httpMethod');
				$path       = $doc->getFirstAnnotation('path');

				if(!empty($httpMethod) && !empty($path))
				{
					// add api
					$path = '/' . trim($endpoint . $path, '/');
					$desc = trim($doc->getText());
					$api  = new Api($path, $desc);

					$this->addOperationByComment($api, $doc, $httpMethod, $models);

					$declaration->addApi($api);
				}
			}
		}
	}

	private function addOperationByComment(Api $api, DocBlock $doc, $httpMethod, array &$models)
	{
		$summary   = $doc->getFirstAnnotation('summary');
		$nickname  = uniqid($doc->getFirstAnnotation('nickname') . '_');
		$response  = $doc->getFirstAnnotation('responseClass');
		$operation = new Operation($httpMethod, $nickname, $response, $summary);
		$params    = $doc->getAnnotation('parameter');
		$dataTypes = array();

		foreach($params as $dfn)
		{
			if(substr($dfn, 0, 1) == '[')
			{
				$dfn = ltrim($dfn, '[');
				$dfn = rtrim($dfn, ']');

				$required = false;
			}
			else
			{
				$required = true;
			}

			$parts    = explode(' ', $dfn, 4);
			$type     = isset($parts[0]) ? $parts[0] : null;
			$name     = isset($parts[1]) ? $parts[1] : null;
			$dataType = isset($parts[2]) ? $parts[2] : null;
			$desc     = isset($parts[3]) ? $parts[3] : null;

			switch(strtolower($type))
			{
				case 'body':
					$parameter = new Parameter\Body($name, $desc, $dataType, $required);
					break;

				case 'header':
					$parameter = new Parameter\Header($name, $desc, $dataType, $required);
					break;

				case 'path':
					$parameter = new Parameter\Path($name, $desc, $dataType, $required);
					break;

				case 'query':
					$parameter = new Parameter\Query($name, $desc, $dataType, $required);
					break;
			}

			if($parameter instanceof ParameterAbstract)
			{
				$operation->addParameter($parameter);
			}

			// if the datatype is not scalar add the model to the api
			if(!ParameterAbstract::isScalar($dataType))
			{
				$dataTypes[] = $dataType;
			}
		}

		$api->addOperation($operation);


		$dataTypes = array_unique($dataTypes);

		if(!empty($dataTypes))
		{
			foreach($dataTypes as $dataType)
			{
				$models[] = $dataType;
			}
		}
	}

	/**
	 * This method returns all annotation wich are defined in this or any parent
	 * class. If an annotation type is present it overwrites all other defined
	 * types if not the annotations from the parent class will be used
	 *
	 * @param ReflectionClass $class
	 * @param string $methodName
	 * @return DocBlock
	 */
	private function getAnnotations(ReflectionClass $class, $methodName)
	{
		// get hierarchy
		$parents[] = $class;

		while($parent = $class->getParentClass())
		{
			$parents[] = $parent;
			$class     = $parent;
		}

		// parse doc comments
		$block = new DocBlock();

		foreach($parents as $class)
		{
			try
			{
				$method = $class->getMethod($methodName);

				if($method)
				{
					$comment = $method->getDocComment();

					if(!empty($comment))
					{
						$doc    = Annotation::parse($comment);
						$params = $doc->getAnnotations();
						$text   = $doc->getText();

						foreach($params as $k => $v)
						{
							$block->setAnnotations($k, $v);
						}

						if(!empty($text))
						{
							$block->setText($text);
						}
					}
				}
			}
			catch(\Exception $e)
			{
				// method probably doesnt exist	
			}
		}

		return $block;
	}
}
