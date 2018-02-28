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
 * search
 *
 * @since	1.4
 * @author Marie Kuntz / Lézard Rouge
 */

require_once "inc/includes.php";
// check if user is connected
$session->isConnected();

$term = htmlspecialchars($_GET['term'], ENT_NOQUOTES, 'UTF-8');

$results = array();

$criteria['f_name'] = $term;
$criteria['f_active'] = 1;

// first search users
if(Access::userCanAccess('contact_list', false)) {
	$daoUsers = new DaoContacts();
	$contacts_list = $daoUsers->getList($criteria, 0, 0);
	foreach ($contacts_list['results'] as $contact) {
		$results[] = array(
			'value' => Utils::displayEscapedString($contact->getFullname()),
			'src' => 'contacts.php?tpl=recap&contact_id=' . $contact->getId()
		);
	}
}

// now search companies
if(Access::userCanAccess('company_list', false)) {
	$daoCompanies = new DaoCompanies();
	$companies_list = $daoCompanies->getList($criteria, 0, 0);
	foreach ($companies_list['results'] as $company) {
		$results[] = array(
			'value' => Utils::displayEscapedString($company->getName()),
			'src' => 'companies.php?tpl=recap&company_id=' . $company->getId()
		);
	}
}

echo json_encode($results);
