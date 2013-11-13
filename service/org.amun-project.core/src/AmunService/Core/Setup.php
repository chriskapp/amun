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

namespace AmunService\Core;

use Amun\SetupAbstract;
use PSX\Data\RecordInterface;

/**
 * Form
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Setup extends SetupAbstract
{
	public function preInstall(RecordInterface $record)
	{
		$queries = array();
		$queries[] = <<<SQL
CREATE TABLE IF NOT EXISTS `{$this->registry['table.core_approval']}` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `table` varchar(64) NOT NULL,
  `field` varchar(32) NOT NULL DEFAULT '',
  `value` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8
SQL;

		$queries[] = <<<SQL
CREATE TABLE IF NOT EXISTS `{$this->registry['table.core_approval_record']}` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `userId` int(10) NOT NULL,
  `type` enum('INSERT','UPDATE','DELETE') NOT NULL,
  `table` varchar(64) NOT NULL,
  `record` text NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8
SQL;

		$queries[] = <<<SQL
CREATE TABLE IF NOT EXISTS `{$this->registry['table.core_event']}` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `interface` varchar(64) DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8
SQL;

		$queries[] = <<<SQL
CREATE TABLE IF NOT EXISTS `{$this->registry['table.core_event_listener']}` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `eventId` int(10) NOT NULL,
  `priority` int(10) NOT NULL,
  `class` varchar(64) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `eventIdClass` (`eventId`,`class`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8
SQL;

		$queries[] = <<<SQL
CREATE TABLE IF NOT EXISTS `{$this->registry['table.core_registry']}` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `type` enum('STRING','INTEGER','FLOAT','BOOLEAN') NOT NULL,
  `class` varchar(64) DEFAULT NULL,
  `value` varchar(128) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8
SQL;

		$queries[] = <<<SQL
CREATE TABLE IF NOT EXISTS `{$this->registry['table.core_service']}` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `status` int(10) NOT NULL,
  `source` varchar(255) NOT NULL,
  `autoloadPath` varchar(255) NOT NULL,
  `config` varchar(255) NOT NULL,
  `name` varchar(64) NOT NULL,
  `path` varchar(255) NOT NULL,
  `namespace` varchar(64) NOT NULL,
  `type` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  `license` varchar(255) NOT NULL,
  `version` varchar(16) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
SQL;

		$queries[] = <<<SQL
INSERT IGNORE INTO `{$this->registry['table.core_registry']}` (`name`, `type`, `class`, `value`) VALUES
('table.core_approval', 'STRING', NULL, '{$this->registry['table.core_approval']}'),
('table.core_approval_record', 'STRING', NULL, '{$this->registry['table.core_approval_record']}'),
('table.core_event', 'STRING', NULL, '{$this->registry['table.core_event']}'),
('table.core_event_listener', 'STRING', NULL, '{$this->registry['table.core_event_listener']}'),
('table.core_registry', 'STRING', NULL, '{$this->registry['table.core_registry']}'),
('table.core_service', 'STRING', NULL, '{$this->registry['table.core_service']}'),
('core.default_timezone', 'STRING', 'DateTimeZone', 'UTC');
SQL;

		$queries[] = <<<SQL
INSERT IGNORE INTO `{$this->registry['table.core_event']}` (`name`, `interface`, `description`) VALUES
('core.service_install', NULL, 'Notifies if a service gets installed'),
('core.record_change', NULL, 'Notifies if a record has changed');
SQL;

		foreach($queries as $query)
		{
			$this->sql->query($query);
		}
	}

	public function postInstall(RecordInterface $record)
	{
	}
}
