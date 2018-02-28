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
 * v1.2.2 => 1.3
 *
 * @since	1.0
 * @author	Marie Kuntz / Lézard Rouge <mariek@lezard-rouge.fr>
 */
include '../inc/config.php';
include '../classes/pdo.class.php';
$pdo = myPDO::getInstance();

$sql_374 = "INSERT INTO `components` (
`component_id` ,
`component_name` ,
`component_description` ,
`component_order`
)
VALUES (
NULL , 'export_search_company', 'Export d''une recherche d''entreprises', '160'
);
INSERT INTO `user_component_relationships` (`rel_user_id`, `rel_component_id`) VALUES ('1', '41');

UPDATE `components`
SET `component_description` = 'Exportation d''une recherche de contacts'
WHERE `components`.`component_id` =40;
";
// execute query
$pdo->query($sql_374);

$sql_477 = "INSERT INTO `components` (
`component_id` ,
`component_name` ,
`component_description` ,
`component_order`
)
VALUES (
NULL , 'alert_assign', 'Attribuer une alerte à un autre utilisateur', '27'
);

INSERT INTO `user_component_relationships` (`rel_user_id`, `rel_component_id`) VALUES ('1', '42');";
// execute query
$pdo->query($sql_477);


$sql_373 = "ALTER TABLE `companies` ADD `company_active` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '1' COMMENT 'tells if a company is active (visible) or inactive (invisible by default)',
ADD INDEX ( `company_active` );
ALTER TABLE `contacts` ADD `contact_active` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '1' COMMENT 'tells if a contact is active (visible) or inactive (invisible by default)',
ADD INDEX ( `contact_active` );";
// execute query
$pdo->query($sql_373);

// now rename the file so it cannot be called again
rename('migration_1-2_1-3.php', 'migration_1-2_1-3_' . time() . '.php');

$version = urldecode('1.3');
// and redirect to ok message
header('location: migration_complete.php?v=' . $version);
exit;