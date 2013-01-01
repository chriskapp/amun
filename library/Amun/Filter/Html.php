<?php
/*
 *  $Id: HtmlPurifier.php 810 2012-07-09 14:18:50Z k42b3.x@googlemail.com $
 *
 * amun
 * A social content managment system based on the psx framework. For
 * the current version and informations visit <http://amun.phpsx.org>
 *
 * Copyright (c) 2010-2012 Christoph Kappestein <k42b3.x@gmail.com>
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

/**
 * Amun_Filter_Html
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Filter
 * @version    $Revision: 810 $
 */
class Amun_Filter_Html extends PSX_FilterAbstract implements PSX_Html_Filter_ElementListenerInterface, PSX_Html_Filter_TextListenerInterface
{
	private $config;
	private $user;
	private $collection;

	private $http;
	private $oembed;
	private $oembedHosts;
	private $oembedMedia;

	public function __construct(PSX_Config $config, Amun_User $user)
	{
		$this->config = $config;
		$this->user   = $user;

		switch($this->user->status)
		{
			case AmunService_User_Account_Record::ADMINISTRATOR:
				$this->collection = new Amun_Html_Filter_Collection_FullTrusted();
				break;

			case AmunService_User_Account_Record::NORMAL:
			case AmunService_User_Account_Record::REMOTE:
				$this->collection = new Amun_Html_Filter_Collection_NormalTrusted();
				break;

			case AmunService_User_Account_Record::ANONYMOUS:
			default:
				$this->collection = new Amun_Html_Filter_Collection_Untrusted();
				break;
		}

		$this->http   = new PSX_Http();
		$this->oembed = new PSX_Oembed($this->http);

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

		$filter = new PSX_Html_Filter($value, $this->collection);
		$filter->addElementListener($this);
		$filter->addTextListener($this);

		$html = $filter->filter();

		// add discovered oembed content if any
		if(!empty($this->oembedMedia))
		{
			$html.= "\n\n" . '<hr />';
			$html.= '<ul>';

			foreach($this->oembedMedia as $type)
			{
				if($type instanceof PSX_Oembed_Type_Photo)
				{
					$html.= '<li><img src="' . $type->url . '" /></li>';
				}
				else if($type instanceof PSX_Oembed_Type_Rich)
				{
					$html.= '<li>' . $type->html . '</li>';
				}
				else if($type instanceof PSX_Oembed_Type_Video)
				{
					$html.= '<li>' . $type->html . '</li>';
				}
			}

			$html.= '</ul>';
		}

		return $html;
	}

	public function getErrorMsg()
	{
		return '%s contains invalid markup';
	}

	public function onElement(PSX_Html_Lexer_Token_Element $element)
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

	public function onText(PSX_Html_Lexer_Token_Text $text)
	{
		$this->replaceProfileUrl($text);
		$this->replacePage($text);
		$this->replaceUrl($text);

		return $text;
	}

	private function replaceProfileUrl(PSX_Html_Lexer_Token_Text $text)
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
				$con = new PSX_Sql_Condition();
				$con->add('name', '=', $part);

				$profileUrl = Amun_Sql_Table_Registry::get('User_Account')->getField('profileUrl', $con);

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

	private function replacePage(PSX_Html_Lexer_Token_Text $text)
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
				$con = new PSX_Sql_Condition();
				$con->add('urlTitle', '=', $part);

				$path = Amun_Sql_Table_Registry::get('Content_Page')->getField('path', $con);

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

	private function replaceUrl(PSX_Html_Lexer_Token_Text $text)
	{
		if(strpos($text->data, 'http://') === false && strpos($text->data, 'https://') === false)
		{
			return false;
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
					$url = new PSX_Url($part);

					foreach($this->oembedHosts as $host => $endpoint)
					{
						if(strpos($url->getHost(), $host) !== false)
						{
							try
							{
								$api = new PSX_Url($endpoint);
								$api->addParam('url', $part);

								$type = $this->oembed->request($api);

								$this->oembedMedia[] = $type;
								break;
							}
							catch(Exception $e)
							{
								// oembed discovery failed
							}
						}
					}

					$data.= '<a href="' . $part . '">' . $part . '</a>';
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

		$con = new PSX_Sql_Condition();
		$con->add('path', '=', $href);

		$path = Amun_Sql_Table_Registry::get('Content_Page')->getField('path', $con);

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

		$con = new PSX_Sql_Condition();
		$con->add('path', '=', $href);

		$globalId = Amun_Sql_Table_Registry::get('Media')->getField('globalId', $con);

		if(!empty($globalId))
		{
			return $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/media/serve/' . $globalId;
		}

		return false;
	}
}

