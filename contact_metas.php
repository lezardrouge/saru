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
 * display and manage contact metas
 *
 * @since	1.0
 * @author Marie Kuntz / Lézard Rouge
 */

require_once "inc/includes.php";
// check if user is connected
$session->isConnected();

$dao = new DaoContactMetas();

//-------------------------------
// get vars
//-------------------------------
// set page to display
$default = "list";
$tpl = "";
$tpl_array = array("list","form", "del", "confirmed");
if (isset($_GET['tpl']) && ! empty($_GET['tpl'])) {
	$tpl = Utils::sanitize($_GET['tpl']);
} elseif (isset($_POST['tpl']) && ! empty($_POST['tpl'])) {
	$tpl = Utils::sanitize($_POST['tpl']);
}
if( ! in_array($tpl, $tpl_array)) {
	$tpl = $default;
}

//-------------------------------
$meta_id = 0;
if(isset($_GET['meta_id']) && ! empty($_GET['meta_id'])) {
	$meta_id = intval($_GET['meta_id']);
} elseif (isset($_POST['meta_id']) && ! empty($_POST['meta_id'])) {
	$meta_id = intval($_POST['meta_id']);
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
// access rights
Access::userCanAccess('admin');

//-------------------------------------
// LIST
//-------------------------------------
if($tpl == "list") {

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
	$criteria = array();
	$metas_list = $dao->getContactMetas($criteria, NB_RECORDS, $dep);

	//---------------------------------
	// additional scripts
	$scripts = '<script type="text/javascript" src="js/jquery.fancybox-1.3.4.pack.js"></script>' . "\n"
		. '<script type="text/javascript" src="js/jquery.fancybox.init.js"></script>' . "\n"
		. '<link type="text/css" media="all" rel="stylesheet" href="css/fancybox.css">';
	// display
	include('templates/header_default.php');
	include('templates/contact_metas_list.php');
	include('templates/footer_default.php');

}

//-------------------------------------
// ADD/UPDATE FORM
//-------------------------------------
elseif($tpl == "form") {

	$message = '';

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
				'meta_name'   => $sanitized_post['meta_name'],
				'meta_order'  => $sanitized_post['meta_order']
			);
			if(isset($sanitized_post['meta_active'])) {
				$data['meta_active'] = 1;
			} else {
				$data['meta_active'] = 0;
			}
			if($sanitized_post['meta_id'] > 0) {
				// check if contact exists
				if($dao->contactMetaExists($sanitized_post['meta_id'])) {
					$data['meta_id'] = $sanitized_post['meta_id'];
				}
			}
			$meta = new ContactMeta($data);
			$errors = false;
			if(empty($data['meta_name'])) {
				$errors = true;
				$msg = "Veuillez indiquer un intitulé.";
				$message = Utils::displayMessage($msg, TYPE_MSG_ERROR);
			}
			if( ! $errors) {
				if(isset($data['meta_id'])) {
					$meta = $dao->updateContactMeta($meta);
				} else {
					$meta = $dao->addContactMeta($meta);
				}
			}
		}

	} else {
		if($dao->contactMetaExists($meta_id)) {
			$meta = $dao->getContactMeta($meta_id);
		} else {
			$meta = new ContactMeta(array());
		}
	}

	//---------------------------------
	// display
	include('templates/header_default.php');
	include('templates/contact_metas_form.php');
	include('templates/footer_default.php');

}
//------------------------------------------------------------------------------
// ASK FOR DELETE CONFIRMATION
//------------------------------------------------------------------------------
elseif($tpl == "del") {

	// access rights
	Access::userCanAccess('admin');

	if($dao->contactMetaExists($meta_id)) {
		$meta = $dao->getContactMeta($meta_id);
		// instead of create all variables, we use an array, it will be extracted in the template
		$data = array(
			'empty'              => false,
			'url_form'           => '?tpl=confirmed',
			'input_hidden_name'  => 'meta_id',
			'input_hidden_value' => $meta_id,
			'item_to_delete'     => 'cette metadonnée',
			'value_to_delete'    => $meta->getName(),
			'infos'              => 'les informations associées aux contacts seront supprimées'
		);
	} else {
		$meta = new ContactMeta(array());
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
	Access::userCanAccess('admin');

	if( ! Utils::isValidToken($_POST[TOKEN_PREFIX . 'token'])) {
		$message = "Jeton de sécurité du formulaire invalide. Merci d'utiliser
			le bouton 'Précédent' de votre navigateur web pour retourner à la page
			précédente et soumettre à nouveau votre validation.";
		include('templates/header_simple.php');
		include('templates/error.php');
		include('templates/footer_simple.php');
	} else {
		$meta = $dao->getContactMeta($meta_id);
		$dao->delContactMeta($meta);
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

?>