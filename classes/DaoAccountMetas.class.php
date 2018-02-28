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
 * The DaoAccountMetas class is used to get and manage account metas
 *
 * @since	1.0
 * @author	Marie Kuntz / Lézard Rouge <mariek@lezard-rouge.fr>
 */

class DaoAccountMetas
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
	 * fetch contacts metas
	 *
	 * @param array $criteria
	 * @param int $num
	 * @param int $limit
	 *
	 * @return array
	 */
	public function getAccountMetas($criteria = array(), $num = NB_RECORDS, $limit = 0)
	{
		$account_metas = array();

		$sql = "SELECT SQL_CALC_FOUND_ROWS
				account_metas.*
			FROM account_metas ";
		//-----------------------------
		// filters
		//-----------------------------
		$conditions = array();
		if ( ! empty($criteria)) {
			// add this so we dont wonder if there is already the WHERE clause
			$sql .= ' WHERE 1=1 ';
			// filter : meta active|not active|all
			if(isset($criteria['f_active'])) {
				$sql .= ' AND meta_active = :active';
				$conditions['active'] = $criteria['f_active'];
			}
		}
		//-----------------------------
		// order
		//-----------------------------
		$sql .= " ORDER BY meta_order ";
		//-----------------------------
		// limit
		//-----------------------------
		if ($num != 0) {
			$sql .= " LIMIT " . $limit . ", " . $num;
		}

		$query = $this->_pdo->prepare($sql);
		$query->execute($conditions);

		$total_query = $this->_pdo->query('SELECT FOUND_ROWS() AS total');
		$total = $total_query->fetchAll(PDO::FETCH_COLUMN, 0);
		while($data = $query->fetch(PDO::FETCH_OBJ)) {
			$account_metas[] = new ContactMeta($data);
		}
		$query->closeCursor();

		return array('total' => $total[0], 'results' => $account_metas);
	}



}

/* End of file DaoAccountMetas.class.php */
/* Location: ./classes/DaoAccountMetas.class.php */