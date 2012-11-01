<?php
/*
 *  $Id: Gadget.php 834 2012-08-26 21:16:47Z k42b3.x@googlemail.com $
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
 * Amun_Gadget
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Gadget
 * @version    $Revision: 834 $
 */
class Amun_Gadget extends ArrayObject
{
	private $config;
	private $sql;
	private $registry;

	private $gadgets = array();

	public function __construct(Amun_Registry $registry)
	{
		$this->config   = $registry->getConfig();
		$this->sql      = $registry->getSql();
		$this->registry = $registry;
	}

	public function hasNext()
	{
		return $this->getIterator()->valid();
	}

	/**
	 * Returns the content next gadget and removes it from the list
	 *
	 * @return string
	 */
	public function get()
	{
		$iterator = $this->getIterator();

		if($iterator->valid())
		{
			$body = $iterator->current()->getBody();

			//$iterator->next();

			$this->offsetUnset($iterator->key());

			return $body;
		}

		return '';
	}

	/**
	 * Loads all gadgets for the page
	 *
	 * @return void
	 */
	public function load(Amun_Page $page)
	{
		$this->exchangeArray($this->gadgets = array());

		$sql = <<<SQL
SELECT

	`pageGadget`.`gadgetId` AS `id`,
	`gadget`.`title`,
	`gadget`.`path`,
	`gadget`.`cache`,
	`gadget`.`expire`,
	`gadget`.`param`

	FROM {$this->registry['table.core_content_page_gadget']} `pageGadget`

		INNER JOIN {$this->registry['table.core_content_gadget']} `gadget`

		ON `pageGadget`.`gadgetId` = `gadget`.`id`

			WHERE `pageGadget`.`pageId` = ?

			ORDER BY `pageGadget`.`sort` ASC
SQL;

		$result = $this->sql->getAll($sql, array($page->id), PSX_Sql::FETCH_OBJECT, 'Amun_Gadget_Item', array($this->config));

		foreach($result as $gadget)
		{
			// execute gadget
			try
			{
				$gadget->parseContent($page->application);
			}
			catch(Exception $e)
			{
				$msg = $e->getMessage();

				if($this->config['psx_debug'] === true)
				{
					$msg.= "\n\n" . $e->getTraceAsString();
				}

				$gadget->setBody($msg);
			}

			$this->append($gadget);
		}
	}
}

