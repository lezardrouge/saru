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
 * Saru configuration
 *
 * @since	1.0
 * @author	Marie Kuntz / Lézard Rouge <mariek@lezard-rouge.fr>
 */


/*
|--------------------------------------------------------------------------
| generic constants
|--------------------------------------------------------------------------
|
| LOCAL_PATH		=>	change this to your own local installation ; do not forget the trailing slash
| URL_PATH			=>	change this to your installation URL ; do not forget the trailing slash
| NB_RECORDS		=>	number of results to display on a page
| HASH_KEY			=>	key for routines scripts
|						IMPORTANT : change the default key before you use the software
| DEBUG_MODE		=>	if set to 1, the security is lower, so pay attention
|						(for example, the HASH_KEY is not required to run routines)
| COOKIE_NAME		=>	cookie name. Change it for whatever you like.
|						Use characters in the range: a-z A-Z 0-9 (no space, no underscore allowed)
| COOKIE_LOGIN		=>	cookie name for remembering login. Change it for whatever you like.
|						Use characters in the range: a-z A-Z 0-9 (no space, no underscore allowed)
| COOKIE_EXPIRE		=>	cookie lifetime. Change it for whatever you like, in seconds.
|						By default, 3 days (60*60*24*3 = 260000)
| SESSION_EXPIRE	=>	session lifetime. Change it for whatever you like, in seconds.
|						By default, 10 hours (60*60*10 = 36000)
| TOKEN_PREFIX		=>  allows you to prefix the session token, to enhance security
|						By default, empty
| LANG				=>	indicate what language to use. Change it for your language
|						if the file exists (in lang directory, format : yourlanguage.lang.class.php)
|						By default, french
| NEXT_DAYS			=>	indicate how much days must be taken in account when
|						displaying alerts for "next days" box on homepage
|
*/
define('LOCAL_PATH', '/local/path/to/saru/');
define('URL_PATH', 'http://url-of-your-saru.org/');
define('NB_RECORDS', 25);
define('HASH_KEY', 'The quick brown fox jumped over the lazy dog.');
define('DEBUG_MODE', 1);
define('COOKIE_NAME', 'sarusess');
define('COOKIE_LOGIN', 'sarulogin');
define('COOKIE_EXPIRE', 2600000);
define('SESSION_EXPIRE', 360000);
define('TOKEN_PREFIX', 'saru_');
define('LANG', 'french');
define('NEXT_DAYS', '10');


/*
|--------------------------------------------------------------------------
| DB parameters
|--------------------------------------------------------------------------
|
| change these parameters to access to your DB
|
| DB_HOST	=>	the host of the DB, typically : localhost ;
| DB_PORT	=>	the port to access your DB ; leave blank if you don't know ;
| DB_NAME	=>	the name of your db ; if you did not change it for installation, it is 'saru' ;
| DB_USER	=>	the user for your DB ; locally, often blank or 'mysql' or 'root' ;
| DB_PWD	=>	the password for your DB ; locally, often blank or 'mysql' or 'root'.
|
*/
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'saru');
define('DB_USER', 'root');
define('DB_PWD', 'root');

/*
|--------------------------------------------------------------------------
| emails contants
|--------------------------------------------------------------------------
|
| EMAIL_FROM_NAME		=>	when an email is sent (alerts), this is the sender's name
|							you put here your own name, your company name
| EMAIL_FROM_EMAIL		=>	when an email is sent (alerts), this is the sender's email
|							you put here your own email, your company email
| EMAIL_TRANSPORT_TYPE	=>	the type of transport to send emails
|							allowed types : mail|smtp ; default is mail
|							if you chose smtp for EMAIL_TRANSPORT_TYPE, then
|							fill the EMAIL_SMTP_* constants below
| EMAIL_SMTP_SERVER		=>	the name of your smtp server
|							i.e. smtp.example.org
| EMAIL_SMTP_PORT		=>	port of your smtp server
|							default : 25
| EMAIL_SMTP_USER		=>	login/user for your smtp server
| EMAIL_SMTP_PWD		=>	password for your smtp server
|
*/
define('EMAIL_FROM_NAME',  'My instance of Saru');
define('EMAIL_FROM_EMAIL', 'my.email@mydomaine.com');
define('EMAIL_TRANSPORT_TYPE', 'mail');
define('EMAIL_SMTP_SERVER', '');
define('EMAIL_SMTP_PORT', 25);
define('EMAIL_SMTP_USER', '');
define('EMAIL_SMTP_PWD', '');


/*
|--------------------------------------------------------------------------
| modules enabled
|--------------------------------------------------------------------------
|
| MOD_ACCESS				=>	module for limited access
|								set to 1 enables login page, checking accesses
|								set to 0 to have unlimited & unlogin access
| MOD_ACCOUNT				=>	module for account use
|								set to 1 to enable
| MOD_CRM					=>	module for advanced CRM utilisation
|								set to 1 to enable
|
*/
define('MOD_ACCESS', 1);
define('MOD_ACCOUNT', 0);
define('MOD_CRM', 0);

?>
