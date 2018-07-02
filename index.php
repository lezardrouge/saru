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
 * display SARU homepage
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
$default = "home";
$tpl = "";
$tpl_array = array("home","admin", "help");
if (isset($_GET['tpl']) && ! empty($_GET['tpl'])) {
	$tpl = Utils::sanitize($_GET['tpl']);
} elseif (isset($_POST['tpl']) && ! empty($_POST['tpl'])) {
	$tpl = Utils::sanitize($_POST['tpl']);
}
if( ! in_array($tpl, $tpl_array)) {
	$tpl = $default;
}

//-------------------------------
$action = "";
if(isset($_GET['action']) && !empty($_GET['action'])) {
	$action = Utils::sanitize($_GET['action']);
} elseif (isset($_POST['action']) && !empty($_POST['action'])) {
	$action = Utils::sanitize($_POST['action']);
}
//-------------------------------

if(isset($_SESSION['sess_message'])) {
	$message = Utils::displayMessage($_SESSION['sess_message'], $_SESSION['sess_message_type']);
	unset($_SESSION['sess_message'], $_SESSION['sess_message_type']);
}

//-------------------------------------
// HOMEPAGE
//-------------------------------------
if($tpl == "home") {

	// if the account module is enabled, we fetch the available accounts
	$account_choice = false;
	if(MOD_ACCOUNT == 1) {
		$accounts = $session->getSessionData('sess_accounts_array');
		// if zero or one account,
		if(count($accounts) <= 1) {
			$account_choice = false;
		} else {
			$dao_accounts = new DaoAccounts();
			$accounts_list = $dao_accounts->getList(array('f_active' => 1), 0);
			$account_choice = true;
		}
	}

	// filtering alerts cookie
	$filterAlert = false;
	if(isset($_COOKIE['onlyme']) && $_COOKIE['onlyme'] == 1) {
		$filterAlert = true;
	} elseif(!isset($_COOKIE['onlyme'])) {
		setcookie('onlyme', '0', time()+60*60*24*30, '/', '', false, false);
	}

	if(Access::userCanAccess('alert_list', false)) {
		if($session->getSessionData('account') !== false && $session->getSessionData('account') != '') {
			$no_account = false;
			$dao_alerts = new DaoAlerts();
			$criteria = array(
				'sort'   => 'date',
				'order'  => 'asc',
				'f_done' => 0,
			);
			if($filterAlert) {
				$criteria['f_user'] = $session->getSessionData('user_id');
			}
			$alerts_list = $dao_alerts->getList($criteria, 0);
		} else {
			$no_account = true;
		}
	}
	//---------------------------------
	// display
	include('templates/header_default.php');
	include('templates/home.php');
	if(Access::userCanAccess('alert_list', false)) {
		// users having an access to this account
		$dao_users = new DaoUsers();
		$account_id = $session->getSessionData('account');
		$users = $dao_users->getUsersFromAccount($account_id);
		include('templates/alerts_recap.php');
	}
	include('templates/footer_default.php');

}
//-------------------------------------
// ADMIN
//-------------------------------------
elseif($tpl == 'admin') {

	// access rights
	Access::userCanAccess('admin');

	//---------------------------------
	// display
	include('templates/header_default.php');
	include('templates/admin_home.php');
	include('templates/footer_default.php');

}
//-------------------------------------
// HELP
//-------------------------------------
elseif($tpl == 'help') {

	//---------------------------------
	// display
	include('templates/header_default.php');
	include('templates/help.php');
	include('templates/footer_default.php');

}
?>
