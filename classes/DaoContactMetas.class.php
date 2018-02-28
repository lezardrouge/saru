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
 * The DaoContactMetas class is used to get and manage contact metas
 *
 * @since	1.0
 * @author	Marie Kuntz / Lézard Rouge <mariek@lezard-rouge.fr>
 */

class DaoContactMetas
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
	public function getContactMetas($criteria = array(), $num = NB_RECORDS, $limit = 0)
	{
		$contact_metas = array();

		$sql = "SELECT SQL_CALC_FOUND_ROWS
				contact_metas.*
			FROM contact_metas ";
		//-----------------------------
		// filters
		//-----------------------------
		if ( ! empty($criteria)) {
			// add this so we dont wonder if there is already the WHERE clause
			$sql .= ' WHERE 1=1 ';
			// filter : meta active|not active|all
			if(isset($criteria['f_active'])) {
				$sql .= ' AND meta_active = ' . $criteria['f_active'];
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

		$query = $this->_pdo->query($sql);

		$total_query = $this->_pdo->query('SELECT FOUND_ROWS() AS total');
		$total = $total_query->fetchAll(PDO::FETCH_COLUMN, 0);
		while($data = $query->fetch(PDO::FETCH_OBJ)) {
			$contact_metas[] = new ContactMeta($data);
		}
		$query->closeCursor();

		return array('total' => $total[0], 'results' => $contact_metas);
	}


	/**
	 * get informations about a contact meta
	 *
	 * @param int $meta_id
	 *
	 * @return object ContactMeta
	 */
	public function getContactMeta($meta_id)
	{
		$query = $this->_pdo->prepare("SELECT * FROM contact_metas WHERE meta_id = :meta_id");
		$query_result = $query->execute(array('meta_id' => $meta_id));
		if( ! $query_result) {
			Utils::dump($query->errorInfo());
		}
		$contact_meta = $query->fetch(PDO::FETCH_OBJ);
		$query->closeCursor();
		if( ! $contact_meta) {
			return false;
		}
		return new ContactMeta($contact_meta);
	}


	/**
	 * check if contact meta exists by id
	 *
	 * @param int $meta_id
	 *
	 * @return boolean
	 */
	public function contactMetaExists($meta_id)
	{
		$query = $this->_pdo->prepare("SELECT COUNT(*) as total
			FROM contact_metas
			WHERE meta_id = :meta_id");
		$query->execute(array('meta_id' => $meta_id));
		$total = $query->fetchAll(PDO::FETCH_COLUMN, 0);
		if($total[0] == 0) {
			return false;
		}
		return true;
	}


	/**
	 * add a contact meta
	 *
	 * @param object ContactMeta
	 *
	 * @return object ContactMeta
	 */
	public function addContactMeta(ContactMeta $meta)
	{
		$query = $this->_pdo->prepare("INSERT INTO `contact_metas`
			SET `meta_name`  = :name,
				`meta_order` = :order,
				`meta_active` = 1");
		$query_result = $query->execute(array(
				'name'  => $meta->getName(),
				'order' => $meta->getOrder(),
			)
		);
		if( ! $query_result) {
			Utils::dump($query->errorInfo());
		}
		$meta_id = $this->_pdo->lastInsertId();
		$query->closeCursor();

		$meta->hydrate(array(
			'meta_id' => $meta_id
		));
		return $meta;
	}


	/**
	 * update a contact meta
	 *
	 * @param object ContactMeta
	 *
	 * @return object ContactMeta
	 */
	public function updateContactMeta(ContactMeta $meta)
	{
		$query = $this->_pdo->prepare("UPDATE `contact_metas`
			SET `meta_name`   = :name,
				`meta_order`  = :order,
				`meta_active` = :active
			WHERE meta_id = :id");
		$query_result = $query->execute(array(
				'name'   => $meta->getName(),
				'order'  => $meta->getOrder(),
				'active' => $meta->getActive(),
				'id'     => $meta->getId()
			)
		);
		if( ! $query_result) {
			Utils::dump($query->errorInfo());
		}
		$query->closeCursor();
		return $meta;
	}


	/**
	 * delete a contact meta
	 *
	 * @param int $meta_id
	 *
	 * @return void
	 */
	public function delContactMeta(ContactMeta $meta)
	{
		// delete meta content
		$relationshipDao = new DaoContactMetaRelationships();
		$relationshipDao->deleteContactMetasByMeta($meta->getId());
		// delete meta
		$query = $this->_pdo->prepare("DELETE FROM contact_metas WHERE meta_id = :id");
		$query_result = $query->execute(array('id' => $meta->getId()));
		if( ! $query_result) {
			Utils::dump($query->errorInfo());
		}
		$query->closeCursor();
	 }


}

/* End of file DaoContactMetas.class.php */
/* Location: ./classes/DaoContactMetas.class.php */