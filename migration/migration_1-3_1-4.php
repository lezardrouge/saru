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
 * v1.3.3 => 1.4
 *
 * @since	1.0
 * @author	Marie Kuntz / Lézard Rouge <mariek@lezard-rouge.fr>
 */
include '../inc/config.php';
include '../classes/pdo.class.php';
$pdo = myPDO::getInstance();

$sql_481 = "ALTER TABLE `alerts` ADD `alert_priority` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `alert_contact_id` ,
ADD INDEX ( `alert_priority` );
";
// execute query
$pdo->query($sql_481);

$sql_494 = "ALTER TABLE `alerts` CHANGE `alert_date` `alert_date` DATE NULL DEFAULT NULL;
ALTER TABLE `alerts` CHANGE `alert_contact_id` `alert_contact_id` INT( 10 ) UNSIGNED NULL DEFAULT NULL COMMENT 'FK contacts.contact_id';";
// execute query
$pdo->query($sql_494);


$sql_373 = "CREATE TABLE IF NOT EXISTS `attachments` (
  `attachment_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `attachment_account_id` int(10) unsigned NOT NULL COMMENT 'FK accounts.PK',
  `attachment_type_item` enum('ct','ci','me') DEFAULT NULL,
  `attachment_item_id` int(10) unsigned NOT NULL COMMENT 'FK contacts.PK ; companies.PK ; meetings.PK',
  `attachment_real_name` varchar(255) NOT NULL COMMENT 'file name as user entered it',
  `attachment_intern_name` varchar(255) NOT NULL COMMENT 'file name used to store it',
  PRIMARY KEY (`attachment_id`),
  KEY `attachment_account_id` (`attachment_account_id`,`attachment_type_item`,`attachment_item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
// execute query
$pdo->query($sql_373);

// now rename the file so it cannot be called again
rename('migration_1-3_1-4.php', 'migration_1-3_1-4_' . time() . '.php');

$version = urldecode('1.4');
// and redirect to ok message
header('location: migration_complete.php?v=' . $version);
exit;