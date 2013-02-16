<?php
/*
 *  $Id: auth.php 875 2012-09-30 13:51:45Z k42b3.x@googlemail.com $
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
 * callback
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @package    application
 * @subpackage my
 * @version    $Revision: 875 $
 */
class auth extends Amun_Module_ApplicationAbstract
{
	private $apiId;
	private $userRights;

	public function onLoad()
	{
		parent::onLoad();

		// get oauth token
		$oauthToken = $this->get->oauth_token('string', array(new PSX_Filter_Length(40, 40), new PSX_Filter_Xdigit()));

		if($this->validate->hasError())
		{
			throw new Amun_Exception($this->validate->getLastError());
		}

		// check whether user is logged in if not redirect them to
		// the login form
		if($this->user->isAnonymous())
		{
			$self = $this->page->url . '/auth?oauth_token=' . $oauthToken;

			header('Location: ' . $this->page->url . '/login?redirect=' . urlencode($self));

			exit;
		}

		if($this->user->hasRight('my_view'))
		{
			// add path
			$this->path->add('Auth', $this->page->url . '/auth');

			try
			{
				if(!empty($oauthToken))
				{
					// check token
					$row = $this->getHandler('Oauth_Request')->getByToken($oauthToken);

					if(!empty($row))
					{
						$this->template->assign('token', $row['token']);

						// assign api id
						$this->apiId = $row['apiId'];

						// check expire
						$now  = new DateTime('NOW', $this->registry['core.default_timezone']);
						$date = new DateTime($row['date'], $this->registry['core.default_timezone']);
						$date->add(new DateInterval($row['expire']));

						if($now > $date)
						{
							$con = new PSX_Sql_Condition(array('token', '=', $oauthToken));

							Amun_Sql_Table_Registry::get('Oauth_Request')->delete($con);

							throw new Amun_Exception('The token is expired');
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
								throw new Amun_Exception('No valid callback was defined in the request');
							}
						}
					}
					else
					{
						throw new Amun_Exception('The consumer provide an invalid token');
					}

					// request consumer informations
					$this->getHandler('Oauth')->getById($this->apiId, array('url', 'title', 'description'));

					if(!empty($row))
					{
						$this->template->assign('consumerTitle', $row['title']);
						$this->template->assign('consumerDescription', $row['description']);
					}
					else
					{
						throw new Amun_Exception('Request is not assigned to an user');
					}

					// check whether access is already allowed
					if($this->getHandler('Oauth_Access')->isAllowed())
					{
						$this->allowAccess($token, $callback);
					}
				}
				else
				{
					throw new Amun_Exception('The consumer has not provide an valid token');
				}
			}
			catch(Exception $e)
			{
				$this->template->assign('error', $e->getMessage());
			}

			// template
			$this->htmlCss->add('my');

			$this->template->set(__CLASS__ . '.tpl');
		}
		else
		{
			throw new Amun_Exception('Access not allowed');
		}
	}

	public function onPost()
	{
		$token = $this->post->token('string', array(new PSX_Filter_Length(40, 40), new PSX_Filter_Xdigit()));

		if($token !== false)
		{
			$row = $this->getHandler('Oauth_Request')->getByToken($token);

			if(!empty($row))
			{
				if($row['status'] == AmunService_Oauth_Record::TEMPORARY)
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
					throw new Amun_Exception('Token has an invalid status');
				}
			}
			else
			{
				throw new Amun_Exception('Invalid user token');
			}
		}
		else
		{
			throw new amun_exception('Invalid token format');
		}
	}

	private function allowAccess($token, $callback)
	{
		// generate verifier
		$verifier = Amun_Security::generateToken(32);

		// insert or update access
		$now = new DateTime('NOW', $this->registry['core.default_timezone']);

		$this->sql->replace($this->registry['table.oauth_access'], array(

			'apiId'   => $this->apiId,
			'userId'  => $this->user->id,
			'allowed' => 1,
			'date'    => $now->format(PSX_DateTime::SQL),

		));

		$accessId = $this->sql->getLastInsertId();

		// insert rights
		$this->insertAppRights($accessId);

		// approve token
		$con = new PSX_Sql_Condition(array('token', '=', $token));

		$this->sql->update($this->registry['table.oauth_request'], array(

			'userId'   => $this->user->id,
			'status'   => AmunService_Oauth_Record::APPROVED,
			'verifier' => $verifier,

		), $con);

		// redirect if callback available
		if($callback != 'oob')
		{
			$url = new PSX_Url($callback);

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
			'userId'  => $this->user->id,
			'allowed' => 0,
			'date'    => $now->format(PSX_DateTime::SQL),

		));

		// delete token
		$con = new PSX_Sql_Condition(array('token', '=', $token));

		$this->sql->delete($this->registry['table.oauth_request'], $con);

		// redirect if callback available
		if($callback != 'oob')
		{
			$url = new PSX_Url($callback);

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
		$con = new PSX_Sql_Condition(array('accessId', '=', $accessId));

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
