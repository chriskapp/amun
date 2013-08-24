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

namespace Amun;

use Amun\Dependency\Container;
use Amun\Logger\ComposerHandler;
use Composer\Script\Event;
use Composer\IO\IOInterface;
use Exception;
use Monolog\Logger;
use PSX\Bootstrap;

/**
 * Installer
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Installer
{
	protected static $_container;

	protected $container;

	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	public function doInstall(Event $event)
	{
		$package = $event->getOperation()->getPackage();
		$dir     = $event->getComposer()->getConfig()->get('vendor-dir');
		$config  = $dir . '/' . $package->getName() . '/config.xml';

		if(is_file($config))
		{
			// create service
			$hm      = $this->container->getHandlerManager();
			$handler = $hm->getHandler('AmunService\\Core\\Service');
			$record  = $handler->getRecord();

			if($package->getInstallationSource() == 'source')
			{
				$source = $package->getSourceUrl();
			}
			else if($package->getInstallationSource() == 'dist')
			{
				$source = $package->getDistUrl();
			}

			$record->setSource($source);
			$record->setConfig($config);
			$record->setName($package->getPrettyName());
			$record->setLink($package->getHomepage());
			$record->setLicense(implode(', ', $package->getLicense()));
			$record->setVersion($package->getPrettyVersion());

			$handler->create($record);
		}
	}

	public static function postInstall(Event $event)
	{
		try
		{
			$package = $event->getOperation()->getPackage();
			$dir     = $event->getComposer()->getConfig()->get('vendor-dir');
			$config  = $dir . '/' . $package->getName() . '/config.xml';

			if(is_file($config))
			{
				$installer = new self(self::getContainer($event->getIo()));
				$installer->doInstall($event);
			}
		}
		catch(\Exception $e)
		{
            $event->getIo()->write('<error>An error occured while installing an service</error>');
            $event->getIo()->write($e->getMessage() . "\n" . $e->getTraceAsString() . "\n");
		}
	}

	public static function postUpdate(Event $event)
	{
	}

	protected static function getContainer(IOInterface $io = null)
	{
		if(self::$_container === null)
		{
			self::$_container = new Container();
			self::$_container->setParameter('config.file', 'configuration.php');
			self::$_container->setParameter('user.id', 1);

			// start bootstrap
			new Bootstrap(self::$_container->get('config'));

			// setup composer logger to redirect all log infos to the console
			if($io !== null)
			{
				$logger = new Logger('amun');
				$logger->pushHandler(new ComposerHandler($io, Logger::INFO));

				self::$_container->set('logger', $logger);
			}
		}

		return self::$_container;
	}
}
