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
 * The DaoMeetings class is used to get and manage meetings with contacts
 *
 * @since	1.0
 * @author	Marie Kuntz / Lézard Rouge <mariek@lezard-rouge.fr>
 */

class DaoMeetings
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
	 * get list of meetings, possibly paginated, ordered and filtered
	 *
	 * @param array $criteria search & order criteria
	 * @param int $num        number of records to fetch ; default : NB_RECORDS
	 * @param int $limit      number from which to fetch ; default : 0
	 *
	 * @return
	 */
	public function getList($criteria = array(), $num = NB_RECORDS, $limit = 0)
	{

		$meetings = array();

		$sql = "SELECT SQL_CALC_FOUND_ROWS
				meetings.*,
				meeting_types.meeting_type_name
			FROM meetings
				LEFT JOIN meeting_types ON (meeting_types.meeting_type_id = meetings.meeting_type_id)
				LEFT JOIN contacts ON (contacts.contact_id = meetings.meeting_contact_id)
				LEFT JOIN companies ON (companies.company_id = contacts.contact_company_id) ";
		//-----------------------------
		// filters
		//-----------------------------
		$conditions = array();
		// always work with current account, except for admin deletion
		if(isset($criteria['f_account_all']) && $criteria['f_account_all'] == 1) {
			$sql .= ' WHERE 1 = 1 ';
		} else {
			$sql .= ' WHERE contacts.contact_account_id = ' . $this->_session->getSessionData('account');
		}
		if ( ! empty($criteria)) {
			if(isset($criteria['f_ctc']) && ! empty($criteria['f_ctc'])) {
				$sql .= " AND (contacts.contact_id = :ctc)";
				$conditions['ctc'] = $criteria['f_ctc'];
			}
			if(isset($criteria['f_name']) && ! empty($criteria['f_name'])) {
				$sql .= " AND (contacts.contact_lastname LIKE :name OR contacts.contact_firstname LIKE :name)";
				$conditions['name'] = "%" . $criteria['f_name'] . "%";
			}
			if(isset($criteria['f_cid']) && ! empty($criteria['f_cid'])) {
				if( ! is_array($criteria['f_cid'])) {
					$criteria['f_cid'] = explode(',', $criteria['f_cid']);
				}
				$sql .= " AND (contacts.contact_id IN (";
				$tmp = array();
				$i = 1;
				foreach($criteria['f_cid'] as $contact_id) {
					$tmp[] = ":cid_" . $i;
					$conditions['cid_' . $i] = $contact_id;
					$i++;
				}
				$tmp2 = implode(',', $tmp);
				$sql .= $tmp2 . " ))";
			}
			if(isset($criteria['f_company']) && ! empty($criteria['f_company'])) {
				$sql .= " AND (companies.company_name LIKE :company)";
				$conditions['company'] = "%" . $criteria['f_company'] . "%";
			}
			if(isset($criteria['f_type']) && ! empty($criteria['f_type'])) {
				$sql .= " AND (meetings.meeting_type_id = :type)";
				$conditions['type'] = $criteria['f_type'];
			}
			if(isset($criteria['f_keyword']) && ! empty($criteria['f_keyword'])) {
				$sql .= " AND (meetings.meeting_comments LIKE :keyword)";
				$conditions['keyword'] = "%" . $criteria['f_keyword'] . "%";
			}
			// dates
			if(isset($criteria['f_date_from']) && ! empty($criteria['f_date_from'])) {
				$sql .= " AND (meetings.meeting_date >= :date_from)";
				$conditions['date_from'] = Utils::date2ISO($criteria['f_date_from']);
			}
			if(isset($criteria['f_date_to']) && ! empty($criteria['f_date_to'])) {
				// transforms the fr date to iso date
				$sql .= " AND (meetings.meeting_date <= :date_to)";
				$conditions['date_to'] = Utils::date2ISO($criteria['f_date_to']);
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
				$sorting = ' ORDER BY contact_lastname ' . $criteria['order'] . ', contact_firstname ';
			} elseif ($criteria['sort'] == 'company') {
				$sorting = ' ORDER BY companies.company_name ' . $criteria['order'] . ', contact_lastname, contact_firstname ';
			} else {
				$sorting = ' ORDER BY meeting_date ' . $criteria['order'] . ', meeting_id ' . $criteria['order'];
			}
		} else {
			$sorting = " ORDER BY meeting_date DESC, meeting_id DESC ";
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
			$meetings[] = new Meeting($data);
		}
		$query->closeCursor();

		return array('total' => $total[0], 'results' => $meetings);

	}


	/**
	 * get informations about a meeting
	 *
	 * @param int $meeting_id
	 *
	 * @return object Meeting
	 */
	public function getMeeting($meeting_id)
	{
		$query = $this->_pdo->prepare("SELECT *
			FROM meetings
			WHERE meeting_id = :meeting_id");
		$query_result = $query->execute(array('meeting_id' => $meeting_id));
		if( ! $query_result) {
			Utils::dump($query->errorInfo());
		}
		$meeting = $query->fetch(PDO::FETCH_OBJ);
		$query->closeCursor();
		if( ! $meeting) {
			return new Meeting();
		}
		return new Meeting($meeting);
	}


	/**
	 * check if meeting exists by id
	 *
	 * @param int $meeting_id
	 *
	 * @return boolean
	 */
	public function meetingExists($meeting_id)
	{
		$query = $this->_pdo->prepare("SELECT COUNT(*) as total
			FROM meetings
			WHERE meeting_id = :meeting_id");
		$query->execute(array('meeting_id' => $meeting_id));
		$total = $query->fetchAll(PDO::FETCH_COLUMN, 0);
		if($total[0] == 0) {
			return false;
		}
		return true;
	}


	/**
	 * add a meeting
	 *
	 * @param object Meeting
	 *
	 * @return object Meeting
	 */
	public function addMeeting(Meeting $meeting)
	{
		// meeting
		$query = $this->_pdo->prepare("INSERT INTO `meetings`
			SET `meeting_contact_id` = :contact,
				`meeting_date`       = :date,
				`meeting_type_id`    = :type,
				`meeting_comments`   = :comment");
		$query_result = $query->execute(array(
				'contact' => $meeting->getContact_id(),
				'date'    => $meeting->getDate(),
				'type'    => $meeting->getType_id(),
				'comment' => $meeting->getComments()
			)
		);
		if( ! $query_result) {
			Utils::dump($query->errorInfo());
		}
		$meeting_id = $this->_pdo->lastInsertId();
		$query->closeCursor();

		$meeting->hydrate(array(
			'meeting_id' => $meeting_id
		));

		return $meeting;
	}


	/**
	 * update a meeting
	 *
	 * @param object Meeting
	 *
	 * @return object Meeting
	 */
	public function updateMeeting(Meeting $meeting)
	{
		// meeting
		$query = $this->_pdo->prepare("UPDATE `meetings`
			SET `meeting_contact_id` = :contact,
				`meeting_date`       = :date,
				`meeting_type_id`    = :type,
				`meeting_comments`   = :comment
			WHERE meeting_id = :id");
		$query_result = $query->execute(array(
				'contact' => $meeting->getContact_id(),
				'date'    => $meeting->getDate(),
				'type'    => $meeting->getType_id(),
				'comment' => $meeting->getComments(),
				'id'      => $meeting->getId()
			)
		);
		if( ! $query_result) {
			Utils::dump($query->errorInfo());
		}
		$query->closeCursor();

		return $meeting;
	}


	/**
	 * delete a meeting
	 *
	 * @param Meeting $meeting
	 *
	 * @return void
	 */
	public function delMeeting(Meeting $meeting)
	{
		// delete meeting
		$query = $this->_pdo->prepare("DELETE FROM meetings WHERE meeting_id = :id");
		$query_result = $query->execute(array('id' => $meeting->getId()));
		if( ! $query_result) {
			Utils::dump($query->errorInfo());
		}
		$query->closeCursor();
		// delete attachments
		$dao_attachments = new DaoAttachments();
		$dao_attachments->delMeetingAttachments($meeting->getId());
	}


	/**
	 * delete a contact's meetings
	 *
	 * @param int $contact_id
	 */
	public function delContactMeetings($contact_id)
	{
		$criteria = array('f_ctc' => $contact_id, 'f_account_all' => 1);
		$meetings = $this->getList($criteria, 0, 0);
		foreach ($meetings['results'] as $meeting) {
			$this->delMeeting($meeting);
		}
	}


}

/* End of file DaoMeetings.class.php */
/* Location: ./classes/DaoMeetings.class.php */