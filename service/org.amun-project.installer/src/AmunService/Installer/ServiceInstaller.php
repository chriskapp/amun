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

namespace AmunService\Installer;

use Amun\Dependency;
use Amun\Logger\ComposerHandler;
use Composer\Plugin\PluginInterface;
use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Installer\LibraryInstaller;
use Monolog\Logger;
use PSX\Bootstrap;
use PSX\DependencyInterface;

/**
 * ServiceInstaller
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class ServiceInstaller extends LibraryInstaller
{
	protected static $_container;

	protected $container;
	protected $classLoader;

	public function __construct(IOInterface $io, Composer $composer, $type = 'amun-service')
	{
		parent::__construct($io, $composer, $type);

		$this->container = self::getContainer();
	}

	public function isInstalled(InstalledRepositoryInterface $repo, PackageInterface $package)
	{
		$installed = parent::isInstalled($repo, $package);

		if($installed)
		{
			// if the files are installed check whether the service is in the 
			// table registered
			$hm      = $this->container->getHandlerManager();
			$handler = $hm->getHandler('AmunService\Core\Service');
			$service = $handler->getOneByName($package->getName(), array('id', 'name'));

			if(!empty($service))
			{
				return true;
			}
		}

		return false;
	}

	public function install(InstalledRepositoryInterface $repo, PackageInterface $package)
	{
		// install files
		if(!parent::isInstalled($repo, $package))
		{
			parent::install($repo, $package);
		}

		// parse config xml
		$dir    = $this->composer->getConfig()->get('vendor-dir');
		$config = $dir . '/' . $package->getName() . '/config.xml';

		if(is_file($config))
		{
			// create service
			$hm      = $this->container->getHandlerManager();
			$handler = $hm->getHandler('AmunService\Core\Service');
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

	/**
	 * Returns the di container
	 *
	 * @return PSX\DependencyInterface
	 */
	protected static function getContainer()
	{
		if(self::$_container === null)
		{
			$container = new Dependency\Install();
			$container->setParameter('config.file', 'configuration.php');
			$container->setParameter('user.id', 1);

			// start bootstrap
			Bootstrap::setupEnvironment($container->get('config'));

			// set io
			$container->set('io', $this->io);

			// setup composer logger to redirect all log infos to the console
			$logger = new Logger('amun');
			$logger->pushHandler(new ComposerHandler($this->io, Logger::INFO));

			$container->set('logger', $logger);

			self::$_container = $container;
		}

		return self::$_container;
	}
}
