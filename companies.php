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
 * display companies
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

$dao = new DaoCompanies();

//-------------------------------
// get vars
//-------------------------------
// set page to display
$default = "list";
$tpl = "";
$tpl_array = array("list","form", "recap","del", "confirmed", "search");
if (isset($_GET['tpl']) && ! empty($_GET['tpl'])) {
	$tpl = Utils::sanitize($_GET['tpl']);
} elseif (isset($_POST['tpl']) && ! empty($_POST['tpl'])) {
	$tpl = Utils::sanitize($_POST['tpl']);
}
if( ! in_array($tpl, $tpl_array)) {
	$tpl = $default;
}

//-------------------------------
$company_id = 0;
if(isset($_GET['company_id']) && ! empty($_GET['company_id'])) {
	$company_id = intval($_GET['company_id']);
} elseif (isset($_POST['company_id']) && ! empty($_POST['company_id'])) {
	$company_id = intval($_POST['company_id']);
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
//--
$message = ''; // message to display


//-------------------------------------
// LIST
//-------------------------------------
if($tpl == "list") {

	// access rights
	Access::userCanAccess('company_list');

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
	if(isset($_GET['f_zipcity'])) {
		$criteria['f_zipcity'] = Utils::sanitize($_GET['f_zipcity']);
		$more_params .= '&f_zipcity=' . Utils::sanitize($_GET['f_zipcity']);
	}
	if(isset($_GET['f_type'])) {
		$criteria['f_type'] = Utils::sanitize($_GET['f_type']);
		$more_params .= '&f_type=' . Utils::sanitize($_GET['f_type']);
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

	$companies_list = $dao->getList($criteria, NB_RECORDS, $dep);
	$company_ids = array();
	foreach ($companies_list['results'] as $company) {
		$company_ids[] = $company->getId();
	}

	// meta to display on the list
	// 2 => main phone ; 4 => email ; 7 & 8 => zip & city
	$meta_array = array(2, 4, 7, 8);
	$dao_metas_rel = new DaoCompanyMetaRelationships();
	$company_infos = $dao_metas_rel->getMetasforCompanies($company_ids, $meta_array);

	// fetch contact types
	$types_class = new ContactType(array());
	$contact_types = $types_class->getContactTypesAsArray(TRUE);

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
	include('templates/companies_list.php');
	include('templates/footer_default.php');

}

//-------------------------------------
// ADD/UPDATE FORM
//-------------------------------------
elseif($tpl == "form") {

	// access rights
	Access::userCanAccess('company_form');
	Access::userCanAccessObject('company', $company_id);

	// fetch company metas
	$dao_metas = new DaoCompanyMetas;
	$metas = $dao_metas->getCompanyMetas(array('f_active' => 1), 0);
	// fetch contacts metas
	$dao_c_metas = new DaoContactMetas();
	$c_metas = $dao_c_metas->getContactMetas(array('f_active' => 1), 0);
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
			// company
			$data = array(
				'company_name'        => $sanitized_post['c_name'],
				'company_comments'    => $sanitized_post['c_comment'],
				'company_account_id'  => $session->getSessionData('account'),
				'company_type_id'     => (empty($sanitized_post['c_type']) ? 1:$sanitized_post['c_type']),
				'company_active'      => (isset($sanitized_post['c_active'])? $sanitized_post['c_active']:0),
				'company_date_update' => date('Y-m-d')
			);
			// metas
			$meta_array = array();
			foreach($metas['results'] as $metaObject) {
				$meta_array[$metaObject->getId()] = $sanitized_post['m_' . $metaObject->getId()];
			}
			$data['company_meta'] = $meta_array;
			if($sanitized_post['company_id'] > 0) {
				// check if company exists
				if($dao->companyExists($sanitized_post['company_id'])) {
					$data['company_id'] = $sanitized_post['company_id'];
				}
			} else {
				$data['company_date_add'] = date('Y-m-d');
				$data['company_active'] = 1;
			}
			$company = new Company($data);
			$contact = new Contact(array());
			$errors = false;
			// check if company already exists
			$exists = $dao->getCompanyByName($sanitized_post['c_name'], $session->getSessionData('account'));
			if($exists !== false
				&& $company->getId() != $exists->getId()
				&& (!isset($sanitized_post['force']) || $sanitized_post['force'] != 1)) {
				$errors = true;
				$msg = "Une entreprise de ce nom existe déjà. Vous pouvez modifier
					votre saisie, ou bien forcer l'ajout de cette entreprise
					en cliquant sur
					<input type=\"button\" name=\"forcesubmit\" id=\"forcesubmit\" value=\"Forcer l'ajout\" class=\"btn btn-danger\"><br>
					Si vous souhaitez voir l'entreprise qui porte déjà ce nom,
					<a href=\"companies.php?tpl=recap&company_id=" . $exists->getId()
					. "\" target=\"_blank\">cliquez ici</a> (un nouvel onglet
					s'ouvrira, fermez-le pour revenir à cette fenêtre).";
				$message = Utils::displayMessage($msg, TYPE_MSG_ERROR);
			}

			if(empty($data['company_name'])) {
				$errors = true;
				$msg = sprintf($lang->error_empty_field, 'Nom de l\'entreprise');
				$message = Utils::displayMessage($msg, TYPE_MSG_ERROR);
			}
			if( ! $errors) {

				if(isset($data['company_id'])) {
					$company = $dao->updateCompany($company);
				} else {
					$company = $dao->addCompany($company);
					$data_ctc = array(
						'contact_account_id'  => $session->getSessionData('account'),
						'contact_firstname'   => $sanitized_post['ctc_firstname'],
						'contact_lastname'    => $sanitized_post['ctc_lastname'],
						'contact_comments'    => $sanitized_post['ctc_comment'],
						'contact_active'      => 1,
						'contact_company_id'  => $company->getId(),
						'contact_company_name' => $company->getName(), // hack to force company_id
						'contact_type_id'     => $company->getType_id(),
						'contact_date_add'    => date('Y-m-d'),
						'contact_date_update' => date('Y-m-d'),
					);
					if( ! empty($data_ctc['contact_firstname']) ||  ! empty($data_ctc['contact_lastname'])) {
						$meta_array = array();
						foreach($c_metas['results'] as $metaObject) {
							$meta_array[$metaObject->getId()] = $sanitized_post['ctc_m_' . $metaObject->getId()];
						}
						$data_ctc['contact_meta'] = $meta_array;
						$contact = new Contact($data_ctc);
						$dao_ctc = new DaoContacts();
						$contact = $dao_ctc->addContact($contact);
					}
				}
				// upload attachment
				if( ! empty($_FILES['attachment']['name'])) {
					$dao_attachments = new DaoAttachments();
					$current_account_id = $session->getSessionData('account');
					$res = $dao_attachments->setAttachment($current_account_id, 'ci', $company->getId(), $_FILES['attachment']);
					if($res['result'] === false) {
						$message = Utils::displayMessage($res['message'], TYPE_MSG_ERROR);
					} else {
						$message = Utils::displayMessage('Informations enregistrées.', TYPE_MSG_SUCCESS);
					}
				} else {
					$message = Utils::displayMessage('Informations enregistrées.', TYPE_MSG_SUCCESS);
				}
			}
		}

	} else {
		if($dao->companyExists($company_id)) {
			$company = $dao->getCompany($company_id);
		} else {
			$company = new Company(array());
			$contact = new Contact(array());
		}
	}
	//---------------------------------
	// display

	// fetch contact types
	$types_class = new ContactType(array());
	$contact_types = $types_class->getContactTypesAsArray(true);
	// company metas infos
	$meta_array = array();
	foreach($metas['results'] as $metaObject) {
		$meta_array[] = $metaObject->getId();
	}
	// contact metas infos
	$c_meta_array = array();
	foreach($c_metas['results'] as $metaObject) {
		$c_meta_array[] = $metaObject->getId();
	}
	$dao_metas_rel = new DaoCompanyMetaRelationships();
	$company_infos = $dao_metas_rel->getMetasforCompanies($company->getId(), $meta_array);

	// include templates files
	include('templates/header_default.php');
	include('templates/companies_form.php');
	include('templates/footer_default.php');

}

