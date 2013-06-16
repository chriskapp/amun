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

namespace my\application;

use Amun\Module\ApplicationAbstract;
use Amun\Exception;
use Amun\DataFactory;
use Amun\Security;
use AmunService\Oauth;
use PSX\DateTime;
use PSX\Filter;
use PSX\Url;
use PSX\Sql\Condition;
use DateInterval;

/**
 * auth
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class auth extends ApplicationAbstract
{
	private $apiId;
	private $userRights;

	public function onLoad()
	{
		parent::onLoad();

		// get oauth token
		$oauthToken = $this->get->oauth_token('string', array(new Filter\Length(40, 40), new Filter\Xdigit()));

		if($this->validate->hasError())
		{
			throw new Exception($this->validate->getLastError());
		}

		// check whether user is logged in if not redirect them to
		// the login form
		if($this->user->isAnonymous())
		{
			$self = $this->page->getUrl() . '/auth?oauth_token=' . $oauthToken;

			header('Location: ' . $this->page->getUrl() . '/login?redirect=' . urlencode($self));

			exit;
		}

		if($this->user->hasRight('my_view'))
		{
			// add path
			$this->path->add('Auth', $this->page->getUrl() . '/auth');

			try
			{
				if(!empty($oauthToken))
				{
					// check token
					$row = $this->getHandler('Oauth_Request')->getOneByToken($oauthToken);

					if(!empty($row))
					{
						$this->template->assign('token', $row['token']);

						// assign api id
						$this->apiId = $row['apiId'];

						// check token status so if a token has access status we
						// can not access this page
						if(!in_array($row['status'], array(Oauth\Record::TEMPORARY, Oauth\Record::APPROVED)))
						{
							throw new Exception('The token was already approved');
						}

						// check expire
						$now  = new DateTime('NOW', $this->registry['core.default_timezone']);
						$date = new DateTime($row['date'], $this->registry['core.default_timezone']);
						$date->add(new DateInterval($row['expire']));

						if($now > $date)
						{
							$con = new Condition(array('token', '=', $oauthToken));

							$this->hm->getTable('Oauth_Request')->delete($con);

							throw new Exception('The token is expired');
						}

						// load user rights
						$this->userRights = $this->getHandler('User_Group_Right')->getByGroupId($this->user->groupId);

						$this->template->assign('userRights', $this->userRights);

						// assign token and callback for later use
						$token    = $row['token'];
						$callback = $row['callback'];

						// parse callback
						if($callback != 'oob')
						{
							$host = parse_url($row['callback'], PHP_URL_HOST);

							if(!empty($host))
							{
								$this->template->assign('consumerHost', $host);
							}
							else
							{
								throw new Exception('No valid callback was defined in the request');
							}
						}
					}
					else
					{
						throw new Exception('The consumer provide an invalid token');
					}

					// request consumer informations
					$row = $this->getHandler('Oauth')->getOneById($this->apiId, array('url', 'title', 'description'));

					if(!empty($row))
					{
						$this->template->assign('consumerTitle', $row['title']);
						$this->template->assign('consumerDescription', $row['description']);
					}
					else
					{
						throw new Exception('Request is not assigned to an user');
					}

					// check whether access is already allowed
					if($this->getHandler('Oauth_Access')->isAllowed($this->apiId, $this->user->getId()))
					{
						$this->allowAccess($token, $callback);
					}
				}
				else
				{
					throw new Exception('The consumer has not provide an valid token');
				}
			}
			catch(\Exception $e)
			{
				$this->template->assign('error', $e->getMessage());
			}

			// template
			$this->htmlCss->add('my');
		}
		else
		{
			throw new Exception('Access not allowed');
		}
	}

	public function onPost()
	{
		$token = $this->post->token('string', array(new Filter\Length(40, 40), new Filter\Xdigit()));

		if($token !== false)
		{
			$row = $this->getHandler('Oauth_Request')->getOneByToken($token);

			if(!empty($row))
			{
				if($row['status'] == Oauth\Record::TEMPORARY)
				{
					if(isset($_POST['allow']))
					{
						$this->allowAccess($row['token'], $row['callback']);
					}

					if(isset($_POST['deny']))
					{
						$this->denyAccess($row['token'], $row['callback']);
					}
				}
				else
				{
					throw new Exception('Token has an invalid status');
				}
			}
			else
			{
				throw new Exception('Invalid user token');
			}
		}
		else
		{
			throw new Exception('Invalid token format');
		}
	}

	private function allowAccess($token, $callback)
	{
		// generate verifier
		$verifier = Security::generateToken(32);

		// insert or update access
		$now = new DateTime('NOW', $this->registry['core.default_timezone']);

		$this->sql->replace($this->registry['table.oauth_access'], array(

			'apiId'   => $this->apiId,
			'userId'  => $this->user->getId(),
			'allowed' => 1,
			'date'    => $now->format(DateTime::SQL),

		));

		$accessId = $this->sql->getLastInsertId();

		// insert rights
		$this->insertAppRights($accessId);

		// approve token
		$con = new Condition(array('token', '=', $token));

		$this->sql->update($this->registry['table.oauth_request'], array(

			'userId'   => $this->user->getId(),
			'status'   => Oauth\Record::APPROVED,
			'verifier' => $verifier,

		), $con);

		// redirect if callback available
		if($callback != 'oob')
		{
			$url = new Url($callback);

			$url->addParam('oauth_token', $token);
			$url->addParam('oauth_verifier', $verifier);

			header('Location: ' . strval($url));

			exit;
		}
		else
		{
			$this->template->assign('verifier', $verifier);
		}
	}

	private function denyAccess($token, $callback)
	{
		// insert access
		$now = new DateTime('NOW', $this->registry['core.default_timezone']);

		$this->sql->replace($this->registry['table.oauth_access'], array(

			'apiId'   => $this->apiId,
			'userId'  => $this->user->getId(),
			'allowed' => 0,
			'date'    => $now->format(DateTime::SQL),

		));

		// delete token
		$con = new Condition(array('token', '=', $token));

		$this->sql->delete($this->registry['table.oauth_request'], $con);

		// redirect if callback available
		if($callback != 'oob')
		{
			$url = new Url($callback);

			// here we can inform the consumer that the request has been denied
			$url->addParam('oauth_token', $token);
			$url->addParam('x_oauth_error', 'request+denied');

			header('Location: ' . strval($url));
			exit;
		}
		else
		{
			header('Location: ' . $this->config['psx_url']);
			exit;
		}
	}

	/**
	 * Inserts the rights for the app. We write here an custom query instead of
	 * calling ->insert() because it is much faster
	 *
	 * @return void
	 */
	private function insertAppRights($accessId)
	{
		// delete any existing rights
		$con = new Condition(array('accessId', '=', $accessId));

		$this->sql->delete($this->registry['table.oauth_access_right'], $con);

		// insert rigts
		$rights = array();

		foreach($this->userRights as $right)
		{
			$key = 'right-' . $right['rightId'];
			$set = isset($_POST[$key]) ? (boolean) $_POST[$key] : false;

			if($set)
			{
				$accessId = (integer) $accessId;
				$rightId  = (integer) $right['rightId'];

				$rights[] = '(' . $accessId . ',' . $rightId . ')';
			}
		}

		if(!empty($rights))
		{
			$sql = implode(',', $rights);
			$sql = <<<SQL
INSERT INTO 
	{$this->registry['table.oauth_access_right']} (accessId, rightId)
VALUES
	{$sql}
SQL;

			$this->sql->query($sql);
		}
	}
}
