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
 * The user class is used to handle users of the CRM
 *
 * @since	1.0
 * @author	Marie Kuntz / Lézard Rouge <mariek@lezard-rouge.fr>
 */

class User
{

	/* private */
	private $_id;
	private $_lastname;
	private $_firstname;
	private $_login;
	private $_pwd;
	private $_email;
	private $_access_perms;
	private $_access_perm_ids;
	private $_account_perms;
	private $_account_perm_ids;


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
	public function getLastname ()
	{
		return $this->_lastname;
	}
	public function getFirstname ()
	{
		return $this->_firstname;
	}
	public function getLogin ()
	{
		return $this->_login;
	}
	public function getPwd ()
	{
		return $this->_pwd;
	}
	public function getEmail ()
	{
		return $this->_email;
	}
	public function getAccess_perms()
	{
		$dao_access = new Access();
		$this->_access_perms = $dao_access->getAccessByUser($this->_id);
		return $this->_access_perms;
	}
	public function getAccess_perm_ids()
	{
		return $this->_access_perm_ids;
	}
	public function getAccount_perms()
	{
		$access = new Access();
		$this->_account_perms = $access->getAccountsForUser($this->_id);
		return $this->_account_perms;
	}
	public function getAccount_perm_ids()
	{
		return $this->_account_perm_ids;
	}



	/* setters */
	public function setId ($id)
	{
		$id = (int) $id;
	    if ($id > 0) {
			$this->_id = $id;
	    }
	}
	public function setLastname ($lastname)
	{
		$this->_lastname = $lastname;
	}
	public function setFirstname ($firstname)
	{
		$this->_firstname = $firstname;
	}
	public function setLogin ($login)
	{
		$this->_login = $login;
	}
	public function setPwd ($pwd)
	{
		$this->_pwd = $pwd;
	}
	public function setEmail ($email)
	{
		$this->_email = $email;
	}
	public function setAccess_perms ($perms)
	{
		if(! is_array($perms)) {
			$perms = array($perms);
		}
		$this->_access_perms = $perms;
	}
	public function setAccess_perm_ids ($perms)
	{
		if(! is_array($perms)) {
			$perms = array($perms);
		}
		$this->_access_perm_ids = $perms;
	}
	public function setAccount_perms ($perms)
	{
		if(! is_array($perms)) {
			$perms = array($perms);
		}
		$this->_account_perms = $perms;
	}
	public function setAccount_perm_ids ($perms)
	{
		if(! is_array($perms)) {
			$perms = array($perms);
		}
		$this->_account_perm_ids = $perms;
	}


	/**
	 * object hydration
	 * please pay attention : in DB, fields are prefixed, so you have to del the prefix first
	 *
	 * @param object $data
	 */
	public function hydrate($data)
	{
		foreach ($data as $key => $value) {
			$method = 'set' . ucfirst(str_replace('user_', "", $key));
			if (method_exists($this, $method)) {
				$this->$method($value);
			}
		}
	}


}

/* End of file User.class.php */
/* Location: ./classes/User.class.php */