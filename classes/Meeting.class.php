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
 * The meeting class is used to handle meetings with contacts
 *
 * @since	1.0
 * @author	Marie Kuntz / Lézard Rouge <mariek@lezard-rouge.fr>
 */

class Meeting
{

	/* private */
	private $_id;
	private $_date;
	private $_date_format;
	private $_comments;
	private $_type_id;
	private $_type_name;
	private $_contact_id;


	/* constants */
	/* none for now */

	/**
	 * constructor
	 *
	 * @param array $data, for hydration
	 */
	public function __construct($data)
	{
		$this->hydrate($data);
	}


	/* getters */
	public function getId ()
	{
		return $this->_id;
	}
	public function getDate ()
	{
		return $this->_date;
	}
	public function getDate_format ()
	{
		$date_format = Utils::date2Fr($this->_date);
		return $date_format;
	}
	public function getType_id ()
	{
		return $this->_type_id;
	}
	public function getType_name() {
		return $this->_type_name;
	}
	public function getComments ()
	{
		return $this->_comments;
	}
	public function getContact_id()
	{
		return $this->_contact_id;
	}
	/**
	 * fetch the related contact
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
	    }
	}
	public function setType_id ($type)
	{
		$this->_type_id = (int) $type;
	}
	public function setType_name($type_name)
	{
		$this->_type_name = $type_name;
	}
	public function setContact_id ($contact_id)
	{
		$this->_contact_id = (int) $contact_id;
	}
	public function setDate ($date)
	{
		if( ! empty($date) && Utils::checkDateValidity($date, 'iso')) {
			$this->_date = $date;
		}
	}
	/* TODO : chekc if really used : */
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


	/**
	 * object hydration
	 * pay attention : in DB, fields are prefixed, so you have to delete the prefix first
	 *
	 * @param object $data
	 */
	public function hydrate($data)
	{
		foreach ($data as $key => $value) {
			$method = 'set' . ucfirst(str_replace('meeting_', "", $key));
			if (method_exists($this, $method)) {
				$this->$method($value);
			}
		}
	}


}

/* End of file Meeting.class.php */
/* Location: ./classes/Meeting.class.php */