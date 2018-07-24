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
 * The contact class is used to handle contacts
 *
 * @since	1.0
 * @author	Marie Kuntz / Lézard Rouge <mariek@lezard-rouge.fr>
 */

class Contact
{

	/* private */
	private $_id;
	private $_lastname;
	private $_firstname;
	private $_comments;
	private $_account_id;
	private $_company_id;
	private $_company_name;
	private $_type_id;
	private $_date_add;
	private $_date_update;
	private $_active;
	private $_meta;


	/* constants */
	/* none for now */

	/**
	 * constructor
	 *
	 * @param array $data for hydration
	 */
	public function __construct($data = array())
	{
		$this->hydrate($data);
	}


	/* getters */
	public function getId ()
	{
		return $this->_id;
	}
	public function getLastname ()
	{
		return $this->_lastname;
	}
	public function getFirstname ()
	{
		return $this->_firstname;
	}
	public function getFullname()
	{
		$fullname = '';
		if( ! empty($this->_firstname) || ! empty($this->_lastname)) {
			$fullname = $this->_firstname . ' ' . $this->_lastname;
			if( ! empty($this->_company_id)) {
				$company = $this->getCompany();
				$fullname .= ' (' . $company->getName() . ')';
			}
		}
		return $fullname;
	}
	public function getName()
	{
		$name = '';
		if( ! empty($this->_firstname) || ! empty($this->_lastname)) {
			$name = $this->_firstname . ' ' . $this->_lastname;
		}
		return $name;
	}
	public function getAccount_id ()
	{
		return $this->_account_id;
	}
	public function getType_id ()
	{
		return $this->_type_id;
	}
	public function getComments ()
	{
		return $this->_comments;
	}
	public function getDate_add ()
	{
		return $this->_date_add;
	}
	public function getDate_add_format ()
	{
		return Utils::date2Fr($this->_date_add);
	}
	public function getDate_update ()
	{
		return $this->_date_update;
	}
	public function getDate_update_format ()
	{
		return Utils::date2Fr($this->_date_update);
	}
	public function getActive ()
	{
		return $this->_active;
	}
	public function getCompany_id ()
	{
		return $this->_company_id;
	}
	public function getCompany_name ()
	{
		return $this->_company_name;
	}
	/**
	 * fetch the contact company
	 *
	 * @return object Company
	 */
	public function getCompany ()
	{
		$dao_company = new DaoCompanies();
		$company = $dao_company->getCompany($this->_company_id);
		return $company;
	}
	public function getType ()
	{
		$dao_types = new DaoContactTypes();
		$type = $dao_types->getContactType($this->_type_id);
		return $type;
	}
	public function getMeta ()
	{
		return $this->_meta;
	}



	/* setters */
	public function setId ($id)
	{
		$id = (int) $id;
	    $this->_id = $id;
	}
	public function setLastname ($lastname)
	{
		$this->_lastname = trim($lastname);
	}
	public function setFirstname ($firstname)
	{
		$this->_firstname = trim($firstname);
	}
	public function setCompany_id ($company_id)
	{
		$this->_company_id = (int) $company_id;
	}
	public function setCompany_name ($company_name)
	{
		$this->_company_name = trim($company_name);
	}
	public function setAccount_id ($account)
	{
		$this->_account_id = (int) $account;
	}
	public function setType_id ($type)
	{
		$this->_type_id = (int) $type;
	}
	public function setComments ($comment)
	{
		$this->_comments = trim($comment);
	}
	public function setDate_add ($date)
	{
		if( ! empty($date) && Utils::checkDateValidity($date, 'iso')) {
			$this->_date_add = $date;
		}
	}
	public function setDate_update ($date)
	{
		if( ! empty($date) && Utils::checkDateValidity($date, 'iso')) {
			$this->_date_update = $date;
		}
	}
	public function setActive ($active)
	{
		$active = (int) $active;
		if($active != 0) {
			$active = 1;
		}
	    $this->_active = $active;
	}
	public function setMeta($metas = array())
	{
		$this->_meta = (array)$this->_meta+(array)$metas;
	}


