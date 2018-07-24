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
 * display users list, form etc
 *
 * @since	1.0
 * @author Marie Kuntz / Lézard Rouge
 */

require_once "inc/includes.php";
// check if user is connected
$session->isConnected();

// access rights
Access::userCanAccess('admin');

$dao = new DaoUsers();

//-------------------------------
// get vars
//-------------------------------
// set page to display
$default = "list";
$tpl = "";
$tpl_array = array("list","form", "del", "confirmed", "pwd");
if (isset($_GET['tpl']) && ! empty($_GET['tpl'])) {
	$tpl = Utils::sanitize($_GET['tpl']);
} elseif (isset($_POST['tpl']) && ! empty($_POST['tpl'])) {
	$tpl = Utils::sanitize($_POST['tpl']);
}
if( ! in_array($tpl, $tpl_array)) {
	$tpl = $default;
}

//-------------------------------
$user_id = 0;
if(isset($_GET['user_id']) && ! empty($_GET['user_id'])) {
	$user_id = intval($_GET['user_id']);
} elseif (isset($_POST['user_id']) && ! empty($_POST['user_id'])) {
	$user_id = intval($_POST['user_id']);
}
//-------------------------------
$action = "";
if(isset($_GET['action']) && !empty($_GET['action'])) {
	$action = Utils::sanitize($_GET['action']);
} elseif (isset($_POST['action']) && !empty($_POST['action'])) {
	$action = Utils::sanitize($_POST['action']);
}
//-------------------------------
$p = ""; // current page number
if(isset($_GET["p"]) && ! empty($_GET['p'])) {
	$p = intval($_GET["p"]);
} elseif (isset($_POST['p']) && ! empty($_POST['p'])) {
	$p = intval($_POST["p"]);
}
//-------------------------------
$message = ''; // message to display

//-------------------------------------
// LIST
//-------------------------------------
if($tpl == "list") {

	// access rights
	Access::userCanAccess('user_list');

	// var limit
	$dep     = 0; 	// limit
	$current = 1; 	// page courante
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
	if(isset($sanitized_get['f_login'])) {
		$criteria['f_login'] = $sanitized_get['f_login'];
		$more_params .= '&f_login=' . $sanitized_get['f_login'];
	}
	if(isset($sanitized_get['f_email'])) {
		$criteria['f_email'] = $sanitized_get['f_email'];
		$more_params .= '&f_email=' . $sanitized_get['f_email'];
	}
	//-- end filters
	$more_params_pag .= $more_params;

	$users_list = $dao->getList($criteria, NB_RECORDS, $dep);
	$user_ids = array();
	foreach($users_list['results'] as $user) {
		array_push($user_ids, $user->getId());
	}
	$access = new Access();
	$accounts = $access->getAccountsbyUsers($user_ids);

	//---------------------------------
	// additional scripts
	$scripts = '<script type="text/javascript" src="js/jquery.fancybox-1.3.4.pack.js"></script>' . "\n"
		. '<script type="text/javascript" src="js/jquery.fancybox.init.js"></script>' . "\n"
		. '<link type="text/css" rel="stylesheet" href="css/fancybox.css">';
	// display
	include('templates/header_default.php');
	include('templates/users_list.php');
	include('templates/footer_default.php');

}

