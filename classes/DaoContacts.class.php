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
 * The DaoContacts class is used to get and manage contacts
 *
 * @since	1.0
 * @author	Marie Kuntz / Lézard Rouge <mariek@lezard-rouge.fr>
 */

class DaoContacts
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
	 * get list of contacts, possibly paginated, ordered and filtered
	 *
	 * @param array $criteria, search & order criteria
	 * @param int $num, number of records to fetch ; default : NB_RECORDS
	 * @param int $limit, number from which to fetch ; default : 0
	 *
	 * @return
	 */
	public function getList($criteria = array(), $num = NB_RECORDS, $limit = 0)
	{
		$contacts = array();

		$sql = "SELECT SQL_CALC_FOUND_ROWS
				contacts.*,
				companies.company_id AS contact_companyId, companies.company_name AS contact_companyName
			FROM contacts
				LEFT JOIN companies ON (companies.company_id = contacts.contact_company_id) ";
		$conditions = array();
		//-----------------------------
		// filters
		//-----------------------------
		// dirty hack
		$sql .= ' WHERE 1 = 1 ';
		if(isset($criteria['f_name']) && ! empty($criteria['f_name'])) {
			$sql .= " AND (contacts.contact_lastname LIKE :name OR contacts.contact_firstname LIKE :name)";
			$conditions['name'] = "%" . $criteria['f_name'] . "%";
		}
		if(isset($criteria['f_ctc']) && ! empty($criteria['f_ctc'])) {
			$sql .= " AND (contacts.contact_lastname LIKE :ctc
				OR contacts.contact_firstname LIKE :ctc
				OR companies.company_name LIKE :ctc)";
			$conditions['ctc'] = "%" . $criteria['f_ctc'] . "%";
		}
		if(isset($criteria['f_company']) && ! empty($criteria['f_company'])) {
			$sql .= " AND (companies.company_name LIKE :company)";
			$conditions['company'] = "%" . $criteria['f_company'] . "%";
		}
		if(isset($criteria['f_company_id']) && ! empty($criteria['f_company_id'])) {
			$sql .= " AND (contacts.contact_company_id = :company_id)";
			$conditions['company_id'] = $criteria['f_company_id'];
		}
		if(isset($criteria['f_companies']) && ($criteria['f_companies'] !== '')) {
			if( ! is_array($criteria['f_companies'])) {
				$criteria['f_companies'] = explode(',', $criteria['f_companies']);
			}
			$sql .= " AND (contacts.contact_company_id IN (";
			$tmp = array();
			$i = 1;
			foreach($criteria['f_companies'] as $company_id) {
				$tmp[] = ":companies_" . $i;
				$conditions['companies_' . $i] = $company_id;
				$i++;
			}
			$tmp2 = implode(',', $tmp);
			$sql .= $tmp2 . " ))";
		}
		if(isset($criteria['f_type']) && ! empty($criteria['f_type'])) {
			$sql .= " AND (contacts.contact_type_id = :type)";
			$conditions['type'] = $criteria['f_type'];
		}
		if(isset($criteria['f_email']) && ! empty($criteria['f_email'])) {
			$sql .= " AND (contacts.contact_id IN (
				SELECT rel_contact_id
				FROM contact_meta_relationships
				WHERE (rel_meta_id = 4 OR rel_meta_id = 5) AND rel_value LIKE :email
				)
			)";
			$conditions['email'] = "%" . $criteria['f_email'] . "%";
		}
		if(isset($criteria['f_zipcity']) && ! empty($criteria['f_zipcity'])) {
			$sql .= " AND (contacts.contact_id IN (
				SELECT rel_contact_id
				FROM contact_meta_relationships
				WHERE (rel_meta_id = 7 OR rel_meta_id = 8) AND rel_value LIKE :zipcity
				)
			)";
			$conditions['zipcity'] = "%" . $criteria['f_zipcity'] . "%";
		}
		if(isset($criteria['f_phone']) && ! empty($criteria['f_phone'])) {
			// for the phone, we search as entered and with no separating space and no separating dot
			$sql .= " AND (contacts.contact_id IN (
				SELECT rel_contact_id
				FROM contact_meta_relationships
				WHERE (rel_meta_id = 2 OR rel_meta_id = 3)
					AND (rel_value LIKE :phone
						OR rel_value LIKE :phone_nospace
						OR rel_value LIKE :phone_nodot
						)
				)
			)";
			$conditions['phone'] = "%" . $criteria['f_phone'] . "%";
			$conditions['phone_nospace'] = "%" . str_replace(' ', '', $criteria['f_phone']) . "%";
			$conditions['phone_nodot'] = "%" . str_replace('.', '', $criteria['f_phone']) . "%";
		}
		// we search in a list of contact ids
		if(isset($criteria['f_contact_id']) && ! empty($criteria['f_contact_id'])) {
			if( ! is_array($criteria['f_contact_id'])) {
				$criteria['f_contact_id'] = explode(',', $criteria['f_contact_id']);
			}
			$sql .= " AND (contacts.contact_id IN (";
			$tmp = array();
			$i = 1;
			foreach($criteria['f_contact_id'] as $contact_id) {
				$tmp[] = ":contact_id_" . $i;
				$conditions['contact_id_' . $i] = $contact_id;
				$i++;
			}
			$tmp2 = implode(',', $tmp);
			$sql .= $tmp2 . " ))";
		}
		// dates
		if(isset($criteria['f_date_add_from']) && ! Utils::dateEmpty($criteria['f_date_add_from'])) {
			$sql .= " AND (contacts.contact_date_add >= :add_date_from)";
			$conditions['add_date_from'] = Utils::date2ISO($criteria['f_date_add_from']);
		}
		if(isset($criteria['f_date_add_to']) && ! Utils::dateEmpty($criteria['f_date_add_to'])) {
			$sql .= " AND (contacts.contact_date_add <= :add_date_to)";
			$conditions['add_date_to'] = Utils::date2ISO($criteria['f_date_add_to']);
		}
		if(isset($criteria['f_date_upd_from']) && ! Utils::dateEmpty($criteria['f_date_upd_from'])) {
			$sql .= " AND (contacts.contact_date_update >= :upd_date_from)";
			$conditions['upd_date_from'] = Utils::date2ISO($criteria['f_date_upd_from']);
		}
		if(isset($criteria['f_date_upd_to']) && ! Utils::dateEmpty($criteria['f_date_upd_to'])) {
			$sql .= " AND (contacts.contact_date_update <= :upd_date_to)";
			$conditions['upd_date_to'] = Utils::date2ISO($criteria['f_date_upd_to']);
		}
		// active
		if(isset($criteria['f_active']) && $criteria['f_active'] == 1) {
			$sql .= " AND (contacts.contact_active = 1)";
		}
		// by account
		if(isset($criteria['f_account']) && ! empty($criteria['f_account'])) {
			$sql .= " AND (contacts.contact_account_id = :account)";
			$conditions['account'] = $criteria['f_account'];
		}
		// else we work with the current account
		else {
			$sql .= ' AND (contacts.contact_account_id = ' . $this->_session->getSessionData('account') . ')';
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
				$sorting = ' ORDER BY contact_lastname ' . $criteria['order'] . ', contact_firstname ';
			} elseif ($criteria['sort'] == 'company') {
				$sorting = ' ORDER BY companies.company_name ' . $criteria['order'] . ', contact_lastname, contact_firstname ';
			}
		} else {
			$sorting = " ORDER BY contact_lastname, contact_firstname ";
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
			$contacts[] = new Contact($data);
		}
		$query->closeCursor();

		return array('total' => $total[0], 'results' => $contacts);

	}


	/**
	 * fetch infos for a list of contacts
	 *
	 * @param mixed str|array $contacts
	 *
	 * @return array
	 */
	public function getContactsById($contact_ids)
	{
		$contacts = array();
		if(is_array($contact_ids)) {
			$contact_ids = implode(',', $contact_ids);
		}
		if(empty($contact_ids)) {
			return $contacts;
		}
		$criteria = array('f_contact_id' => $contact_ids);
		$data = $this->getList($criteria, 0);

		foreach($data['results'] as $contact) {
			$contacts[$contact->getId()] = $contact;
		}
		return $contacts;
	}


	/**
	 * fetch infos for a list of contacts by company
	 *
	 * @param mixed str|array $company_ids
	 *
	 * @return array $contacts
	 */
	public function getContactsByCompanies($company_ids)
	{
		$contacts = array();
		if(is_array($company_ids)) {
			$company_ids = implode(',', $company_ids);
		}
		if($company_ids === '') {
			return $contacts;
		}
		$criteria = array(
			'f_companies' => $company_ids,
			'sort' => 'company',
			'order' => 'asc');
		$data = $this->getList($criteria, 0);

		foreach($data['results'] as $contact) {
			$contacts[$contact->getCompany_id()][] = $contact;
		}
		return $contacts;
	}


	/**
	 * get informations about a contact
	 *
	 * @param int $contact_id
	 *
	 * @return object Contact
	 */
	public function getContact($contact_id)
	{
		$query = $this->_pdo->prepare("SELECT *
			FROM contacts
			WHERE contact_id = :contact_id");
		$query_result = $query->execute(array('contact_id' => $contact_id));
		if( ! $query_result) {
			Utils::dump($query->errorInfo());
		}
		$contact = $query->fetch(PDO::FETCH_OBJ);
		$query->closeCursor();
		if( ! $contact) {
			return new Contact(array());
		}
		return new Contact($contact);
	}


	/**
	 * check if contact exists by id
	 *
	 * @param int $contact_id
	 *
	 * @return boolean
	 */
	public function contactExists($contact_id)
	{
		$query = $this->_pdo->prepare("SELECT COUNT(*) as total
			FROM contacts
			WHERE contact_account_id = " . $this->_session->getSessionData('account') . "
				AND contact_id = :contact_id");
		$query->execute(array('contact_id' => $contact_id));
		$total = $query->fetchAll(PDO::FETCH_COLUMN, 0);
		if($total[0] == 0) {
			return false;
		}
		return true;
	}


	/**
	 * add a contact
	 *
	 * @param object Contact
	 *
	 * @return object Contact
	 */
	public function addContact(Contact $contact)
	{
		// type
		$type_id = $contact->getType_id();
		// if empty, fetch the default
		if(empty($type_id)) {
			$dao_contact_type = new DaoContactTypes();
			$contact_type = $dao_contact_type->getDefaultContactType();
			$type_id = $contact_type->getId();
		}

		// company
		$dao_company = new DaoCompanies();
		$existing_company = $dao_company->setCompany($contact->getCompany_id(), $contact->getCompany_name(), $type_id);

		// contact
		$query = $this->_pdo->prepare("INSERT INTO `contacts`
			SET `contact_account_id`  = :account,
				`contact_lastname`    = :lastname,
				`contact_firstname`   = :firstname,
				`contact_company_id`  = :company,
				`contact_type_id`     = :type,
				`contact_comments`    = :comment,
				`contact_date_add`    = :date_add,
				`contact_date_update` = :date_update,
				`contact_active`      = :active");
		$query_result = $query->execute(array(
				'account'     => $this->_session->getSessionData('account'),
				'lastname'    => $contact->getLastname(),
				'firstname'   => $contact->getFirstname(),
				'company'     => $existing_company->getId(),
				'type'        => $type_id,
				'comment'     => $contact->getComments(),
				'date_add'    => $contact->getDate_add(),
				'date_update' => $contact->getDate_update(),
				'active'      => $contact->getActive()
			)
		);
		if( ! $query_result) {
			Utils::dump($query->errorInfo());
		}
		$contact_id = $this->_pdo->lastInsertId();
		$query->closeCursor();

		$contact->hydrate(array(
			'contact_id' => $contact_id,
			'contact_company_id' => $existing_company->getId(),
			'contact_type_id' => $type_id
		));

		// metas
		$dao_metas_rel = new DaoContactMetaRelationships();
		$dao_metas_rel->addContactMetas($contact_id, $contact->getMeta());

		return $contact;
	}


	/**
	 * update a contact
	 *
	 * @param object Contact
	 *
	 * @return object Contact
	 */
	public function updateContact(Contact $contact)
	{
		// type
		$type_id = $contact->getType_id();
		// if empty, fetch the default
		if(empty($type_id)) {
			$dao_contact_type = new DaoContactTypes();
			$contact_type = $dao_contact_type->getDefaultContactType();
			$type_id = $contact_type->getId();
		}

		// company
		$dao_company = new DaoCompanies();
		$existing_company = $dao_company->setCompany($contact->getCompany_id(), $contact->getCompany_name(), $type_id);

		// contact
		$query = $this->_pdo->prepare("UPDATE `contacts`
			SET `contact_lastname`    = :lastname,
				`contact_firstname`   = :firstname,
				`contact_company_id`  = :company,
				`contact_type_id`     = :type,
				`contact_comments`    = :comment,
				`contact_date_update` = :date_update,
				`contact_active`      = :active
			WHERE contact_id = :id");
		$query_result = $query->execute(array(
				'lastname'    => $contact->getLastname(),
				'firstname'   => $contact->getFirstname(),
				'company'     => $existing_company->getId(),
				'type'        => $type_id,
				'comment'     => $contact->getComments(),
				'date_update' => $contact->getDate_update(),
				'active'      => $contact->getActive(),
				'id'          => $contact->getId()
			)
		);
		if( ! $query_result) {
			Utils::dump($query->errorInfo());
		}
		$contact->hydrate(array(
			'contact_company_name' => '',
			'contact_company_id' => $existing_company->getId(),
			'contact_type_id' => $type_id
		));
		$query->closeCursor();

		// metas
		$contacts_meta = $contact->getMeta();
		if(! empty($contacts_meta)) {
			$dao_metas_rel = new DaoContactMetaRelationships();
			$dao_metas_rel->updateContactMetas($contact->getId(), $contact->getMeta());
		}

		return $contact;
	}


	/**
	 * update contacts
	 *
	 * @param array $updates, attributes to update
	 * @param array $where, conditions to match
	 */
	function updateContactBy($updates, $where)
	{
		if(! empty($updates)) {
			$sql = "UPDATE `contacts` SET ";
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
	 * activate a contact
	 *
	 * @param Contact
	 */
	public function activateContact(Contact $contact)
	{
		$update = array('contact_active' => '1');
		$where = array('contact_id' => $contact->getId());
		$this->updateContactBy($update, $where);
	}


	/**
	 * deactivate a contact
	 *
	 * @param Contact
	 */
	public function deactivateContact(Contact $contact)
	{
		$update = array('contact_active' => '0');
		$where = array('contact_id' => $contact->getId());
		$this->updateContactBy($update, $where);
	}


	/**
	 * delete a contact
	 *
	 * @param int $contact_id
	 *
	 * @return void
	 */
	public function delContact(Contact $contact)
	{
		// delete meetings
		$dao_meetings = new DaoMeetings();
		$dao_meetings->delContactMeetings($contact->getId());
		// delete alerts
		$dao_alerts = new DaoAlerts();
		$dao_alerts->delContactAlerts($contact->getId());
		// delete contact infos
		$dao_metas_rel = new DaoContactMetaRelationships();
		$dao_metas_rel->deleteContactMetas($contact->getId());
		// delete contact
		$query = $this->_pdo->prepare("DELETE FROM contacts WHERE contact_id = :id");
		$query_result = $query->execute(array('id' => $contact->getId()));
		if( ! $query_result) {
			Utils::dump($query->errorInfo());
		}
		$query->closeCursor();
		// delete attachments
		$dao_attachments = new DaoAttachments();
		$dao_attachments->delContactAttachments($contact->getId());
	}


	/**
	  * delete an account's contacts
	  *
	  * @param object Account $account
	  *
	  * @return void
	  */
	 public function delContactsByAccount(Account $account)
	 {
		 // fetch all contacts
		 $criteria = array('f_account' => $account->getId());
		 $contacts = $this->getList($criteria, 0);
		 // del each contact
		 foreach ($contacts['results'] as $contact) {
			 $this->delContact($contact);
		 }
	 }


	 /**
	  * delete a company's contacts
	  *
	  * @param object Company $company
	  *
	  * @return void
	  */
	 public function delContactsByCompany(Company $company)
	 {
		 // fetch all contacts
		 $criteria = array('f_company_id' => $company->getId(), 'f_account' => $company->getAccount_id());
		 $contacts = $this->getList($criteria, 0);
		 // del each contact
		 foreach ($contacts['results'] as $contact) {
			 $this->delContact($contact);
		 }
	 }


	 /**
	  * activate a company's contacts
	  *
	  * @param object Company $company
	  *
	  * @return void
	  */
	 public function activateContactsByCompany(Company $company)
	 {
		 // fetch all contacts
		 $criteria = array('f_company_id' => $company->getId(), 'f_account' => $company->getAccount_id());
		 $contacts = $this->getList($criteria, 0);
		 // deactivate each contact
		 foreach ($contacts['results'] as $contact) {
			 $this->activateContact($contact);
		 }
	 }


	 /**
	  * deactivate a company's contacts
	  *
	  * @param object Company $company
	  *
	  * @return void
	  */
	 public function deactivateContactsByCompany(Company $company)
	 {
		 // fetch all contacts
		 $criteria = array('f_company_id' => $company->getId(), 'f_account' => $company->getAccount_id());
		 $contacts = $this->getList($criteria, 0);
		 // deactivate each contact
		 foreach ($contacts['results'] as $contact) {
			 $this->deactivateContact($contact);
		 }
	 }


}

/* End of file DaoContacts.class.php */
/* Location: ./classes/DaoContacts.class.php */
