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
 * The DaoUsers class is used to get and manage users
 *
 * @since	1.0
 * @author	Marie Kuntz / Lézard Rouge <mariek@lezard-rouge.fr>
 */

class DaoUsers
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
	 * get list of users, possibly paginated, ordered and filtered
	 *
	 * @param array $criteria, search & order criteria
	 * @param int $num, number of records to fetch ; default : NB_RECORDS
	 * @param int $limit, number from which to fetch ; default : 0
	 *
	 * @return array(total, results)
	 */
	public function getList($criteria = array(), $num = NB_RECORDS, $limit = 0)
	{
		$users = array();

		$sql = "SELECT SQL_CALC_FOUND_ROWS
				users.*
			FROM users ";
		//-----------------------------
		// filters
		//-----------------------------
		// dirty hack
		$sql .= ' WHERE 1 = 1 ';
		$conditions = array();
		if ( ! empty($criteria)) {
			if(isset($criteria['f_name']) && ! empty($criteria['f_name'])) {
				$sql .= " AND (users.user_lastname LIKE :name OR users.user_firstname LIKE :name)";
				$conditions['name'] = "%" . $criteria['f_name'] . "%";
			}
		}
		//-----------------------------
		// sorting
		//-----------------------------
		if(isset($criteria['sort'])) {
			if($criteria['order'] != 'desc') {
				$criteria['order'] = 'asc';
			}
			if($criteria['sort'] == 'name') {
				$sorting = ' ORDER BY user_lastname ' . $criteria['order'] . ', user_firstname ';
			} elseif ($criteria['sort'] == 'login') {
				$sorting = ' ORDER BY user_login ' . $criteria['order'] . ', user_lastname, user_firstname ';
			}
		} else {
			$sorting = " ORDER BY user_lastname, user_firstname ";
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
			$users[] = new User($data);
		}
		$query->closeCursor();

		return array('total' => $total[0], 'results' => $users);
	}


	/**
	 * get users that have access to an account
	 *
	 * @param int $account_id
	 *
	 * @return array users(id => name)
	 */
	public function getUsersFromAccount($account_id)
	{
		$users = array();
		$query = $this->_pdo->prepare("SELECT users.*
			FROM users
			LEFT JOIN user_account_relationships ON (rel_user_id = user_id)
			WHERE rel_account_id = :account_id
			ORDER BY user_lastname, user_firstname");
		$query->execute(array('account_id' => $account_id));
		while($data = $query->fetch(PDO::FETCH_OBJ)) {
			$users[$data->user_id] = $data->user_firstname . ' ' . $data->user_lastname;
		}
		$query->closeCursor();
		return $users;
	}


	/**
	 * get informations about a user
	 *
	 * @param int $user_id
	 *
	 * @return object User
	 */
	public function getUser($user_id)
	{
		$query = $this->_pdo->prepare("SELECT *
			FROM users
			WHERE user_id = :user_id");
		$query_result = $query->execute(array('user_id' => $user_id));
		if( ! $query_result) {
			Utils::dump($query->errorInfo());
		}
		$user = $query->fetch(PDO::FETCH_OBJ);
		$query->closeCursor();
		if( ! $user) {
			return new User(array());
		}
		return new User($user);
	}


	/**
	 * fetch a user by his login
	 *
	 * @param string $login
	 * @return boolean|User
	 */
	public function getUserByLogin($login)
	{
		$query = $this->_pdo->prepare("SELECT *
			FROM users
			WHERE user_login = :login");
		$query_result = $query->execute(array('login' => $login));
		if( ! $query_result) {
			Utils::dump($query->errorInfo());
		}
		$user = $query->fetch(PDO::FETCH_OBJ);
		$query->closeCursor();
		if( ! $user) {
			return false;
		}
		return new User($user);
	}


	/**
	 * check if a login is unique in DB
	 *
	 * @param string $login
	 * @param int $user_id, the user id to exclude from search
	 *
	 * @return boolean
	 */
	public function checkUniqueLogin($login, $user_id)
	{
		$user = $this->getUserByLogin($login);
		if($user === false) {
			return true;
		}
		if($user->getId() == $user_id) {
			return true;
		}
		return false;
	}


	/**
	 * check if a couple username/password exists
	 *
	 * @param string $username
	 * @param string $password
	 *
	 * @return boolean
	 */
	public function checkUserCredentials($username, $password)
	{
		$query = $this->_pdo->prepare("SELECT COUNT(*) AS total
			FROM users
			WHERE user_login = :login
				AND user_pwd = :pwd");
		$query->execute(array(
			'login' => $username,
			'pwd'   => Utils::hashPwd($username, $password)
		));

		$total = $query->fetchAll(PDO::FETCH_COLUMN, 0);
		if($total[0] == 0) {
			return false;
		}
		return true;
	}


	/**
	 * check if user exists by id
	 *
	 * @param int $user_id
	 *
	 * @return boolean
	 */
	public function userExists($user_id)
	{
		$query = $this->_pdo->prepare("SELECT COUNT(*) as total
			FROM users
			WHERE user_id = :user_id");
		$query->execute(array('user_id' => $user_id));
		$total = $query->fetchAll(PDO::FETCH_COLUMN, 0);
		if($total[0] == 0) {
			return false;
		}
		return true;
	}


	/**
	 * add a user
	 *
	 * @param object User
	 *
	 * @return object User
	 */
	public function addUser(User $user)
	{
		// user
		$query = $this->_pdo->prepare("INSERT INTO `users`
			SET `user_lastname`  = :lastname,
				`user_firstname` = :firstname,
				`user_login`     = :login,
				`user_pwd`       = :pwd,
				`user_email`     = :email");
		$query_result = $query->execute(array(
				'lastname'  => $user->getLastname(),
				'firstname' => $user->getFirstname(),
				'login'     => $user->getLogin(),
				'pwd'       => Utils::hashPwd($user->getLogin(), $user->getPwd()),
				'email'     => $user->getEmail()
			)
		);
		if( ! $query_result) {
			Utils::dump($query->errorInfo());
		}
		$user_id = $this->_pdo->lastInsertId();
		$query->closeCursor();

		// permissions
		$dao_access = new Access();
		$dao_access->setAccessByUser($user_id, $user->getAccess_perm_ids());
		$dao_access->setAccountByUser($user_id, $user->getAccount_perm_ids());

		$user->hydrate(array(
			'user_id' => $user_id,
			'user_access_perms' => $user->getAccess_perm_ids(),
			'user_account_perms' => $user->getAccount_perm_ids()
		));

		return $user;
	}


	/**
	 * update a user
	 *
	 * @param object User
	 *
	 * @return object User
	 */
	public function updateUser(User $user)
	{
		// user
		$sql = "UPDATE `users`
			SET `user_lastname`  = :lastname,
				`user_firstname` = :firstname,
				`user_login`     = :login,
				%s
				`user_email`     = :email
			WHERE user_id = :id";
		$inputs = array(
				'lastname'  => $user->getLastname(),
				'firstname' => $user->getFirstname(),
				'login'     => $user->getLogin(),
				'email'     => $user->getEmail(),
				'id'        => $user->getId()
			);
		$pwd = $user->getPwd();
		if( ! empty($pwd)) {
			$sql = sprintf($sql, '`user_pwd` = :pwd,');
			$inputs['pwd'] = Utils::hashPwd($user->getLogin(), $user->getPwd());
		} else {
			$sql = sprintf($sql, '');
		}
		$query = $this->_pdo->prepare($sql);
		$query_result = $query->execute($inputs);
		if( ! $query_result) {
			Utils::dump($query->errorInfo());
		}

		// permissions
		$dao_access = new Access();
		$dao_access->setAccessByUser($user->getId(), $user->getAccess_perm_ids());
		$dao_access->setAccountByUser($user->getId(), $user->getAccount_perm_ids());

		$user->hydrate(array(
			'user_access_perms'  => $user->getAccess_perm_ids(),
			'user_account_perms' => $user->getAccount_perm_ids()
		));

		$query->closeCursor();

		return $user;
	}


	/**
	 * update a user's password
	 *
	 * @param object User
	 *
	 * @return object User
	 */
	public function updateUserPwd(User $user)
	{
		$sql = "UPDATE `users` SET `user_pwd` = :pwd WHERE user_id = :id";
		$hashed_pwd = Utils::hashPwd($user->getLogin(), $user->getPwd());
		$inputs = array(
			'pwd' => $hashed_pwd,
			'id'  => $user->getId()
		);
		$query = $this->_pdo->prepare($sql);
		$query_result = $query->execute($inputs);
		if( ! $query_result) {
			Utils::dump($query->errorInfo());
		}
		$user->hydrate(array(
			'user_pwd' => $hashed_pwd
		));
		$query->closeCursor();
		return $user;
	}


	/**
	 * delete a user
	 *
	 * @param int $user_id
	 *
	 * @return void
	 */
	public function delUser($user_id)
	{
		// delete user access rights
		$dao_access = new Access();
		$dao_access->deleteAccessByUser($user_id);
		// delete user
		$query = $this->_pdo->prepare("DELETE FROM users WHERE user_id = :id");
		$query_result = $query->execute(array('id' => $user_id));
		if( ! $query_result) {
			Utils::dump($query->errorInfo());
		}
		$query->closeCursor();
	}


}

/* End of file DaoUsers.class.php */
/* Location: ./classes/DaoUsers.class.php */