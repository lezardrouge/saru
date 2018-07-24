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
 * The contact type class is used to handle contact types
 *
 * @since	1.0
 * @author	Marie Kuntz / Lézard Rouge <mariek@lezard-rouge.fr>
 */

class ContactType
{

	/* private */
	private $_id;
	private $_name;
	private $_default;
	private $_active;


	/**
	 * constructor
	 *
	 * @param array $data for hydration
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
	public function getName ()
	{
		return $this->_name;
	}
	public function getDefault ()
	{
		return $this->_default;
	}
	public function getActive ()
	{
		return $this->_active;
	}



	/* setters */
	public function setId ($id)
	{
		$id = (int) $id;
	    if ($id > 0) {
	      $this->_id = $id;
	    }
	}
	public function setName ($name)
	{
		if( ! empty($name)) {
			$this->_name = $name;
		}
	}
	public function setDefault ($default)
	{
		$default = (int) $default;
	    if ($default != 1) {
			$default = 0;
	    }
		$this->_default = $default;
	}
	public function setActive ($active)
	{
		$active = (int) $active;
	    if ($active != 1) {
			$active = 0;
	    }
		$this->_active = $active;
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
			$method = 'set' . ucfirst(str_replace('contact_type_', "", $key));

			if (method_exists($this, $method)) {
				$this->$method($value);
			}
		}
	}


	/**
	 * fetch all types as an array
	 *
	 * @param bool $default if true add default option for select ; TRUE|FALSE ; default FALSE
	 *
	 * @return array $contact_types
	 */
	public function getContactTypesAsArray($default = FALSE)
	{
		$contact_types = array();
		$dao = new DaoContactTypes();
		$all_types = $dao->getContactTypes(array('f_active' => 1), 0);
		if($default) {
			$contact_types[0] = '--';
		}
		foreach($all_types['results'] as $type) {
			$contact_types[$type->getId()] = $type->getName();
		}
		return $contact_types;
	}

}

/* End of file ContactType.class.php */
/* Location: ./classes/ContactType.class.php */