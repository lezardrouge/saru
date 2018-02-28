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

class DaoCompanies
{

	/* instance de PDO */
	private $_pdo;
	/* instance de session */
	private $_session;

	/**
	 * constructor
	 */
	public function __construct()
	{
		$this->_pdo = myPDO::getInstance();
		$this->_session = Session::getInstance();
	}


	/**
	 * get companies
	 *
	 * @param array $criteria
	 * @param int $num
	 * @param int $limit
	 *
	 * @return array (total => number of results without the limit, results => array(objects Company) )
	 */
	public function getList($criteria = array(), $num = NB_RECORDS, $limit = 0)
	{
		$companies = array();

		$sql = "SELECT SQL_CALC_FOUND_ROWS
				companies.*
			FROM companies ";
		//-----------------------------
		// filters
		//-----------------------------
		$conditions = array();
		// dirty hack
		$sql .= ' WHERE 1 = 1 ';
		// by account
		if(isset($criteria['f_account']) && ! empty($criteria['f_account'])) {
			$sql .= " AND (companies.company_account_id = :account)";
			$conditions['account'] = $criteria['f_account'];
		}
		// else we work with the current account
		else {
			$sql .= ' AND (companies.company_account_id = ' . $this->_session->getSessionData('account') . ')';
		}
		if(isset($criteria['f_company']) && ! empty($criteria['f_company'])) {
			if( ! is_array($criteria['f_company'])) {
				$criteria['f_company'] = explode(',', $criteria['f_company']);
			}
			$sql .= " AND (companies.company_id IN (";
			$tmp = array();
			$i = 1;
			foreach($criteria['f_company'] as $company_id) {
				$tmp[] = ":company_id_" . $i;
				$conditions['company_id_' . $i] = $company_id;
				$i++;
			}
			$tmp2 = implode(',', $tmp);
			$sql .= $tmp2 . " ))";
		}
		if(isset($criteria['f_name']) && ! empty($criteria['f_name'])) {
			$sql .= " AND (companies.company_name LIKE :name)";
			$conditions['name'] = "%" . $criteria['f_name'] . "%";
		}
		if(isset($criteria['f_type']) && ! empty($criteria['f_type'])) {
			$sql .= " AND (companies.company_type_id = :type)";
			$conditions['type'] = $criteria['f_type'];
		}
		if(isset($criteria['f_zipcity']) && ! empty($criteria['f_zipcity'])) {
			$sql .= " AND (companies.company_id IN (
				SELECT rel_company_id
				FROM company_meta_relationships
				WHERE (rel_meta_id = 7 OR rel_meta_id = 8) AND rel_value LIKE :zipcity
				)
			)";
			$conditions['zipcity'] = "%" . $criteria['f_zipcity'] . "%";
		}
		// dates
		if(isset($criteria['f_date_add_from']) && ! Utils::dateEmpty($criteria['f_date_add_from'])) {
			$sql .= " AND (companies.company_date_add >= :add_date_from)";
			$conditions['add_date_from'] = Utils::date2ISO($criteria['f_date_add_from']);
		}
		if(isset($criteria['f_date_add_to']) && ! Utils::dateEmpty($criteria['f_date_add_to'])) {
			$sql .= " AND (companies.company_date_add <= :add_date_to)";
			$conditions['add_date_to'] = Utils::date2ISO($criteria['f_date_add_to']);
		}
		if(isset($criteria['f_date_upd_from']) && ! Utils::dateEmpty($criteria['f_date_upd_from'])) {
			$sql .= " AND (companies.company_date_update >= :upd_date_from)";
			$conditions['upd_date_from'] = Utils::date2ISO($criteria['f_date_upd_from']);
		}
		if(isset($criteria['f_date_upd_to']) && ! Utils::dateEmpty($criteria['f_date_upd_to'])) {
			$sql .= " AND (companies.company_date_update <= :upd_date_to)";
			$conditions['upd_date_to'] = Utils::date2ISO($criteria['f_date_upd_to']);
		}
		// active
		if(isset($criteria['f_active']) && $criteria['f_active'] == 1) {
			$sql .= " AND (companies.company_active = 1)";
		}

		//-----------------------------
		// order
		//-----------------------------
		if(isset($criteria['sort'])) {
			if( ! isset($criteria['order']) || $criteria['order'] != 'desc') {
				$criteria['order'] = 'asc';
			}
			if($criteria['sort'] == 'name') {
				$sorting = ' ORDER BY company_name ' . $criteria['order'] . ' ';
			}
		} else {
			$sorting = " ORDER BY company_name ";
		}
		$sql .= $sorting;
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
		//$results = $query->fetchAll();
		while($data = $query->fetch(PDO::FETCH_OBJ)) {
			$companies[] = new Company($data);
		}
		$query->closeCursor();

		return array('total' => $total[0], 'results' => $companies);
	}



	/**
	 * fetch infos for a list of companies by contact
	 *
	 * @param mixed str|array $contact_ids
	 *
	 * @return array $companies
	 */
	public function getCompaniesById($company_ids)
	{
		$companies = array();
		if(is_array($company_ids)) {
			$company_ids = implode(',', $company_ids);
		}
		if(empty($company_ids)) {
			return $companies;
		}
		$criteria = array('f_company' => $company_ids);
		$data = $this->getList($criteria, 0);
		foreach($data['results'] as $company) {
			$companies[$company->getId()] = $company;
		}
		return $companies;
	}


	/**
	 * get informations about a company
	 *
	 * @param int $company_id
	 *
	 * @return object Company
	 */
	public function getCompany($company_id)
	{
		$query = $this->_pdo->prepare("SELECT * FROM companies WHERE company_id = :company_id");
		$query_result = $query->execute(array('company_id' => $company_id));
		if( ! $query_result) {
			Utils::dump($query->errorInfo());
		}
		$company = $query->fetch(PDO::FETCH_OBJ);
		$query->closeCursor();
		if( ! $company) {
			return new Company(array());
		}
		return new Company($company);
	}


	/**
	 * get informations about a company
	 *
	 * @param str $company_name
	 *
	 * @return object Company
	 */
	public function getCompanyByName($company_name, $account_id)
	{
		$query = $this->_pdo->prepare("SELECT * FROM companies
			WHERE company_name LIKE :company_name
			AND company_account_id = :account");
		$query_result = $query->execute(array('company_name' => $company_name, 'account' => $account_id));
		if( ! $query_result) {
			Utils::dump($query->errorInfo());
		}
		$company = $query->fetch(PDO::FETCH_OBJ);
		$query->closeCursor();
		if( ! $company) {
			return false;
		}
		return new Company($company);
	}


	/**
	 * check if company exists by id
	 *
	 * @param int $company_id
	 *
	 * @return boolean
	 */
	public function companyExists($company_id)
	{
		$query = $this->_pdo->prepare("SELECT COUNT(*) as total
			FROM companies
			WHERE company_id = :company_id");
		$query_result = $query->execute(array('company_id' => $company_id));
		if( ! $query_result) {
			Utils::dump($query->errorInfo());
		}
		$total = $query->fetchAll(PDO::FETCH_COLUMN, 0);
		if($total[0] == 0) {
			return false;
		}
		return true;
	}


	/**
	 * insert a company if it does not exists
	 *
	 * @param object Company $company
	 *
	 * @return object Company, existing or newly inserted
	 */
	public function insertCompany(Company $company)
	{
		if($this->companyExists($company->getId())) {
			$existing_company = $this->getCompany($company->getId());
			return $existing_company;
		} else {
			$new_company = $this->addCompany($company);
			return $new_company;
		}
	}


	/**
	 * set or fetch a company
	 *
	 * @param int $company_id
	 * @param string $company_name
	 * @param int $type_id
	 *
	 * @return object Company
	 */
	function setCompany($company_id, $company_name, $type_id)
	{
		// if company name is empty, do not set company
		if(empty($company_name)) {
			$company = new Company(array('company_id' => 0));
		}
		// if company_id != 0, then the company already exists
		elseif( ! empty($company_id)) {
			$company = $this->getCompany($company_id);
		}
		// if company_id is empty but not company_name, it's a new one -> insert
		elseif( ! empty($company_name)) {
			$contact_company = new Company(array(
				'company_id'          => $company_id,
				'company_name'        => $company_name,
				'company_account_id'  => $this->_session->getSessionData('account'),
				'company_type_id'     => $type_id,
				'company_date_add'    => date('Y-m-d'),
				'company_date_update' => date('Y-m-d'),
				'company_active'      => 1
			));
			$company = $this->addCompany($contact_company);
		}
		return $company;
	}


	/**
	 * add a company
	 *
	 * @param object Company
	 *
	 * @return object Company
	 */
	public function addCompany(Company $company)
	{
		// company
		$query = $this->_pdo->prepare("INSERT INTO `companies`
			SET `company_name`        = :name,
				`company_account_id`  = :account,
				`company_type_id`     = :type,
				`company_comments`    = :comments,
				`company_date_add`    = :date_add,
				`company_date_update` = :date_update,
				`company_active`      = :active");
		$query_result = $query->execute(array(
				'name'        => $company->getName(),
				'account'     => $company->getAccount_id(),
				'type'        => $company->getType_id(),
				'comments'    => $company->getComments(),
				'date_add'    => $company->getDate_add(),
				'date_update' => $company->getDate_update(),
				'active'     => 1
			)
		);
		if( ! $query_result) {
			Utils::dump($query->errorInfo());
		}
		$company_id = $this->_pdo->lastInsertId();
		$query->closeCursor();

		$company->hydrate(array(
			'company_id' => $company_id
		));

		// metas
		$metas = $company->getMeta();
		if( ! empty($metas)) {
			$dao_metas_rel = new DaoCompanyMetaRelationships();
			$dao_metas_rel->addCompanyMetas($company_id, $metas);
		}

		return $company;
	}


	/**
	 * update a company
	 *
	 * @param object Company
	 *
	 * @return object Company
	 */
	public function updateCompany(Company $company)
	{
		$query = $this->_pdo->prepare("UPDATE `companies`
			SET `company_name`        = :name,
				`company_account_id`  = :account,
				`company_type_id`     = :type,
				`company_comments`    = :comments,
				`company_date_update` = :date_update,
				`company_active`      = :active
			WHERE company_id = :id");
		$query_result = $query->execute(array(
				'name'     => $company->getName(),
				'account'  => $company->getAccount_id(),
				'type'     => $company->getType_id(),
				'comments' => $company->getComments(),
				'date_update' => $company->getDate_update(),
				'active'      => $company->getActive(),
				'id'       => $company->getId()
			)
		);
		if( ! $query_result) {
			Utils::dump($query->errorInfo());
		}
		$query->closeCursor();

		// update type for contacts
		$dao_contacts = new DaoContacts();
		$update = array('contact_type_id' => $company->getType_id(), 'contact_active' => $company->getActive());
		$where = array('contact_company_id' => $company->getId());
		$dao_contacts->updateContactBy($update, $where);

		// metas
		$metas = $company->getMeta();
		if( ! empty($metas)) {
			$dao_metas_rel = new DaoCompanyMetaRelationships();
			$dao_metas_rel->updateCompanyMetas($company->getId(), $metas);
		}

		return $company;
	}


	/**
	 * update companies
	 *
	 * @param array $updates, attributes to update
	 * @param array $where, conditions to match
	 */
	function updateCompanyBy($updates, $where)
	{
		if(! empty($updates)) {
			$sql = "UPDATE `companies` SET ";
			$upd = "";
			$execute = array();
			foreach($updates as $column => $new_value) {
				$upd .= " `" . $column . "` = :" . $column . ", ";
				$execute[$column] = $new_value;
			}
			$upd = substr($upd, 0, -2);
			$cond = "";
			foreach($where as $where_column => $where_value) {
				if( ! empty($cond)) {
					$cond .= " AND ";
				}
				$cond .= " `" . $where_column . "` = :" . $where_column;
				$execute[$where_column] = $where_value;
			}
			if( ! empty($cond)) {
				$cond = " WHERE " . $cond;
			}
			$sql .= $upd . $cond;
			$query = $this->_pdo->prepare($sql);
			$query_result = $query->execute($execute);
			if( ! $query_result) {
				Utils::dump($query->errorInfo());
			}
			$query->closeCursor();
		}
	}


	/**
	 * activate a company
	 *
	 * @param Company
	 */
	public function activateCompany(Company $company)
	{
		// activate contacts
		$dao_contacts = new DaoContacts();
		$dao_contacts->activateContactsByCompany($company);
		// activate company
		$update = array('company_active' => '1');
		$where = array('company_id' => $company->getId());
		$this->updateCompanyBy($update, $where);
	}


	/**
	 * deactivate a company
	 *
	 * @param Company $company
	 */
	public function deactivateCompany(Company $company)
	{
		// deactivate contacts
		$dao_contacts = new DaoContacts();
		$dao_contacts->deactivateContactsByCompany($company);
		// deactivate company
		$update = array('company_active' => '0');
		$where = array('company_id' => $company->getId());
		$this->updateCompanyBy($update, $where);
	}


	/**
	 * delete a company
	 *
	 * @param int $company_id
	 *
	 * @return void
	 */
	public function delCompany(Company $company)
	{
		// delete contacts
		$dao_contacts = new DaoContacts();
		$dao_contacts->delContactsByCompany($company);
		// delete metas
		$dao_meta_rel = new DaoCompanyMetaRelationships();
		$dao_meta_rel->deleteCompanyMetas($company->getId());
		// delete company
		$query = $this->_pdo->prepare("DELETE FROM companies WHERE company_id = :id");
		$query_result = $query->execute(array('id' => $company->getId()));
		if( ! $query_result) {
			Utils::dump($query->errorInfo());
		}
		$query->closeCursor();
		// delete attachments
		$dao_attachments = new DaoAttachments();
		$dao_attachments->delContactAttachments($company->getId());
	 }


	 /**
	  * delete an account's companies
	  *
	  * @param object Account $account
	  *
	  * @return void
	  */
	 public function delCompaniesByAccount(Account $account)
	 {
		 // fetch all companies
		 $criteria = array('f_account' => $account->getId());
		 $companies = $this->getList($criteria, 0);
		 // del each
		 foreach ($companies['results'] as $company) {
			 $this->delCompany($company);
		 }
	 }


}

/* End of file DaoCompanies.class.php */
/* Location: ./classes/DaoCompanies.class.php */