	/**
	 * object hydration
	 * please pay attention : in DB, fields are prefixed, so you have to del the prefix first
	 *
	 * @param object $data
	 */
	public function hydrate($data)
	{
		foreach ($data as $key => $value) {
			$method = 'set' . ucfirst(str_replace('contact_', "", $key));
			if (method_exists($this, $method)) {
				$this->$method($value);
			}
		}
	}


	/**
	 * exports contacts
	 */
	public function exportContacts()
	{
		$file_class = new Files();
		$dao_companies = new DaoCompanies();
		$companies_list = $dao_companies->getList(array('sort' => 'name'), 0, 0);
		$company_ids = array();
		foreach ($companies_list['results'] as $company) {
			$company_ids[] = $company->getId();
		}
		// fetch metas for companies
		$dao_company_metas = new DaoCompanyMetas();
		$company_metas = $dao_company_metas->getCompanyMetas(array('f_active' => 1), null, 0);
		$company_meta_array = array();
		foreach ($company_metas['results'] as $meta) {
			$company_meta_array[] = $meta->getId();
		}
		$dao_company_metas_rel = new DaoCompanyMetaRelationships();
		$company_infos = $dao_company_metas_rel->getMetasforCompanies($company_ids, $company_meta_array);
		// add 0 for contacts not attached to a company
		$company_ids[] = 0;

		/* fetch contacts */
		$dao = new DaoContacts();
		$contacts_list = $dao->getContactsByCompanies($company_ids);
		$contact_ids = array();
		foreach ($contacts_list as $company_id => $value) {
			foreach ($value as $contact) {
				$contact_ids[] = $contact->getId();
			}
		}
		// fetch metas for contacts
		$dao_contacts_metas = new DaoContactMetas();
		$contact_metas = $dao_contacts_metas->getContactMetas(array('f_active' => 1), null, 0);
		$contact_meta_array = array();
		foreach ($contact_metas['results'] as $meta) {
			$contact_meta_array[] = $meta->getId();
		}
		$dao_metas_rel = new DaoContactMetaRelationships();
		$contact_infos = $dao_metas_rel->getMetasforContacts($contact_ids, $contact_meta_array);

		// fetch contact types
		$types_class = new ContactType(array());
		$contact_types = $types_class->getContactTypesAsArray(false);

		$header = $file_class->generateCsvHeader(true);
		$buffer = '';
		foreach ($companies_list['results'] as $company) {
			// if there are contacts for this company
			if(isset($contacts_list[$company->getId()])) {
				foreach ($contacts_list[$company->getId()] as $contact) {
					$type = '';
					$company_type = $company->getType_id();
					if( ! empty($company_type) && isset($contact_types[$company_type])) {
						$type = $contact_types[$company_type];
					} elseif(isset($contact_types[$contact->getType_id()])) {
						$type = $contact_types[$contact->getType_id()];
					}

					$buffer .= '"' . Utils::displayEscapedString($type) . '"' . $file_class->csv_sep
							. '"' . Utils::displayEscapedString($company->getName()) . '"' . $file_class->csv_sep;
					foreach ($company_meta_array as $meta_id) {
						$buffer .= '"' . (isset($company_infos[$company->getId()][$meta_id])? Utils::displayEscapedString($company_infos[$company->getId()][$meta_id]):'') . '"' . $file_class->csv_sep;
					}
					$buffer .= '"' . Utils::displayEscapedString($company->getComments()) . '"' . $file_class->csv_sep
							. Utils::displayEscapedString($contact->getLastname()) . $file_class->csv_sep
							. Utils::displayEscapedString($contact->getFirstname()) . $file_class->csv_sep;
					foreach ($contact_meta_array as $meta_id) {
						$buffer .= '"' . (isset($contact_infos[$contact->getId()][$meta_id])? Utils::displayEscapedString($contact_infos[$contact->getId()][$meta_id]):'') . '"' . $file_class->csv_sep;
					}
					$buffer .= '"' . Utils::displayEscapedString($contact->getComments()) . '"' . $file_class->csv_sep
							. $file_class->csv_eol;
				}
			}
			// if there is no contact for this company
			else {
				$type = '';
				$company_type = $company->getType_id();
				if( ! empty($company_type) && isset($contact_types[$company_type])) {
					$type = $contact_types[$company_type];
				}
				$buffer .= '"' . Utils::displayEscapedString($type) . '"' . $file_class->csv_sep
						. '"' . Utils::displayEscapedString($company->getName()) . '"' . $file_class->csv_sep;
				foreach ($company_meta_array as $meta_id) {
						$buffer .= '"' . (isset($company_infos[$company->getId()][$meta_id])? Utils::displayEscapedString($company_infos[$company->getId()][$meta_id]):'') . '"' . $file_class->csv_sep;
				}
				$buffer .= '"' . Utils::displayEscapedString($company->getComments()) . '"' . $file_class->csv_sep
						. '""' . $file_class->csv_sep // lastname
						. '""' . $file_class->csv_sep; // firstname
				foreach ($contact_meta_array as $meta_id) {
					$buffer .= '""' . $file_class->csv_sep;
				}
				$buffer .= '""' . $file_class->csv_sep // contact comment
						. $file_class->csv_eol;
			}
		}

		// for contacts with no company
		if(isset($contacts_list[0])) {
			foreach ($contacts_list[0] as $contact) {
				$type = '';
				if(isset($contact_types[$contact->getType_id()])) {
					$type = $contact_types[$contact->getType_id()];
				}
				$buffer .= '"' . Utils::displayEscapedString($type) . '"' . $file_class->csv_sep
						. '""' . $file_class->csv_sep; // company name
				foreach ($company_meta_array as $meta_id) {
						$buffer .= '""' . $file_class->csv_sep;
				}
				$buffer .= '""' . $file_class->csv_sep // company comment
						. '"' . Utils::displayEscapedString($contact->getLastname()) . '"' . $file_class->csv_sep
						. '"' . Utils::displayEscapedString($contact->getFirstname()) . '"' . $file_class->csv_sep;
				foreach ($contact_meta_array as $meta_id) {
					$buffer .= '"' . (isset($contact_infos[$contact->getId()][$meta_id])? Utils::displayEscapedString($contact_infos[$contact->getId()][$meta_id]):'') . '"' . $file_class->csv_sep;
				}
				$buffer .= '"' . Utils::displayEscapedString($contact->getComments()) . '"' . $file_class->csv_sep //comm
						. $file_class->csv_eol;
			}
		}
		$encod_buffer = Utils::convertEncoding($buffer, 'ISO-8859-15', 'UTF-8');

		$random = Utils::generateRandomString();
		$filename = $file_class->default_dir . 'export_contact_' . date('Ymd_His') . "_" . $random . '.csv';
		$res = $file_class->writeFile($filename, $header . $encod_buffer);
		if($res !== false) {
			return $filename;
		}
		return $res;
	}


