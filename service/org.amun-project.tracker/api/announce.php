<?php
/*
 *  $Id: announce.php 875 2012-09-30 13:51:45Z k42b3.x@googlemail.com $
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

namespace tracker\api;

use Amun_Module_DefaultAbstract;
use Amun_Sql_Table_Registry;
use DateInterval;
use DateTime;
use PSX_Data_Exception;
use PSX_Data_Message;
use PSX_Filter_InArray;
use PSX_Filter_Ip;
use PSX_Filter_Length;
use PSX_Sql;
use PSX_Sql_Condition;
use PSX_DateTime;
use PSX_Util_Bencoding;

/**
 * Torrent announce endpoint. Here an example request from the transmission
 * torrent client
 * <code>
 * array (
 *   'info_hash' => ':;Ç³Ï«<9c>ÐÃ^Nøv<84>g<9e>^GRëf<84>',
 *   'peer_id' => '-TR2030-41pf6xz7mnkp',
 *   'port' => '51413',
 *   'uploaded' => '0',
 *   'downloaded' => '0',
 *   'left' => '989392',
 *   'numwant' => '80',
 *   'key' => 'n9ggcuv2',
 *   'compact' => '1',
 *   'supportcrypto' => '1',
 *   'event' => 'started',
 * )
 * </code>
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @package    admin
 * @subpackage service_tracker
 * @version    $Revision: 875 $
 */
class announce extends Amun_Module_DefaultAbstract
{
	const REPORT_INTERVAL = 60;
	const MIN_INTERVAL    = 20;

	public function onGet()
	{
		$infoHash   = $this->get->info_hash('string', array(new PSX_Filter_Length(3, 256)), 'infoHash', 'Info hash');
		$peerId     = $this->get->peer_id('string', array(new PSX_Filter_Length(3, 256)), 'peerId', 'Peer id');
		$ip         = $this->get->ip('string', array(new PSX_Filter_Ip()), 'ip', 'Ip', false);
		$port       = $this->get->port('integer', array(), 'port', 'Port');
		$uploaded   = $this->get->uploaded('integer', array(), 'uploaded', 'Uploaded');
		$downloaded = $this->get->downloaded('integer', array(), 'downloaded', 'Downloaded');
		$left       = $this->get->left('integer', array(), 'left', 'Left');
		$event      = $this->get->event('string', array(new PSX_Filter_InArray(array('started', 'stopped', 'completed'))), 'event', 'Event', false);
		$compact    = $this->get->compact('integer', array(), 'compact', 'Compact', false);
		$noPeerId   = $this->get->no_peer_id('integer', array(), 'noPeerId', 'No peer id', false);
		$numwant    = $this->get->numwant('integer', array(new PSX_Filter_Length(0, 128)), 'numwant', 'Numwant', false, 48);

		if(!$this->validate->hasError())
		{
			// decode info hash and peer id
			$infoHash = bin2hex($infoHash);
			$peerId   = bin2hex($peerId);

			// validate
			if(strlen($infoHash) != 40)
			{
				throw new PSX_Data_Exception('Invalid hash');
			}

			if(empty($port))
			{
				throw new PSX_Data_Exception('Invalid port');
			}

			// check whether hash exists
			$con     = new PSX_Sql_Condition(array('infoHash', '=', $infoHash));
			$torrent = Amun_Sql_Table_Registry::get('Tracker')->getRow(array('id', 'completed'), $con);

			if(empty($torrent))
			{
				throw new PSX_Data_Exception('Torrent is not listed on this server');
			}

			// needed values
			$ip        = empty($ip) ? $_SERVER['REMOTE_ADDR'] : $ip;
			$completed = $torrent['completed'];

			// update peers
			$date = new DateTime('NOW', $this->registry['core.default_timezone']);
			$data = array(

				'infoHash'   => $infoHash,
				'peerId'     => $peerId,
				'ip'         => $ip,
				'port'       => $port,
				'uploaded'   => $uploaded,
				'downloaded' => $downloaded,
				'left'       => $left,
				'event'      => strtoupper($event),
				'status'     => $left == 0 ? 'SEEDER' : 'LEECHER',
				'client'     => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Unknown',
				'date'       => $date->format(PSX_DateTime::SQL),

			);

			switch($event)
			{
				case 'stopped':
					$this->deletePeer($data);
					break;

				case 'completed':
					$completed = $completed + 1;

				case 'started':
				default:
					$this->insertPeer($data);
					break;
			}

			// get peers
			$peerList = $this->getPeerList($infoHash, $ip, $numwant, $compact);

			echo PSX_Util_Bencoding::encode($peerList);

			// update torrent
			$con = new PSX_Sql_Condition(array('id', '=', $torrent['id']));

			$this->sql->update($this->registry['table.tracker'], array(

				'seeder'    => $peerList['complete'],
				'leecher'   => $peerList['incomplete'],
				'completed' => $completed,

			), $con);

			// kill lost peers
			$this->killLostPeers($infoHash);
		}
		else
		{
			if($this->config['psx_debug'] === true)
			{
				throw new PSX_Data_Exception(implode(', ', $this->validate->getError()));
			}
			else
			{
				throw new PSX_Data_Exception('Invalid request');
			}
		}
	}

