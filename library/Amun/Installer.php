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

use Amun\Dependency;
use Amun\Logger\ComposerHandler;
use Composer\Script\Event;
use Composer\IO\IOInterface;
use Exception;
use Monolog\Logger;
use PSX\Bootstrap;
use PSX\DependencyInterface;

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

	public function __construct(DependencyInterface $container)
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
            $event->getIo()->write('    - ' . $e->getMessage() . "\n");
            $event->getIo()->write('      ' . $e->getTraceAsString() . "\n");
		}
	}

	public static function postUpdate(Event $event)
	{
	}

	/**
	 * Returns the di container. If $installerDi is true the installer container
	 * gets returned wich uses a User and Registry object wich doesnt need a db
	 * connection
	 *
	 * @param Composer\IO\IOInterface $io
	 * @param boolean $installerDi
	 * @return PSX\DependencyInterface
	 */
	protected static function getContainer(IOInterface $io = null)
	{
		if(self::$_container === null)
		{
			$container = new Dependency\Install();
			$container->setParameter('config.file', 'configuration.php');
			$container->setParameter('user.id', 1);

			// start bootstrap
			Bootstrap::setupEnvironment($container->get('config'));

			// setup composer logger to redirect all log infos to the console
			if($io !== null)
			{
				$logger = new Logger('amun');
				$logger->pushHandler(new ComposerHandler($io, Logger::INFO));

				$container->set('logger', $logger);
			}

			self::$_container = $container;
		}

		return self::$_container;
	}
}
