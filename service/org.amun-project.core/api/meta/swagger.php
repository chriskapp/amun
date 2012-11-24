<?php
/*
 *  $Id: lrdd.php 799 2012-07-08 05:52:43Z k42b3.x@googlemail.com $
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

namespace core\api\meta;

use Amun_Base;
use Amun_Module_ApiAbstract;
use Amun_Sql_Table_Registry;
use Exception;
use PSX_Data_Message;
use PSX_Sql;
use PSX_Sql_Join;
use PSX_Swagger_Api;
use PSX_Swagger_Declaration;
use PSX_Swagger_Operation;
use PSX_Swagger_ParameterAbstract;
use PSX_Swagger_Parameter_Body;
use PSX_Swagger_Parameter_Header;
use PSX_Swagger_Parameter_Path;
use PSX_Swagger_Parameter_Query;
use PSX_Util_Annotation;
use PSX_Util_Annotation_DocBlock;
use ReflectionClass;

/**
 * swagger
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @version    $Revision: 799 $
 */
class swagger extends Amun_Module_ApiAbstract
{
	public function onLoad()
	{
		try
		{
			header('Content-type: application/json');

			$version  = Amun_Base::getVersion();
			$basePath = $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api';

			$declaration = new PSX_Swagger_Declaration($version, $basePath, null);
			$serviceName = current($this->uriFragments);

			if(empty($serviceName))
			{
				$this->buildApiIndex($declaration);
			}
			else
			{
				$this->buildApiDetails($declaration, $serviceName);
			}

			$this->setResponse($declaration);
		}
		catch(Exception $e)
		{
			$msg = new PSX_Data_Message($e->getMessage(), false);

			$this->setResponse($msg);
		}
	}

	private function buildApiIndex(PSX_Swagger_Declaration $declaration)
	{
		$result = Amun_Sql_Table_Registry::get('Core_Content_Service')
			->select(array('name', 'path'))
			->orderBy('name', PSX_Sql::SORT_ASC)
			->getAll();

		foreach($result as $row)
		{
			// add api
			$desc = '-';
			$api  = new PSX_Swagger_Api('/core/meta/swagger/' . $row['name'], $desc);

			$declaration->addApi($api);
		}
	}

	private function buildApiDetails(PSX_Swagger_Declaration $declaration, $serviceName)
	{
		$declaration->setResourcePath('/core/meta/swagger/' . $serviceName);

		$result = Amun_Sql_Table_Registry::get('Core_Content_Api')
			->select(array('priority', 'endpoint'))
			->join(PSX_Sql_Join::INNER, Amun_Sql_Table_Registry::get('Core_Content_Service')
				->select(array('source', 'name', 'namespace'), 'service')
			)
			->orderBy('id', PSX_Sql::SORT_ASC)
			->where('serviceName', '=', $serviceName)
			->getAll();

		foreach($result as $row)
		{
			$endpoint = trim($row['endpoint'], '/');
			$name     = str_replace('/', '_', $endpoint);

			try
			{
				$provider = $this->getDataProvider($name);

				// add api
				$desc = '-';
				$api  = new PSX_Swagger_Api($row['endpoint'], $desc);

				// get the api class
				$src   = str_replace($row['serviceNamespace'], $row['serviceSource'] . '/api', $endpoint);
				$ns    = str_replace('_', '\\', str_replace($row['serviceNamespace'], $row['serviceNamespace'] . '_api', $name));
				$class = $this->getClass($src, $ns);

				if($class instanceof ReflectionClass)
				{
					$models = array();

					// scan for GET, POST, PUT and DELETE methods and add 
					// operations to the api
					$this->scanMethods($api, $class, 'GET', $models);
					$this->scanMethods($api, $class, 'POST', $models);
					$this->scanMethods($api, $class, 'PUT', $models);
					$this->scanMethods($api, $class, 'DELETE', $models);

					// add api
					$declaration->addApi($api);

					// add models
					$models = array_unique($models);

					if(!empty($models))
					{
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
					}
				}
			}
			catch(Exception $e)
			{
			}
		}
	}

	private function getClass($src, $ns)
	{
		$path = strtolower($src);
		$ns   = str_replace('_', '\\', $ns);

		$normalFile = $this->config['amun_service_path'] . '/' . $path . '.php';
		$indexFile  = $this->config['amun_service_path'] . '/' . $path . '/index.php';

		if(is_file($normalFile))
		{
			require_once($normalFile);

			return new ReflectionClass($ns);
		}
		else if(is_file($indexFile))
		{
			require_once($indexFile);

			return new ReflectionClass($ns . '\\index');
		}
	}

	private function scanMethods(PSX_Swagger_Api $api, ReflectionClass $class, $httpMethod, array &$models)
	{
		$methods = $class->getMethods();

		foreach($methods as $method)
		{
			if($method->isPublic())
			{
				$this->addOperationByComment($api, $this->getAnnotations($class, $method->getName()), $httpMethod, $models);
			}
		}
	}

	private function addOperationByComment(PSX_Swagger_Api $api, PSX_Util_Annotation_DocBlock $doc, $httpMethod, array &$models)
	{
		if($doc->getFirstAnnotation('httpMethod') == $httpMethod)
		{
			$summary   = $doc->getFirstAnnotation('summary');
			$nickname  = $doc->getFirstAnnotation('nickname');
			$response  = $doc->getFirstAnnotation('responseClass');
			$operation = new PSX_Swagger_Operation($httpMethod, $nickname, $response, $summary);
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
						$parameter = new PSX_Swagger_Parameter_Body($name, $desc, $dataType, $required);
						break;

					case 'header':
						$parameter = new PSX_Swagger_Parameter_Header($name, $desc, $dataType, $required);
						break;

					case 'path':
						$parameter = new PSX_Swagger_Parameter_Path($name, $desc, $dataType, $required);
						break;

					case 'query':
						$parameter = new PSX_Swagger_Parameter_Query($name, $desc, $dataType, $required);
						break;
				}

				if($parameter instanceof PSX_Swagger_ParameterAbstract)
				{
					$operation->addParameter($parameter);
				}

				// if the datatype is not scalar add the model to the api
				if(!PSX_Swagger_ParameterAbstract::isScalar($dataType))
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
	}

	/**
	 * This method returns all annotation wich are defined in this or any parent
	 * class. If an annotation type is present it overwrites all other defined
	 * types if not the annotations from the parent class will be used
	 *
	 * @param ReflectionClass $class
	 * @param string $methodName
	 * @return PSX_Util_Annotation_DocBlock
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
		$block = new PSX_Util_Annotation_DocBlock();

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
						$doc    = PSX_Util_Annotation::parse($comment);
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
			catch(Exception $e)
			{
				// method probably doesnt exist	
			}
		}

		return $block;
	}
}
