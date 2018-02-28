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
 * The DaoCompanyMetas class is used to get and manage company metas
 *
 * @since	1.0
 * @author	Marie Kuntz / Lézard Rouge <mariek@lezard-rouge.fr>
 */

class DaoCompanyMetas
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
	 * fetch company metas
	 *
	 * @param array $criteria
	 * @param int $num
	 * @param int $limit
	 *
	 * @return array
	 */
	public function getCompanyMetas($criteria = array(), $num = NB_RECORDS, $limit = 0)
	{
		$company_metas = array();

		$sql = "SELECT SQL_CALC_FOUND_ROWS
				company_metas.*
			FROM company_metas ";
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
				$conditions['active'] =  $criteria['f_active'];
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
			$company_metas[] = new CompanyMeta($data);
		}
		$query->closeCursor();

		return array('total' => $total[0], 'results' => $company_metas);
	}


	/**
	 * get informations about a company meta
	 *
	 * @param int $meta_id
	 *
	 * @return object CompanyMeta
	 */
	public function getCompanyMeta($meta_id)
	{
		$query = $this->_pdo->prepare("SELECT * FROM company_metas WHERE meta_id = :meta_id");
		$query_result = $query->execute(array('meta_id' => $meta_id));
		if( ! $query_result) {
			Utils::dump($query->errorInfo());
		}
		$company_meta = $query->fetch(PDO::FETCH_OBJ);
		$query->closeCursor();
		if( ! $company_meta) {
			return false;
		}
		return new CompanyMeta($company_meta);
	}


	/**
	 * check if company meta exists by id
	 *
	 * @param int $meta_id
	 *
	 * @return boolean
	 */
	public function companyMetaExists($meta_id)
	{
		$query = $this->_pdo->prepare("SELECT COUNT(*) as total
			FROM company_metas
			WHERE meta_id = :meta_id");
		$query->execute(array('meta_id' => $meta_id));
		$total = $query->fetchAll(PDO::FETCH_COLUMN, 0);
		if($total[0] == 0) {
			return false;
		}
		return true;
	}


	/**
	 * add a company meta
	 *
	 * @param object CompanyMeta
	 *
	 * @return object CompanyMeta
	 */
	public function addCompanyMeta(CompanyMeta $meta)
	{
		$query = $this->_pdo->prepare("INSERT INTO `company_metas`
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
	 * update a company meta
	 *
	 * @param object CompanyMeta
	 *
	 * @return object CompanyMeta
	 */
	public function updateCompanyMeta(CompanyMeta $meta)
	{
		$query = $this->_pdo->prepare("UPDATE `company_metas`
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
	 * delete a company meta
	 *
	 * @param int $meta_id
	 *
	 * @return void
	 */
	public function delCompanyMeta(CompanyMeta $meta)
	{
		// delete meta content
		$relationshipDao = new DaoCompanyMetaRelationships();
		$relationshipDao->deleteCompanyMetasByMeta($meta->getId());
		// delete meta
		$query = $this->_pdo->prepare("DELETE FROM company_metas WHERE meta_id = :id");
		$query_result = $query->execute(array('id' => $meta->getId()));
		if( ! $query_result) {
			Utils::dump($query->errorInfo());
		}
		$query->closeCursor();
	 }


}

/* End of file DaoCompanyMetas.class.php */
/* Location: ./classes/DaoCompanyMetas.class.php */