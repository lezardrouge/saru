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
 * TEMPLATE
 * help page
 *
 * @since	1.0
 * @author	Marie Kuntz / Lézard Rouge <mariek@lezard-rouge.fr>
 */

if( ! defined('LOCAL_PATH')) { exit; }

?>
<h1><img src="images/information.png" class="icon" alt="">Aide pour l'import/export</h1>

<h2>Import</h2>
<p>Si vous souhaitez importer des contacts, téléchargez le modèle à partir de l'interface.</p>
<p class="help-image"><img src="images/help/import_modele.png" alt="télécharger le modèle"></p>

<p>À l'ouverture du modèle téléchargé (le fichier peut être ouvert avec un tableur comme Excel),
	le tableur peut vous demander de confirmer le format&nbsp;; si c'est le cas, choisissez le
	séparateur point-virgule. Si le jeu de caractère est demandé, choisissez
	"Europe occidentale (ISO8859-15/EURO)".</p>
<p class="help-image"><img src="images/help/ouverture_csv.png" alt="ouverture du csv"></p>

<p>Une fois le fichier renseigné, lors de l'enregistrement, le tableur peut vous demander
	de confirmer le format (CSV). Choisissez de confirmer ("Oui").</p>
<p>Attention : n'utilisez pas plusieurs feuilles de calcul, seule la première sera traitée.</p>
<p class="help-image"><img src="images/help/import_enregistrement_excel.png" alt="enregistrement du csv"></p>

<h2>Export</h2>
<p>L'export des contacts et des recherches génère un fichier avec une extension CSV.
	Ce type de fichier peut être ouvert avec un tableur comme Excel. À l'ouverture,
	le tableur peut vous demander de confirmer le format&nbsp;; si c'est le cas, choisissez le
	séparateur point-virgule. Si le jeu de caractère est demandé, choisissez
	"Europe occidentale (ISO8859-15/EURO)".</p>
<p class="help-image"><img src="images/help/ouverture_csv.png" alt="ouverture du csv"></p>