	/**
	 * exports searched contacts
	 */
	public function exportSearchContacts()
	{
		$file_class = new Files();

		/* fetch contacts */
		$criteria = array();
		// sorting
		if(isset($_GET["sort"]) && ! empty($_GET['sort'])) {
			$criteria['sort'] = Utils::sanitize($_GET["sort"]);
			if(isset($_GET["order"]) && ! empty($_GET['order'])) {
				$criteria['order'] = Utils::sanitize($_GET['order']);
			} else {
				$criteria['order'] = 'asc';
			}
		}
		// filter
		if(isset($_GET['f_name'])) {
			$criteria['f_name'] = Utils::sanitize($_GET['f_name']);
		}
		if(isset($_GET['f_company'])) {
			$criteria['f_company'] = Utils::sanitize($_GET['f_company']);
		}
		if(isset($_GET['f_type'])) {
			$criteria['f_type'] = Utils::sanitize($_GET['f_type']);
		}
		if(isset($_GET['f_email'])) {
			$criteria['f_email'] = Utils::sanitize($_GET['f_email']);
		}
		if(isset($_GET['f_zipcity'])) {
			$criteria['f_zipcity'] = Utils::sanitize($_GET['f_zipcity']);
		}
		if(isset($_GET['f_phone'])) {
			$criteria['f_phone'] = Utils::sanitize($_GET['f_phone']);
		}
		if(isset($_GET['f_date_add_from'])) {
			$criteria['f_date_add_from'] = Utils::sanitize($_GET['f_date_add_from']);
		}
		if(isset($_GET['f_date_add_to'])) {
			$criteria['f_date_add_to'] = Utils::sanitize($_GET['f_date_add_to']);
		}
		if(isset($_GET['f_date_upd_from'])) {
			$criteria['f_date_upd_from'] = Utils::sanitize($_GET['f_date_upd_from']);
		}
		if(isset($_GET['f_date_upd_to'])) {
			$criteria['f_date_upd_to'] = Utils::sanitize($_GET['f_date_upd_to']);
		}
		if(isset($_GET['f_active'])) {
			$criteria['f_active'] = Utils::sanitize($_GET['f_active']);
		}
		//-- end filters
		$dao = new DaoContacts();
		$contacts_list = $dao->getList($criteria, 0, 0);
		$contact_ids = array();
		$company_ids = array();
		foreach ($contacts_list['results'] as $contact) {
			$contact_ids[] = $contact->getId();
			$company_ids[] = $contact->getCompany_id();
		}
		// fetch metas for contacts
		$dao_contacts_metas = new DaoContactMetas();
		$contact_metas = $dao_contacts_metas->getContactMetas(array('f_active' => 1), null, 0);
		$contact_meta_array = array();
		foreach ($contact_metas['results'] as $meta) {
			$contact_meta_array[] = $meta->getId();
		}
		$dao_metas_rel = new DaoContactMetaRelationships();
		$contact_infos = $dao_metas_rel->getMetasforContacts($contact_ids, $contact_meta_array);

		// fetch contact types
		$types_class = new ContactType();
		$contact_types = $types_class->getContactTypesAsArray(false);

		/* fetch companies */
		$dao_companies = new DaoCompanies();
		$companies_list = $dao_companies->getCompaniesById($company_ids);

		// fetch metas for companies
		$dao_companies_metas = new DaoCompanyMetas();
		$company_metas = $dao_companies_metas->getCompanyMetas(array('f_active' => 1), null, 0);
		$company_meta_array = array();
		foreach ($company_metas['results'] as $meta) {
			$company_meta_array[] = $meta->getId();
		}
		$dao_company_metas_rel = new DaoCompanyMetaRelationships();
		$company_infos = $dao_company_metas_rel->getMetasforCompanies($company_ids, $contact_meta_array);

		$header = $file_class->generateCsvHeader(true);
		$buffer = '';
		foreach ($contacts_list['results'] as $contact) {

			$type = '';
			$contact_type = $contact->getType_id();
			if( ! empty($contact_type) && isset($contact_types[$contact_type])) {
				$type = $contact_types[$contact_type];
			}
			$buffer .= '"' . Utils::convertEncoding(Utils::displayEscapedString($type), 'ISO-8859-15', 'UTF-8') . '"' . $file_class->csv_sep;

			// contact company
			if(isset($companies_list[$contact->getCompany_Id()])) {
				$buffer .= '"' . Utils::convertEncoding(Utils::displayEscapedString($companies_list[$contact->getCompany_Id()]->getName()), 'ISO-8859-15', 'UTF-8') . '"' . $file_class->csv_sep;
				foreach ($company_meta_array as $meta_id) {
					$buffer .= '"' . (isset($company_infos[$contact->getCompany_Id()][$meta_id])? Utils::convertEncoding(Utils::displayEscapedString($company_infos[$contact->getCompany_Id()][$meta_id]), 'ISO-8859-15', 'UTF-8'):'') . '"' . $file_class->csv_sep;
				}
				$buffer .= '"' . Utils::convertEncoding(Utils::displayEscapedString($companies_list[$contact->getCompany_Id()]->getComments()), 'ISO-8859-15', 'UTF-8') . '"' . $file_class->csv_sep;
			}
			// if there is no company for this contact
			else {
				$buffer .= '""' . $file_class->csv_sep; // company name
				foreach ($company_meta_array as $meta_id) {
						$buffer .= '""' . $file_class->csv_sep;
				}
				$buffer .= '""' . $file_class->csv_sep; // company comment
			}
			// contact info
			$buffer .= '"' . Utils::convertEncoding(Utils::displayEscapedString($contact->getLastname()), 'ISO-8859-15', 'UTF-8') . '"' . $file_class->csv_sep
					. '"' . Utils::convertEncoding(Utils::displayEscapedString($contact->getFirstname()), 'ISO-8859-15', 'UTF-8') . '"' . $file_class->csv_sep;
			foreach ($contact_meta_array as $meta_id) {
				$buffer .= '"' . (isset($contact_infos[$contact->getId()][$meta_id])? Utils::convertEncoding(Utils::displayEscapedString($contact_infos[$contact->getId()][$meta_id]), 'ISO-8859-15', 'UTF-8'):'') . '"' . $file_class->csv_sep;
			}
			$buffer .= '"' . Utils::convertEncoding(Utils::displayEscapedString($contact->getComments()), 'ISO-8859-15', 'UTF-8') . '"' . $file_class->csv_sep
					. $file_class->csv_eol;
		}

		$random = Utils::generateRandomString();
		$filename = $file_class->default_dir . 'export_recherche_' . date('Ymd_His') . "_" . $random . '.csv';
		$res = $file_class->writeFile($filename, $header . $buffer);
		if($res !== false) {
			return $filename;
		}
		return $res;
	}


