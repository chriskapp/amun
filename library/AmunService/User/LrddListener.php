<?php
/*
 *  $Id: Log.php 635 2012-05-01 19:46:37Z k42b3.x@googlemail.com $
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
 * AmunService_Xrds_Listener
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    AmunService_Xrds
 * @version    $Revision: 635 $
 */
class AmunService_User_LrddListener extends Amun_Module_ListenerAbstract
{
	public function notify(XMLWriter $writer, $uri)
	{
		$account = $this->getAccount($uri);

		if($account instanceof AmunService_User_Account_Record)
		{
			// subject
			$writer->writeElement('Subject', $uri);

			// alias
			$writer->writeElement('Alias', $account->profileUrl);

			// id
			$writer->startElement('Property');
			$writer->writeAttribute('type', 'http://ns.amun-project.org/2011/meta/id');
			$writer->text($account->globalId);
			$writer->endElement();

			// name
			$writer->startElement('Property');
			$writer->writeAttribute('type', 'http://ns.amun-project.org/2011/meta/name');
			$writer->text($account->name);
			$writer->endElement();

			// timezone
			$writer->startElement('Property');
			$writer->writeAttribute('type', 'http://ns.amun-project.org/2011/meta/timezone');
			$writer->text($account->timezone);
			$writer->endElement();

			// date
			$writer->startElement('Property');
			$writer->writeAttribute('type', 'http://ns.amun-project.org/2011/meta/date');
			$writer->text($account->getDate()->format(DateTime::ATOM));
			$writer->endElement();

			// profile
			$writer->startElement('Link');
			$writer->writeAttribute('rel', 'profile');
			$writer->writeAttribute('type', 'text/html');
			$writer->writeAttribute('href', $account->profileUrl);
			$writer->endElement();


			if($this->registry->hasService('my'))
			{
				// activity atom feed
				$href = $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/my/activity/' . $account->name . '?format=atom';

				$writer->startElement('Link');
				$writer->writeAttribute('rel', 'alternate');
				$writer->writeAttribute('type', 'application/atom+xml');
				$writer->writeAttribute('href', $href);
				$writer->endElement();

				// json activity streams
				$href = $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/my/activity/' . $account->name . '?format=json';

				$writer->startElement('Link');
				$writer->writeAttribute('rel', 'alternate');
				$writer->writeAttribute('type', 'application/stream+json');
				$writer->writeAttribute('href', $href);
				$writer->endElement();

				// ostatus subcribe
				$template = $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/my/subscription?topic={uri}';

				$writer->startElement('Link');
				$writer->writeAttribute('rel', 'http://ostatus.org/schema/1.0/subscribe');
				$writer->writeAttribute('template', $template);
				$writer->endElement();
			}
		}
	}

	protected function getAccount($uri)
	{
		if(substr($uri, 0, 5) == 'acct:')
		{
			$filter = new PSX_Filter_Email();
			$email  = substr($uri, 5);

			if($filter->apply($email) === true)
			{
				// split mail
				list($name, $host) = explode('@', $email);

				// get account record
				$account = Amun_Sql_Table_Registry::get('User_Account')
					->select(array('id', 'globalId', 'name', 'profileUrl', 'timezone', 'date'))
					->where('name', '=', $name)
					->getRow(PSX_Sql::FETCH_OBJECT);

				return $account;
			}
		}
	}
}

