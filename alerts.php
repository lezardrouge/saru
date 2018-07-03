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
 * display alerts list, form etc
 *
 * @since	1.0
 * @author	Marie Kuntz / Lézard Rouge <mariek@lezard-rouge.fr>
 */

require_once "inc/includes.php";
// check if user is connected
$session->isConnected();
$tmp_account = $session->getSessionData('account');
if(empty($tmp_account)) {

	$message = Utils::mb_ucfirst($lang->must_select_account);
	// include templates files
	include('templates/header_default.php');
	include('templates/error.php');
	include('templates/footer_default.php');
	exit;
}
unset($tmp_account);

$dao = new DaoAlerts();

//-------------------------------
// get vars
//-------------------------------
// set page to display
$default = "list";
$tpl = "";
$tpl_array = array("list","form", "form_s", "del", "confirmed", "done", "filter");
if (isset($_GET['tpl']) && ! empty($_GET['tpl'])) {
	$tpl = Utils::sanitize($_GET['tpl']);
} elseif (isset($_POST['tpl']) && ! empty($_POST['tpl'])) {
	$tpl = Utils::sanitize($_POST['tpl']);
}
if( ! in_array($tpl, $tpl_array)) {
	$tpl = $default;
}

//-------------------------------
$alert_id = 0;
if(isset($_GET['alert_id']) && ! empty($_GET['alert_id'])) {
	$alert_id = intval($_GET['alert_id']);
} elseif (isset($_POST['alert_id']) && ! empty($_POST['alert_id'])) {
	$alert_id = intval($_POST['alert_id']);
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

//-------------------------------------
// LIST
//-------------------------------------
if($tpl == "list") {

	// access rights
	Access::userCanAccess('alert_list');

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
	// sorting
	if(isset($_GET["sort"]) && ! empty($_GET['sort'])) {
		$criteria['sort'] = Utils::sanitize($_GET["sort"]);
		$more_params_pag .= '&sort=' . Utils::sanitize($_GET['sort']);
		if(isset($_GET["order"]) && ! empty($_GET['order'])) {
			$criteria['order'] = Utils::sanitize($_GET['order']);
		} else {
			$criteria['order'] = 'asc';
		}
		$more_params_pag .= '&order=' . $criteria['order'];
	}
	// filter
	if(isset($_GET['f_name'])) {
		$criteria['f_name'] = Utils::sanitize($_GET['f_name']);
		$more_params .= '&f_name=' . Utils::sanitize($_GET['f_name']);
	}
	if(isset($_GET['f_done'])) {
		$criteria['f_done'] = Utils::sanitize($_GET['f_done']);
		$more_params .= '&f_done=' . Utils::sanitize($_GET['f_done']);
	} else {
		$criteria['f_done'] = 0;
		$more_params .= '&f_done=0';
	}
	if(isset($_GET['f_keyword'])) {
		$criteria['f_keyword'] = Utils::sanitize($_GET['f_keyword']);
		$more_params .= '&f_keyword=' . Utils::sanitize($_GET['f_keyword']);
	}
	if(isset($_GET['f_user'])) {
		$criteria['f_user'] = Utils::sanitize($_GET['f_user']);
		$more_params .= '&f_user=' . Utils::sanitize($_GET['f_user']);
	}
	if(isset($_GET['f_date_from'])) {
		$criteria['f_date_from'] = Utils::sanitize($_GET['f_date_from']);
		$more_params .= '&f_date_from=' . Utils::sanitize($_GET['f_date_from']);
	}
	if(isset($_GET['f_date_to'])) {
		$criteria['f_date_to'] = Utils::sanitize($_GET['f_date_to']);
		$more_params .= '&f_date_to=' . Utils::sanitize($_GET['f_date_to']);
	}
	if(isset($_GET['f_when'])) {
		$criteria['f_when'] = Utils::sanitize($_GET['f_when']);
		$more_params .= '&f_when=' . Utils::sanitize($_GET['f_when']);
	}
	//-- end filters
	$more_params_pag .= $more_params;

	$alerts_list = $dao->getList($criteria, NB_RECORDS, $dep);
	$contact_ids = array();
	foreach ($alerts_list['results'] as $alert) {
		$contact_ids[] = $alert->getContact_id();
	}

	// contacts to display on the list
	$dao_contacts = new DaoContacts();
	$contacts = $dao_contacts->getContactsById($contact_ids);

	// users having an access to this account
	if(MOD_ACCESS == 1) {
		$current_user_isadmin = $session->getSessionData('isadmin');
	} else {
		$current_user_isadmin = 1;
	}
	$dao_users = new DaoUsers();
	$account_id = $session->getSessionData('account');
	$users = $dao_users->getUsersFromAccount($account_id, $current_user_isadmin);

	//---------------------------------
	// additional scripts
	$scripts = '<script type="text/javascript" src="js/jquery.ui.datepicker-fr.js"></script>' . "\n"
		. '<script type="text/javascript">
		$(function() {
			$.datepicker.setDefaults($.datepicker.regional["fr"]);
			$("#f_date_from, #f_date_to").datepicker({
				showOn: "both",
				buttonImage: "images/calendar.png",
				buttonImageOnly: true,
				showButtonPanel: true,
				changeMonth: true,
				changeYear: true,
				dateFormat: "dd/mm/yy",
				showAnim: "slideDown"
			});
		});

		</script>' . "\n"
		. '<script type="text/javascript" src="js/jquery.fancybox-1.3.4.pack.js"></script>' . "\n"
		. '<script type="text/javascript" src="js/jquery.fancybox.init.js"></script>' . "\n"
		. '<link type="text/css" media="all" rel="stylesheet" href="css/fancybox.css">';
	// display
	include('templates/header_default.php');
	include('templates/alerts_list.php');
	include('templates/footer_default.php');

}

//-------------------------------------
// ADD/UPDATE FORM
//-------------------------------------
elseif($tpl == "form" || $tpl == "form_s") {

	// access rights
	Access::userCanAccess('alert_form');
	Access::userCanAccessObject('alert', $alert_id);
	$message = '';
	$current_user_id = $session->getSessionData('user_id');
	// to assign an alert to another user
	if(Access::userCanAccess('alert_assign', false)) {
		if(MOD_ACCESS == 1) {
			$current_user_isadmin = $session->getSessionData('isadmin');
		} else {
			$current_user_isadmin = 1;
		}
		$dao_users = new DaoUsers();
		$account_id = $session->getSessionData('account');
		$users = $dao_users->getUsersFromAccount($account_id, $current_user_isadmin);
	}

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
				'alert_contact_name' => $sanitized_post['contact_name'],
				'alert_contact_id'   => $sanitized_post['contact_id'],
				'alert_date_format'  => $sanitized_post['a_date'],
				'alert_date'         => Utils::date2ISO($sanitized_post['a_date']),
				'alert_comments'     => $sanitized_post['a_comment']
			);
			if(isset($sanitized_post['a_priority'])) {
				$data['alert_priority'] = 1;
			} else {
				$data['alert_priority'] = 0;
			}
			if(isset($sanitized_post['a_done'])) {
				$data['alert_done'] = 1;
			} else {
				$data['alert_done'] = 0;
			}
			if(isset($sanitized_post['user_id']) && isset($users[$sanitized_post['user_id']])) {
				$data['alert_user_id'] = $sanitized_post['user_id'];
			} else {
				$data['alert_user_id'] = $current_user_id;
			}
			if($sanitized_post['alert_id'] > 0) {
				// check if alert exists
				if($dao->alertExists($sanitized_post['alert_id'])) {
					$data['alert_id'] = $sanitized_post['alert_id'];
				}
			}
			$alert = new Alert($data);
			$errors = false;
			if(empty($data['alert_contact_id'])) {
				$errors = true;
				$msg = $lang->error_no_valid_contact;
				$message = Utils::displayMessage($msg, TYPE_MSG_ERROR);
			}
			/*if(empty($data['alert_date_format'])) {
				$errors = TRUE;
				$msg = 'Vous n\'avez pas renseigné la date. ';
				$message .= Utils::displayMessage($msg, TYPE_MSG_ERROR);
			}*/
			if( ! $errors) {
				if(isset($data['alert_id'])) {
					$alert = $dao->updateAlert($alert);
				} else {
					$alert = $dao->addAlert($alert);
				}
				$message = Utils::displayMessage('Informations enregistrées', TYPE_MSG_SUCCESS);
			}
		}


	} else {
		if($dao->alertExists($alert_id)) {
			$alert = $dao->getAlert($alert_id);
		} else {
			// fetch contact_id if given
			$contact_id = 0;
			if(isset($_GET['contact_id']) && ! empty($_GET['contact_id'])) {
				$contact_id = intval($_GET['contact_id']);
			}
			$alert = new Alert(array(
				'alert_contact_id' => $contact_id
			));
		}
	}

	//---------------------------------
	// display
	$scripts = '<script type="text/javascript" src="js/jquery.ui.datepicker-fr.js"></script>' . "\n"
		. '<script type="text/javascript">
		$(function() {
			$.datepicker.setDefaults($.datepicker.regional["fr"]);
			$("#m_date").datepicker({
				showOn: "both",
				buttonImage: "images/calendar.png",
				buttonImageOnly: true,
				showButtonPanel: true,
				changeMonth: true,
				changeYear: true,
				dateFormat: "dd/mm/yy",
				showAnim: "slideDown"
			});

			function fillContactId(contact_id) {
				$("#contact_id").val(contact_id);
			}
			$( "#contact_name" ).autocomplete({
				delay: 500,
				source: "contacts.php?tpl=search",
				minLength: 2,
				select: function( event, ui ) {
					fillContactId( ui.item ? ui.item.id:0 );
				}
			});
		});
	</script>' . "\n";
	// include templates files
	if($tpl == 'form_s') {
		$type_tpl = "simple";
	} else {
		$type_tpl = "default";
	}
	include('templates/header_' . $type_tpl . '.php');
	include('templates/alerts_form.php');
	include('templates/footer_' . $type_tpl . '.php');

}