	/**
	 * exports contacts history
	 */
	public function exportHistory()
	{
		$file_class = new Files();

		$dao = new DaoContacts();
		$contacts_list = $dao->getList(array(), 0, 0);
		$contact_ids = array();
		foreach ($contacts_list['results'] as $contact) {
			$contact_ids[] = $contact->getId();
		}

		$meta_array = array(1);
		$dao_metas_rel = new DaoContactMetaRelationships();
		$contact_infos = $dao_metas_rel->getMetasforContacts($contact_ids, $meta_array);

		// fetch contact types
		$types_class = new ContactType(array());
		$contact_types = $types_class->getContactTypesAsArray(false);

		$dao_meetings = new DaoMeetings();

		$header = '"Type de contact"' . $file_class->csv_sep . '"Nom"' . $file_class->csv_sep
				. '"' . Utils::convertEncoding('Prénom', 'ISO-8859-15', 'UTF-8') . '"' . $file_class->csv_sep
				. '"Entreprise"'      . $file_class->csv_sep
				. '"Fonction"'        . $file_class->csv_sep . '"Date"' . $file_class->csv_sep
				. '"Type"'            . $file_class->csv_sep . '"Compte-rendu"'
				. $file_class->csv_eol;
		$buffer = '';
		$buffer_cols_empty = '""' . $file_class->csv_sep . '""' . $file_class->csv_sep
				. '""' . $file_class->csv_sep . '""' . $file_class->csv_sep
				. '""' . $file_class->csv_sep;
		foreach($contacts_list['results'] as $contact) {

			$type = '';
			if(isset($contact_types[$contact->getType_id()])) {
				$type = $contact_types[$contact->getType_id()];
			}
			$buffer .= '"' . Utils::convertEncoding(Utils::displayEscapedString($type), 'ISO-8859-15', 'UTF-8') . '"' . $file_class->csv_sep
					. '"' . Utils::convertEncoding(Utils::displayEscapedString($contact->getLastname()), 'ISO-8859-15', 'UTF-8')           . '"' . $file_class->csv_sep
					. '"' . Utils::convertEncoding(Utils::displayEscapedString($contact->getFirstname()), 'ISO-8859-15', 'UTF-8')          . '"' . $file_class->csv_sep
					. '"' . Utils::convertEncoding(Utils::displayEscapedString($contact->getCompany()->getName()), 'ISO-8859-15', 'UTF-8') . '"' . $file_class->csv_sep
					. '"' . (isset($contact_infos[$contact->getId()][1])? Utils::convertEncoding(Utils::displayEscapedString($contact_infos[$contact->getId()][1]), 'ISO-8859-15', 'UTF-8'):'') . '"' . $file_class->csv_sep // fonction
					. $file_class->csv_eol;

			// meetings
			$criteria = array('f_ctc' => $contact->getId(), 'sort' => 'date', 'order' => 'asc');
			$meetings = $dao_meetings->getList($criteria);
			foreach ($meetings['results'] as $meeting) {
				$buffer .= $buffer_cols_empty
						. '"' . Utils::convertEncoding(Utils::displayEscapedString($meeting->getDate_format()), 'ISO-8859-15', 'UTF-8') . '"' . $file_class->csv_sep // date
						. '"' . Utils::convertEncoding(Utils::displayEscapedString($meeting->getType_name()), 'ISO-8859-15', 'UTF-8')   . '"' . $file_class->csv_sep // type
						. '"' . Utils::convertEncoding(Utils::displayEscapedString($meeting->getComments()), 'ISO-8859-15', 'UTF-8')    . '"' . $file_class->csv_sep // CR
						. $file_class->csv_eol;
			}

		}

		$random = Utils::generateRandomString();
		$filename = $file_class->default_dir . 'export_historique_' . date('Ymd_His') . "_" . $random . '.csv';
		$res = $file_class->writeFile($filename, $header . $buffer);
		if($res !== false) {
			return $filename;
		}
		return $res;
	}


