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
 * along with SARU.  If not, see <http://www.gnu.org/licenses/>.
 * See LICENSE.TXT file for more information.
 *
 * Saru is released under dual license, AGPL and commercial license.
 * If you need a commercial license or if you don't know which licence you need,
 * please contact us at <info@saru.fr>
 *
 */

/**
 * programmed routines
 *
 * @since	1.0
 * @author Marie Kuntz / Lézard Rouge
 */

require_once "inc/includes.php";

//-------------------------------
// get vars
//-------------------------------
// set page to display
$tpl = "";
$tpl_array = array("alerts", "backup", "salt", "copycgpme");
if( ! IS_CLI) {
	if (isset($_GET['tpl']) && ! empty($_GET['tpl'])) {
		$tpl = Utils::sanitize($_GET['tpl']);
	} elseif(isset($_POST['tpl']) && ! empty($_POST['tpl'])) {
		$tpl = Utils::sanitize($_POST['tpl']);
	}
} else {
	if(isset($argv[1])) {
		$tpl = $argv[1];
	}
}
if( ! in_array($tpl, $tpl_array)) {
	Utils::log('tpl => ' .$tpl);
	exit('Une erreur est survenue.');
}

/*
 | in order to prevent non-allowed access to the following scripts
 | we set a hash key which must be passed when not in CLI
 | if debug mode is set, the hash key is not verified so pay attention to put
 | debug mode to 0 in production (in the inc/constants.php file)
 */
$key = '';
if(isset($_GET['key'])) {
	$key = $_GET['key'];
}
if( ! DEBUG_MODE && ! IS_CLI && $key !== hash('ripemd160', HASH_KEY)) {
	Utils::log(array(
		'debug' => DEBUG_MODE,
		'cli'   => intval(IS_CLI),
		'key'   => $key
	));
	exit("you're not allowed to be here.");
}


//-------------------------------------
// send alerts by email
//-------------------------------------
if($tpl == 'alerts') {

	$alerts = new Alert();
	$alerts->sendAlerts();

}
//-------------------------------------
// backup DB
//-------------------------------------
elseif($tpl == 'backup') {

	Utils::backup_tables();

} elseif ($tpl == 'salt') {

	Utils::updatePwd();

}

?>