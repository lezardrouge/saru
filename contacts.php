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
 * display contacts list, form etc
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

$dao = new DaoContacts();

//-------------------------------
// get vars
//-------------------------------
// set page to display
$default = "list";
$tpl = "";
$tpl_array = array("list","form", "form_s", "recap","del", "confirmed", "search");
if (isset($_GET['tpl']) && ! empty($_GET['tpl'])) {
	$tpl = Utils::sanitize($_GET['tpl']);
} elseif (isset($_POST['tpl']) && ! empty($_POST['tpl'])) {
	$tpl = Utils::sanitize($_POST['tpl']);
}
if( ! in_array($tpl, $tpl_array)) {
	$tpl = $default;
}

//-------------------------------
$contact_id = 0;
if(isset($_GET['contact_id']) && ! empty($_GET['contact_id'])) {
	$contact_id = intval($_GET['contact_id']);
} elseif (isset($_POST['contact_id']) && ! empty($_POST['contact_id'])) {
	$contact_id = intval($_POST['contact_id']);
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
	Access::userCanAccess('contact_list');

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
	// sorting
	if(isset($_GET["sort"]) && ! empty($_GET['sort'])) {
		$criteria['sort'] = Utils::sanitize($_GET["sort"]);
		$more_params_pag .= '&sort=' . $_GET['sort'];
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
	if(isset($_GET['f_company'])) {
		$criteria['f_company'] = Utils::sanitize($_GET['f_company']);
		$more_params .= '&f_company=' . Utils::sanitize($_GET['f_company']);
	}
	if(isset($_GET['f_type'])) {
		$criteria['f_type'] = Utils::sanitize($_GET['f_type']);
		$more_params .= '&f_type=' . Utils::sanitize($_GET['f_type']);
	}
	if(isset($_GET['f_email'])) {
		$criteria['f_email'] = Utils::sanitize($_GET['f_email']);
		$more_params .= '&f_email=' . Utils::sanitize($_GET['f_email']);
	}
	if(isset($_GET['f_zipcity'])) {
		$criteria['f_zipcity'] = Utils::sanitize($_GET['f_zipcity']);
		$more_params .= '&f_zipcity=' . Utils::sanitize($_GET['f_zipcity']);
	}
	if(isset($_GET['f_phone'])) {
		$criteria['f_phone'] = Utils::sanitize($_GET['f_phone']);
		$more_params .= '&f_phone=' . Utils::sanitize($_GET['f_phone']);
	}
	if(isset($_GET['f_date_add_from'])) {
		$criteria['f_date_add_from'] = Utils::sanitize($_GET['f_date_add_from']);
		$more_params .= '&f_date_add_from=' . Utils::sanitize($_GET['f_date_add_from']);
	}
	if(isset($_GET['f_date_add_to'])) {
		$criteria['f_date_add_to'] = Utils::sanitize($_GET['f_date_add_to']);
		$more_params .= '&f_date_add_to=' . Utils::sanitize($_GET['f_date_add_to']);
	}
	if(isset($_GET['f_date_upd_from'])) {
		$criteria['f_date_upd_from'] = Utils::sanitize($_GET['f_date_upd_from']);
		$more_params .= '&f_date_upd_from=' . Utils::sanitize($_GET['f_date_upd_from']);
	}
	if(isset($_GET['f_date_upd_to'])) {
		$criteria['f_date_upd_to'] = Utils::sanitize($_GET['f_date_upd_to']);
		$more_params .= '&f_date_upd_to=' . Utils::sanitize($_GET['f_date_upd_to']);
	}
	if(isset($_GET['f_active'])) {
		$criteria['f_active'] = Utils::sanitize($_GET['f_active']);
		$more_params .= '&f_active=' . Utils::sanitize($_GET['f_active']);
	} else {
		$criteria['f_active'] = 1;
		$more_params .= '&f_active=1';
	}
	//-- end filters
	$more_params_pag .= $more_params;

	$contacts_list = $dao->getList($criteria, NB_RECORDS, $dep);
	$contact_ids = array();
	foreach ($contacts_list['results'] as $contact) {
		$contact_ids[] = $contact->getId();
	}

	// meta to display on the list
	// 2 => main phone ; 3 => other phone ; 4 => email ; 7 => cp ; 8 => ville
	$meta_array = array(2, 3, 4, 7, 8);
	$dao_metas_rel = new DaoContactMetaRelationships();
	$contact_infos = $dao_metas_rel->getMetasforContacts($contact_ids, $meta_array);

	// fetch contact types
	$types_class = new ContactType(array());
	$contact_types = $types_class->getContactTypesAsArray(true);

	//---------------------------------
	// additional scripts
	$scripts = '<script type="text/javascript" src="js/jquery.ui.datepicker-fr.js"></script>' . "\n"
		. '<script type="text/javascript">
		$(function() {
			$.datepicker.setDefaults($.datepicker.regional["fr"]);
			$(".date").datepicker({
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
		. '<link type="text/css" rel="stylesheet" href="css/fancybox.css">';
	// display
	include('templates/header_default.php');
	include('templates/contacts_list.php');
	include('templates/footer_default.php');

}

//-------------------------------------
// ADD/UPDATE FORM
//-------------------------------------
elseif($tpl == "form" || $tpl == "form_s") {

	// access rights
	Access::userCanAccess('contact_form');
	Access::userCanAccessObject('contact', $contact_id);

	// fetch contacts metas
	$dao_metas = new DaoContactMetas();
	$metas = $dao_metas->getContactMetas(array('f_active' => 1), 0);

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
			// prepare data
			$data = array(
				'contact_firstname'    => $sanitized_post['c_firstname'],
				'contact_lastname'     => $sanitized_post['c_lastname'],
				'contact_company_id'   => $sanitized_post['c_company_id'],
				'contact_company_name' => $sanitized_post['c_company_name'],
				'contact_type_id'      => $sanitized_post['c_type'],
				'contact_comments'     => $sanitized_post['c_comment'],
				'contact_active'       => (isset($sanitized_post['c_active'])? $sanitized_post['c_active']:0),
				'contact_date_update'  => date('Y-m-d')
			);
			// metas
			$meta_array = array();
			foreach($metas['results'] as $metaObject) {
				$meta_array[$metaObject->getId()] = $sanitized_post['m_' . $metaObject->getId()];
			}
			$data['contact_meta'] = $meta_array;
			if($sanitized_post['contact_id'] > 0) {
				// check if contact exists
				if($dao->contactExists($sanitized_post['contact_id'])) {
					$data['contact_id'] = $sanitized_post['contact_id'];
				}
			} else {
				$data['contact_date_add'] = date('Y-m-d');
				$data['contact_active'] = 1;
			}
			$contact = new Contact($data);
			if(isset($data['contact_id'])) {
				$contact = $dao->updateContact($contact);
			} else {
				$contact = $dao->addContact($contact);
			}
			// upload attachment
			if( ! empty($_FILES['attachment']['name'])) {
				$dao_attachments = new DaoAttachments();
				$current_account_id = $session->getSessionData('account');
				$res = $dao_attachments->setAttachment($current_account_id, 'ct', $contact->getId(), $_FILES['attachment']);
				if($res['result'] === false) {
					$message = Utils::displayMessage($res['message'], TYPE_MSG_ERROR);
				} else {
					$message = Utils::displayMessage('Informations enregistrées.', TYPE_MSG_SUCCESS);
				}
			} else {
				$message = Utils::displayMessage('Informations enregistrées.', TYPE_MSG_SUCCESS);
			}
		}

	} else {
		if($dao->contactExists($contact_id)) {
			$contact = $dao->getContact($contact_id);
		} else {
			// fetch company_id if given
			$company_id = 0;
			if(isset($_GET['company_id']) && ! empty($_GET['company_id'])) {
				$company_id = intval($_GET['company_id']);
			}
			$contact = new Contact(array(
				'contact_company_id' => $company_id
			));
		}
	}
	//---------------------------------
	// display

	// fetch contact types
	$types_class = new ContactType(array());
	$contact_types = $types_class->getContactTypesAsArray(TRUE);
	// metas infos
	$meta_array = array();
	foreach($metas['results'] as $metaObject) {
		$meta_array[] = $metaObject->getId();
	}
	$dao_metas_rel = new DaoContactMetaRelationships();
	$contact_infos = $dao_metas_rel->getMetasforContacts($contact->getId(), $meta_array);

	$scripts = '<script type="text/javascript">
		$(function() {
			function fillContactId(company_id) {
				$("#c_company_id").val(company_id);
			}
			$( "#c_company_name" ).autocomplete({
				delay: 500,
				search: function(event, ui) { fillContactId(0) },
				source: "companies.php?tpl=search",
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
	include('templates/contacts_form.php');
	include('templates/footer_' . $type_tpl . '.php');

}

//------------------------------------------------------------------------------
// RECAP
//------------------------------------------------------------------------------
elseif($tpl == "recap") {

	// access rights
	Access::userCanAccess('contact_recap');
	Access::userCanAccessObject('contact', $contact_id);

	if( ! $dao->contactExists($contact_id)) {
		$message = "Le contact n'existe pas.";
		// include templates files
		include('templates/header_default.php');
		include('templates/error.php');
		include('templates/footer_default.php');
		exit;
	}

	$contact = $dao->getContact($contact_id);

	// fetch contacts metas
	$dao_metas = new DaoContactMetas();
	$metas = $dao_metas->getContactMetas(array('f_active' => 1), 0);
	// fetch contact infos
	$meta_array = array();
	foreach($metas['results'] as $metaObject) {
		$meta_array[] = $metaObject->getId();
	}
	// metas infos
	$dao_metas_rel = new DaoContactMetaRelationships();
	$contact_infos = $dao_metas_rel->getMetasforContacts($contact->getId(), $meta_array);

	// company info
	$dao_company = new DaoCompanies();
	$company = $dao_company->getCompany($contact->getCompany_id());
	$dao_cie_metas = new DaoCompanyMetas();
	$cie_metas = $dao_cie_metas->getCompanyMetas(array('f_active' => 1), 0);
	// fetch contact infos
	$cie_meta_array = array();
	foreach($cie_metas['results'] as $metaObject) {
		$cie_meta_array[] = $metaObject->getId();
	}
	// metas infos
	$dao_cie_metas_rel = new DaoCompanyMetaRelationships();
	$company_infos = $dao_cie_metas_rel->getMetasforCompanies($company->getId(), $cie_meta_array);

	// meetings & alerts
	$criteria = array(
		'f_ctc' => $contact->getId(),
		'sort'  => 'date',
		'order' => 'desc'
	);
	$dao_meetings = new DaoMeetings();
	$meetings_list = $dao_meetings->getList($criteria, 0);
	$dao_alerts = new DaoAlerts();
	$criteria['f_done'] = 0;
	$criteria['order'] = 'asc';
	$alerts_list = $dao_alerts->getList($criteria, 0);

	// attachments
	$dao_attachments = new DaoAttachments();
	// attachments for meetings
	$meeting_ids = array();
	foreach($meetings_list['results'] as $meeting) {
		$meeting_ids[] = $meeting->getId();
	}
	$meeting_attachments = $dao_attachments->getAttachmentsByMeetings($meeting_ids);
	$contact_attachments = $dao_attachments->getAttachmentsByContacts(array($contact->getId()));

	//---------------------------------
	// additional scripts
	$scripts = '<script type="text/javascript" src="js/jquery.fancybox-1.3.4.pack.js"></script>' . "\n"
		. '<script type="text/javascript" src="js/jquery.fancybox.init.js"></script>' . "\n"
		. '<link type="text/css" rel="stylesheet" href="css/fancybox.css">';
	// include templates files
	include('templates/header_default.php');
	include('templates/contacts_recap.php');
	include('templates/footer_default.php');

}

//------------------------------------------------------------------------------
// ASK FOR DELETE CONFIRM
//------------------------------------------------------------------------------
elseif($tpl == "del") {

	// access rights
	Access::userCanAccess('contact_del');
	Access::userCanAccessObject('contact', $contact_id);

	if($dao->contactExists($contact_id)) {
		$contact = $dao->getContact($contact_id);
		// instead of create all variables, we use an array, it will be extracted in the template
		$data = array(
			'empty'              => false,
			'url_form'           => '?tpl=confirmed',
			'input_hidden_name'  => 'contact_id',
			'input_hidden_value' => $contact_id,
			'item_to_delete'     => 'ce contact',
			'value_to_delete'    => $contact->getFirstname() . ' ' . $contact->getLastname(),
			'infos'              => 'cette suppression est définitive'
		);
	} else {
		$contact = new Contact(array());
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
	Access::userCanAccess('contact_del');
	Access::userCanAccessObject('contact', $contact_id);

	if( ! Utils::isValidToken($_POST[TOKEN_PREFIX . 'token'])) {
		$message = "Jeton de sécurité du formulaire invalide. Merci d'utiliser
			le bouton 'Précédent' de votre navigateur web pour retourner à la page
			précédente et soumettre à nouveau votre validation.";
		include('templates/header_simple.php');
		include('templates/error.php');
		include('templates/footer_simple.php');
	} else {
		// del contact
		$contact = $dao->getContact($contact_id);
		$dao->delContact($contact);
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
// JSON SEARCH
//------------------------------------------------------------------------------
elseif($tpl == 'search') {

	$search = Utils::sanitize($_GET['term']);
	$dao_contacts = new DaoContacts();
	$contacts = $dao_contacts->getList(array('f_ctc' => $search), 0);
	$array = array();
	foreach($contacts['results'] as $contact) {
		$value = $contact->getFirstname() . ' ' . $contact->getLastname();
		$company = $contact->getCompany()->getName();
		if( ! empty($company)) {
			$value .= ' (' . $contact->getCompany()->getName() . ')';
		}
		$array[] = array('id' => $contact->getId(),
						'value' => Utils::displayEscapedString($value));
	}
	echo json_encode($array);
}
?>