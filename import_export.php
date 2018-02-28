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
 * imports and exports
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
$tpl_array = array("export_contacts", "import_contacts", "export_history", "get_csv_template", "export_search_contacts", "export_search_companies");
if (isset($_GET['tpl']) && ! empty($_GET['tpl'])) {
	$tpl = Utils::sanitize($_GET['tpl']);
} elseif(isset($_POST['tpl']) && ! empty($_POST['tpl'])) {
	$tpl = Utils::sanitize($_POST['tpl']);
}
if( ! in_array($tpl, $tpl_array)) {
	exit('Une erreur est survenue.');
}

//-------------------------------
$id = 0;
if(isset($_GET['id']) && ! empty($_GET['id'])) {
	$id = intval($_GET['id']);
}
//-------------------------------
$message = ''; // message to display

$current_account_id = $session->getSessionData('account');
if(empty($current_account_id)) {
	$message = Utils::mb_ucfirst($lang->must_select_account) . '.';
	// include templates files
	include('templates/header_default.php');
	include('templates/error.php');
	include('templates/footer_default.php');
	exit;
}

//-------------------------------------
// CONTACTS EXPORT
//-------------------------------------
if($tpl == "export_contacts") {

	// access rights
	Access::userCanAccess('contact_export');

	$contact = new Contact();
	$res = $contact->exportContacts();
	if($res === false) {
		include('templates/header_default.php');
		$message = "L'export a échoué.";
		include('templates/error.php');
		include('templates/footer_default.php');
		exit;
	} else {
		header('Content-Description: File Transfer');
		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename=' . basename($res));
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Content-Length: ' . filesize($res));
		readfile($res);
		$file_class = new Files;
		$file_class->deleteFile($res);
	}

}
//-------------------------------------
// EXPORT CONTACTS HISTORY
//-------------------------------------
elseif($tpl == "export_history") {

	// access rights
	Access::userCanAccess('meeting_export');

	$contact = new Contact();
	$res = $contact->exportHistory();
	if($res === false) {
		include('templates/header_default.php');
		$message = "L'export a échoué.";
		include('templates/error.php');
		include('templates/footer_default.php');
		exit;
	} else {
		header('Content-Description: File Transfer');
		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename=' . basename($res));
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Content-Length: ' . filesize($res));
		readfile($res);
		$file_class = new Files;
		$file_class->deleteFile($res);
	}


}
//-------------------------------------
// CONTACTS IMPORT
//-------------------------------------
elseif($tpl == "import_contacts") {

	// access rights
	Access::userCanAccess('contact_import');

	$contact = new Contact();
	$files = new Files();

	if(isset($_POST['tpl'])) {

		if( ! empty($_FILES['imported_file']['name'])) {

			$res = $contact->importContacts($current_account_id, $_FILES['imported_file']);
			if($res['result'] === false) {
				$message = Utils::displayMessage($res['message'], TYPE_MSG_ERROR);
			} else {
				$message = Utils::displayMessage($res['message'], TYPE_MSG_PERM_SUCCESS);
			}
		} else {
			$message = Utils::displayMessage("Vous avez oublié de choisir un fichier.", TYPE_MSG_ERROR);
		}
	}

	$page_src = 'import_export.php?tpl=import_contacts';
	$page_title = 'Importer un fichier de contacts';
	$page_legend = "Vous devez importer un fichier CSV (vous pouvez trouver un
		modèle <a href='import_export.php?tpl=get_csv_template'>ici</a>). Respectez
		l'ordre des champs, n'en enlevez aucun, laissez une colonne vide à la place.
		Laissez la première ligne d'entête.<br>En cas de problème avec les accents,
		reportez-vous à la <a href='http://www.saru.fr/doku/importer-fichier-contacts/' target='_blank'>documentation sur le site</a>.";
	$page_legend = Utils::displayMessage($page_legend, TYPE_MSG_INFO);
	$link_cancel = "";

	// display
	include('templates/header_default.php');
	include('templates/tpl_import.php');
	include('templates/footer_default.php');

}
//-------------------------------------
// EXPORT MODELE CSV
//-------------------------------------
elseif($tpl == "get_csv_template") {

	// access rights
	Access::userCanAccess('export_template');

	$files = new Files();
	$res = $files->generateCsvTemplate();
	if($res === false) {
		include('templates/header_default.php');
		$message = "La génération a échoué.";
		include('templates/error.php');
		include('templates/footer_default.php');
		exit;
	} else {
		$filename = $files->csv_template;
		$filepath = $files->default_dir . $files->csv_template;
		header('Content-Description: File Transfer');
		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename=' . $filename);
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Content-Length: ' . filesize($filepath));
		readfile($filepath);
	}

}

//-------------------------------------
// CONTACTS SEARCH EXPORT
//-------------------------------------
elseif($tpl == "export_search_contacts") {

	// access rights
	Access::userCanAccess('export_search_contact');

	$contact = new Contact();
	$res = $contact->exportSearchContacts();
	if($res === false) {
		include('templates/header_default.php');
		$message = "L'export a échoué.";
		include('templates/error.php');
		include('templates/footer_default.php');
		exit;
	} else {
		header('Content-Description: File Transfer');
		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename=' . basename($res));
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Content-Length: ' . filesize($res));
		readfile($res);
		$file_class = new Files;
		$file_class->deleteFile($res);
	}

}

//-------------------------------------
// COMPANY SEARCH EXPORT
//-------------------------------------
elseif($tpl == "export_search_companies") {

	// access rights
	Access::userCanAccess('export_search_company');

	$companies = new Company();
	$res = $companies->exportSearchCompanies();
	if($res === false) {
		include('templates/header_default.php');
		$message = "L'export a échoué.";
		include('templates/error.php');
		include('templates/footer_default.php');
		exit;
	} else {
		header('Content-Description: File Transfer');
		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename=' . basename($res));
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Content-Length: ' . filesize($res));
		readfile($res);
		$file_class = new Files;
		$file_class->deleteFile($res);
	}

}

?>