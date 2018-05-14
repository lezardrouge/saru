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
 * Manage access rights
 *
 * @since	1.0
 * @author	Marie Kuntz / Lézard Rouge <mariek@lezard-rouge.fr>
 */

class Access
{

	/* instance de PDO */
	private $_pdo;


	public function __construct()
	{
		$this->_pdo = myPDO::getInstance();
	}


	/**
	 * check if a user can access to a component and redirect if necessary
	 *
	 * @param type $component
	 * @param type $redirection
	 * @return boolean
	 */
	public static function userCanAccess($component, $redirection = true)
	{
		// if no access is required
		if(MOD_ACCESS === 0) {
			return true;
		}
		$session = Session::getInstance();

		$is_connected = $session->isConnected($redirection);
		if( ! $is_connected) {
			return false;
		} else {
			// get permissions
			$user_id = $session->getSessionData('user_id');
			$self = new Access();
			if($self->checkAccessByUser($user_id, $component)) {
				return true;
			}
		}

		//-------------------------------------
		// if the user has no access to the component :
		//	if redirection == TRUE, redirection to error page
		//	else (redirection == FALSE), return FALSE
		if($redirection) {
			$_SESSION['sess_message'] = "Vous n'avez pas accès à cette page.";
			$_SESSION['sess_message_type'] = TYPE_MSG_ERROR;
			header("Location: index.php");
			exit;
		}
		return false;
	}


	/**
	 * check if a user can access the object he's trying to access
	 *
	 * @param string $object_type	contact|account|company|meeting|alert
	 * @param int    $object_id		the object id
	 * @param bool   $redirection	true if user is redirected in case of violation access
	 *
	 * @return mixed bool|void, true if user can access, false if user cannot access and redirection is set to false, else void (redirection)
	 */
	public static function userCanAccessObject($object_type, $object_id, $redirection = true)
	{
		// if no access is required or if it is a new object
		if(MOD_ACCESS === 0 || $object_id === 0) {
			return true;
		}
		$session = Session::getInstance();
		$is_connected = $session->isConnected($redirection);
		// this checking should not be necessary, but just in case...
		if( ! $is_connected) {
			return false;
		} else {
			$account_id = $session->getSessionData('account');
			// check permissions according to object
			switch ($object_type) {
				case "account":
					$dao_accounts = new DaoAccounts();
					$result = $dao_accounts->getList(array(
						'f_account' => $object_id
					), 0);
					if ($result["total"] == 1) {
						return true;
					}
					break;
				case "contact":
					$dao_contacts = new DaoContacts();
					$contact = $dao_contacts->getContact($object_id);
					if ($contact->getAccount_id() == $account_id) {
						return true;
					}
					break;
				case "company":
					$dao_companies = new DaoCompanies();
					$company = $dao_companies->getCompany($object_id);
					if ($company->getAccount_id() == $account_id) {
						return true;
					}
					break;
				case "meeting":
					$dao_meetings = new DaoMeetings();
					$meeting = $dao_meetings->getMeeting($object_id);
					if ($meeting->getContact()->getAccount_id() == $account_id) {
						return true;
					}
					break;
				case "alert":
					$dao_alerts = new DaoAlerts();
					$alert = $dao_alerts->getAlert($object_id);
					if ($alert->getContact()->getAccount_id() == $account_id) {
						return true;
					}
					break;
				default:
					break;
			}
		}

		//-------------------------------------
		// if the user has no access to the component :
		//	if redirection == TRUE, redirection to error page
		//	else (redirection == FALSE), return FALSE
		if($redirection) {
			$_SESSION['sess_message'] = "Vous n'avez pas accès à cette information.";
			$_SESSION['sess_message_type'] = TYPE_MSG_ERROR;
			header("Location: index.php");
			exit;
		}
		return false;
	}


