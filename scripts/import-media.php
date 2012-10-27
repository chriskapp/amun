<?php
/*
 *  $Id: import-media.php 811 2012-07-09 14:23:49Z k42b3.x@googlemail.com $
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

require_once('bootstrap.php');

$userId  = isset($_SERVER['argv'][1]) ? intval($_SERVER['argv'][1]) : null;
$path    = isset($_SERVER['argv'][2]) ? $_SERVER['argv'][2] : null;
$rightId = isset($_SERVER['argv'][3]) ? intval($_SERVER['argv'][3]) : null;

if(empty($userId) || empty($path))
{
	echo <<<USAGE
NAME
	import-media.php - imports all files from an specific directory into the
	content media table

SYNOPSIS
	import-media.php [USER_ID] [PATH] [RIGHT_ID]

DESCRIPTION
	This script can be used to import recursively an folder into the content
	media table. The user id parameter represents the user on wich behalf the
	media files gets imported. Optional if set assigns all media entries to the
	given right id.
USAGE;
}
else
{
	try
	{
		$base = Amun_Base::getInstance();
		$base->setUser($userId);

		PSX_Log::getLogger()->setLevel(PSX_Log::INFO);
		PSX_Log::getLogger()->addHandler(new PSX_Log_Handler_Print());

		$handler = new Amun_Content_Media_Handler($base->getUser());
		$handler->import($path, $rightId);

		echo 'Import successful';
		exit(0);
	}
	catch(Exception $e)
	{
		echo 'Exception: ' . $e->getMessage() . "\n";
		exit(1);
	}
}