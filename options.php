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
 * SARU options
 *
 * @since	1.0
 * @author Marie Kuntz / Lézard Rouge
 */

require_once "inc/includes.php";
// check if user is connected
$session->isConnected();


//-------------------------------
// get vars
//-------------------------------
// set page to display
$default = "";
$tpl = "";
$tpl_array = array("logo");
if (isset($_GET['tpl']) && ! empty($_GET['tpl'])) {
	$tpl = Utils::sanitize($_GET['tpl']);
} elseif (isset($_POST['tpl']) && ! empty($_POST['tpl'])) {
	$tpl = Utils::sanitize($_POST['tpl']);
}
if( ! in_array($tpl, $tpl_array)) {
	$tpl = $default;
}

//-------------------------------
$message = '';
if(isset($_SESSION['sess_message'])) {
	$message = Utils::displayMessage($_SESSION['sess_message'], $_SESSION['sess_message_type']);
	unset($_SESSION['sess_message'], $_SESSION['sess_message_type']);
}

//-------------------------------------
// LOGO MANAGEMENT
//-------------------------------------
if($tpl == "logo") {

	// access rights
	Access::userCanAccess('admin');

	$options = new Options();
	if(isset($_POST['tpl'])) {
		// prepare data
		if(isset($_POST['logo_del']) && $_POST['logo_del'] == 1) {
			$logo_del = 1;
		} else {
			$logo_del = 0;
		}
		$data = array(
			'logo_file' => $_FILES['logo'],
			'logo_del'  => $logo_del
		);
		$result = $options->manageLogo($data);
		if( ! $result) {
			$message = Utils::displayMessage('La demande a échoué.', TYPE_MSG_ERROR);
		} else {
			$message = Utils::displayMessage('Le logo a été modifié.', TYPE_MSG_SUCCESS);
		}
	}
	$logo = $options->getOptionByKey('logo');

	//---------------------------------
	// display
	include('templates/header_default.php');
	include('templates/admin_logo.php');
	include('templates/footer_default.php');

}

?>