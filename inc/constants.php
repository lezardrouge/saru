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
 * constants
 * definition of constants
 *
 * @since	1.0
 * @author	Marie Kuntz / Lézard Rouge <mariek@lezard-rouge.fr>
 */


/*
|--------------------------------------------------------------------------
| generic constants
|--------------------------------------------------------------------------
| IS_CLI	=>	DO NOT TOUCH THIS ONE ! you'd break it all (at least the routines)
*/

define('IS_CLI', (PHP_SAPI === 'cli' OR PHP_SAPI === 'cgi'));


/*
|--------------------------------------------------------------------------
| messages types
|--------------------------------------------------------------------------
|
| TYPE_MSG_ERROR	=>	type for error messages
| TYPE_MSG_WARNING	=>	type for warning messages
| TYPE_MSG_SUCCESS	=>	type for success messages
| TYPE_MSG_INFO		=>	type for info messages
|
*/
define('TYPE_MSG_ERROR',   'error');
define('TYPE_MSG_WARNING', 'warning');
define('TYPE_MSG_SUCCESS', 'success');
define('TYPE_MSG_PERM_SUCCESS', 'perm_success');
define('TYPE_MSG_INFO',    'info');


/*
|--------------------------------------------------------------------------
| Saru official version
| do not touch, even if you know what you are doing
|--------------------------------------------------------------------------
*/
define('VERSION', '1.4.2');

?>
