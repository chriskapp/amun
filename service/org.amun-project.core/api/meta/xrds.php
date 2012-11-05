<?php
/*
 *  $Id: xrds.php 799 2012-07-08 05:52:43Z k42b3.x@googlemail.com $
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
 * xrds
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @package    api
 * @subpackage system_xrds
 * @version    $Revision: 799 $
 */
class xrds extends Amun_Module_ApiAbstract
{
	private $writer;

	public function onGet()
	{
		try
		{
			header('Content-type: application/xrds+xml');

			$this->writer = new XMLWriter();
			$this->writer->openURI('php://output');
			$this->writer->setIndent(true);
			$this->writer->startDocument('1.0', 'UTF-8');

			$this->writer->startElementNs('xrds', 'XRDS', 'xri://$xrds');
			$this->writer->writeAttribute('xmlns', 'xri://$xrd*($v*2.0)');
			$this->writer->startElement('XRD');

			$result = Amun_Sql_Table_Registry::get('Core_Content_Api_Type')
				->select(array('apiId', 'type'))
				->join(PSX_Sql_Join::INNER, Amun_Sql_Table_Registry::get('Core_Content_Api')
					->select(array('priority', 'endpoint'), 'api')
				)
				->orderBy('apiId', PSX_Sql::SORT_ASC)
				->getAll();

			$baseUrl  = $this->config['psx_url'] . '/' . $this->config['psx_dispatch'];
			$services = array();

			foreach($result as $row)
			{
				if(!isset($services[$row['apiId']]))
				{
					$services[$row['apiId']] = array(
						'priority' => (integer) $row['apiPriority'],
						'endpoint' => $row['apiEndpoint'],
						'types'    => array(),
					);
				}

				$services[$row['apiId']]['types'][] = $row['type'];
			}

			foreach($services as $service)
			{
				$this->writer->startElement('Service');

				if(!empty($service['priority']))
				{
					$this->writer->writeAttribute('priority', $service['priority']);
				}

				foreach($service['types'] as $type)
				{
					$this->writer->writeElement('Type', $type);
				}

				$this->writer->writeElement('URI', $baseUrl . 'api' . $service['endpoint']);

				$this->writer->endElement();
			}

			$this->writer->endElement();
			$this->writer->endDocument();
		}
		catch(Exception $e)
		{
			$msg = new PSX_Data_Message($e->getMessage(), false);

			$this->setResponse($msg);
		}
	}
}