	public function onPost()
	{
		$msg = new PSX_Data_Message('Method not allowed', false);

		$this->setResponse($msg, null, 405);
	}

	protected function insertPeer(array $data)
	{
		$this->sql->replace($this->registry['table.tracker_peer'], $data);
	}

	protected function deletePeer($id)
	{
		$con = new PSX_Sql_Condition($this->registry['table.tracker_peer'], array('id', '=', $id));

		$this->sql->delete($con);
	}

	protected function getPeerList($infoHash, $ip, $numwant, $compact)
	{
		$con   = new PSX_Sql_Condition();
		$con->add('infoHash', '=', $infoHash);
		$con->add('ip', '!=', $ip);

		$result  = Amun_Sql_Table_Registry::get('Tracker_Peer')->getAll(array('peer_id', 'ip', 'port', 'status'), $con, 'priority', PSX_Sql::SORT_DESC, 0, $numwant);
		$peers   = array();
		$seeder  = 0;
		$leecher = 0;

		foreach($result as $row)
		{
			// add peer
			$peer = array();

			if($noPeerId !== 1)
			{
				$peer['peer id'] = (string) $row['peer_id'];
			}

			$peer['ip'] = (string) $row['ip'];
			$peer['port'] = (integer) $row['port'];

			$peers[] = $peer;

			// get seeder leecher count
			if($row['status'] == 'SEEDER')
			{
				$seeder = $seeder + 1;
			}

			if($row['status'] == 'LEECHER')
			{
				$leecher = $leecher + 1;
			}
		}

		$peerList = array(

			'interval'     => self::REPORT_INTERVAL,
			'min interval' => self::MIN_INTERVAL,
			'complete'     => $seeder,
			'incomplete'   => $leecher,
			'peers'        => $peers,

		);

		if($compact === 'not-implemented')
		{
			$peers = '';

			foreach($peerList['peers'] as $peer)
			{
				$peers.= pack('Nn', ip2long($peer['ip']), $peer['port']);
			}

			$peerList['peers'] = $peers;
		}

		return $peerList;
	}

	/**
	 * We tell the clients in the peer list to report every REPORT_INTERVAL
	 * seconds if they have not notified us in the double amount of time we kill
	 * the peer
	 */
	protected function killLostPeers($infoHash)
	{
		$date = new DateTime('NOW', $this->registry['core.default_timezone']);
		$date->sub(new DateInterval('PT' . (self::REPORT_INTERVAL * 2) . 'S'));

		$con = new PSX_Sql_Condition();
		$con->add('infoHash', '=', $infoHash);
		$con->add('date', '<', $date->format(PSX_DateTime::SQL));

		Amun_Sql_Table_Registry::get('Tracker_Peer')->delete($con);
	}
}

