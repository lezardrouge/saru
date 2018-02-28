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
 * The DaoCompanies class is used to get and manage companies
 *
 * @since	1.0
 * @author	Marie Kuntz / Lézard Rouge <mariek@lezard-rouge.fr>
 */

class DaoContactTypes
{

	/* instance de PDO */
	private $_pdo;

	/**
	 * constructor
	 */
	public function __construct()
	{
		$this->_pdo = myPDO::getInstance();
	}


	/**
	 * get the contact types
	 *
	 * @param array $criteria
	 * @param int $num
	 * @param int $limit
	 *
	 * @return array (total => number of results without the limit, results => array(objects ContactType) )
	 */
	public function getContactTypes($criteria = array(), $num = NB_RECORDS, $limit = 0)
	{
		$types = array();

		$sql = "SELECT SQL_CALC_FOUND_ROWS
				 contact_types.*
			FROM  contact_types ";
		//-----------------------------
		// filters
		//-----------------------------
		if ( ! empty($criteria)) {
			// add this so we dont wonder if there is already the WHERE clause
			$sql .= ' WHERE 1=1 ';
			// filter : active|not active|all
			if(isset($criteria['f_active'])) {
				$sql .= ' AND contact_type_active = ' . $criteria['f_active'];
			}
		}
		//-----------------------------
		// order
		//-----------------------------
		$sql .= " ORDER BY contact_type_name ";
		//-----------------------------
		// limit
		//-----------------------------
		if ($num != 0) {
			$sql .= " LIMIT " . $limit . ", " . $num;
		}

		$query = $this->_pdo->query($sql);

		$total_query = $this->_pdo->query('SELECT FOUND_ROWS() AS total');
		$total = $total_query->fetchAll(PDO::FETCH_COLUMN, 0);
		while($data = $query->fetch(PDO::FETCH_OBJ)) {
			$types[] = new ContactType($data);
		}
		$query->closeCursor();

		return array('total' => $total[0], 'results' => $types);
	}


	/**
	 * get a contact type by type_id
	 *
	 * @param int $type_id
	 * @return object ContactType
	 */
	public function getContactType($type_id)
	{
		$query = $this->_pdo->prepare("SELECT * FROM  contact_types WHERE contact_type_id = :id");
		$query_result = $query->execute(array('id' => $type_id));
		if( ! $query_result) {
			Utils::dump($query->errorInfo());
		}
		$contact_type = $query->fetch(PDO::FETCH_OBJ);
		$query->closeCursor();
		if( ! $contact_type) {
			return new ContactType(array());
		}
		return new ContactType($contact_type);
	}


	/**
	 * fetch the default type (must be active)
	 *
	 * @return object ContactType
	 */
	public function getDefaultContactType()
	{
		$sql = "SELECT * FROM  contact_types WHERE contact_type_default = 1 AND contact_type_active = 1";
		$query = $this->_pdo->query($sql);
		$contact_type = $query->fetch(PDO::FETCH_OBJ);
		$query->closeCursor();
		if( ! $contact_type) {
			return new ContactType(array());
		}
		return new ContactType($contact_type);
	}

}

/* End of file DaoContactTypes.class.php */
/* Location: ./classes/DaoContactTypes.class.php */