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
 * display accounts list, form etc
 *
 * @since	1.0
 * @author	Marie Kuntz / Lézard Rouge <mariek@lezard-rouge.fr>
 */

require_once "inc/includes.php";
// check if user is connected
$session->isConnected();

if(MOD_ACCOUNT == 0) {

	$message = "Le module n'est pas activé.";
	// include templates files
	include('templates/header_default.php');
	include('templates/error.php');
	include('templates/footer_default.php');
	exit;

}

$dao = new DaoAccounts();

//-------------------------------
// get vars
//-------------------------------
// set page to display
$default = "list";
$tpl = "";
$tpl_array = array("list","form", "recap", "del", "confirmed", "choice");
if (isset($_GET['tpl']) && ! empty($_GET['tpl'])) {
	$tpl = Utils::sanitize($_GET['tpl']);
} elseif (isset($_POST['tpl']) && ! empty($_POST['tpl'])) {
	$tpl = Utils::sanitize($_POST['tpl']);
}
if( ! in_array($tpl, $tpl_array)) {
	$tpl = $default;
}

//-------------------------------
$account_id = 0;
if(isset($_GET['account_id']) && ! empty($_GET['account_id'])) {
	$account_id = intval($_GET['account_id']);
} elseif (isset($_POST['account_id']) && ! empty($_POST['account_id'])) {
	$account_id = intval($_POST['account_id']);
}
//-------------------------------
$action = "";
if(isset($_GET['action']) && !empty($_GET['action'])) {
	$action = Utils::sanitize($_GET['action']);
} elseif (isset($_POST['action']) && !empty($_POST['action'])) {
	$action = Utils::sanitize($_POST['action']);
}//-------------------------------
$p = ""; // current page number
if(isset($_GET["p"]) && ! empty($_GET['p'])) {
	$p = intval($_GET["p"]);
} elseif (isset($_POST['p']) && ! empty($_POST['p'])) {
	$p = intval($_POST["p"]);
}


//-------------------------------
$message = '';
if(isset($_SESSION['sess_message'])) {
	$message = Utils::displayMessage($_SESSION['sess_message'], $_SESSION['sess_message_type']);
	unset($_SESSION['sess_message'], $_SESSION['sess_message_type']);
}

//-------------------------------------
// LIST
//-------------------------------------
if($tpl == "list") {

	// access rights
	Access::userCanAccess('account_list');

	// var limit
	$dep     = 0; 	// limit
	$current = 1; 	// current page
	// set current page
	if($p != "") {
		$current = $p;
		if ($current < 1) {
			$current = 1;
		}
		$dep = ($current - 1) * NB_RECORDS;
	}
	$more_params = ''; // params to add to sorting in display
	$more_params_pag = ''; // params to add to pagination in display
	$criteria = array();
	$sanitized_get = Utils::sanitizeArray($_GET);
	// sorting
	if(isset($sanitized_get["sort"]) && ! empty($sanitized_get['sort'])) {
		$criteria['sort'] = $sanitized_get["sort"];
		$more_params_pag .= '&sort=' . $sanitized_get['sort'];
		if(isset($sanitized_get["order"]) && ! empty($sanitized_get['order'])) {
			$criteria['order'] = $sanitized_get['order'];
		} else {
			$criteria['order'] = 'asc';
		}
		$more_params_pag .= '&order=' . $criteria['order'];
	}
	// filter
	if(isset($sanitized_get['f_name'])) {
		$criteria['f_name'] = $sanitized_get['f_name'];
		$more_params .= '&f_name=' . $sanitized_get['f_name'];
	}
	if(isset($sanitized_get['f_active'])) {
		$criteria['f_active'] = $sanitized_get['f_active'];
		$more_params .= '&f_active=' . $sanitized_get['f_active'];
	} else {
		$criteria['f_active'] = 1;
		$more_params .= '&f_active=1';
	}
	if(isset($sanitized_get['f_ref'])) {
		$criteria['f_ref'] = $sanitized_get['f_ref'];
		$more_params .= '&f_ref=' . $sanitized_get['f_ref'];
	}
	//-- end filters
	$more_params_pag .= $more_params;

	$accounts_list = $dao->getList($criteria, NB_RECORDS, $dep);

	//---------------------------------
	// additional scripts
	$scripts = '<script type="text/javascript" src="js/jquery.fancybox-1.3.4.pack.js"></script>' . "\n"
		. '<script type="text/javascript" src="js/jquery.fancybox.init.js"></script>' . "\n"
		. '<link type="text/css" media="all" rel="stylesheet" href="css/fancybox.css">';
	// display
	include('templates/header_default.php');
	include('templates/accounts_list.php');
	include('templates/footer_default.php');

}

