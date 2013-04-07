<?php

require_once('amun/library/PSX/Config.php');
require_once('amun/library/PSX/Bootstrap.php');

$config    = new PSX\Config('amun/configuration.php');
$bootstrap = new PSX\Bootstrap($config);

echo str_replace(' ', '_', Amun\Base::getVersion());

