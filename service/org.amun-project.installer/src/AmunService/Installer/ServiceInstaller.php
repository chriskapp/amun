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
use Composer\Repository\InstalledRepositoryInterface;
use Composer\Repository\PlatformRepository;
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

		$this->container = self::getContainer($io);
	}

	public function isInstalled(InstalledRepositoryInterface $repo, PackageInterface $package)
	{
		$installed = parent::isInstalled($repo, $package);

		if($installed)
		{
			// if the files are installed check whether the service is in the 
			// table registered
			return $this->container->get('registry')->hasService($package->getName());
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
			// make service autoloadable
			$classLoader = $this->getClassLoader($package);
			$classLoader->register();

			try
			{
				// create service
				$hm      = $this->container->get('handlerManager');
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
				$record->setAutoloadPath($this->getAutoloadPath($package, $config));
				$record->setConfig($config);
				$record->setName($package->getPrettyName());
				$record->setLink($package->getHomepage());
				$record->setLicense(implode(', ', $package->getLicense()));
				$record->setVersion($package->getPrettyVersion());

				$handler->create($record);
			}
			catch(\Exception $e)
			{
				$this->container->get('logger')->error($e->getMessage());
			}

			$classLoader->unregister();
		}
	}

	protected function getAutoloadPath($package, $configFile)
	{
		$dir      = $this->composer->getConfig()->get('vendor-dir');
		$config   = simplexml_load_file($configFile);
		$autoload = $package->getAutoload();

		if(isset($autoload['psr-0']) && is_array($autoload['psr-0']))
		{
			$namespace = (string) $config->namespace;
			$namespace = trim($namespace, '\\');

			foreach($autoload['psr-0'] as $ns => $path)
			{
				$ns = trim($ns, '\\');

				if($namespace == $ns)
				{
					return $dir . '/' . $package->getName() . '/' . $path;
				}
			}
		}

		throw new \Exception('Could not find namespace in autoload psr-0');
	}

	/**
	 * Registers an autoloader
	 *
	 * @param PackageInterface $package
	 */
	protected function getClassLoader(PackageInterface $package)
	{
		$map       = $this->getAutoloadMap($package);
		$generator = $this->composer->getAutoloadGenerator();

		return $generator->createLoader(array('psr-0' => $map));
	}

	protected function getAutoloadMap(PackageInterface $package)
	{
		$map      = array();
		$map      = array_merge($map, $this->getAutoloadMapForPackage($package));
		$requires = $package->getRequires();
		$repo     = $this->composer->getRepositoryManager()->getLocalRepository();
		$packages = $repo->getPackages();

		if(!empty($requires))
		{
			foreach($requires as $name => $link)
			{
				if(preg_match(PlatformRepository::PLATFORM_PACKAGE_REGEX, $link->getTarget()))
				{
					continue;
				}

				foreach($packages as $installedPackage)
				{
					if($installedPackage->getName() == $name)
					{
						$map = array_merge($map, $this->getAutoloadMap($installedPackage));
					}
				}
			}
		}

		return $map;
	}

	protected function getAutoloadMapForPackage(PackageInterface $package)
	{
		$dir      = $this->composer->getConfig()->get('vendor-dir');
		$autoload = $package->getAutoload();
		$map      = array();

		if(isset($autoload['psr-0']) && is_array($autoload['psr-0']))
		{
			foreach($autoload['psr-0'] as $ns => $src)
			{
				$map[$ns] = $dir . '/' . $package->getName() . '/' . $src;
			}
		}

		return $map;
	}

	/**
	 * Returns the di container
	 *
	 * @return PSX\DependencyInterface
	 */
	protected static function getContainer($io)
	{
		if(self::$_container === null)
		{
			// create di container
			$container = new Dependency\Install();
			$container->setParameter('config.file', 'configuration.php');
			$container->setParameter('user.id', 1);

			// start bootstrap
			Bootstrap::setupEnvironment($container->get('config'));

			// set io
			$container->set('io', $io);

			// setup composer logger to redirect all log infos to the console
			$logger = new Logger('amun');
			$logger->pushHandler(new ComposerHandler($io, Logger::INFO));

			$container->set('logger', $logger);

			self::$_container = $container;
		}

		return self::$_container;
	}
}
