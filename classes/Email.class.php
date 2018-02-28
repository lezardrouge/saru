<?php
/**
 * SARU
 * organize your contacts
 *
 * Copyright (c) 2012-2018 Marie Kuntz - Lezard Rouge
 *
 * This file is part of SARU.
 * SARU is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * SARU is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.

 * You should have received a copy of the GNU Affero General Public License
 * along with SARU. If not, see <http://www.gnu.org/licenses/>.
 * See LICENSE.TXT file for more information.
 *
 * Saru is released under dual license, AGPL and commercial license.
 * If you need a commercial license or if you don't know which licence you need,
 * please contact us at <info@saru.fr>
 *
 */

/**
 * email functions
 *
 * @since	1.0
 * @author	Marie Kuntz / Lézard Rouge <mariek@lezard-rouge.fr>
 */

class Email
{

	/* private */
	private $_from_name;
	private $_from_email;


	public function __construct()
	{
		if( ! function_exists('proc_open')) {
			Utils::log("proc_* functions are not available. No email can be sent.");
			return false;
		}
		// init swiftMailer
		require_once 'swiftmailer/swift_required.php';
		$this->_from_name = EMAIL_FROM_NAME;
		$this->_from_email = EMAIL_FROM_EMAIL;
	}


	/**
	 * format a mail to send an alert
	 *
	 * @param object Alert $alert
	 */
	public function sendAlert($alert)
	{
		$recipient = array($alert->user_email => $alert->user_firstname . ' ' . $alert->user_lastname);
		// formating the body message
		$who = '';
		if(! empty($alert->contact_lastname)
			|| ! empty($alert->contact_firstname)
			|| ! empty($alert->company_name)) {
			$contact_fullname = $alert->contact_lastname . ' ' . $alert->contact_firstname;
			if( ! empty($alert->company_name)) {
				$contact_fullname .= ' (' . $alert->company_name . ')';
			}
			$who = " vous devez contacter " . Utils::displayEscapedString($contact_fullname);
		}
		$body = 'Rappel : le ' . Utils::date2Fr($alert->alert_date)
				. $who
				. ' au sujet de : ' . Utils::displayEscapedString($alert->alert_comments);
		$from = array($this->_from_email => $this->_from_name);
		$subject = 'Rappel de SARU';
		$priority = 3;
		if($alert->alert_priority == 1) {
			$subject .= " - Important";
			$priority = 2;
		}
		$this->sendMail($recipient, $from, $subject, $body, $priority);
	}


	/**
	 * send a mail
	 *
	 * @param array $recipients
	 * @param array $from
	 * @param string $subject
	 * @param string $body
	 * @param int $priority, priority sending
	 */
	private function sendMail($recipients, $from, $subject, $body, $priority = 3)
	{
		$transport = $this->selectTransport();
		$mailer = Swift_Mailer::newInstance($transport);

		$message = Swift_Message::newInstance()
			->setSubject($subject)
			->setFrom($from)
			->setTo($recipients)
			->setBody($body, 'text/html')
			->setPriority($priority);

		$result = $mailer->send($message, $failures);
		array_unshift($failures, "email sending results");
		array_unshift($failures, "results => " . $result);
		Utils::log($failures);
	}


	/**
	 * create swift transport
	 *
	 * @return object $transport
	 */
	private function selectTransport()
	{
		if(EMAIL_TRANSPORT_TYPE == 'smtp') {
			$transport = Swift_SmtpTransport::newInstance(EMAIL_SMTP_SERVER, EMAIL_SMTP_PORT)
						->setUsername(EMAIL_SMTP_USER)
						->setPassword(EMAIL_SMTP_PWD);
		} else {
			$transport = Swift_MailTransport::newInstance();
		}
		return $transport;
	}

}
?>