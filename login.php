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
 * display SARU login page
 *
 * @since	1.0
 * @author Marie Kuntz / Lézard Rouge
 */

require_once "inc/includes.php";

// if access module is disable, no need to be here
if(MOD_ACCESS === 0) {
	header('Location:index.php');
	exit();
}


$action = "";
if(isset($_GET['action']) && ! empty($_GET['action'])) {
	$action = Utils::sanitize($_GET['action']);
} elseif (isset($_POST['action']) && ! empty($_POST['action'])) {
	$action = Utils::sanitize($_POST['action']);
}
//-------------------------------
$message = ''; // message to display

if(isset($_SESSION['sess_message'])) {
	$message = Utils::displayMessage($_SESSION['sess_message'], $_SESSION['sess_message_type']);
	unset($_SESSION['sess_message'], $_SESSION['sess_message_type']);
}



//-------------------------------------
// LOGOUT
//-------------------------------------
if($action == 'logout') {

	$token = Utils::sanitize($_GET['token']);
	if( ! Utils::isValidToken($token)) {
		$prov = $_SERVER['HTTP_REFERER'];
		header("Location:" . $prov);
		exit;
	} else {
		$_SESSION['sess_message'] = 'Vous êtes maintenant déconnecté.';
		$_SESSION['sess_message_type'] = TYPE_MSG_SUCCESS;
		$session->destroySession();
	}

}


//-------------------------------------
// FORGOTTEN PASSWORD
//-------------------------------------
elseif($action == 'pwd') {

	if (isset($_POST['submitform']) && ! empty($_POST['submitform'])) {
		$username = Utils::sanitize($_POST['username']);
		if( ! empty($username)) {
			//check if exists
			$daoUsers = new DaoUsers();
			$exists = $daoUsers->askForResetPassword($username);
			if( ! $exists) {
				$message = Utils::displayMessage("L'identifiant indiqué n'existe pas.", TYPE_MSG_ERROR);
			} else {
				$message = Utils::displayMessage("Un mail vient d'être envoyé. Suivez les instructions indiquées dans le mail pour réinitialiser votre mot de passe.", TYPE_MSG_SUCCESS);
			}
		} else {
			$message = Utils::displayMessage('Veuillez saisir votre identifiant (login).', TYPE_MSG_ERROR);
		}
	} else {
		$cookie_login = filter_input(INPUT_COOKIE, COOKIE_LOGIN);
		$username = $cookie_login;
	}

	//---------------------------------
	// display
	include('templates/header_default.php');
	include('templates/forgotten_pwd.php');
	include('templates/footer_default.php');

}


//-------------------------------------
// RESET PASSWORD
//-------------------------------------
elseif($action == 'reset') {

	$daoUsers = new DaoUsers();
	$login = '';
	$token = '';
	if(isset($_GET['l']) && ! empty($_GET['l'])) {
		$login = Utils::sanitize($_GET['l']);
	} elseif (isset($_POST['l']) && ! empty($_POST['l'])) {
		$login = Utils::sanitize($_POST['l']);
	}
	if(isset($_GET['t']) && ! empty($_GET['t'])) {
		$token = Utils::sanitize($_GET['t']);
	} elseif (isset($_POST['t']) && ! empty($_POST['t'])) {
		$token = Utils::sanitize($_POST['t']);
	}

	// check if the user actually ask for a new pwd
	$valid = $daoUsers->isValidPasswordReset($login, $token);
	if(!$valid) {
		$message = "Une erreur est survenue.";
		$tpl = "error.php";
	} else {
		if (isset($_POST['submitform']) && ! empty($_POST['submitform'])) {
			$new_password = Utils::sanitize($_POST['password']);
			$check = $daoUsers->checkValidPassword($new_password);
			if($check) {
				$daoUsers->resetPassword($login, $new_password);
				$_SESSION['sess_message'] = 'Mot de passe changé avec succès. Veuillez vous connecter.';
				$_SESSION['sess_message_type'] = TYPE_MSG_SUCCESS;
				header("Location: login.php");
			} else {
				$message = Utils::displayMessage('Veuillez saisir un mot de passe valide.', TYPE_MSG_ERROR);
			}
		}
		$tpl = "reset_password.php";
	}

	//---------------------------------
	// display
	include('templates/header_default.php');
	include('templates/' . $tpl);
	include('templates/footer_default.php');

}


//-------------------------------------
// LOGIN
//-------------------------------------
else {

	if (isset($_POST['submitform']) && ! empty($_POST['submitform'])) {
		// CSRF token validity
		if( ! Utils::isValidLoginToken($_POST['login_token'])) {
			$message = "Vous avez mis trop de temps à vous connecter et le jeton
				de sécurité a expiré. Désolé pour le dérangement, mais la sécurité
				de vos données est importante à nos yeux. Vous pouvez vous connecter
				maintenant, ça va fonctionner.";
			$message = Utils::displayMessage($message, TYPE_MSG_ERROR);
		} else {
			$username = Utils::sanitize($_POST['username']);
			$password = Utils::sanitize($_POST['password']);
			setcookie(COOKIE_LOGIN, $username, time() + COOKIE_EXPIRE);
			if( ! empty($username) && ! empty($password)) {
				$connected = $session->connectUser($username, $password);
				if( ! $connected) {
					$message = Utils::displayMessage('Mauvais identifiant ou mot de passe.', TYPE_MSG_ERROR);
				} else {
					// clean up session table
					$session->cleanSessions();
					// then redirect
					header('Location: index.php');
					exit;
				}
			} else {
				$message = Utils::displayMessage('Veuillez saisir votre identifiant et votre mot de passe.', TYPE_MSG_ERROR);
			}
		}
	} else {
		if(isset($_COOKIE[COOKIE_LOGIN])) {
			$username = $_COOKIE[COOKIE_LOGIN];
		}
	}
	Utils::generateLoginToken();

	//---------------------------------
	// display
	include('templates/header_default.php');
	include('templates/login.php');
	include('templates/footer_default.php');

}
?>
