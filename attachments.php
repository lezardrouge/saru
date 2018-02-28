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
 * actions for attachments
 *
 * @since	1.4
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

$dao = new DaoAttachments();

//-------------------------------
// get vars
//-------------------------------
// set page to display
$default = "";
$tpl = "";
$tpl_array = array("del", "download");
if (isset($_GET['tpl']) && ! empty($_GET['tpl'])) {
	$tpl = Utils::sanitize($_GET['tpl']);
} elseif (isset($_POST['tpl']) && ! empty($_POST['tpl'])) {
	$tpl = Utils::sanitize($_POST['tpl']);
}
if( ! in_array($tpl, $tpl_array)) {
	$tpl = $default;
}

//-------------------------------
$attachment_id = 0;
if(isset($_GET['attachment_id']) && ! empty($_GET['attachment_id'])) {
	$attachment_id = intval($_GET['attachment_id']);
} elseif (isset($_POST['attachment_id']) && ! empty($_POST['attachment_id'])) {
	$attachment_id = intval($_POST['attachment_id']);
}


//-------------------------------
$message = '';
if(isset($_SESSION['sess_message'])) {
	$message = Utils::displayMessage($_SESSION['sess_message'], $_SESSION['sess_message_type']);
	unset($_SESSION['sess_message'], $_SESSION['sess_message_type']);
}

//------------------------------------------------------------------------------
// DELETE
//------------------------------------------------------------------------------
if($tpl == "del") {

	// access rights
	Access::userCanAccess('contact_recap');
	Access::userCanAccess('company_recap');

	if( ! isset($_GET[TOKEN_PREFIX . 'token']) || ! Utils::isValidToken($_GET[TOKEN_PREFIX . 'token'])) {
		$message = "Jeton de sécurité du formulaire invalide. Merci d'utiliser
			le bouton 'Précédent' de votre navigateur web pour retourner à la page
			précédente et soumettre à nouveau votre validation.";
		include('templates/header_default.php');
		include('templates/error.php');
		include('templates/footer_default.php');
	} else {
		if($dao->attachmentExists($attachment_id)) {
			$attachment = $dao->getAttachment($attachment_id);
			Access::userCanAccessObject('account', $attachment->getAccount_id());
			$dao->delAttachment($attachment);
			// redirection
			$referrer = $_SERVER['HTTP_REFERER'];
			if(empty($referrer)) {
				$referrer = 'index.php';
			}
			header('location: ' . $referrer);
			exit;
		}
	}

}

//-------------------------------------
// ATTACHMENT EXPORT
//-------------------------------------
elseif($tpl == "download") {

	// access rights
	Access::userCanAccess('contact_recap');
	Access::userCanAccess('company_recap');

	$dao_attachment = new DaoAttachments();
	$attachment = $dao_attachment->getAttachment($attachment_id);

	// access to this attachment (check by account)
	Access::userCanAccessObject('account', $attachment->getAccount_id());

	if( ! $attachment) {
		include('templates/header_default.php');
		$message = "Le téléchargement a échoué.";
		include('templates/error.php');
		include('templates/footer_default.php');
		exit;
	} else {
		$file_class = new Files();
		$pathfile = LOCAL_PATH . $file_class->attachment_dir . '/a' . $attachment->getAccount_id() . '/' . $attachment->getIntern_name();
		$mime = $file_class->getMimeType($pathfile);
		if(empty($mime)) {
			$mime = 'application/octet-stream';
		}

		header('Content-Type: ' . $mime);
		header('Content-Disposition: attachment; filename="' . $attachment->getReal_name() . '"');
		header('Expires: 0');
		header('Pragma: no-cache');
		header('Content-Length: ' . filesize($pathfile));
		readfile($pathfile);
	}

}

?>