//------------------------------------------------------------------------------
// COMPANY RECAP (INFOS, CONTACTS, ALERTS,  MEETINGS)
//------------------------------------------------------------------------------
elseif($tpl == "recap") {

	// access rights
	Access::userCanAccess('company_recap');
	Access::userCanAccessObject('company', $company_id);

	if( ! $dao->companyExists($company_id)) {
		$message = "L'entreprise n'existe pas.";
		// include templates files
		include('templates/header_default.php');
		include('templates/error.php');
		include('templates/footer_default.php');
		exit;
	}

	// fetch company metas
	$dao_cie_metas = new DaoCompanyMetas();
	$cie_metas = $dao_cie_metas->getCompanyMetas(array('f_active' => 1), 0);

	// fetch company infos
	$meta_array = array();
	foreach($cie_metas['results'] as $metaObject) {
		$meta_array[] = $metaObject->getId();
	}
	$dao_cie_metas_rel = new DaoCompanyMetaRelationships();

	$dao_accounts = new DaoAccounts();
	$account = $dao_accounts->getAccount($session->getSessionData('account'));
	$company = $dao->getCompany($company_id);
	// company metas infos
	$company_infos = $dao_cie_metas_rel->getMetasforCompanies($company->getId(), $meta_array);
	// contacts
	$criteria_ctc = array(
		'f_company_id' => $company->getId(),
		'sort'         => 'name',
		'order'        => 'asc'
	);
	$dao_ctc = new DaoContacts();
	$contacts_list = $dao_ctc->getList($criteria_ctc, 0);
	$contact_ids = array();
	foreach ($contacts_list['results'] as $contact) {
		$contact_ids[] = $contact->getId();
	}
	// meta to display on the list
	// 1 => function ; 2 => main phone ; 3 => other phone ; 4 => email
	$meta_array = array(1, 2, 3, 4);
	$dao_metas_rel = new DaoContactMetaRelationships();
	$contact_infos = $dao_metas_rel->getMetasforContacts($contact_ids, $meta_array);

	// if there is no contact for company, put the list to -1 so it's not empty
	if(empty($contact_ids)) {
		$contact_ids = -1;
	}
	// meetings infos
	$criteria_mtg = array(
		'f_cid' => $contact_ids,
		'sort'  => 'date',
		'order' => 'desc'
	);
	$dao_meetings = new DaoMeetings();
	$meetings_list = $dao_meetings->getList($criteria_mtg, 0);
	$meetings_contacts = $dao_ctc->getContactsById($contact_ids);

	$dao_alerts = new DaoAlerts();
	$criteria_alt = array(
		'f_cid'  => $contact_ids,
		'f_done' => 0,
		'sort'   => 'date',
		'order'  => 'asc'
	);
	$alerts_list = $dao_alerts->getList($criteria_alt, 0);

	// attachments
	$dao_attachments = new DaoAttachments();
	// attachments for meetings
	$meeting_ids = array();
	foreach($meetings_list['results'] as $meeting) {
		$meeting_ids[] = $meeting->getId();
	}
	$meeting_attachments = $dao_attachments->getAttachmentsByMeetings($meeting_ids);
	$company_attachments = $dao_attachments->getAttachmentsByCompanies(array($company->getId()));

	//---------------------------------
	// additional scripts
	$scripts = '<script type="text/javascript" src="js/jquery.fancybox-1.3.4.pack.js"></script>' . "\n"
		. '<script type="text/javascript" src="js/jquery.fancybox.init.js"></script>' . "\n"
		. '<link type="text/css" rel="stylesheet" href="css/fancybox.css">';

	// include templates files
	include('templates/header_default.php');
	include('templates/companies_recap.php');
	include('templates/footer_default.php');


}

