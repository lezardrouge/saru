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
 * The DaoAttachments class is used to get and manage attachments
 *
 * @since	1.4
 * @author	Marie Kuntz / Lézard Rouge <mariek@lezard-rouge.fr>
 */

class DaoAttachments
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
	 * fetch attachments
	 *
	 * @param array $criteria
	 * @param int $num
	 * @param int $limit
	 *
	 * @return array
	 */
	public function getList($criteria = array(), $num = NB_RECORDS, $limit = 0)
	{
		$attachments = array();

		$sql = "SELECT SQL_CALC_FOUND_ROWS
				attachments.*
			FROM attachments ";
		//-----------------------------
		// filters
		//-----------------------------
		$conditions = array();
		if ( ! empty($criteria)) {
			// add this so we dont wonder if there is already the WHERE clause
			$sql .= ' WHERE 1=1 ';
			if(isset($criteria['f_ids']) && ! empty($criteria['f_ids'])) {
				if( ! is_array($criteria['f_ids'])) {
					$criteria['f_ids'] = explode(',', $criteria['f_ids']);
				}
				$sql .= " AND (attachment_item_id IN (";
				$tmp = array();
				$i = 1;
				foreach($criteria['f_ids'] as $id) {
					$tmp[] = ":item_id_" . $i;
					$conditions['item_id_' . $i] = $id;
					$i++;
				}
				$tmp2 = implode(',', $tmp);
				$sql .= $tmp2 . " ))";
			}
			if(isset($criteria['f_type']) && ! empty($criteria['f_type'])) {
				$sql .= ' AND attachment_type_item = :type';
				$conditions['type'] = $criteria['f_type'];
			}
		}
		//-----------------------------
		// order
		//-----------------------------
		$sql .= " ORDER BY attachment_account_id, attachment_type_item, attachment_item_id, attachment_real_name ";
		//-----------------------------
		// limit
		//-----------------------------
		if ($num != 0) {
			$sql .= " LIMIT " . $limit . ", " . $num;
		}
		//echo $sql;

		$query = $this->_pdo->prepare($sql);
		$query->execute($conditions);

		$total_query = $this->_pdo->query('SELECT FOUND_ROWS() AS total');
		$total = $total_query->fetchAll(PDO::FETCH_COLUMN, 0);
		while($data = $query->fetch(PDO::FETCH_OBJ)) {
			$attachments[] = new Attachment($data);
		}
		$query->closeCursor();

		return array('total' => $total[0], 'results' => $attachments);
	}


	/**
	 * get attachments for meetings
	 *
	 * @param array $ids
	 *
	 * @return array
	 */
	public function getAttachmentsByMeetings($ids)
	{
		$arrayAttachments = array();
		$attachments = $this->getList(array('f_type' => 'me', 'f_ids' => $ids), 0, 0);
		foreach ($attachments['results'] as $attachment) {
			$arrayAttachments[$attachment->getItem_id()][] = $attachment;
		}
		return $arrayAttachments;
	}


	/**
	 * get attachments for contacts
	 *
	 * @param array $ids
	 *
	 * @return array
	 */
	public function getAttachmentsByContacts($ids)
	{
		$arrayAttachments = array();
		$attachments = $this->getList(array('f_type' => 'ct', 'f_ids' => $ids), 0, 0);
		foreach ($attachments['results'] as $attachment) {
			$arrayAttachments[$attachment->getItem_id()][] = $attachment;
		}
		return $arrayAttachments;
	}


	/**
	 * get attachments for companies
	 *
	 * @param array $ids
	 *
	 * @return array
	 */
	public function getAttachmentsByCompanies($ids)
	{
		$arrayAttachments = array();
		$attachments = $this->getList(array('f_type' => 'ci', 'f_ids' => $ids), 0, 0);
		foreach ($attachments['results'] as $attachment) {
			$arrayAttachments[$attachment->getItem_id()][] = $attachment;
		}
		return $arrayAttachments;
	}


	/**
	 * get informations about an attachment
	 *
	 * @param int $attachment_id
	 *
	 * @return object Attachment
	 */
	public function getAttachment($attachment_id)
	{
		$query = $this->_pdo->prepare("SELECT * FROM attachments WHERE attachment_id = :attachment_id");
		$query_result = $query->execute(array('attachment_id' => $attachment_id));
		if( ! $query_result) {
			Utils::dump($query->errorInfo());
		}
		$attachment = $query->fetch(PDO::FETCH_OBJ);
		$query->closeCursor();
		if( ! $attachment) {
			return false;
		}
		return new Attachment($attachment);
	}


	/**
	 * check if attachment exists by id
	 *
	 * @param int $attachment_id
	 *
	 * @return boolean
	 */
	public function attachmentExists($attachment_id)
	{
		$attachment = $this->getAttachment($attachment_id);
		if( ! $attachment) {
			return false;
		}
		// now check physical file
		$file_class = new Files();
		$filename = $file_class->attachment_dir . '/a' . $attachment->getAccount_id() . '/' . $attachment->getIntern_name();
		$res = $file_class->checkFileExists($filename);
		return $res;
	}


	/**
	 * set an attechment to an item : upload file and insert in DB
	 *
	 * @param int $account_id, the account to attach the file
	 * @param str $type_item, the type of item : ct|ci|me
	 * @param int $item_id, the id (pk) of item the file is linked to
	 * @param array $uploaded_file, $_FILES array
	 *
	 * @return boolean
	 */
	public function setAttachment($account_id, $type_item, $item_id, $uploaded_file)
	{
		$file_class = new Files();
		$result = $file_class->uploadAttachment($account_id, $uploaded_file);
		if( ! $result['success']) {
			Utils::log("Impossible d'uploader le fichier " . $result['old_filename'] . ' : ' . $result['message']);
			return array('result' => false, 'message' => 'Impossible d\'uploader le fichier ' . $result['old_filename'] . ' : ' . $result['message']);
		}
		$new_filename = $result['filename'];
		// insert in DB
		$data = array(
			'attachment_account_id'  => $account_id,
			'attachment_type_item'   => $type_item,
			'attachment_item_id'     => $item_id,
			'attachment_real_name'   => $result['old_filename'],
			'attachment_intern_name' => $new_filename,
		);
		$attachment = new Attachment($data);
		$insertedData = $this->addAttachment($attachment);
		return array('result' => true, 'message' => 'Fichier joint', 'object' => $insertedData);
	}


	/**
	 * add an attachment
	 *
	 * @param object Attachment
	 *
	 * @return object Attachment
	 */
	public function addAttachment(Attachment $attachment)
	{
		$query = $this->_pdo->prepare("INSERT INTO `attachments`
			SET `attachment_account_id`  = :account,
				`attachment_type_item`   = :type,
				`attachment_item_id`     = :item,
				`attachment_real_name`   = :real,
				`attachment_intern_name` = :intern");
		$query_result = $query->execute(array(
				'account' => $attachment->getAccount_id(),
				'type'    => $attachment->getType_item(),
				'item'    => $attachment->getItem_id(),
				'real'    => $attachment->getReal_name(),
				'intern'  => $attachment->getIntern_name(),
			)
		);
		if( ! $query_result) {
			Utils::dump($query->errorInfo());
		}
		$attachment_id = $this->_pdo->lastInsertId();
		$query->closeCursor();

		$attachment->hydrate(array(
			'attachment_id' => $attachment_id
		));
		return $attachment;
	}


	/**
	 * delete a meeting's attachments
	 *
	 * @param int $meeting_id
	 */
	public function delMeetingAttachments($meeting_id)
	{
		$attachments = $this->getAttachmentsByMeetings(array($meeting_id));
		if(isset($attachments[$meeting_id])) {
			foreach($attachments[$meeting_id] as $attachment) {
				$this->delAttachment($attachment);
			}
		}
	}


	/**
	 * delete a contact's attachments
	 *
	 * @param int $contact_id
	 */
	public function delContactAttachments($contact_id)
	{
		$attachments = $this->getAttachmentsByContacts(array($contact_id));
		if(isset($attachments[$contact_id])) {
			foreach($attachments[$contact_id] as $attachment) {
				$this->delAttachment($attachment);
			}
		}
	}


	/**
	 * delete a company's attachments
	 *
	 * @param int $company_id
	 */
	public function delCompanyAttachments($company_id)
	{
		$attachments = $this->getAttachmentsByCompanies(array($company_id));
		if(isset($attachments[$company_id])) {
			foreach($attachments[$company_id] as $attachment) {
				$this->delAttachment($attachment);
			}
		}
	}


	/**
	 * delete an attachment
	 *
	 * @param Attachment $attachment
	 *
	 * @return void
	 */
	public function delAttachment(Attachment $attachment)
	{
		// delete attachment in DB
		$query = $this->_pdo->prepare("DELETE FROM attachments WHERE attachment_id = :id");
		$query_result = $query->execute(array('id' => $attachment->getId()));
		if( ! $query_result) {
			Utils::dump($query->errorInfo());
		}
		$query->closeCursor();
		// delete physical file
		$this->delPhysicalAttachment($attachment);
	 }


	 /**
	  * delete physical file
	  *
	  * @param Attachment $attachment
	  */
	 private function delPhysicalAttachment(Attachment $attachment)
	 {
		 $file_class = new Files();
		 $filename = $file_class->attachment_dir . '/a' . $attachment->getAccount_id() . '/' . $attachment->getIntern_name();
		 $file_class->deleteFile($filename);
	 }


}

/* End of file DaoAttachments.class.php */
/* Location: ./classes/DaoAttachments.class.php */