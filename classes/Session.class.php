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
 * Manage sessions information
 *
 * @since	1.0
 * @author	Marie Kuntz / Lézard Rouge <mariek@lezard-rouge.fr>
 */

class Session
{

	private static $_instance;
	private $_pdo;
	private $_session_data;

	public $session_id;


	public function __construct()
	{
		$this->_pdo = myPDO::getInstance();

		// if no cookie, create one
		if( ! isset($_COOKIE[COOKIE_NAME])) {
			$this->createCookie();
			$this->_session_data = array();
			$session_id = session_id();
			if(empty($session_id)) {
				session_start();
			}
			// if the access module is disabled, create automatic session
			if(MOD_ACCESS === 0) {
				$created = $this->createSessionInfo(1);
			}
			// else create session with session_id
			else {
				$created = $this->createSession($this->_session_data);
			}
			if( ! $created) {
				throw new Exception('Session could not be created !');
			}
		}
		// if cookie, check session expiration
		else {
			$session_id = session_id();
			if(empty($session_id)) {
				session_start();
			}
			$this->session_id = $_COOKIE[COOKIE_NAME];
			$this->checkSessionValidity();
			$this->_session_data = $this->getSessionData();
		}


	}


	public static function getInstance()
	{
		if ( ! isset(self::$_instance)) {
			self::$_instance = new Session();
		}
		return self::$_instance;
	}


	/**
	 * retrieve a stored session information
	 * method & code borrowed to Code Igniter (http://codeigniter.com)
	 *
	 * @param string $key, the name of the session information to get ; if empty, all
	 *
	 * @return mixed ; returns false if $key does not exists
	 */
	public function getSessionData($key = '')
	{
		if(empty($this->_session_data)) {
			$session = $this->getSession();
			if( ! empty($session)) {
				$this->_session_data = unserialize($session->session_data);
			}
		}
		if($key != '' && isset($this->_session_data[$key])) {
			return $this->_session_data[$key];
		} elseif($key == '') {
			return $this->_session_data;
		}
		return false;
	}


	/**
	 * get a session
	 *
	 * @return query object session
	 */
	private function getSession()
	{
		$query = $this->_pdo->prepare("SELECT * FROM sessions WHERE session_id = :id");
		$query_result = $query->execute(array('id' => $this->session_id));
		if( ! $query_result) {
			Utils::dump($query->errorInfo());
		}
		$session = $query->fetch(PDO::FETCH_OBJ);
		$query->closeCursor();
		return $session;
	}


	/**
	 * check if a session is running and valid
	 */
	private function checkSessionValidity()
	{
		$session = $this->getSession();
		$last = $session->session_last_activity;
		$now = time();
		// if last activity is more than SESSION_EXPIRE, the session is not valid
		if(($last + SESSION_EXPIRE) < $now) {
			// message
			$_SESSION['sess_message'] = 'Votre session a expiré.';
			$_SESSION['sess_message_type'] = TYPE_MSG_ERROR;
			$this->destroySession();
		}
		// else, update last activity (every 10 minutes)
		else {
			if($session->session_last_activity + 10 < $now) {
				$this->updateLastActivity();
			}
		}
	}


	/**
	 * check if the user is connected
	 *
	 * @param bool $redirection
	 */
	public function isConnected($redirection = true)
	{
		if(MOD_ACCESS === 1) {
			$user_id = $this->getSessionData('user_id');
			// if the user is not connected, go to login page
			if(empty($user_id)) {
				$_SESSION['sess_message'] = 'Veuillez vous identifier.';
				$_SESSION['sess_message_type'] = TYPE_MSG_ERROR;
				if($redirection) {
					header("Location: login.php");
					exit;
				} else {
					return false;
				}
			}
		}
		return true;
	}


	/**
	 * create a session for the user if exists
	 *
	 * @param string $username
	 * @param string $password
	 *
	 * @return boolean
	 */
	public function connectUser($username, $password)
	{
		// first check if exists
		$dao_user = new DaoUsers();
		$ok = $dao_user->checkUserCredentials($username, $password);
		// if not
		if( ! $ok) {
			return false;
		}
		// else, connect user
		else {
			$user = $dao_user->getUserByLogin($username);
			if(empty($user)) {
				return false;
			}
			// delete previous DB session information
			$this->deleteSession();
			// create new DB session
			$created = $this->createSessionInfo($user->getId());
			$log_info = " login ; username: " . $username . " ; IP: " . Utils::getIp()
					. " ; UserAgent: " . Utils::getUserAgent();
			Utils::connectionLogs($log_info);
			return $created;
		}
	}


