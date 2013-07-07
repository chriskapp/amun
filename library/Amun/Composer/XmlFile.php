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

use Composer\Composer;
use Composer\Util\RemoteFilesystem;
use Composer\Downloader\TransportException;
use DOMDocument;

/**
 * XmlFile
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class XmlFile
{
	const NS = 'http://composer.org/2013/composer/config';

	protected $dom;

	public function __construct(DOMDocument $dom)
	{
		$this->dom = $dom;
	}

	public function read()
	{
		return $this->buildArrayStructure($this->dom);
	}

	protected function buildArrayStructure(DOMDocument $dom)
	{
		$config = array();
		$elements = $dom->getElementsByTagNameNS(self::NS, '*');

		for($i = 0; $i < $elements->length; $i++)
		{
			$element = $elements->item($i);
			switch($element->localName) 
			{
				case 'name':
				case 'description':
				case 'version':
				case 'type':
				case 'homepage':
				case 'time':
				case 'license':
				case 'target-dir':
				case 'minimum-stability':
				case 'prefer-stable':
					$config[$element->localName] = $element->nodeValue;
					break;

				case 'keywords':
					$config[$element->localName] = array_map('trim', explode(',', $element->nodeValue));
					break;

				case 'authors':
					$authors = array();
					$authorElements = $element->getElementsByTagNameNS(self::NS, 'author');

					for($j = 0; $j < $authorElements->length; $j++) 
					{
						$author = array();
						$authorElement = $authorElements->item($j);

						$name = $authorElement->getElementsByTagNameNS(self::NS, 'name')->item(0);
						$email = $authorElement->getElementsByTagNameNS(self::NS, 'email')->item(0);
						$homepage = $authorElement->getElementsByTagNameNS(self::NS, 'homepage')->item(0);
						$role = $authorElement->getElementsByTagNameNS(self::NS, 'role')->item(0);

						if(!empty($name)) 
						{
							$author['name'] = $name->nodeValue;
						}

						if(!empty($email)) 
						{
							$author['email'] = $email->nodeValue;
						}

						if(!empty($homepage)) 
						{
							$author['homepage'] = $homepage->nodeValue;
						}

						if(!empty($role)) 
						{
							$author['role'] = $role->nodeValue;
						}

						if(!empty($author)) 
						{
							$authors[] = $author;
						}
					}

					if(!empty($authors)) 
					{
						$config[$element->localName] = $authors;
					}
					break;

				case 'support':
					$support = array();

					$email = $element->getElementsByTagNameNS(self::NS, 'email')->item(0);
					$issues = $element->getElementsByTagNameNS(self::NS, 'issues')->item(0);
					$forum = $element->getElementsByTagNameNS(self::NS, 'forum')->item(0);
					$wiki = $element->getElementsByTagNameNS(self::NS, 'wiki')->item(0);
					$irc = $element->getElementsByTagNameNS(self::NS, 'irc')->item(0);
					$source = $element->getElementsByTagNameNS(self::NS, 'source')->item(0);

					if(!empty($email)) 
					{
						$support['email'] = $email->nodeValue;
					}

					if(!empty($issues)) 
					{
						$support['issues'] = $issues->nodeValue;
					}

					if(!empty($forum)) 
					{
						$support['forum'] = $forum->nodeValue;
					}

					if(!empty($wiki)) 
					{
						$support['wiki'] = $wiki->nodeValue;
					}

					if(!empty($irc)) 
					{
						$support['irc'] = $irc->nodeValue;
					}

					if(!empty($source)) 
					{
						$support['source'] = $source->nodeValue;
					}

					if(!empty($support)) 
					{
						$config[$element->localName] = $support;
					}
					break;

				case 'require':
				case 'require-dev':
				case 'conflict':
				case 'replace':
				case 'provide':
				case 'suggest':
					$packages = array();
					$packageElements = $element->getElementsByTagNameNS(self::NS, 'package');

					for($j = 0; $j < $packageElements->length; $j++) 
					{
						$packageElement = $packageElements->item($j);

						if($packageElement->hasAttribute('name') && $packageElement->hasAttribute('version')) 
						{
							$name = $packageElement->getAttribute('name');
							$version = $packageElement->getAttribute('version');

							$packages[$name] = $version;
						}
					}

					if(!empty($packages)) 
					{
						$config[$element->localName] = $packages;
					}
					break;
			}
		}

		return $config;
	}
}
