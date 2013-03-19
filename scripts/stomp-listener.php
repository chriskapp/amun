<?php
/*
 *  $Id: stomp-listener.php 639 2012-05-01 20:54:24Z k42b3.x@googlemail.com $
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

$manager = new Amun\Stomp\Manager($config);

//$manager->add(new Amun\Stomp\Listener\NotifySubmitter());
//$manager->add(new Amun\Stomp\Listener\PshbContentNotification());
$manager->add(new Amun\Stomp\Listener\RemoteActivitySubmitter());

$manager->run();


