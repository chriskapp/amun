<?php
/*
 *  $Id: download.php 875 2012-09-30 13:51:45Z k42b3.x@googlemail.com $
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
 * download
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @package    application
 * @subpackage tracker
 * @version    $Revision: 875 $
 */
class download extends Amun_Module_ApplicationAbstract
{
	/**
	 * @httpMethod GET
	 * @path /
	 */
	public function doDownload()
	{
		parent::onLoad();

		if($this->user->hasRight('tracker_view'))
		{
			// load tracker
			$resultTracker = $this->getTracker();

			$this->template->assign('resultTracker', $resultTracker);

			// download
			$path = $this->registry['tracker.upload_path'] . '/' . $resultTracker->torrent;

			if(is_file($path))
			{
				header('Content-Type: application/octet-stream');
				header('Content-Disposition: attachment; filename=' . $resultTracker->name . '.torrent');

				echo file_get_contents($path);
				exit;
			}
			else
			{
				throw new Amun_Exception('File not found', 404);
			}
		}
		else
		{
			throw new Amun_Exception('Access not allowed');
		}
	}

	private function getTracker()
	{
		// get id
		$fragments = $this->getUriFragments();
		$id = isset($fragments[0]) ? intval($fragments[0]) : $this->get->id('integer');

		$result = Amun_Sql_Table_Registry::get('Tracker')
			->select(array('id', 'urlTitle', 'title', 'urlTitle', 'name', 'torrent', 'date'))
			->where('id', '=', $id)
			->getRow(PSX_Sql::FETCH_OBJECT);

		$this->trackerId = $result->id;

		return $result;
	}
}

