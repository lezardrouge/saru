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
 * The company class is used to handle companies
 *
 * @since	1.0
 * @author	Marie Kuntz / Lézard Rouge <mariek@lezard-rouge.fr>
 */

class Company
{

	/* private */
	private $_id;
	private $_name;
	private $_account_id;
	private $_type_id;
	private $_comments;
	private $_date_add;
	private $_date_update;
	private $_active;
	private $_meta;


	/* constants */
	/* none for now */


	/**
	 * constructor
	 *
	 * @param array $data, for hydration
	 */
	public function __construct($data = array())
	{
		if( ! empty($data)) {
			$this->hydrate($data);
		}
	}


	/* getters */
	public function getId ()
	{
		return $this->_id;
	}
	public function getName ()
	{
		return $this->_name;
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
	public function setName ($name)
	{
		if( ! empty($name)) {
			$this->_name = $name;
		}
	}
	public function setAccount_id ($account_id)
	{
			$this->_account_id = $account_id;
	}
	public function setType_id ($type)
	{
		$this->_type_id = $type;
	}
	public function setComments ($comment)
	{
		$this->_comments = $comment;
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
	public function setMeta($metas)
	{
		$this->_meta = (array)$this->_meta + (array)$metas;
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
			$method = 'set' . ucfirst(str_replace('company_', "", $key));

			if (method_exists($this, $method)) {
				$this->$method($value);
			}
		}
	}


	/**
	 * fetch all companies as an array
	 *
	 * @param bool $default, if true add default option for select ; TRUE|FALSE ; default FALSE
	 *
	 * @return array $companies
	 */
	public function getCompaniesAsArray($default = FALSE)
	{
		$companies = array();
		$dao_company = new DaoCompanies();
		$all_companies = $dao_company->getList(array(), 0);
		if($default) {
			$companies[0] = '--';
		}
		foreach($all_companies['results'] as $company) {
			$companies[$company->getId()] = $company->getName();
		}
		return $companies;
	}


	/**
	 * exports searched companies
	 */
	public function exportSearchCompanies()
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
		if(isset($_GET['f_type'])) {
			$criteria['f_type'] = Utils::sanitize($_GET['f_type']);
		}
		if(isset($_GET['f_zipcity'])) {
			$criteria['f_zipcity'] = Utils::sanitize($_GET['f_zipcity']);
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

		$dao = new DaoCompanies();
		$companies_list = $dao->getList($criteria, 0, 0);
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

		// get companies contacts
		$contact_ids = array();
		$dao_contact = new DaoContacts();
		$contacts = $dao_contact->getContactsByCompanies($company_ids);
		foreach($contacts as $company_id => $key) {
			foreach($key as $i => $contact) {
				$contact_ids[] = $contact->getId();
			}
		}
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


		$header = $file_class->generateCsvHeader(true);
		$buffer = '';
		foreach ($companies_list['results'] as $company) {

			$tmp_buffer = '';
			$type = '';
			$company_type = $company->getType_id();
			if( ! empty($company_type) && isset($contact_types[$company_type])) {
				$type = $contact_types[$company_type];
			}
			$tmp_buffer .= '"' . Utils::convertEncoding(Utils::displayEscapedString($type), 'ISO-8859-15', 'UTF-8') . '"' . $file_class->csv_sep;

			// company
			$tmp_buffer .= '"' . Utils::convertEncoding(Utils::displayEscapedString($company->getName()), 'ISO-8859-15', 'UTF-8') . '"' . $file_class->csv_sep;
			foreach ($company_meta_array as $meta_id) {
				$tmp_buffer .= '"' . (isset($company_infos[$company->getId()][$meta_id])? Utils::convertEncoding(Utils::displayEscapedString($company_infos[$company->getId()][$meta_id]), 'ISO-8859-15', 'UTF-8'):'') . '"' . $file_class->csv_sep;
			}
			$tmp_buffer .= '"' . Utils::convertEncoding(Utils::displayEscapedString($company->getComments()), 'ISO-8859-15', 'UTF-8') . '"' . $file_class->csv_sep;

			// contacts
			if(isset($contacts[$company->getId()])) {
				foreach($contacts[$company->getId()] as $contact) {
					$buffer .= $tmp_buffer
							. '"' . Utils::convertEncoding(Utils::displayEscapedString($contact->getLastname()), 'ISO-8859-15', 'UTF-8') . '"' . $file_class->csv_sep
							. '"' . Utils::convertEncoding(Utils::displayEscapedString($contact->getFirstname()), 'ISO-8859-15', 'UTF-8') . '"' . $file_class->csv_sep;
					foreach ($contact_meta_array as $meta_id) {
						$buffer .= '"' . (isset($contact_infos[$contact->getId()][$meta_id])? Utils::convertEncoding(Utils::displayEscapedString($contact_infos[$contact->getId()][$meta_id]), 'ISO-8859-15', 'UTF-8'):'') . '"' . $file_class->csv_sep;
					}
					$buffer .= '"' . Utils::convertEncoding(Utils::displayEscapedString($contact->getComments()), 'ISO-8859-15', 'UTF-8') . '"' . $file_class->csv_sep
							. $file_class->csv_eol;
				}
			} else {
				$buffer .= $tmp_buffer . $file_class->csv_eol;
			}
		}

		$filename = $file_class->default_dir . 'export_recherche_' . date('Ymd_His') . '.csv';
		$res = $file_class->writeFile($filename, $header . $buffer);
		if($res !== false) {
			return $filename;
		}
		return $res;
	}


}

/* End of file Company.class.php */
/* Location: ./classes/Company.class.php */