//------------------------------------------------------------------------------
// ASK FOR DELETE CONFIRMATION
//------------------------------------------------------------------------------
elseif($tpl == "del") {

	// access rights
	Access::userCanAccess('alert_del');
	Access::userCanAccessObject('alert', $alert_id);

	if($dao->alertExists($alert_id)) {
		$alert = $dao->getAlert($alert_id);
		// instead of create all variables, we use an array, it will be extracted in the template
		$data = array(
			'empty'              => false,
			'url_form'           => '?tpl=confirmed',
			'input_hidden_name'  => 'alert_id',
			'input_hidden_value' => $alert_id,
			'item_to_delete'     => 'cette alerte',
			'value_to_delete'    => $alert->getDate_format() . ' ' . $alert->getContact()->getFullname() . '<br>' . Utils::shorten($alert->getComments()),
			'infos'              => 'cette suppression est définitive'
		);
	} else {
		$alert = new Alert(array());
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
	Access::userCanAccess('alert_del');
	Access::userCanAccessObject('alert', $alert_id);

	if( ! Utils::isValidToken($_POST[TOKEN_PREFIX . 'token'])) {
		$message = "Jeton de sécurité du formulaire invalide. Merci d'utiliser
			le bouton 'Précédent' de votre navigateur web pour retourner à la page
			précédente et soumettre à nouveau votre validation.";
		include('templates/header_simple.php');
		include('templates/error.php');
		include('templates/footer_simple.php');
	} else {
		// del alert
		$alert = $dao->getAlert($alert_id);
		$dao->delAlert($alert);
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
// MARK AS DONE
//------------------------------------------------------------------------------
elseif($tpl == "done") {

	// access rights
	Access::userCanAccess('alert_form');
	Access::userCanAccessObject('alert', $alert_id);
	$message = '';

	// CSRF token validity
	if( ! Utils::isValidToken($_GET[TOKEN_PREFIX . 'token'])) {
		$message = "Jeton de sécurité invalide. Merci d'utiliser le bouton
			'Précédent' de votre navigateur web pour retourner à la page
			précédente et retenter votre action.";
		include('templates/header_default.php');
		include('templates/error.php');
		include('templates/footer_default.php');
		exit;
	} elseif($alert_id > 0) {
		// check if alert exists
		if($dao->alertExists($alert_id)) {
			$dao->switchAlert($alert_id, 1);
		}
	}
	$prov = $_SERVER['HTTP_REFERER'];
	header("Location:" . $prov);
	exit;

}

//------------------------------------------------------------------------------
// SEND ALERTS
//------------------------------------------------------------------------------
elseif($tpl == 'alert') {

	$dao->sendAlerts();

}

//------------------------------------------------------------------------------
// HOME FILTER
//------------------------------------------------------------------------------
elseif($tpl == 'filter') {

	// access rights
	Access::userCanAccess('alert_list');

	if(isset($_GET['prov']) && ! empty($_GET['prov'])) {
		$prov = Utils::sanitize(urlencode($_GET['prov'])) . '.php';
	} else {
		$prov = 'index.php';
	}
	$onlyMe = Utils::sanitize($_POST['onlyme']);
	if($onlyMe == 1) {
		setcookie('onlyme', '1', time()+60*60*24*30, '/', '', false, false);
	} else {
		setcookie('onlyme', '0', time()+60*60*24*30, '/', '', false, false);
	}
	header("Location:" . $prov);
	exit;

}



?>
