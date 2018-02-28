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
 * The DaoAccounts class is used to get and manage accounts
 *
 * @since	1.0
 * @author	Marie Kuntz / Lézard Rouge <mariek@lezard-rouge.fr>
 */

class DaoAccounts
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
	 * get list of accounts, possibly paginated, ordered and filtered
	 *
	 * @param array $criteria, search & order criteria
	 * @param int $num, number of records to fetch ; default : NB_RECORDS
	 * @param int $limit, number from which to fetch ; default : 0
	 *
	 * @return
	 */
	public function getList($criteria = array(), $num = NB_RECORDS, $limit = 0)
	{
		$accounts = array();

		$sql = "SELECT SQL_CALC_FOUND_ROWS
				accounts.*
			FROM accounts";
		//-----------------------------
		// filters
		//-----------------------------
		$conditions = array();
		// for current user (owner or assigned)
		$sql .= " WHERE (account_user_id = " . $this->_session->getSessionData('user_id');
		$account_list = $this->_session->getSessionData('sess_accounts_list');
		if( ! empty($account_list)) {
			$sql .= " OR account_id IN (";
			if( ! is_array($account_list)) {
				$account_list = explode(',', $account_list);
			}
			$tmp = array();
			$i = 1;
			foreach($account_list as $account_id) {
				$tmp[] = ":account_id_" . $i;
				$conditions['account_id_' . $i] = $account_id;
				$i++;
			}
			$tmp2 = implode(',', $tmp);
			$sql .= $tmp2 . " )";
		}
		$sql .= ")";
		if ( ! empty($criteria)) {
			if(isset($criteria['f_account']) && ! empty($criteria['f_account'])) {
				$sql .= " AND (accounts.account_id = :id)";
				$conditions[':id'] = $criteria['f_account'];
			}
			if(isset($criteria['f_name']) && ! empty($criteria['f_name'])) {
				$sql .= " AND (accounts.account_name LIKE :name)";
				$conditions[':name'] = '%' . $criteria['f_name'] . '%';
			}
			if(isset($criteria['f_active']) && $criteria['f_active'] != -1) {
				$sql .= " AND (accounts.account_active = :active)";
				$conditions[':active'] = $criteria['f_active'];
			}

		}
		//echo $sql;
		//-----------------------------
		// sorting
		//-----------------------------
		if(isset($criteria['sort'])) {
			if($criteria['order'] != 'desc') {
				$criteria['order'] = 'asc';
			}
			if($criteria['sort'] == 'name') {
				$sorting = ' ORDER BY account_name ' . $criteria['order'];
			}
		} else {
			$sorting = " ORDER BY account_name ";
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

		while($data = $query->fetch(PDO::FETCH_OBJ)) {
			$accounts[] = new Account($data);
		}
		$query->closeCursor();

		return array('total' => $total[0], 'results' => $accounts);

	}


	/**
	 * get informations about a account
	 *
	 * @param int $account_id
	 *
	 * @return object Account
	 */
	public function getAccount($account_id)
	{
		$query = $this->_pdo->prepare("SELECT *
			FROM accounts
			WHERE account_id = :account_id");
		$query_result = $query->execute(array('account_id' => $account_id));
		if( ! $query_result) {
			Utils::dump($query->errorInfo());
		}
		$account = $query->fetch(PDO::FETCH_OBJ);
		$query->closeCursor();
		if( ! $account) {
			return new Account(array());
		}
		return new Account($account);
	}


	/**
	 * check if account exists by id
	 *
	 * @param int $account_id
	 *
	 * @return boolean
	 */
	public function accountExists($account_id)
	{
		$query = $this->_pdo->prepare("SELECT COUNT(*) as total
			FROM accounts
			WHERE account_id = :account_id");
		$query->execute(array('account_id' => $account_id));
		$total = $query->fetchAll(PDO::FETCH_COLUMN, 0);
		if($total[0] == 0) {
			return false;
		}
		return true;
	}


	/**
	 * add an account
	 *
	 * @param object Account
	 *
	 * @return object Account
	 */
	public function addAccount(Account $account)
	{
		// insert account
		$query = $this->_pdo->prepare("INSERT INTO `accounts`
			SET `account_user_id` = :user_id,
				`account_name`    = :name,
				`account_active`  = :active");
		$query_result = $query->execute(array(
				'user_id' => $account->getUser_id(),
				'name'    => $account->getName(),
				'active'  => $account->getActive()
			)
		);
		if( ! $query_result) {
			Utils::dump($query->errorInfo());
		}
		$account_id = $this->_pdo->lastInsertId();
		$query->closeCursor();

		$account->hydrate(array(
			'account_id'   => $account_id,
		));

		// account access
		$access = new Access();
		$access->addAccountByUser($account->getUser_id(), array(0 => $account_id));
		// metas
		$dao_metas_rel = new DaoAccountMetaRelationships();
		$dao_metas_rel->addAccountMetas($account_id, $account->getMeta());


		return $account;
	}


	/**
	 * update an account
	 *
	 * @param object Account
	 *
	 * @return object Account
	 */
	public function updateAccount(Account $account)
	{
		$query = $this->_pdo->prepare("UPDATE `accounts`
			SET `account_name`    = :name,
				`account_active`  = :active
			WHERE account_id = :id");
		$query_result = $query->execute(array(
				'name'    => $account->getName(),
				'active'  => $account->getActive(),
				'id'      => $account->getId()
			)
		);
		if( ! $query_result) {
			Utils::dump($query->errorInfo());
		}
		$query->closeCursor();

		// metas
		$dao_metas_rel = new DaoAccountMetaRelationships();
		$dao_metas_rel->updateAccountMetas($account->getId(), $account->getMeta());

		return $account;
	}


	/**
	 * delete an account
	 *
	 * @param int $account_id
	 *
	 * @return void
	 */
	public function delAccount(Account $account)
	{
		// delete companies
		$dao_companies = new DaoCompanies();
		$dao_companies->delCompaniesByAccount($account);
		// delete contacts
		$dao_contacts = new DaoContacts();
		$dao_contacts->delContactsByAccount($account);
		// delete account infos
		$dao_metas_rel = new DaoAccountMetaRelationships();
		$dao_metas_rel->deleteAccountMetas($account->getId());
		// TODO delete user account rel
		$dao_access = new Access();
		$dao_access->deleteUsersByAccount($account->getId());

		// delete account
		$query = $this->_pdo->prepare("DELETE FROM accounts WHERE account_id = :id");
		$query_result = $query->execute(array('id' => $account->getId()));
		if( ! $query_result) {
			Utils::dump($query->errorInfo());
		}
		$query->closeCursor();
	 }


}

/* End of file DaoAccounts.class.php */
/* Location: ./classes/DaoAccounts.class.php */