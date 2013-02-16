<?php
/*
 *  $Id: TitleExists.php 635 2012-05-01 19:46:37Z k42b3.x@googlemail.com $
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
 * AmunService_Core_Content_Page_Filter_TitleExists
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Content_Page
 * @version    $Revision: 635 $
 */
class AmunService_Content_Page_Filter_TitleExists extends PSX_FilterAbstract
{
	private $sql;
	private $registry;
	private $parentId;

	public function __construct(Amun_Registry $registry, $parentId = 0)
	{
		$this->sql      = $registry->getSql();
		$this->registry = $registry;
		$this->parentId = $parentId;
	}

	public function apply($value)
	{
		$sql = <<<SQL
SELECT
	`page`.`id` AS `pageId`
FROM 
	{$this->registry['table.content_page']} `page`
WHERE 
	`page`.`parentId` = {$this->parentId}
AND 
	`page`.`urlTitle` = ?
SQL;

		return $this->sql->getField($sql, array($value));
	}

	public function getErrorMsg()
	{
		return '%s already exists';
	}
}
