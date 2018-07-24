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
 * The DaoCompanyMetaRelationships class is used to get and manage metas for companies
 *
 * @since	1.0
 * @author	Marie Kuntz / Lézard Rouge <mariek@lezard-rouge.fr>
 */

class DaoCompanyMetaRelationships
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
	 * fetch infos metas for a list of companies
	 *
	 * @param mixed str|array $companies
	 * @param mixed str|array $company_metas
	 *
	 * @return array
	 */
	public function getMetasforCompanies($companies, $company_metas)
	{
		$metas = array();
		if(empty($companies) || empty($company_metas)) {
			return $metas;
		}

		$conditions = array();
		$sql = "SELECT company_meta_relationships.*
			FROM company_meta_relationships
			WHERE rel_company_id IN (";

		if( ! is_array($companies)) {
			$companies = explode(',', $companies);
		}
		$tmp_company = array();
		$i = 1;
		foreach($companies as $company_id) {
			$tmp_company[] = ":company_id_" . $i;
			$conditions['company_id_' . $i] = $company_id;
			$i++;
		}
		$tmp_company2 = implode(',', $tmp_company);
		$sql .= $tmp_company2 . " ) AND rel_meta_id IN (";

		if( ! is_array($company_metas)) {
			$company_metas = explode(',', $company_metas);
		}
		$tmp = array();
		$i = 1;
		foreach($company_metas as $meta_id) {
			$tmp[] = ":meta_id_" . $i;
			$conditions['meta_id_' . $i] = $meta_id;
			$i++;
		}
		$tmp2 = implode(',', $tmp);
		$sql .= $tmp2 . " )";

		$query = $this->_pdo->prepare($sql);
		$query->execute($conditions);

		while($data = $query->fetch(PDO::FETCH_OBJ)) {
			$metas[$data->rel_company_id][$data->rel_meta_id] = $data->rel_value;
		}
		$query->closeCursor();

		return $metas;
	}


	/**
	 * add metas values for a company
	 *
	 * @param int $company_id
	 * @param array $metas the metas id and values
	 *
	 * @return void
	 */
	public function addCompanyMetas($company_id, $metas)
	{
		foreach ($metas as $meta_id => $meta_value) {
			if( ! empty($meta_value)) {
				$query = $this->_pdo->prepare("INSERT INTO `company_meta_relationships`
					SET rel_company_id = :company_id,
						rel_meta_id = :meta_id,
						rel_value = :value");
				$query_result = $query->execute(array(
						'company_id' => $company_id,
						'meta_id'    => $meta_id,
						'value'      => $meta_value
					)
				);
				if( ! $query_result) {
					Utils::dump($query->errorInfo());
				}
				$query->closeCursor();
			}
		}

		return;
	}


	/**
	 * update metas values for a company
	 *
	 * @param int $company_id
	 * @param array $metas the metas id and values
	 *
	 * @return void
	 */
	public function updateCompanyMetas($company_id, $metas)
	{
		// first delete metas information for the company
		$this->deleteCompanyMetas($company_id);
		// then add them
		$this->addCompanyMetas($company_id, $metas);
		return;
	}


	/**
	 * delete metas information for a company
	 *
	 * @param int $company_id
	 *
	 * @return void
	 */
	public function deleteCompanyMetas($company_id)
	{
		$query = $this->_pdo->prepare("DELETE FROM company_meta_relationships WHERE rel_company_id = :id");
		$query_result = $query->execute(array('id' => $company_id));
		if( ! $query_result) {
			Utils::dump($query->errorInfo());
		}
		$query->closeCursor();
	 }


	/**
	 * delete metas information for a meta
	 *
	 * @param int $meta_id
	 *
	 * @return void
	 */
	public function deleteCompanyMetasByMeta($meta_id)
	{
		$query = $this->_pdo->prepare("DELETE FROM company_meta_relationships WHERE rel_meta_id = :id");
		$query_result = $query->execute(array('id' => $meta_id));
		if( ! $query_result) {
			Utils::dump($query->errorInfo());
		}
		$query->closeCursor();
	 }


}

/* End of file DaoCompanyMetaRelationships.class.php */
/* Location: ./classes/DaoCompanyMetaRelationships.class.php */