//-------------------------------------
// ADD/UPDATE FORM
//-------------------------------------
elseif($tpl == "form") {

	// access rights
	Access::userCanAccess('user_form');

	// fetch access rights
	$dao_access = new Access();
	$components = $dao_access->getComponents();
	// fetch accounts
	$dao_accounts = new DaoAccounts();
	$criteria_accounts = array('f_active' => 1);
	$accounts = $dao_accounts->getList($criteria_accounts, 0);

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
			// prepare data
			$sanitized_post = Utils::sanitizeArray($_POST);
			$data = array(
				'user_firstname'    => $sanitized_post['u_firstname'],
				'user_lastname'     => $sanitized_post['u_lastname'],
				'user_login'        => $sanitized_post['u_login'],
				'user_currentlogin' => $sanitized_post['u_currentlogin'],
				'user_pwd'          => $sanitized_post['u_pwd'],
				'user_new_pwd'      => 0,
				'user_email'        => $sanitized_post['u_email'],
				'user_send_alerts'  => 0,
			);
			if(isset($sanitized_post['u_alert'])) {
				$data['user_send_alerts'] = $sanitized_post['u_alert'];
			}
			// perms
			$perms_array = array();
			if(isset($sanitized_post['access'])) {
				foreach($sanitized_post['access'] as $permission) {
					$perms_array[] = $permission;
				}
			}
			$data['user_access_perm_ids'] = $perms_array;
			unset($perms_array);
			// account perms
			$accounts_array = array();
			if(isset($sanitized_post['account'])) {
				foreach($sanitized_post['account'] as $account) {
					$accounts_array[] = $account;
				}
			}
			$data['user_account_perm_ids'] = $accounts_array;
			unset($accounts_array);
			//--
			if($sanitized_post['user_id'] > 0) {
				// check if user exists
				if($dao->userExists($sanitized_post['user_id'])) {
					$data['user_id'] = $sanitized_post['user_id'];
				}
			}
			$user = new User($data);

			$errors = FALSE;
			if(empty($data['user_lastname'])) {
				$errors = true;
				$msg = 'Le champ Nom est obligatoire.';
				$message .= Utils::displayMessage($msg, TYPE_MSG_ERROR);
			}
			if(empty($data['user_firstname'])) {
				$errors = true;
				$msg = 'Le champ Prénom est obligatoire.';
				$message .= Utils::displayMessage($msg, TYPE_MSG_ERROR);
			}
			// fetch current login to check difference, instead of using posted data, which is not trustable
			$buffer_user = $dao->getUser($sanitized_post['user_id']);
			$current_login = $buffer_user->getLogin();
			if(empty($data['user_login'])
				|| (strlen($data['user_login']) < 6)
				|| ( ! preg_match("/^([a-z0-9-_.@])+$/i", $data['user_login']))) {
				$errors = true;
				$msg = 'Veuillez remplir le champ Identifiant avec au moins 6 caractères, alphanumérique et .-_@ acceptés, ne mettez pas d\'espace.';
				$message .= Utils::displayMessage($msg, TYPE_MSG_ERROR);
			} elseif( ! $dao->checkUniqueLogin($data['user_login'], $user_id)) {
				$errors = true;
				$msg = 'Cet identifiant est déjà pris pour un autre utilisateur.';
				$message .= Utils::displayMessage($msg, TYPE_MSG_ERROR);
			} elseif( (! empty($current_login))
				&& ($data['user_login'] != $buffer_user->getLogin())
				&& empty($data['user_pwd'])) {
					$errors = true;
					$msg = 'Veuillez re-saisir le mot de passe pour pouvoir modifier l\'identifiant. Vous pouvez changer le mot de passe.';
					$message .= Utils::displayMessage($msg, TYPE_MSG_ERROR);
			}
			if (empty($data['user_pwd']) &&  ! isset($data['user_id'])) {
				$errors = true;
				$msg = 'Veuillez indiquer un mot de passe.';
				$message .= Utils::displayMessage($msg, TYPE_MSG_ERROR);
			} elseif ( ! empty($data['user_pwd'])
				&& (
					(strlen($data['user_pwd']) < 8)
					|| (strlen($data['user_pwd']) > 20)
					|| ( ! preg_match("/^([-a-z0-9_\-@!\?\$])+$/i", $data['user_pwd'])))
				) {
				$errors = true;
				$msg = 'Veuillez utiliser de 8 à 20 caractères alphanumériques et les caractères spéciaux -_@!?$ (pas d\'espace) pour le champ Mot de passe.';
				$message .= Utils::displayMessage($msg, TYPE_MSG_ERROR);
			}
			if(empty($data['user_email'])
				|| ( ! preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $data['user_email']))) {
				$errors = true;
				$msg = 'L\'email indiqué n\'est pas valide. ';
				$message .= Utils::displayMessage($msg, TYPE_MSG_ERROR);
			}
			if( ! $errors) {
				if(isset($data['user_id'])) {
					$user = $dao->updateUser($user);
				} else {
					$user = $dao->addUser($user);
				}
				$message = Utils::displayMessage('Informations enregistrées.', TYPE_MSG_SUCCESS);
			}
		}

	} else {
		if($dao->userExists($user_id)) {
			$user = $dao->getUser($user_id);
		} else {
			$user = new User(array());
		}
	}
	//---------------------------------
	// display

	// include templates files
	include('templates/header_default.php');
	include('templates/users_form.php');
	include('templates/footer_default.php');

}

//------------------------------------------------------------------------------
// ASK FOR DELETE CONFIRM
//------------------------------------------------------------------------------
elseif($tpl == "del") {

	// access rights
	Access::userCanAccess('user_del');

	if($dao->userExists($user_id)) {
		$user = $dao->getuser($user_id);
		// instead of create all variables, we use an array, it will be extracted in the template
		$data = array(
			'empty'              => false,
			'url_form'           => '?tpl=confirmed',
			'input_hidden_name'  => 'user_id',
			'input_hidden_value' => $user_id,
			'item_to_delete'     => 'cet utilisateur',
			'value_to_delete'    => $user->getFirstname() . ' ' . $user->getLastname(),
			'infos'              => 'cette suppression est définitive'
		);
	} else {
		$user = new User(array());
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
	Access::userCanAccess('user_del');

	if( ! Utils::isValidToken($_POST[TOKEN_PREFIX . 'token'])) {
		$message = "Jeton de sécurité du formulaire invalide. Merci d'utiliser
			le bouton 'Précédent' de votre navigateur web pour retourner à la page
			précédente et soumettre à nouveau votre validation.";
		include('templates/header_simple.php');
		include('templates/error.php');
		include('templates/footer_simple.php');
	} else {
		// del user
		$dao->delUser($user_id);
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
// GET A PASSWORD
//------------------------------------------------------------------------------
elseif($tpl == 'pwd') {

	$password = Utils::generatePwd();
	echo $password;

}
?>
