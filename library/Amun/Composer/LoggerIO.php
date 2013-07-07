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

namespace Amun\Composer;

use Composer\IO\IOInterface;
use Monolog\Logger;

/**
 * LoggerIO
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class LoggerIO implements IOInterface
{
	protected $logger;

	public function __construct(Logger $logger)
	{
		$this->logger = $logger;
	}

	/**
	 * {@inheritDoc}
	 */
	public function isInteractive()
	{
		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function isVerbose()
	{
		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function isVeryVerbose()
	{
		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function isDebug()
	{
		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function isDecorated()
	{
		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function write($messages, $newline = true)
	{
		if(is_array($messages))
		{
			foreach($messages as $message)
			{
				$this->logger->info($message);
			}
		}
		else
		{
			$this->logger->info((string) $messages);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function overwrite($messages, $newline = true, $size = 80)
	{
		if(is_array($messages))
		{
			foreach($messages as $message)
			{
				$this->logger->info($message);
			}
		}
		else
		{
			$this->logger->info((string) $messages);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function ask($question, $default = null)
	{
		return $default;
	}

	/**
	 * {@inheritDoc}
	 */
	public function askConfirmation($question, $default = true)
	{
		return $default;
	}

	/**
	 * {@inheritDoc}
	 */
	public function askAndValidate($question, $validator, $attempts = false, $default = null)
	{
		return $default;
	}

	/**
	 * {@inheritDoc}
	 */
	public function askAndHideAnswer($question)
	{
		return null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getAuthentications()
	{
		return array();
	}

	/**
	 * {@inheritDoc}
	 */
	public function hasAuthentication($repositoryName)
	{
		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getAuthentication($repositoryName)
	{
		return array('username' => null, 'password' => null);
	}

	/**
	 * {@inheritDoc}
	 */
	public function setAuthentication($repositoryName, $username, $password = null)
	{
	}
}
