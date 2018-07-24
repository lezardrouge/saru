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
 * The DaoContactMetaRelationships class is used to get and manage metas for contacts
 *
 * @since	1.0
 * @author	Marie Kuntz / Lézard Rouge <mariek@lezard-rouge.fr>
 */

class DaoContactMetaRelationships
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
	 * fetch infos metas for a list of contacts
	 *
	 * @param mixed str|array $contacts
	 * @param mixed str|array $contact_metas
	 *
	 * @return array
	 */
	public function getMetasforContacts($contacts, $contact_metas)
	{
		$metas = array();
		if(empty($contacts) || empty($contact_metas)) {
			return $metas;
		}

		$conditions = array();
		$sql = "SELECT contact_meta_relationships.*
			FROM contact_meta_relationships
			WHERE rel_contact_id IN (";

		if( ! is_array($contacts)) {
			$contacts = explode(',', $contacts);
		}
		$tmp_contact = array();
		$i = 1;
		foreach($contacts as $contact_id) {
			$tmp_contact[] = ":company_id_" . $i;
			$conditions['company_id_' . $i] = $contact_id;
			$i++;
		}
		$tmp_contact2 = implode(',', $tmp_contact);
		$sql .= $tmp_contact2 . " ) AND rel_meta_id IN (";

		if( ! is_array($contact_metas)) {
			$contact_metas = explode(',', $contact_metas);
		}
		$tmp = array();
		$i = 1;
		foreach($contact_metas as $meta_id) {
			$tmp[] = ":meta_id_" . $i;
			$conditions['meta_id_' . $i] = $meta_id;
			$i++;
		}
		$tmp2 = implode(',', $tmp);
		$sql .= $tmp2 . " )";

		$query = $this->_pdo->prepare($sql);
		$query->execute($conditions);

		while($data = $query->fetch(PDO::FETCH_OBJ)) {
			$metas[$data->rel_contact_id][$data->rel_meta_id] = $data->rel_value;
		}
		$query->closeCursor();

		return $metas;
	}


	/**
	 * add metas values for a contact
	 *
	 * @param int $contact_id
	 * @param array $metas the metas id and values
	 *
	 * @return void
	 */
	public function addContactMetas($contact_id, $metas)
	{
		foreach ($metas as $meta_id => $meta_value) {
			if( ! empty($meta_value)) {
				$query = $this->_pdo->prepare("INSERT INTO `contact_meta_relationships`
					SET rel_contact_id = :contact_id,
						rel_meta_id = :meta_id,
						rel_value = :value");
				$query_result = $query->execute(array(
						'contact_id' => $contact_id,
						'meta_id'    => $meta_id,
						'value'      => $meta_value
					)
				);
				if( ! $query_result) {
					Utils::dump($query->errorInfo());
				}
				$query->closeCursor();
			}
		}

		return;
	}


	/**
	 * update metas values for a contact
	 *
	 * @param int $contact_id
	 * @param array $metas the metas id and values
	 *
	 * @return void
	 */
	public function updateContactMetas($contact_id, $metas)
	{
		// first delete metas information for the contact
		$this->deleteContactMetas($contact_id);
		// then add them
		$this->addContactMetas($contact_id, $metas);
		return;
	}


	/**
	 * delete metas information for a contact
	 *
	 * @param int $contact_id
	 *
	 * @return void
	 */
	public function deleteContactMetas($contact_id)
	{
		$query = $this->_pdo->prepare("DELETE FROM contact_meta_relationships WHERE rel_contact_id = :id");
		$query_result = $query->execute(array('id' => $contact_id));
		if( ! $query_result) {
			Utils::dump($query->errorInfo());
		}
		$query->closeCursor();
	 }


	/**
	 * delete metas information for a meta
	 *
	 * @param int $meta_id
	 *
	 * @return void
	 */
	public function deleteContactMetasByMeta($meta_id)
	{
		$query = $this->_pdo->prepare("DELETE FROM contact_meta_relationships WHERE rel_meta_id = :id");
		$query_result = $query->execute(array('id' => $meta_id));
		if( ! $query_result) {
			Utils::dump($query->errorInfo());
		}
		$query->closeCursor();
	 }


}

/* End of file DaoContactMetaRelationships.class.php */
/* Location: ./classes/DaoContactMetaRelationships.class.php */