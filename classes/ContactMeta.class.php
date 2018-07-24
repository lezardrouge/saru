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
 * The contact meta class is used to get and manage contact metas
 *
 * @since	1.0
 * @author	Marie Kuntz / Lézard Rouge <mariek@lezard-rouge.fr>
 */

class ContactMeta
{

	/* private */
	private $_id;
	private $_name;
	private $_order;
	private $_active;

	/**
	 * constructor
	 *
	 * @param array $data for hydration
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
	public function getName ()
	{
		return $this->_name;
	}
	public function getOrder ()
	{
		return $this->_order;
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
		if(! empty($name)) {
			$this->_name = $name;
		}
	}
	public function setOrder ($order)
	{
		$order = (int) $order;
		$this->_order = $order;
	}
	public function setActive ($active)
	{
		$active = (int) $active;
		if($active != 1) {
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
			$method = 'set' . ucfirst(str_replace('meta_', "", $key));

			if (method_exists($this, $method)) {
				$this->$method($value);
			}
		}
	}

}
/* End of file ContactMeta.class.php */
/* Location: ./classes/ContactMeta.class.php */