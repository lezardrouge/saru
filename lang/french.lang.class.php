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
 * along with SARU. If not, see <http://www.gnu.org/licenses/>.
 * See LICENSE.TXT file for more information.
 *
 * Saru is released under dual license, AGPL and commercial license.
 * If you need a commercial license or if you don't know which licence you need,
 * please contact us at <info@saru.fr>
 *
 */

/**
 * this is where you put french translation for labels, text, etc // TODO
 *
 * @since	1.0
 * @author	Marie Kuntz / Lézard Rouge <mariek@lezard-rouge.fr>
 */

class French
{
	/* meetings */
	public $meeting = "historique";
	public $meetings = "Historique";
	public $this_meeting = "cet historique";
	public $no_meeting = "aucun historique";
	public $new_meeting = "nouvel historique";
	public $edit_meeting = "éditer l'historique";
	public $export_meeting = "exporter l'historique";
	/* accounts */
	public $accounts = "comptes";
	public $account = "compte";
	public $account_saved = "compte enregistré";
	public $account_dont_exist = "le compte n'existe pas";
	public $this_account = "ce compte";
	public $account_switch_ok = "changement de compte effectué";
	public $must_select_account = "vous devez choisir un compte";
	public $account_mod_not_activated = "le module Comptes n'est pas activé";
	public $before_export_contacts = "avant d'exporter les contacts";
	public $edit_account = "éditer un compte";
	public $no_account = "aucun compte";
	public $work_with_account = "travailler avec ce compte";
	public $account_history = "historique pour le compte";
	public $account_access = "accès aux comptes";
	public $check_account = "cochez la case correspondante pour donner un droit d'accès au compte";
	public $account_inactive = "compte inactif";
	public $select_account = "sélectionnez un compte";
	/* contacts */
	public $error_no_valid_contact = "Vous n'avez pas sélectionné de contact valide. Si le contact n'existe pas encore, veuillez le créer avant d'effectuer votre opération.";
	/* companies */
	/* alerts */
	/* misc messages */
	public $error_empty_field = "Vous n'avez rien indiqué dans le champ %s.";


	public function __construct() {}

}

?>