//------------------------------------------------------------------------------
// ASK FOR DELETE CONFIRM
//------------------------------------------------------------------------------
elseif($tpl == "del") {

	// access rights
	Access::userCanAccess('company_del');
	Access::userCanAccessObject('company', $company_id);

	if($dao->companyExists($company_id)) {
		$company = $dao->getCompany($company_id);
		// instead of create all variables, we use an array, it will be extracted in the template
		$data = array(
			'empty'              => false,
			'url_form'           => '?tpl=confirmed',
			'input_hidden_name'  => 'company_id',
			'input_hidden_value' => $company_id,
			'item_to_delete'     => 'cette entreprise',
			'value_to_delete'    => $company->getName(),
			'infos'              => 'Cette suppression est définitive. Les contacts attachés à l\'entreprise seront également supprimés.'
		);
	} else {
		$company = new Company(array());
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
	Access::userCanAccess('company_del');
	Access::userCanAccessObject('company', $company_id);

	if( ! Utils::isValidToken($_POST[TOKEN_PREFIX . 'token'])) {
		$message = "Jeton de sécurité du formulaire invalide. Merci d'utiliser
			le bouton 'Précédent' de votre navigateur web pour retourner à la page
			précédente et soumettre à nouveau votre validation.";
		include('templates/header_simple.php');
		include('templates/error.php');
		include('templates/footer_simple.php');
	} else {
		// del company
		$company = $dao->getCompany($company_id);
		$dao->delCompany($company);
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

//-------------------------------------
// SEARCH
//-------------------------------------
elseif($tpl == 'search') {

	$search = Utils::sanitize($_GET['term']);
	$companies = $dao->getList(array('f_name' => $search), 0);
	$array = array();
	foreach($companies['results'] as $company) {
		$array[] = array('id' => $company->getId(),
						'value' => Utils::displayEscapedString($company->getName()));
	}
	echo json_encode($array);
}
?>