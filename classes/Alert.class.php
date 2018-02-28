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
 * The alert class is used to handle alerts
 *
 * @since	1.0
 * @author	Marie Kuntz / Lézard Rouge <mariek@lezard-rouge.fr>
 */

class Alert
{

	/* private */
	private $_id;
	private $_user_id;
	private $_contact_id;
	private $_priority;
	private $_date;
	private $_comments;
	private $_done;


	/* constants */
	/* none for now */

	/**
	 * constructor
	 *
	 * @param array $data, for hydration
	 */
	public function __construct($data = array())
	{
		$this->hydrate($data);
	}


	/* getters */
	public function getId ()
	{
		return $this->_id;
	}
	public function getUser_id ()
	{
		return $this->_user_id;
	}
	public function getUser()
	{
		$dao_user = new DaoUsers();
		$user = $dao_user->getUser($this->_user_id);
		return $user;
	}
	public function getContact_id ()
	{
		return $this->_contact_id;
	}
	public function getPriority ()
	{
		return $this->_priority;
	}
	public function getDate ()
	{
		return $this->_date;
	}
	public function getDate_format ()
	{
		return Utils::date2Fr($this->_date);
	}
	public function getComments ()
	{
		return $this->_comments;
	}
	public function getDone ()
	{
		return $this->_done;
	}
	/**
	 * fetch the contact
	 *
	 * @return object Contact
	 */
	public function getContact ()
	{
		$dao_contact = new DaoContacts();
		$contact = $dao_contact->getContact($this->_contact_id);
		return $contact;
	}


	/* setters */
	public function setId ($id)
	{
		$id = (int) $id;
	    if ($id > 0) {
			$this->_id = $id;
	    } else {
			$this->_id = 0;
		}
	}
	public function setUser_id ($user_id)
	{
		$this->_user_id = (int) $user_id;
	}
	public function setContact_id ($contact_id)
	{
		$this->_contact_id = (int) $contact_id;
	}
	public function setPriority ($priority)
	{
		$priority = (int) $priority;
		if ($priority > 0) {
			$this->_priority = $priority;
	    } else {
			$this->_priority = 0;
		}
	}
	public function setDate ($date)
	{
		if( ! empty($date) && Utils::checkDateValidity($date, 'iso')) {
			$this->_date = $date;
		}
	}
	/* TODO check if used : */
	public function setDateFormat($date)
	{
		if( ! empty($date) && Utils::checkDateValidity($date, 'fr')) {
			$this->_date_format = $date;
		}
	}
	/* end todo */
	public function setComments ($comment)
	{
		$this->_comments = $comment;
	}
	public function setDone ($done)
	{
		$done = (int) $done;
		if($done != 1) {
			$done = 0;
		}
		$this->_done = $done;
	}


	/**
	 * object hydration
	 * please pay attention : in DB, fields are prefixed, so you have to del the prefix first
	 *
	 * @param object $data
	 */
	public function hydrate($data = array())
	{
		foreach ($data as $key => $value) {
			$method = 'set' . ucfirst(str_replace('alert_', "", $key));
			if (method_exists($this, $method)) {
				$this->$method($value);
			}
		}
	}


	/**
	 * send alerts
	 *
	 */
	public function sendAlerts()
	{
		// get today's alerts
		$alerts = DaoAlerts::getAlertsForRoutine();
		if(empty($alerts)) {
			Utils::log("Pas d'alerte cette nuit");
		}
		// send them
		else {
			$email_class = new Email();
			if($email_class !== false) {
				foreach($alerts as $alert) {
					$email_class->sendAlert($alert);
				}
			}
		}

	}


}

/* End of file Alert.class.php */
/* Location: ./classes/Alert.class.php */