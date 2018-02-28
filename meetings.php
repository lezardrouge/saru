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
 * display meeting list, form, etc
 *
 * @since	1.0
 * @author Marie Kuntz / Lézard Rouge
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

$dao = new DaoMeetings();

//-------------------------------
// get vars
//-------------------------------
// set page to display
$default = "list";
$tpl = "";
$tpl_array = array("list","form","form_s","del", "confirmed", "search");
if (isset($_GET['tpl']) && ! empty($_GET['tpl'])) {
	$tpl = Utils::sanitize($_GET['tpl']);
} elseif (isset($_POST['tpl']) && ! empty($_POST['tpl'])) {
	$tpl = Utils::sanitize($_POST['tpl']);
}
if( ! in_array($tpl, $tpl_array)) {
	$tpl = $default;
}

//-------------------------------
$meeting_id = 0;
if(isset($_GET['meeting_id']) && ! empty($_GET['meeting_id'])) {
	$meeting_id = intval($_GET['meeting_id']);
} elseif (isset($_POST['meeting_id']) && ! empty($_POST['meeting_id'])) {
	$meeting_id = intval($_POST['meeting_id']);
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
	Access::userCanAccess('meeting_list');

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
	if(isset($sanitized_get['f_ctc'])) {
		$criteria['f_ctc'] = $sanitized_get['f_ctc'];
		$more_params .= '&f_ctc=' . $sanitized_get['f_ctc'];
	}
	if(isset($sanitized_get['f_name'])) {
		$criteria['f_name'] = $sanitized_get['f_name'];
		$more_params .= '&f_name=' . $sanitized_get['f_name'];
	}
	if(isset($sanitized_get['f_company'])) {
		$criteria['f_company'] = $sanitized_get['f_company'];
		$more_params .= '&f_company=' . $sanitized_get['f_company'];
	}
	if(isset($sanitized_get['f_type'])) {
		$criteria['f_type'] = $sanitized_get['f_type'];
		$more_params .= '&f_type=' . $sanitized_get['f_type'];
	}
	if(isset($sanitized_get['f_keyword'])) {
		$criteria['f_keyword'] = $sanitized_get['f_keyword'];
		$more_params .= '&f_keyword=' . $sanitized_get['f_keyword'];
	}
	if(isset($sanitized_get['f_date_from'])) {
		$criteria['f_date_from'] = $sanitized_get['f_date_from'];
		$more_params .= '&f_date_from=' . $sanitized_get['f_date_from'];
	}
	if(isset($sanitized_get['f_date_to'])) {
		$criteria['f_date_to'] = $sanitized_get['f_date_to'];
		$more_params .= '&f_date_to=' . $sanitized_get['f_date_to'];
	}
	//-- end filters
	$more_params_pag .= $more_params;

	$meetings_list = $dao->getList($criteria, NB_RECORDS, $dep);
	$contact_ids = array();
	foreach ($meetings_list['results'] as $meeting) {
		$contact_ids[] = $meeting->getContact_id();
	}

	// contacts to display on the list
	$dao_contacts = new DaoContacts();
	$contacts = $dao_contacts->getContactsById($contact_ids);

	// fetch meeting types
	$types_class = new MeetingType(array());
	$meeting_types = $types_class->getMeetingTypesAsArray(TRUE);

	//---------------------------------
	// additional scripts
	$scripts = '<script type="text/javascript" src="js/jquery.ui.datepicker-fr.js"></script>' . "\n"
		. '<script type="text/javascript">
		$(function() {
			$.datepicker.setDefaults($.datepicker.regional["fr"]);
			$("#f_date_from").datepicker({
				showOn: "both",
				buttonImage: "images/calendar.png",
				buttonImageOnly: true,
				showButtonPanel: true,
				changeMonth: true,
				changeYear: true,
				dateFormat: "dd/mm/yy",
				showAnim: "slideDown"
			});
			$("#f_date_to").datepicker({
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
	include('templates/meetings_list.php');
	include('templates/footer_default.php');

}

//-------------------------------------
// ADD/UPDATE FORM
//-------------------------------------
elseif($tpl == "form" || $tpl == "form_s") {

	// access rights
	Access::userCanAccess('meeting_form');
	Access::userCanAccessObject('meeting', $meeting_id);

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
				'meeting_type_id'      => $sanitized_post['m_type'],
				'meeting_contact_name' => $sanitized_post['contact_name'],
				'meeting_contact_id'   => $sanitized_post['contact_id'],
				'meeting_date_format'  => $sanitized_post['m_date'],
				'meeting_date'         => Utils::date2ISO($sanitized_post['m_date']),
				'meeting_comments'     => $sanitized_post['m_comment'],
			);
			if($sanitized_post['meeting_id'] > 0) {
				// check if meeting exists
				if($dao->meetingExists($sanitized_post['meeting_id'])) {
					$data['meeting_id'] = $sanitized_post['meeting_id'];
				}
			}
			$meeting = new Meeting($data);
			$errors = false;
			if(empty($data['meeting_contact_id'])) {
				$errors = true;
				$msg = $lang->error_no_valid_contact;
				$message = Utils::displayMessage($msg, TYPE_MSG_ERROR);
			}
			if(empty($data['meeting_type_id'])) {
				$errors = true;
				$msg = 'Vous n\'avez pas sélectionné de type de rendez-vous. ';
				$message .= Utils::displayMessage($msg, TYPE_MSG_ERROR);
			}
			if(empty($data['meeting_date_format'])) {
				$errors = true;
				$msg = 'Vous n\'avez pas renseigné la date. ';
				$message .= Utils::displayMessage($msg, TYPE_MSG_ERROR);
			}
			if( ! $errors) {
				if(isset($data['meeting_id'])) {
					$meeting = $dao->updateMeeting($meeting);
				} else {
					$meeting = $dao->addMeeting($meeting);
				}
				// upload attachment
				if( ! empty($_FILES['attachment']['name'])) {
					$dao_attachments = new DaoAttachments();
					$current_account_id = $session->getSessionData('account');
					$res = $dao_attachments->setAttachment($current_account_id, 'me', $meeting->getId(), $_FILES['attachment']);
					if($res['result'] === false) {
						$message = Utils::displayMessage($res['message'], TYPE_MSG_ERROR);
					} else {
						$message = Utils::displayMessage('Informations enregistrées.', TYPE_MSG_PERM_SUCCESS);
					}
				} else {
					$message = Utils::displayMessage('Informations enregistrées.', TYPE_MSG_SUCCESS);
				}
			}
		}

	} else {
		if($dao->meetingExists($meeting_id)) {
			$meeting = $dao->getMeeting($meeting_id);
		} else {
			// fetch contact_id if given
			$contact_id = 0;
			if(isset($_GET['contact_id']) && ! empty($_GET['contact_id'])) {
				$contact_id = intval($_GET['contact_id']);
			}
			$meeting = new Meeting(array(
				'meeting_date' => date('Y-m-d'),
				'meeting_contact_id' => $contact_id
			));
		}
	}



	// fetch meeting types
	$types_class = new MeetingType(array());
	$meeting_types = $types_class->getMeetingTypesAsArray(TRUE);

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
	include('templates/meetings_form.php');
	include('templates/footer_' . $type_tpl . '.php');

}
//------------------------------------------------------------------------------
// ASK FOR DELETE CONFIRM
//------------------------------------------------------------------------------
elseif($tpl == "del") {

	// access rights
	Access::userCanAccess('meeting_del');
	Access::userCanAccessObject('meeting', $meeting_id);

	if($dao->meetingExists($meeting_id)) {
		$meeting = $dao->getMeeting($meeting_id);
		// instead of create all variables, we use an array, it will be extracted in the template
		$data = array(
			'empty'              => false,
			'url_form'           => '?tpl=confirmed',
			'input_hidden_name'  => 'meeting_id',
			'input_hidden_value' => $meeting_id,
			'item_to_delete'     => $lang->this_meeting,
			'value_to_delete'    => $meeting->getDate_format() . ' ' . $meeting->getContact()->getFullname(),
			'infos'              => 'cette suppression est définitive'
		);
	} else {
		$meeting = new Meeting(array());
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
	Access::userCanAccess('meeting_del');
	Access::userCanAccessObject('meeting', $meeting_id);

	if( ! Utils::isValidToken($_POST[TOKEN_PREFIX . 'token'])) {
		$message = "Jeton de sécurité du formulaire invalide. Merci d'utiliser
			le bouton 'Précédent' de votre navigateur web pour retourner à la page
			précédente et soumettre à nouveau votre validation.";
		include('templates/header_simple.php');
		include('templates/error.php');
		include('templates/footer_simple.php');
	} else {
		// del meeting
		$meeting = $dao->getMeeting($meeting_id);
		$dao->delMeeting($meeting);
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