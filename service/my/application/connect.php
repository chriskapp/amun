<?php
/*
 *  $Id: connect.php 875 2012-09-30 13:51:45Z k42b3.x@googlemail.com $
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
 * connect
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @package    application
 * @subpackage my
 * @version    $Revision: 875 $
 */
class connect extends Amun_Module_ApplicationAbstract
{
	private $request;
	private $assoc;

	private $claimedId;
	private $identity;
	private $assocHandle;
	private $returnTo;
	private $sreg;
	private $oauth;

	public function onLoad()
	{
		if($this->user->hasRight('service_my_view'))
		{
			// add path
			$this->path->add('Connect', $this->page->url . '/connect');


			// check whether connection was started
			$this->request = isset($_SESSION['amun_openid_request']) ? $_SESSION['amun_openid_request'] : null;

			if($this->request instanceof PSX_OpenId_Provider_Data_SetupRequest)
			{
				// sreg extension
				$sreg     = null;
				$params   = $this->request->getExtension(PSX_OpenId_Extension_Sreg::NS);
				$required = isset($params['required']) ? explode(',', $params['required']) : array();
				$optional = isset($params['optional']) ? explode(',', $params['optional']) : array();
				$fields   = array_merge($required, $optional);

				if(!empty($fields))
				{
					$sreg = PSX_OpenId_Extension_Sreg::validateFields($fields);
				}

				// oauth extension
				$oauth    = null;
				$params   = $this->request->getExtension(PSX_OpenId_Extension_Oauth::NS);
				$consumer = isset($params['consumer']) ? $params['consumer'] : null;

				if(!empty($consumer))
				{
					$oauth = array('consumer' => $consumer);
				}

				$this->claimedId   = $this->request->getClaimedId();
				$this->identity    = $this->request->getIdentity();
				$this->assocHandle = $this->request->getAssocHandle();
				$this->returnTo    = $this->request->getReturnTo();
				$this->sreg        = $sreg;
				$this->oauth       = $oauth;

				$this->template->assign('rpData', array_intersect_key($this->getAvailableSregExtFields(), array_flip($sreg)));
				$this->template->assign('rpHost', $this->returnTo->getHost());
			}
			else
			{
				throw new Amun_Exception('No connection was initialized');
			}

			// get association
			$this->assoc = $this->getAssociation();

			// check whether access is already allowed or denied
			$status = (integer) Amun_Sql_Table_Registry::get('System_Connect')
				->select(array('status'))
				->where('userId', '=', $this->user->id)
				->where('assocId', '=', $this->assoc['id'])
				->getField();

			if($status === Amun_System_Connect::APPROVED)
			{
				$this->allowAccess();
			}

			if($status === Amun_System_Connect::DENIED)
			{
				$this->denyAccess();
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
		try
		{
			if(isset($_POST['allow']))
			{
				$this->allowAccess();
			}

			if(isset($_POST['deny']))
			{
				$this->denyAccess();
			}
		}
		catch(Exception $e)
		{
			// cancel request
			$this->returnTo->addParam('openid.ns', PSX_OpenId_ProviderAbstract::NS);
			$this->returnTo->addParam('openid.mode', 'error');
			$this->returnTo->addParam('openid.error', $e->getMessage());

			header('Location: ' . strval($this->returnTo));
			exit;
		}
	}

	private function allowAccess()
	{
		// delete session
		$_SESSION['amun_openid_request'] = null;


		// build redirect
		$nonce = gmdate('Y-m-d\TH:i:s\Z') . Amun_Security::generateToken(15);

		$redirect = new PSX_OpenId_Provider_Data_Redirect();
		$redirect->setOpEndpoint($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/service/my/signon');
		$redirect->setClaimedId($this->claimedId);
		$redirect->setIdentity($this->identity);
		$redirect->setReturnTo($this->returnTo);
		$redirect->setResponseNonce($nonce);
		$redirect->setAssocHandle($this->assoc['assocHandle']);
		$redirect->setParams($this->request->getParams());

		if(!empty($this->sreg))
		{
			$redirect->addExtension($this->handleSregExt());
		}

		if(!empty($this->oauth))
		{
			$redirect->addExtension($this->handleOauthExt());
		}


		// insert or update connect
		$now  = new DateTime('NOW', $this->registry['core.default_timezone']);
		$data = array(

			'userId'        => $this->user->id,
			'assocId'       => $this->assoc['id'],
			'claimedId'     => $this->claimedId,
			'identity'      => $this->identity,
			'returnTo'      => (string) $this->returnTo,
			'responseNonce' => $nonce,
			'date'          => $now->format(PSX_Time::SQL),

		);

		if(isset($_POST['remember']) && $_POST['remember'] === '1')
		{
			$data['status'] = Amun_System_Connect::APPROVED;
		}

		Amun_Sql_Table_Registry::get('System_Connect')->replace($data);


		// redirect to rp
		$redirect->redirect($this->assoc['secret'], $this->assoc['assocType']);
	}

	private function denyAccess()
	{
		// delete session
		$_SESSION['amun_openid_request'] = null;


		// delete oauth token
		if(!empty($this->oauth))
		{
			$con = new PSX_Sql_Condition(array('id', '=', $this->oauth['requestId']));

			$this->sql->delete($this->registry['table.system_api_request'], $con);
		}


		// insert or update connect
		$nonce = gmdate('Y-m-d\TH:i:s\Z') . Amun_Security::generateToken(15);
		$now   = new DateTime('NOW', $this->registry['core.default_timezone']);
		$data  = array(

			'userId'        => $this->user->id,
			'assocId'       => $this->assoc['id'],
			'claimedId'     => $this->claimedId,
			'identity'      => $this->identity,
			'returnTo'      => (string) $this->returnTo,
			'responseNonce' => $nonce,
			'date'          => $now->format(PSX_Time::SQL),

		);

		if(isset($_POST['remember']) && $_POST['remember'] === '1')
		{
			$data['status'] = Amun_System_Connect::DENIED;
		}

		Amun_Sql_Table_Registry::get('System_Connect')->replace($data);


		// cancel request
		$this->returnTo->addParam('openid.ns', PSX_OpenId_ProviderAbstract::NS);
		$this->returnTo->addParam('openid.mode', 'cancel');

		header('Location: ' . strval($this->returnTo));
		exit;
	}

	private function getAssociation()
	{
		if(!empty($this->assocHandle))
		{
			$row = Amun_Sql_Table_Registry::get('System_Connect_Assoc')
				->select(array('id', 'assocHandle', 'assocType', 'sessionType', 'secret', 'expires', 'date'))
				->where('assocHandle', '=', $this->assocHandle)
				->getRow();

			if(!empty($row))
			{
				return $row;
			}
			else
			{
				throw new Amun_Exception('Invalid association');
			}
		}
		else
		{
			throw new Amun_Exception('Assoc handle not set');
		}
	}

	private function handleSregExt()
	{
		$params = array();
		$params['openid.ns.sreg'] = PSX_OpenId_Extension_Sreg::NS;

		$fields = $this->getAvailableSregExtFields();
		$keys   = array_intersect(array_keys($fields), $this->sreg);

		foreach($keys as $k)
		{
			$params['openid.sreg.' . $k] = $fields[$k];
		}

		return $params;
	}

	private function getAvailableSregExtFields()
	{
		$fields = array();
		$fields['nickname'] = $this->user->name;

		if(!empty($this->user->email))
		{
			$fields['email'] = $this->user->email;
		}

		if(!empty($this->user->gender) && $this->user->gender != 'undisclosed')
		{
			$fields['gender'] = strtoupper(substr($this->user->gender, 0, 1));
		}

		if($this->user->timezone instanceof DateTimeZone)
		{
			$fields['timezone'] = $this->user->timezone->getName();
		}

		return $fields;
	}

	private function handleOauthExt()
	{
		$consumerKey = isset($this->oauth['consumer']) ? $this->oauth['consumer'] : null;

		$row = Amun_Sql_Table_Registry::get('System_Api')
			->select(array('id', 'consumerKey'))
			->where('consumerKey', '=', $consumerKey)
			->getRow();

		if(!empty($row))
		{
			$token    = Amun_Security::generateToken(40);
			$verifier = Amun_Security::generateToken(32);
			$date     = new DateTime('NOW', $this->registry['core.default_timezone']);

			$this->sql->insert($this->registry['table.system_api_request'], array(

				'apiId'       => $row['id'],
				'userId'      => $this->user->id,
				'status'      => Amun_System_Api::APPROVED,
				'ip'          => $_SERVER['REMOTE_ADDR'],
				'nonce'       => Amun_Security::generateToken(16),
				'callback'    => 'oob',
				'token'       => $token,
				'tokenSecret' => '',
				'verifier'    => $verifier,
				'timestamp'   => time(),
				'expire'      => 'PT30M',
				'date'        => $date->format(PSX_Time::SQL),

			));


			// insert access
			$this->sql->replace($this->registry['table.system_api_access'], array(

				'apiId'   => $row['id'],
				'userId'  => $this->user->id,
				'allowed' => 1,
				'date'    => $date->format(PSX_Time::SQL),

			));


			// return params
			$params = array();
			$params['openid.ns.oauth'] = PSX_OpenId_Extension_Oauth::NS;
			$params['openid.oauth.request_token'] = $token;
			$params['openid.oauth.verifier'] = $verifier;

			return $params;
		}
		else
		{
			throw new Amun_Exception('Invalid consumer');
		}
	}
}