	/**
	 * record in DB the session information
	 * method & code borrowed to Code Igniter (http://codeigniter.com)
	 *
	 * @param mixed string|array $new_data
	 * @param string $new_val
	 */
	public function setSessionData($new_data = array(), $new_val = '')
	{
		if (is_string($new_data)) {
			$new_data = array($new_data => $new_val);
		}
		if (count($new_data) > 0) {
			 $this->_session_data = array_merge($this->_session_data, $new_data);
		}
		$serialized_data = serialize($this->_session_data);
		$this->updateSession($serialized_data);
	}



	/**
	 * set a cookie
	 */
	private function createCookie()
	{
		$sessid = '';
		while (strlen($sessid) < 32) {
			$sessid .= mt_rand(0, mt_getrandmax());
		}
		$this->session_id = md5(uniqid($sessid, TRUE));
		setcookie(COOKIE_NAME, $this->session_id, time() + COOKIE_EXPIRE);
	}


	/**
	 * create basic session information for a user
	 *
	 * @param int $user_id
	 *
	 */
	private function createSessionInfo ($user_id)
	{
		$access = new Access();
		// get user's access rights on accounts
		$accounts = $access->getAccountsForUser($user_id);
		$this->_session_data = array(
			'user_id'              => $user_id,
			'sess_accounts_array'  => $accounts,
			'sess_accounts_list'   => implode(',', $accounts) // as list, for sql queries
		);
		// if there is only one account, we assigne it immediatly
		if(count($accounts) == 1) {
			$this->_session_data['account'] = $accounts[0];
		}
		// register session information
		$created = $this->createSession($this->_session_data);

		return $created;
	}


	/**
	 * insert a session in DB
	 *
	 * @param array $data
	 */
	private function createSession($data)
	{
		if(empty($this->session_id)) {
			return false;
		} else {
			$data['token'] = Utils::generateCsrfToken();
			$serialized_data = serialize($data);
			$sql = "INSERT INTO sessions
				SET session_id = :id,
					session_last_activity = :last,
					session_data = :data";
			$query = $this->_pdo->prepare($sql);
			$query_result = $query->execute(array(
				'last' => time(),
				'data' => $serialized_data,
				'id'   => $this->session_id
			));
			if( ! $query_result) {
				Utils::dump($query->errorInfo());
			}
			$query->closeCursor();
			return true;
		}
	}


	/**
	 * update last activity of session
	 */
	private function updateLastActivity()
	{
		$sql = "UPDATE sessions
			SET session_last_activity = :time
			WHERE session_id = :id";
		$query = $this->_pdo->prepare($sql);
		$query_result = $query->execute(array(
			'time' => time(),
			'id'   => $this->session_id
		));
		if( ! $query_result) {
			Utils::dump($query->errorInfo());
		}
		$query->closeCursor();
	}


	/**
	 * update a session information in DB
	 *
	 * @param string $data
	 */
	private function updateSession($data)
	{
		$sql = "UPDATE sessions
			SET session_last_activity = :last,
				session_data = :data
			WHERE session_id = :id";
		$query = $this->_pdo->prepare($sql);
		$query_result = $query->execute(array(
			'last' => time(),
			'data' => $data,
			'id'   => $this->session_id
			));
		if( ! $query_result) {
			Utils::dump($query->errorInfo());
		}
		$query->closeCursor();
	}


	/**
	 * destroy a session
	 */
	public function destroySession()
	{
		// destroy session in DB
		$this->deleteSession();
		// destroy cookie
		$this->deleteCookie();
		// send to login page
		header("Location: login.php");
		exit;
	}


	/**
	 * delete a session in DB
	 */
	private function deleteSession()
	{
		$sql = "DELETE FROM sessions
			WHERE session_id = :id";
		$query = $this->_pdo->prepare($sql);
		$query_result = $query->execute(array('id' => $this->session_id));
		if( ! $query_result) {
			Utils::dump($query->errorInfo());
		}
		$query->closeCursor();
	}


	/**
	 * clean old sessions from table
	 * (2 days)
	 */
	public function cleanSessions()
	{
		$sql = "DELETE FROM sessions
			WHERE session_last_activity < :past";
		$query = $this->_pdo->prepare($sql);
		$past = (time() - (2 * 24 * 60 * 60));
		$query_result = $query->execute(array('past' => $past));
		if( ! $query_result) {
			Utils::dump($query->errorInfo());
		}
		$query->closeCursor();
	}


	/**
	 * delete a cookie
	 */
	private function deleteCookie()
	{
		setcookie(COOKIE_NAME, "", time() - 3600);
	}


}
?>