//-------------------------------------
// ADD/UPDATE FORM
//-------------------------------------
elseif($tpl == "form") {

	// access rights
	Access::userCanAccess('account_form');
	Access::userCanAccessObject('account', $account_id);

	// fetch accounts metas
	$dao_metas = new DaoAccountMetas();
	$metas = $dao_metas->getAccountMetas(array('f_active' => 1), 0);

	if(isset($_POST['tpl'])) {
		// CSRF token validity
		if( ! Utils::isValidToken($_POST[TOKEN_PREFIX . 'token'])) {
			$message = "Jeton de sécurité du formulaire invalide. Merci d'utiliser
				le bouton 'Précédent' de votre navigateur web pour retourner à la page
				précédente et soumettre à nouveau le formulaire.";
			include('templates/header_default.php');
			include('templates/error.php');
			include('templates/footer_default.php');
			exit;
		} else {
			$sanitized_post = Utils::sanitizeArray($_POST);
			$data = array(
				'account_name'    => $sanitized_post['a_name'],
				'account_active'  => (isset($sanitized_post['a_active']) ? $sanitized_post['a_active']:0),
				'account_user_id' => $session->getSessionData('user_id')
			);
			// metas
			$meta_array = array();
			foreach($metas['results'] as $metaObject) {
				$meta_array[$metaObject->getId()] = $sanitized_post['m_' . $metaObject->getId()];
			}
			$data['account_meta'] = $meta_array;
			if($sanitized_post['account_id'] > 0) {
				// check if contact exists
				if($dao->accountExists($sanitized_post['account_id'])) {
					$data['account_id'] = $sanitized_post['account_id'];
				}
			}
			$account = new Account($data);
			$errors = false;
			if(empty($data['account_name'])) {
				$errors = true;
				$msg = "Veuillez indiquer un nom pour le compte.";
				$message = Utils::displayMessage($msg, TYPE_MSG_ERROR);
			}
			if( ! $errors) {
				if(isset($data['account_id'])) {
					$account = $dao->updateAccount($account);
				} else {
					$account = $dao->addAccount($account);
				}
				$message = Utils::displayMessage(Utils::mb_ucfirst($lang->account_saved), TYPE_MSG_SUCCESS);
			}
		} // end CSRF token

	} else {
		if($dao->accountExists($account_id)) {
			$account = $dao->getAccount($account_id);
		} else {
			$account = new Account(array('account_active' => 1));
		}
	}
	//---------------------------------
	// display
	// metas infos
	$meta_array = array();
	foreach($metas['results'] as $metaObject) {
		$meta_array[] = $metaObject->getId();
	}
	$dao_metas_rel = new DaoAccountMetaRelationships();
	$account_infos = $dao_metas_rel->getMetasforAccounts($account->getId(), $meta_array);
	// include templates files
	include('templates/header_default.php');
	include('templates/accounts_form.php');
	include('templates/footer_default.php');

}
//------------------------------------------------------------------------------
// ASK FOR DELETE CONFIRMATION
//------------------------------------------------------------------------------
elseif($tpl == "del") {

	// access rights
	Access::userCanAccess('account_del');
	Access::userCanAccessObject('account', $account_id);

	if($dao->accountExists($account_id)) {
		$account = $dao->getAccount($account_id);
		// instead of create all variables, we use an array, it will be extracted in the template
		$data = array(
			'empty'              => false,
			'url_form'           => '?tpl=confirmed',
			'input_hidden_name'  => 'account_id',
			'input_hidden_value' => $account_id,
			'item_to_delete'     => $lang->this_account,
			'value_to_delete'    => $account->getName(),
			'infos'              => 'les entreprises & contacts associés seront supprimés'
		);
	} else {
		$account = new Account(array());
		$data = array('empty' => true);
	}
	$scripts = '<script type="text/javascript" src="js/jquery.fancybox-1.3.4.pack.js"></script>' . "\n"
		. '<script type="text/javascript" src="js/jquery.fancybox.init.js"></script>' . "\n"
		. '<link type="text/css" media="all" rel="stylesheet" href="css/fancybox.css">';
	// display
	include('templates/header_simple.php');
	include('templates/tpl_del_confirm.php');
	include('templates/footer_simple.php');

}
//------------------------------------------------------------------------------
// DELETE
//------------------------------------------------------------------------------
elseif($tpl == 'confirmed') {

	// access rights
	Access::userCanAccess('account_del');
	Access::userCanAccessObject('account', $account_id);

	if( ! Utils::isValidToken($_POST[TOKEN_PREFIX . 'token'])) {
		$message = "Jeton de sécurité du formulaire invalide. Merci d'utiliser
			le bouton 'Précédent' de votre navigateur web pour retourner à la page
			précédente et soumettre à nouveau votre validation.";
		include('templates/header_simple.php');
		include('templates/error.php');
		include('templates/footer_simple.php');
	} else {
		// del account
		$account = $dao->getAccount($account_id);
		$dao->delAccount($account);
		// if the account was the current working account, unset in session
		$current_account = $session->getSessionData('account');
		if($account_id == $current_account) {
			$session->setSessionData('account', '');
			$session->setSessionData('account_name', false);
		}
		// close box
		$data = array('empty' => true);
		$scripts = '<script type="text/javascript" src="js/jquery.fancybox-1.3.4.pack.js"></script>' . "\n"
			. '<script type="text/javascript" src="js/jquery.fancybox.init.js"></script>' . "\n"
			. '<link type="text/css" media="all" rel="stylesheet" href="css/fancybox.css">';
		// display
		include('templates/header_simple.php');
		include('templates/tpl_del_confirm.php');
		include('templates/footer_simple.php');
	}

}
//------------------------------------------------------------------------------
// SESSION CHOICE
//------------------------------------------------------------------------------
elseif($tpl == 'choice') {

	// access rights
	Access::userCanAccess('account_choice');
	Access::userCanAccessObject('account', $account_id);

	if(isset($_GET['prov']) && ! empty($_GET['prov'])) {
		$prov = Utils::sanitize(urlencode($_GET['prov'])) . '.php';
	} else {
		$prov = 'index.php';
	}
	$account = $dao->getAccount($account_id);
	$session->setSessionData('account', $account_id);
	$session->setSessionData('account_name', $account->getName());
	$_SESSION['sess_message'] = Utils::mb_ucfirst($lang->account_switch_ok);
	$_SESSION['sess_message_type'] = TYPE_MSG_SUCCESS;
	header("Location:" . $prov);
	exit;

}
?>