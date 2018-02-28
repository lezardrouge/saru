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
 * Saru migration file
 * v1.1 => v1.2.2
 *
 * @since	1.0
 * @author	Marie Kuntz / Lézard Rouge <mariek@lezard-rouge.fr>
 */
include '../inc/config.php';
include '../classes/pdo.class.php';
$pdo = myPDO::getInstance();

$sql_448 = "ALTER TABLE `companies` ADD `company_date_add` DATE NOT NULL COMMENT 'date the company was added',
ADD `company_date_update` DATE NOT NULL COMMENT 'date the company was updated last';
UPDATE `companies` SET `company_date_add`=NOW(),`company_date_update`=NOW();
ALTER TABLE `contacts` ADD `contact_date_add` DATE NOT NULL COMMENT 'date the contact was added',
ADD `contact_date_update` DATE NOT NULL COMMENT 'date the contact was updated last';
UPDATE `contacts` SET `contact_date_add`=NOW(),`contact_date_update`=NOW();";
// execute query
$pdo->query($sql_448);

$sql_374 = "INSERT INTO `components` (`component_id`, `component_name`, `component_description`, `component_order`)
VALUES (39, 'export_template', 'Exportation du template', '150'),
		(40, 'export_search_contact', 'Exportation d''une recherche', '155');
INSERT INTO `user_component_relationships` (
`rel_user_id` ,
`rel_component_id`
)
VALUES (
'1', '39'
), (
'1', '40'
);";
// execute query
$pdo->query($sql_374);


$sql_432 = "UPDATE `users` set user_pwd = 'saru';";
// execute query
$pdo->query($sql_432);

// now call routines.php?tpl=salt
$chandle = curl_init(URL_PATH . "routines.php?tpl=salt&key=" . hash('ripemd160', HASH_KEY));
curl_exec($chandle);
curl_close($chandle);


// now rename the file so it cannot be called again
rename('migration_1-1_1-2.php', 'migration_1-1_1-2_' . time() . '.php');

$version = urldecode('1.2');
// and redirect to ok message
header('location: migration_complete.php?v=' . $version);
exit;