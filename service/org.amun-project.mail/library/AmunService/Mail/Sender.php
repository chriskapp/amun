<?php
/*
 *  $Id: Mail.php 635 2012-05-01 19:46:37Z k42b3.x@googlemail.com $
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
 * AmunService_Mail_Sender
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    AmunService_Mail
 * @version    $Revision: 635 $
 */
class AmunService_Mail_Sender implements Amun_Mail_SenderInterface
{
	private $config;
	private $sql;
	private $registry;

	public function __construct(Amun_Registry $registry)
	{
		$this->config   = $registry->getConfig();
		$this->sql      = $registry->getSql();
		$this->registry = $registry;
	}

	public function send($name, $email, array $values = array())
	{
		if(empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL))
		{
			// no valid email provided
			return;
		}

		$sql = <<<SQL
SELECT

	`mail`.`from`,
	`mail`.`subject`,
	`mail`.`text`,
	`mail`.`html`,
	`mail`.`values`

	FROM {$this->registry['table.mail']} `mail`

		WHERE `mail`.`name` = ?
SQL;

		$row = $this->sql->getRow($sql, array($name));

		if(!empty($row))
		{
			// check values
			$neededValues = array();

			if(!empty($row['values']))
			{
				$neededValues = explode(';', $row['values']);
			}

			$missingValues = array_diff($neededValues, array_keys($values));

			if(count($missingValues) > 0)
			{
				throw new Amun_Mail_Exception('Missing values "' . implode(', ', $missingValues) . '"" in ' . $name);
			}

			// send mail
			$mail = new Zend_Mail();
			$mail->setBodyText($this->substituteVars($row['text'], $values));
			$mail->setBodyHtml($this->substituteVars($row['html'], $values));
			$mail->setFrom($row['from']);
			$mail->addTo($email);
			$mail->setSubject($this->substituteVars($row['subject'], $values));
			$mail->send();
		}
		else
		{
			throw new Amun_Mail_Exception('Invalid mail template');
		}
	}

	private function substituteVars($content, array $values)
	{
		foreach($values as $k => $v)
		{
			$content = str_replace('{' . $k . '}', $v, $content);
		}

		return $content;
	}
}
