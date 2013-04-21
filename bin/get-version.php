<?php

require_once('amun/vendor/autoload.php');

$config    = new PSX\Config('amun/configuration.php');
$bootstrap = new PSX\Bootstrap($config);

echo str_replace(' ', '_', Amun\Base::getVersion());

