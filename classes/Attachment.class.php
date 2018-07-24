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
 * The attachment class is used to handle contact,s companies & meetings attachments
 *
 * @since	1.4
 * @author	Marie Kuntz / Lézard Rouge <mariek@lezard-rouge.fr>
 */

class Attachment
{

	/* private */
	private $_id;
	private $_account_id;
	private $_type_item;
	private $_item_id;
	private $_real_name;
	private $_intern_name;


	/* constants */
	/* none for now */


	/**
	 * constructor
	 *
	 * @param array $data for hydration
	 */
	public function __construct($data = array())
	{
		if( ! empty($data)) {
			$this->hydrate($data);
		}
	}


	/* getters */
	public function getId ()
	{
		return $this->_id;
	}
	public function getAccount_id ()
	{
		return $this->_account_id;
	}
	public function getType_item ()
	{
		return $this->_type_item;
	}
	public function getItem_id ()
	{
		return $this->_item_id;
	}
	public function getReal_name ()
	{
		return $this->_real_name;
	}
	public function getIntern_name ()
	{
		return $this->_intern_name;
	}


	/* setters */
	public function setId ($id)
	{
		$id = (int) $id;
	    $this->_id = $id;
	}
	public function setAccount_id ($account_id)
	{
		$this->_account_id = $account_id;
	}
	public function setType_item ($type_item)
	{
		$this->_type_item = $type_item;
	}
	public function setItem_id ($item_id)
	{
		$this->_item_id = $item_id;
	}
	public function setReal_name ($name)
	{
		$this->_real_name = $name;
	}
	public function setIntern_name ($name)
	{
		$this->_intern_name = $name;
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
			$method = 'set' . ucfirst(str_replace('attachment_', "", $key));

			if (method_exists($this, $method)) {
				$this->$method($value);
			}
		}
	}


}

/* End of file Attachment.class.php */
/* Location: ./classes/Attachment.class.php */