	/**
	 * check if a user has access to a component
	 *
	 * @param int $user_id	the user_id
	 * @param string $component	the component name
	 *
	 * @return boolean
	 */
	public function checkAccessByUser($user_id, $component)
	{
		$query = $this->_pdo->prepare("SELECT COUNT(*) AS total
			FROM user_component_relationships
				LEFT JOIN components ON (component_id = rel_component_id)
			WHERE component_name = :component
				AND rel_user_id = :id");
		$query->execute(array(
			'component' => $component,
			'id' => $user_id,
		));

		$total = $query->fetchAll(PDO::FETCH_COLUMN, 0);
		if($total[0] == 0) {
			return false;
		}
		return true;
	}


	/**
	 * get access (components list) by user
	 *
	 * @param int $user_id
	 *
	 * @return array
	 */
	public function getAccessByUser($user_id)
	{
		$components = array();
		$query = $this->_pdo->prepare("SELECT rel_component_id
			FROM user_component_relationships
			WHERE rel_user_id = :id");
		$query->execute(array('id' => $user_id));
		while($data = $query->fetch(PDO::FETCH_OBJ)) {
			$components[] = $data->rel_component_id;
		}
		$query->closeCursor();
		return $components;
	}


	/**
	 * set access rights for a user
	 *
	 * @param int $user_id
	 * @param array $perms
	 *
	 * @return void
	 */
	public function setAccessByUser($user_id, $perms)
	{
		$this->deleteAccessByUser($user_id);
		$query = $this->_pdo->prepare("INSERT INTO user_component_relationships
			SET rel_user_id = :id,
				rel_component_id = :component");
		foreach($perms as $component_id) {
			$query->execute(array(
				'id' => $user_id,
				'component' => $component_id
			));
		}
		$query->closeCursor();
	}


	/**
	 * set account access for a user
	 *
	 * @param int $user_id
	 * @param array $perms
	 *
	 * @return void
	 */
	public function setAccountByUser($user_id, $perms)
	{
		$this->deleteAccountByUser($user_id);
		$this->addAccountByUser($user_id, $perms);
	}


	/**
	 * add accounts to a user
	 *
	 * @param int $user_id
	 * @param array $perms
	 *
	 * @return void
	 */
	public function addAccountByUser($user_id, $perms)
	{
		$query = $this->_pdo->prepare("INSERT INTO user_account_relationships
			SET rel_user_id = :id,
				rel_account_id = :account");
		foreach($perms as $account_id) {
			$query->execute(array(
				'id'      => $user_id,
				'account' => $account_id
			));
		}
		$query->closeCursor();
	}


	/**
	 * get accounts available for a user (either owner or assigned)
	 * please keep it there and do not put in DaoAccounts
	 *
	 * @param int $user_id
	 *
	 * @return type
	 */
	public function getAccountsForUser($user_id)
	{
		$sql = "SELECT rel_account_id
			FROM user_account_relationships
				LEFT JOIN accounts ON (accounts.account_id = user_account_relationships.rel_account_id)
			WHERE (rel_user_id = :user_id
				OR account_user_id = :user_id)
				AND account_active = 1
			GROUP BY rel_account_id
			ORDER BY account_name";
		$query = $this->_pdo->prepare($sql);
		$query->execute(array('user_id' => $user_id));

		$accounts = array();
		while($data = $query->fetch(PDO::FETCH_OBJ)) {
			$accounts[] = $data->rel_account_id;
		}
		$query->closeCursor();
		return $accounts;
	}


	/**
	 * delete access rights for a user
	 *
	 * @param int $user_id
	 *
	 * @return void
	 */
	public function deleteAccessByUser($user_id)
	{
		$query = $this->_pdo->prepare("DELETE FROM user_component_relationships
			WHERE rel_user_id = :id");
		$query->execute(array('id' => $user_id));
	}


	/**
	 * delete account access for a user
	 *
	 * @param int $user_id
	 *
	 * @return void
	 */
	public function deleteAccountByUser($user_id)
	{
		$query = $this->_pdo->prepare("DELETE FROM user_account_relationships
			WHERE rel_user_id = :id");
		$query->execute(array('id' => $user_id));
	}


	/**
	 * delete users access for an account
	 *
	 * @param int $account_id
	 *
	 * @return void
	 */
	public function deleteUsersByAccount($account_id)
	{
		$query = $this->_pdo->prepare("DELETE FROM user_account_relationships
			WHERE rel_account_id = :id");
		$query->execute(array('id' => $account_id));
	}


	//--------------------------------------------------------------------------
	// MANAGE COMPONENTS
	//--------------------------------------------------------------------------

	/**
	 * get components
	 *
	 * @return array $components
	 */
	public function getComponents()
	{
		$sql = "SELECT components.*
			FROM components
			ORDER BY component_order, component_name ";
		$query = $this->_pdo->query($sql);

		$components = array();
		while($data = $query->fetch(PDO::FETCH_OBJ)) {
			$components[] = new Component($data);
		}
		$query->closeCursor();

		return $components;
	}

}
?>