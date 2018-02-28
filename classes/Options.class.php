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
 * options class
 *
 * @since	1.0
 * @author	Marie Kuntz / Lézard Rouge <mariek@lezard-rouge.fr>
 */


class Options
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
	 * manage general logo
	 *
	 * @param array $data
	 *
	 * @return bool
	 */
	public function manageLogo($data)
	{
		// if logo must be deleted
		if($data['logo_del'] == 1) {
			$result = $this->deleteLogo();
			return $result;
		}
		// else, if the logo must be changed
		elseif( ! empty($data['logo_file']['name'])) {
			$image = new Image();
			$result = $image->uploadLogo($data['logo_file'], 'my_logo');
			if($result['success']) {
				$this->updateOptionByKey('logo', $result['filename']);
			}
			return $result['success'];
		}
		// well, if nothing has been set...
		else {
			return false;
		}
	}


	/**
	 * get an option by its key
	 *
	 * @param str $key
	 *
	 * @return object
	 */
	public function getOptionByKey($key)
	{
		// get logo filename
		$query = $this->_pdo->prepare("SELECT *
			FROM `options`
			WHERE option_key = :key
			LIMIT 1");
		$query_result = $query->execute(array('key' => $key));
		if( ! $query_result) {
			Utils::dump($query->errorInfo());
		}
		$option = $query->fetch(PDO::FETCH_OBJ);
		$query->closeCursor();
		return $option;
	}


	/**
	 * update an option, retrieved by its key
	 *
	 * @param str $key
	 * @param str $value
	 */
	public function updateOptionByKey($key, $value)
	{
		$query = $this->_pdo->prepare("UPDATE `options`
			SET option_value = :value
			WHERE option_key = :key");
		$query_result = $query->execute(array(
				'key'   => $key,
				'value' => $value
			)
		);
		if( ! $query_result) {
			Utils::dump($query->errorInfo());
		}
		$query->closeCursor();
	}


	/**
	 * delete the actual logo
	 *
	 * @return bool
	 */
	public function deleteLogo()
	{
		$result = false;
		// get logo filename
		$logo = $this->getOptionByKey('logo');
		// delete physical logo
		$file_class = new Files();
		if(! empty($logo->option_value)) {
			$result = $file_class->deleteFile( LOCAL_PATH . 'logos/' . $logo->option_value);
		}
		// update table options
		$this->updateOptionByKey('logo', '');
		return $result;
	}

}

/* End of file Options.class.php */
/* Location: ./classes/Options.class.php */