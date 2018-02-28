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
 * The PDO class is used to manage and connect to the DB
 *
 * @since	1.0
 * @author	Marie Kuntz / Lézard Rouge <mariek@lezard-rouge.fr>
 */

class myPDO
{

	private static $_instance;


	public function __construct()
	{
		//
	}

	public static function getInstance()
	{
		if ( ! isset(self::$_instance)) {
			try {
				self::$_instance = new PDO('mysql:host='.DB_HOST.';port='.DB_PORT.';dbname='.DB_NAME, DB_USER, DB_PWD);
				self::$_instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // ERRMODE_WARNING / ERRMODE_SILENT
			} catch (Exception $e) {
				echo 'PDO Erreur : '.$e->getMessage().'<br>No : '.$e->getCode();
				die();
			}
		}

		return self::$_instance;

	}


	/**
	 * execute a query with PDO::exec and display error if necessary
	 *
	 * @param str $sql
	 *
	 * @return void
	 */
	public static function execSql($sql)
	{
		$req = self::$_instance->exec($sql);
		if($req === FALSE) {
			Utils::dump(self::$_instance->errorInfo());
			Utils::dump($sql);
		}
	}

	public function prepareAndExecute($sql, $args)
	{
		$query = self::$_instance->prepare($sql);
		$query_result = $query->execute($args);
		if( ! $query_result) {
			Utils::dump($query->errorInfo());
		}
		$query->closeCursor();
	}


}

?>