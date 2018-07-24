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
 * The DaoAccountMetaRelationships class is used to get and manage metas for accounts
 *
 * @since	1.0
 * @author	Marie Kuntz / Lézard Rouge <mariek@lezard-rouge.fr>
 */

class DaoAccountMetaRelationships
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
	 * fetch infos metas for a list of accounts
	 *
	 * @param mixed str|array $accounts
	 * @param mixed str|array $account_metas
	 *
	 * @return array
	 */
	public function getMetasforAccounts($accounts, $account_metas)
	{
		$metas = array();
		if(empty($accounts)) {
			return $metas;
		}

		$conditions = array();
		$sql = "SELECT account_meta_relationships.*
			FROM account_meta_relationships
			WHERE rel_account_id IN (";
		if( ! is_array($accounts)) {
			$accounts = explode(',', $accounts);
		}
		$tmp_account = array();
		$i = 1;
		foreach($accounts as $account_id) {
			$tmp_account[] = ":account_id_" . $i;
			$conditions['account_id_' . $i] = $account_id;
			$i++;
		}
		$tmp_account2 = implode(',', $tmp_account);
		$sql .= $tmp_account2 . " ) AND rel_meta_id IN (";

		if( ! is_array($account_metas)) {
			$account_metas = explode(',', $account_metas);
		}
		$tmp = array();
		$i = 1;
		foreach($account_metas as $meta_id) {
			$tmp[] = ":meta_id_" . $i;
			$conditions['meta_id_' . $i] = $meta_id;
			$i++;
		}
		$tmp2 = implode(',', $tmp);
		$sql .= $tmp2 . " )";

		$query = $this->_pdo->prepare($sql);
		$query->execute($conditions);

		while($data = $query->fetch(PDO::FETCH_OBJ)) {
			$metas[$data->rel_account_id][$data->rel_meta_id] = $data->rel_value;
		}
		$query->closeCursor();

		return $metas;
	}


	/**
	 * add metas values for a contact
	 *
	 * @param int $account_id
	 * @param array $metas the metas id and values
	 *
	 * @return void
	 */
	public function addAccountMetas($account_id, $metas)
	{
		foreach ($metas as $meta_id => $meta_value) {
			if( ! empty($meta_value)) {
				$query = $this->_pdo->prepare("INSERT INTO `account_meta_relationships`
					SET rel_account_id = :contact_id,
						rel_meta_id = :meta_id,
						rel_value = :value");
				$query_result = $query->execute(array(
						'contact_id' => $account_id,
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
	 * @param int $account_id
	 * @param array $metas the metas id and values
	 *
	 * @return void
	 */
	public function updateAccountMetas($account_id, $metas)
	{
		// first delete metas information for the contact
		$this->deleteAccountMetas($account_id);
		// then add them
		$this->addAccountMetas($account_id, $metas);
		return;
	}


	/**
	 * delete metas information for a contact
	 *
	 * @param int $account_id
	 *
	 * @return void
	 */
	public function deleteAccountMetas($account_id)
	{
		$query = $this->_pdo->prepare("DELETE FROM account_meta_relationships WHERE rel_account_id = :id");
		$query_result = $query->execute(array('id' => $account_id));
		if( ! $query_result) {
			Utils::dump($query->errorInfo());
		}
		$query->closeCursor();
	 }


}

/* End of file DaoAccountMetaRelationships.class.php */
/* Location: ./classes/DaoAccountMetaRelationships.class.php */