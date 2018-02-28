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
 * The DaoAlerts class is used to get and manage the alerts
 *
 * @since	1.0
 * @author	Marie Kuntz / Lézard Rouge <mariek@lezard-rouge.fr>
 */

class DaoAlerts
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
	 * get list of alerts, possibly paginated, ordered and filtered
	 *
	 * @param array $criteria, search & order criteria
	 * @param int $num, number of records to fetch ; default : NB_RECORDS
	 * @param int $limit, number from which to fetch ; default : 0
	 *
	 * @return
	 */
	public function getList($criteria = array(), $num = NB_RECORDS, $limit = 0)
	{
		$alerts = array();

		$sql = "SELECT SQL_CALC_FOUND_ROWS
				alerts.*, contacts.*
			FROM alerts
				LEFT JOIN contacts ON (contacts.contact_id = alerts.alert_contact_id)
				LEFT JOIN users ON (users.user_id = alerts.alert_user_id)";
		//-----------------------------
		// filters
		//-----------------------------
		$conditions = array();
		// always work with an account (if no account selected, no alerts shown)
		// except for sending alerts
		if(isset($criteria['f_account_all']) && $criteria['f_account_all'] == 1) {
			// That's for alerts
			$sql .= ' WHERE 1 = 1 ';
		} elseif(isset($criteria['f_account']) && ! empty($criteria['f_account'])) {
			$sql .= ' WHERE contacts.contact_account_id = :account';
			$conditions['account'] = $criteria['f_account'];
		} elseif(isset($criteria['f_account_ids']) && ! empty($criteria['f_account_ids'])) {
			if( ! is_array($criteria['f_account_ids'])) {
				$criteria['f_account_ids'] = explode(',', $criteria['f_account_ids']);
			}
			$sql .= " WHERE contacts.contact_account_id IN (";
			$tmp = array();
			$i = 1;
			foreach($criteria['f_account_ids'] as $account_id) {
				$tmp[] = ":account_id_" . $i;
				$conditions['account_id_' . $i] = $account_id;
				$i++;
			}
			$tmp2 = implode(',', $tmp);
			$sql .= $tmp2 . " )";
		} elseif($this->_session->getSessionData('account') !== false && $this->_session->getSessionData('account') != '') {
			$sql .= ' WHERE contacts.contact_account_id = ' . $this->_session->getSessionData('account');
		} else {
			$sql .= ' WHERE contacts.contact_account_id = 0';
		}
		if(isset($criteria['f_name']) && ! empty($criteria['f_name'])) {
			$sql .= " AND (contacts.contact_lastname LIKE :name OR contacts.contact_firstname LIKE :name)";
			$conditions['name'] = "%" . $criteria['f_name'] . "%";
		}
		if(isset($criteria['f_ctc']) && ! empty($criteria['f_ctc'])) {
			$sql .= " AND (alerts.alert_contact_id = :ctc)";
			$conditions['ctc'] = $criteria['f_ctc'];
		}
		if(isset($criteria['f_cid']) && ! empty($criteria['f_cid'])) {
			if( ! is_array($criteria['f_cid'])) {
				$criteria['f_cid'] = explode(',', $criteria['f_cid']);
			}
			$sql .= " AND (contacts.contact_id IN (";
			$tmp = array();
			$i = 1;
			foreach($criteria['f_cid'] as $cid) {
				$tmp[] = ":cid_" . $i;
				$conditions['cid_' . $i] = $cid;
				$i++;
			}
			$tmp2 = implode(',', $tmp);
			$sql .= $tmp2 . " ))";
		}
		// dates
		if(isset($criteria['f_date_from']) && ! empty($criteria['f_date_from'])) {
			$sql .= " AND (alerts.alert_date >= :date_from)";
			$conditions['date_from'] = Utils::date2ISO($criteria['f_date_from']);
		}
		if(isset($criteria['f_date_to']) && ! empty($criteria['f_date_to'])) {
			$sql .= " AND (alerts.alert_date <= :date_to)";
			$conditions['date_to'] = Utils::date2ISO($criteria['f_date_to']);
		}
		// keyword
		if(isset($criteria['f_keyword']) && ! empty($criteria['f_keyword'])) {
			$sql .= " AND (alerts.alert_comments LIKE :keyword)";
			$conditions['keyword'] = "%" . $criteria['f_keyword'] . "%";
		}
		// user assignment
		if(isset($criteria['f_user']) && ! empty($criteria['f_user'])) {
			$sql .= " AND (alerts.alert_user_id LIKE :user_id)";
			$conditions['user_id'] = "%" . $criteria['f_user'] . "%";
		}
		// done/not done
		if(isset($criteria['f_done']) && $criteria['f_done'] != '-1') {
			$sql .= " AND (alerts.alert_done = :done)";
			$conditions['done'] = intval($criteria['f_done']);
		}
		// priority
		if(isset($criteria['f_priority']) && $criteria['f_priority'] != '-1') {
			$sql .= " AND (alerts.alert_priority = :priority)";
			$conditions['priority'] = intval($criteria['f_priority']);
		}

		//-----------------------------
		// sorting
		//-----------------------------
		if(isset($criteria['sort'])) {
			if($criteria['order'] != 'desc') {
				$criteria['order'] = 'asc';
			}
			if($criteria['sort'] == 'name') {
				$sorting = ' ORDER BY contact_lastname ' . $criteria['order'] . ', contact_firstname, alert_date, alert_priority DESC ';
			} elseif($criteria['sort'] == 'priority') {
				$sorting = ' ORDER BY alert_priority ' . $criteria['order'] . ', alert_date, contact_lastname, contact_firstname ';
			} elseif($criteria['sort'] == 'user') {
				$sorting = ' ORDER BY user_lastname ' . $criteria['order'] . ', user_firstname, alert_date, alert_priority DESC ';
			} else {
				$sorting = ' ORDER BY alert_date ' . $criteria['order'] . ', alert_priority DESC, contact_lastname, contact_firstname ';
			}
		} else {
			$sorting = " ORDER BY alert_date, alert_priority DESC, contact_lastname, contact_firstname ";
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
			$alerts[] = new Alert($data);
		}
		$query->closeCursor();

		return array('total' => $total[0], 'results' => $alerts);

	}


	/**
	 * get the alerts for nightly send
	 * method is static to prevent to load session class
	 *
	 * @return array $alerts
	 */
	public static function getAlertsForRoutine()
	{
		$pdo = myPDO::getInstance();
		$today = date('Y-m-d');
		$sql = "SELECT alerts.*, contacts.*,
				users.user_lastname, users.user_firstname, users.user_email,
				companies.company_name
			FROM alerts
				LEFT JOIN users ON (users.user_id = alerts.alert_user_id)
				LEFT JOIN contacts ON (contacts.contact_id = alerts.alert_contact_id)
				LEFT JOIN companies ON (companies.company_id = contacts.contact_company_id)
			 WHERE  (alerts.alert_date = :today)
				AND (alerts.alert_done = 0)";
		$conditions = array('today' => $today);

		$query = $pdo->prepare($sql);
		$query->execute($conditions);

		$alerts = $query->fetchAll(PDO::FETCH_OBJ);
		$query->closeCursor();
		return $alerts;
	}



	/**
	 * get informations about an alert
	 *
	 * @param int $alert_id
	 *
	 * @return object Alert
	 */
	public function getAlert($alert_id)
	{
		$query = $this->_pdo->prepare("SELECT *
			FROM alerts
			WHERE alert_id = :alert_id");
		$query_result = $query->execute(array('alert_id' => $alert_id));
		if( ! $query_result) {
			Utils::dump($query->errorInfo());
		}
		$alert = $query->fetch(PDO::FETCH_OBJ);
		$query->closeCursor();
		if( ! $alert) {
			return false;
		}
		return new Alert($alert);
	}


	/**
	 * check if alert exists by id
	 *
	 * @param int $alert_id
	 *
	 * @return boolean
	 */
	public function alertExists($alert_id)
	{
		$query = $this->_pdo->prepare("SELECT COUNT(*) as total
			FROM alerts
			WHERE alert_id = :alert_id");
		$query->execute(array('alert_id' => $alert_id));
		$total = $query->fetchAll(PDO::FETCH_COLUMN, 0);
		if($total[0] == 0) {
			return false;
		}
		return true;
	}


	/**
	 * add a alert
	 *
	 * @param object Alert
	 *
	 * @return object Alert
	 */
	public function addAlert(Alert $alert)
	{
		// alert
		$query = $this->_pdo->prepare("INSERT INTO `alerts`
			SET `alert_contact_id` = :contact,
				`alert_user_id`    = :user,
				`alert_priority`   = :priority,
				`alert_date`       = :date,
				`alert_comments`   = :comment,
				`alert_done`       = 0");
		$query_result = $query->execute(array(
				'contact'  => $alert->getContact_id(),
				'user'     => $alert->getUser_id(),
				'priority' => $alert->getPriority(),
				'date'     => $alert->getDate(),
				'comment'  => $alert->getComments()
			)
		);
		if( ! $query_result) {
			Utils::dump($query->errorInfo());
		}
		$alert_id = $this->_pdo->lastInsertId();
		$query->closeCursor();

		$alert->hydrate(array(
			'alert_id' => $alert_id
		));

		return $alert;
	}


	/**
	 * update a alert
	 *
	 * @param object Alert
	 *
	 * @return object Alert
	 */
	public function updateAlert(Alert $alert)
	{
		// alert
		$query = $this->_pdo->prepare("UPDATE `alerts`
			SET `alert_contact_id` = :contact,
				`alert_user_id`    = :user,
				`alert_priority`   = :priority,
				`alert_date`       = :date,
				`alert_comments`   = :comment,
				`alert_done`       = :done
			WHERE alert_id = :id");
		$query_result = $query->execute(array(
				'contact'  => $alert->getContact_id(),
				'user'     => $alert->getUser_id(),
				'priority' => $alert->getPriority(),
				'date'     => $alert->getDate(),
				'comment'  => $alert->getComments(),
				'done'     => $alert->getDone(),
				'id'       => $alert->getId()
			)
		);
		if( ! $query_result) {
			Utils::dump($query->errorInfo());
		}
		$query->closeCursor();

		return $alert;
	}


	/**
	 * activate or deactivate an alert
	 *
	 * @param int $alert_id
	 * @param int $done
	 *
	 * @return boolean  TRUE
	 */
	public function switchAlert($alert_id, $done)
	{
		$query = $this->_pdo->prepare("UPDATE `alerts`
			SET `alert_done` = :done
			WHERE alert_id   = :id");
		$query_result = $query->execute(array(
				'done' => $done,
				'id'   => $alert_id
			)
		);
		if( ! $query_result) {
			Utils::dump($query->errorInfo());
		}
		$query->closeCursor();

		return TRUE;
	}


	/**
	 * delete a alert
	 *
	 * @param int $alert_id
	 *
	 * @return void
	 */
	public function delAlert(Alert $alert)
	{
		// delete alert
		$query = $this->_pdo->prepare("DELETE FROM alerts WHERE alert_id = :id");
		$query_result = $query->execute(array('id' => $alert->getId()));
		if( ! $query_result) {
			Utils::dump($query->errorInfo());
		}
		$query->closeCursor();
	 }


	 /**
	  * delete a contact's alerts
	  *
	  * @param int $contact_id
	  */
	 public function delContactAlerts($contact_id)
	 {
		$query = $this->_pdo->prepare("DELETE FROM alerts WHERE alert_contact_id = :id");
		$query_result = $query->execute(array('id' => $contact_id));
		if( ! $query_result) {
			Utils::dump($query->errorInfo());
		}
		$query->closeCursor();
	 }


}

/* End of file DaoAlerts.class.php */
/* Location: ./classes/DaoAlerts.class.php */