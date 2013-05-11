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

namespace Amun\Filter;

use Amun\User;
use Amun\Html\Filter\Collection;
use Amun\Sql\Table\Registry;
use AmunService\User\Account\Record;
use PSX\FilterAbstract;
use PSX\Html\Filter;
use PSX\Html\Filter\ElementListenerInterface;
use PSX\Html\Filter\TextListenerInterface;
use PSX\Html\Lexer\Token;
use PSX\Config;
use PSX\Http;
use PSX\Url;
use PSX\Oembed;
use PSX\Oembed\Type;
use PSX\Sql;
use PSX\Sql\Condition;

/**
 * Html
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Html extends FilterAbstract implements ElementListenerInterface, TextListenerInterface
{
	private $config;
	private $user;
	private $discover;
	private $collection;

	private $http;
	private $oembed;
	private $oembedHosts;
	private $oembedMedia;

	public function __construct(Config $config, User $user, $discoverOembed = false)
	{
		$this->config = $config;
		$this->user   = $user;

		switch($this->user->status)
		{
			case Record::ADMINISTRATOR:
				$this->collection = new Collection\FullTrusted();
				$this->discover   = $discoverOembed;
				break;

			case Record::NORMAL:
			case Record::REMOTE:
				$this->collection = new Collection\NormalTrusted();
				$this->discover   = $discoverOembed;
				break;

			case Record::ANONYMOUS:
			default:
				$this->collection = new Collection\Untrusted();
				$this->discover   = false;
				break;
		}

		$this->http   = new Http();
		$this->oembed = new Oembed($this->http);

		// whitelist of available oembed endpoints
		$this->oembedHosts = array(
			'youtube.com'     => 'http://www.youtube.com/oembed', 
			'youtu.be'        => 'http://www.youtube.com/oembed', 
			'blip.tv'         => 'http://blip.tv/oembed/', 
			'vimeo.com'       => 'http://vimeo.com/api/oembed.json', 
			'dailymotion.com' => 'http://www.dailymotion.com/services/oembed', 
			'flickr.com'      => 'http://www.flickr.com/services/oembed/', 
			'smugmug.com'     => 'http://api.smugmug.com/services/oembed/', 
			'hulu.com'        => 'http://www.hulu.com/api/oembed.json',
			'viddler.com'     => 'http://lab.viddler.com/services/oembed/',
			'qik.com'         => 'http://qik.com/api/oembed.json',
			'revision3.com'   => 'http://revision3.com/api/oembed/',
			'photobucket.com' => 'http://photobucket.com/oembed',
			'scribd.com'      => 'http://www.scribd.com/services/oembed',
			'wordpress.tv'    => 'http://wordpress.tv/oembed/',
			'funnyordie.com'  => 'http://www.funnyordie.com/oembed',
			'twitter.com'     => 'http://api.twitter.com/1/statuses/oembed.json',
		);
	}

	public function apply($value)
	{
		$this->oembedMedia = array();

		$filter = new Filter($value, $this->collection);
		$filter->addElementListener($this);
		$filter->addTextListener($this);

		$html = $filter->filter();

		// add discovered oembed content if any
		if(!empty($this->oembedMedia) && strpos($html, 'class="amun-oembed-media"') === false)
		{
			$html.= "\n\n";
			$html.= '<div class="amun-oembed-media">';
			$html.= '<ul>';

			foreach($this->oembedMedia as $type)
			{
				if($type instanceof Type\Photo)
				{
					$html.= '<li><div><img src="' . $type->url . '" /></div></li>';
				}
				else if($type instanceof Type\Rich)
				{
					$html.= '<li><div>' . $type->html . '</div></li>';
				}
				else if($type instanceof Type\Video)
				{
					$html.= '<li><div>' . $type->html . '</div></li>';
				}
			}

			$html.= '</ul>';
			$html.= '<hr />';
			$html.= '</div>';
		}

		return $html;
	}

	public function getErrorMsg()
	{
		return '%s contains invalid markup';
	}

	public function onElement(Token\Element $element)
	{
		if($element->getName() == 'a')
		{
			$href = $element->getAttribute('href');

			if(!empty($href))
			{
				// page
				$page = $this->getPage($href);

				if(!empty($page))
				{
					$element->setAttribute('href', $page);

					return $element;
				}

				// media
				$media = $this->getMedia($href);

				if(!empty($media))
				{
					$element->setAttribute('href', $media);

					return $element;
				}
			}
		}
		else if($element->getName() == 'img')
		{
			$src = $element->getAttribute('src');

			if(!empty($src))
			{
				// media
				$media = $this->getMedia($src);

				if(!empty($media))
				{
					$element->setAttribute('src', $media);

					return $element;
				}
			}
		}
	}

	public function onText(Token\Text $text)
	{
		$this->replaceUrl($text);
		$this->replaceProfileUrl($text);
		$this->replacePage($text);

		return $text;
	}

	private function replaceProfileUrl(Token\Text $text)
	{
		if(strpos($text->data, '@') === false)
		{
			return false;
		}

		$parts = preg_split('/@([A-Za-z0-9.]{3,32})/S', $text->data, -1, PREG_SPLIT_DELIM_CAPTURE);
		$data  = '';

		foreach($parts as $i => $part)
		{
			if($i % 2 == 0)
			{
				$data.= $part;
			}
			else
			{
				$con = new Condition();
				$con->add('name', '=', $part);

				$profileUrl = Registry::get('User_Account')->getField('profileUrl', $con);

				if(!empty($profileUrl))
				{
					$data.= '<a href="' . $profileUrl . '">@' . $part . '</a>';
				}
				else
				{
					$data.= '@' . $part;
				}
			}
		}

		$text->data = $data;
	}

	private function replacePage(Token\Text $text)
	{
		if(strpos($text->data, '&') === false)
		{
			return false;
		}

		$parts = preg_split('/&([a-z0-9-_]{2,32})/S', $text->data, -1, PREG_SPLIT_DELIM_CAPTURE);
		$data  = '';

		foreach($parts as $i => $part)
		{
			if($i % 2 == 0)
			{
				$data.= $part;
			}
			else
			{
				$con = new Condition();
				$con->add('urlTitle', '=', $part);

				$path = Registry::get('Content_Page')->getField('path', $con);

				if(!empty($path))
				{
					$href = $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . $path;

					$data.= '<a href="' . $href . '">&' . $part . '</a>';
				}
				else
				{
					$data.= '&' . $part;
				}
			}
		}

		$text->data = $data;
	}

	private function replaceUrl(Token\Text $text)
	{
		if(strpos($text->data, 'http://') === false && strpos($text->data, 'https://') === false)
		{
			return false;
		}

		// if parent element of the text is an link dont replace links
		$isHref = false;

		if($text->parentNode instanceof Token\Element && strtolower($text->parentNode->name) == 'a')
		{
			$isHref = true;
		}

		$parts = preg_split('/(https?:\/\/\S*)/S', $text->data, -1, PREG_SPLIT_DELIM_CAPTURE);
		$data  = '';

		foreach($parts as $i => $part)
		{
			if($i % 2 == 0)
			{
				$data.= $part;
			}
			else
			{
				try
				{
					$url = new Url($part);

					if($this->discover)
					{
						foreach($this->oembedHosts as $host => $endpoint)
						{
							if(strpos($url->getHost(), $host) !== false)
							{
								try
								{
									$api = new Url($endpoint);
									$api->addParam('url', $part);
									$api->addParam('maxwidth', 240);
									$api->addParam('maxheight', 180);

									$type = $this->oembed->request($api);

									$this->oembedMedia[] = $type;
									break;
								}
								catch(\Exception $e)
								{
									// oembed discovery failed
								}
							}
						}
					}

					if(!$isHref)
					{
						$data.= '<a href="' . $part . '">' . $part . '</a>';
					}
					else
					{
						$data.= $part;
					}
				}
				catch(Exception $e)
				{
					$data.= $part;
				}
			}
		}

		$text->data = $data;
	}

	private function getPage($href)
	{
		if(strpos($href, '://') !== false)
		{
			return false;
		}

		$con = new Condition();
		$con->add('path', '=', $href);

		$path = Registry::get('Content_Page')->getField('path', $con);

		if(!empty($path))
		{
			return $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . $path;
		}

		return false;
	}

	private function getMedia($href)
	{
		if(strpos($href, '://') !== false)
		{
			return false;
		}

		$con = new Condition();
		$con->add('path', '=', $href);

		$globalId = Registry::get('Media')->getField('globalId', $con);

		if(!empty($globalId))
		{
			return $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/media/serve/' . $globalId;
		}

		return false;
	}
}

