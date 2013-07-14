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

require_once('../vendor/autoload.php');

$start = isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : null;

if($start != 'start')
{
	echo <<<USAGE
NAME
	install.php - installs amun via the console

SYNOPSIS
	install.php start

DESCRIPTION
	This script installs amun via the console. It uses infact the same 
	controller as the web installer but instead of POST parameters it asks for
	user input

USAGE;
}
else
{
	try
	{
		$container = new Amun\Dependency\Install();
		$container->setParameter('config.file', '../configuration.php');
		$container->setParameter('user.id', 1);

		$container->set('session', new PSX\Session\Void('amun-install'));

		$logger = new Monolog\Logger('amun');
		$logger->pushHandler(new Amun\Logger\EchoHandler(Monolog\Logger::INFO));

		$bootstrap  = new PSX\Bootstrap($container->get('config'));
		$request    = new PSX\Http\Request(new PSX\Url($container->getConfig()->get('psx_url') . '/install'), 'GET');
		$controller = $container->get('loader')->load('/', $request);

		// account handler to verify user input
		$handler = new AmunService\User\Account\Handler($container, $container->get('user'));

		// get user input
		$title = askForValidInput('Title', function($title) use ($container){
			$container->get('validate')->clearError();
			$result = $container->get('validate')->apply($title, 'string', array(new PSX\Filter\Length(3, 64), new PSX\Filter\Html()), 'title', 'Title');
			if($result === false)
			{
				throw new Exception($container->get('validate')->getLastError());
			}
			return true;
		});

		$subTitle = askForValidInput('Sub title (optional)', function($subTitle) use ($container){
			$container->get('validate')->clearError();
			$result = $container->get('validate')->apply($subTitle, 'string', array(new PSX\Filter\Length(0, 128), new PSX\Filter\Html()), 'subTitle', 'Sub Title');
			if($result === false)
			{
				throw new Exception($container->get('validate')->getLastError());
			}
			return true;
		});

		$name = askForValidInput('Username', function($name) use ($container, $handler){
			$container->get('validate')->clearError();
			$handler->getRecord()->setName($name);
			return true;
		});

		$pw = askForValidInput('Password', function($pw) use ($container, $handler){
			$container->get('validate')->clearError();
			$handler->getRecord()->setPw($pw);
			return true;
		});

		$email = askForValidInput('Email', function($email) use ($container, $handler){
			$container->get('validate')->clearError();
			$handler->getRecord()->setIdentity($email);
			return true;
		});

		// start installation
		$logger->info('Setup check requirements');
		executeMethod(array($controller, 'setupCheckRequirements'));

		$logger->info('Setup create tables');
		executeMethod(array($controller, 'setupCreateTables'));

		$logger->info('Setup insert data');
		executeMethod(array($controller, 'setupInsertData'));

		$logger->info('Setup install service');
		executeMethod(array($controller, 'setupInstallService'));

		$_POST['title'] = $title;
		$_POST['subTitle'] = $subTitle;

		$logger->info('Setup insert registry');
		executeMethod(array($controller, 'setupInsertRegistry'));

		$logger->info('Setup insert group');
		executeMethod(array($controller, 'setupInsertGroup'));
		
		$_POST['name'] = $name;
		$_POST['pw'] = $pw;
		$_POST['email'] = $email;

		$logger->info('Setup insert admin');
		executeMethod(array($controller, 'setupInsertAdmin'));

		$logger->info('Setup insert api');
		executeMethod(array($controller, 'setupInsertApi'));

		$logger->info('Setup install sample');
		executeMethod(array($controller, 'setupInstallSample'));

		echo 'Installation successful';
		exit(0);
	}
	catch(\Exception $e)
	{
		echo 'Exception: ' . $e->getMessage() . "\n";
		exit(1);
	}
}

function executeMethod($callback)
{
	ob_start();

	call_user_func_array($callback, array());

	$content = ob_get_contents();

	ob_end_clean();

	$response = PSX\Json::decode($content);

	if(isset($response['success']) && $response['success'] == true)
	{
		return true;
	}
	else
	{
		throw new Exception(isset($response['msg']) ? $response['msg'] : $response);
	}
}

function askForValidInput($name, Closure $validator)
{
	$isValid = false;

	do
	{
		try
		{
			echo $name . ': ';
			$value = trim(fgets(STDIN));

			$isValid = $validator($value);
		}
		catch(Exception $e)
		{
			echo 'Invalid input: ' . $e->getMessage() . "\n";

			$isValid = false;
		}
	}
	while($isValid === false);

	return $value;
}