	/**
	 * import contacts from a csv
	 *
	 * @param array $uploaded_file $_FILES array
	 *
	 * @return boolean
	 */
	public function importContacts($account_id, $uploaded_file)
	{
		$file_class = new Files();
		$result = $file_class->uploadCsv($uploaded_file);
		if( ! $result['success']) {
			Utils::log("Impossible d'uploader le CSV " . $result['filename']);
			return array('result' => false, 'message' => 'Impossible d\'uploader le CSV ' . $result['filename']);
		}
		$new_filename = $result['filename'];
		// open
		$handle = $file_class->openFile($file_class->default_dir . $new_filename, 'rb');
		if( ! $handle) {
			Utils::log('Impossible d\'ouvrir le csv' . $file_class->default_dir . $new_filename);
			return array('result' => false, 'message' => 'Impossible d\'ouvrir le CSV ' . $new_filename);
		} else {
			$dao_company = new DaoCompanies();
			$dao_contact = new DaoContacts();
			// company metas
			$dao_company_metas = new DaoCompanyMetas();
			$company_metas = $dao_company_metas->getCompanyMetas(array('f_active' => 1), null, 0);
			$company_meta_array = array();
			foreach ($company_metas['results'] as $meta) {
				$company_meta_array[] = $meta->getId();
			}
			// contacts meta
			$dao_contacts_metas = new DaoContactMetas();
			$contact_metas = $dao_contacts_metas->getContactMetas(array('f_active' => 1), null, 0);
			$contact_meta_array = array();
			foreach ($contact_metas['results'] as $meta) {
				$contact_meta_array[] = $meta->getId();
			}
			// encoding
			$whole_content = fread($handle, filesize($file_class->default_dir . $new_filename));
			rewind($handle);
			$to_encoding = 'UTF-8';
			$from_encoding = mb_detect_encoding($whole_content, $file_class->supported_encoding, true);

			// fixed indexes
			$index_company_comment   = count($company_meta_array) + 1; // 1 = company name
			$index_contact_lastname  = count($company_meta_array) + 2; // 2 = company name + company comment
			$index_contact_firstname = count($company_meta_array) + 3; // 2 = company name + company comment + contact lastname
			$index_contact_comment   = count($company_meta_array) + count($contact_meta_array) + 4; // 4 = company name + company comment + contact lastname + firstname
			$created_companies = 0;
			$created_contacts = 0;
			$i = 0; // line number
			// loop
			while(($content = fgetcsv($handle, 0, $file_class->csv_sep)) !== false) {
				if($i > 0) { // ignore first line
					// incremential indexes
					$index_company_meta = 1;
					$index_contact_meta = count($company_meta_array) + 4; // 4 = company name + company comment + contact lastname + firstname
					// insert company if not empty
					$company_id = 0;
					if(trim($content[0]) != '') {
						// check if company exists
						$exists = $dao_company->getCompanyByName(Utils::protectImportedData($content[0], $to_encoding, $from_encoding), $account_id);
						// if don't exists, create it
						if($exists === false) {
							$data_cie = array(
								'company_account_id'  => $account_id,
								'company_type_id'     => 1,
								'company_date_add'    => date('Y-m-d'),
								'company_date_update' => date('Y-m-d'),
								'company_name'        => Utils::protectImportedData($content[0], $to_encoding, $from_encoding),
								'company_comments'    => (isset($content[$index_company_comment])? Utils::protectImportedData($content[$index_company_comment], $to_encoding, $from_encoding):''),
								'company_meta'        => array(),
								'company_active'      => 1
							);
							foreach ($company_meta_array as $meta_id) {
								$data_cie['company_meta'][$meta_id] = (isset($content[$index_company_meta])? Utils::protectImportedData($content[$index_company_meta], $to_encoding, $from_encoding):'');
								$index_company_meta++;
							}
							$company = new Company($data_cie);
							$company_inserted = $dao_company->addCompany($company);
							$company_id = $company_inserted->getId();
							// increment counter
							if( ! empty($company_id)) {
								$created_companies++;
							}
						}
						// else, get company id
						else {
							$company_id = $exists->getId();
						}
					}
					// insert contact if not empty (based on lastname and firstname)
					if(isset($content[$index_contact_lastname]) && (!empty($content[$index_contact_lastname]) || !empty($content[$index_contact_firstname]))) {
						$data_ctc = array(
							'contact_account_id'  => $account_id,
							'contact_type_id'     => 1,
							'contact_date_add'    => date('Y-m-d'),
							'contact_date_update' => date('Y-m-d'),
							'contact_lastname'    => Utils::protectImportedData($content[$index_contact_lastname], $to_encoding, $from_encoding),
							'contact_firstname'   => (isset($content[$index_contact_firstname])? Utils::protectImportedData($content[$index_contact_firstname], $to_encoding, $from_encoding):''),
							'contact_company_id'  => $company_id,
							'contact_company_name' => $company_id, // hack to force company_id
							'contact_comments'    => (isset($content[$index_contact_comment])? Utils::protectImportedData($content[$index_contact_comment], $to_encoding, $from_encoding):''),
							'contact_meta'        => array(),
							'contact_active'      => 1
						);
						foreach ($contact_meta_array as $meta_id) {
							$data_ctc['contact_meta'][$meta_id] = (isset($content[$index_contact_meta])? Utils::protectImportedData($content[$index_contact_meta], $to_encoding, $from_encoding):'');
							$index_contact_meta++;
						}
						$contact = new Contact($data_ctc);
						$new_contact = $dao_contact->addContact($contact);
						$contact_id = $new_contact->getId();
						if( ! empty($contact_id)) {
							$created_contacts++;
						}
					}
				}
				$i++;
			}
			$message = ($i - 1) . " lignes traitées. " . $created_companies . " entreprises créées. "
					. $created_contacts . " contacts créés";
			return array('result' => true, 'message' => $message);
		}
	}

}

/* End of file Contact.class.php */
/* Location: ./classes/Contact.class.php */