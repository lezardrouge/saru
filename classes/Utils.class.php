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
 * misc, generalist functions
 *
 * @since	1.0
 * @author	Marie Kuntz / Lézard Rouge <mariek@lezard-rouge.fr>
 */

class Utils
{

	/**
	 * escape a filename
	 *
	 * @param str $str, input string
	 *
	 * @return str $str, safe string
	 */
	public static function escapeFilename($str)
	{
		$str = trim($str);
		$str = str_replace(" ", "_", $str);
		$str = strtr(
			$str,
			"ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ",
			"aaaaaaaaaaaaooooooooooooeeeeeeeecciiiiiiiiuuuuuuuuynn");
		$str = preg_replace("#[^a-zA-Z-0-9._]#", "", $str);
		return $str;
	}

	/**
	 * protects posted data
	 *
	 * @param str $str, input string
	 *
	 * @return str $str, safe string
	 */
	public static function protege($str)
	{
		$str = trim($str);
		$str = str_replace("#", "@", $str);
		$str = html_entity_decode($str, ENT_QUOTES);
		$str = self::n2br($str);
		return $str;
	}


	/**
	 * sanitize and encode an imported data
	 *
	 * @param string $string, the string to protect
	 * @param string $to_encoding, the new encoding
	 * @param string $from_encoding, the original encoding
	 *
	 * @return string $sanitized
	 */
	public static function protectImportedData($string, $to_encoding, $from_encoding)
	{
		$converted = Utils::convertEncoding($string, $to_encoding, $from_encoding);
		$sanitized = Utils::sanitize($converted);
		return $sanitized;
	}


	/**
	 * sanitize user inputs
	 *
	 * @param string $input
	 *
	 * @return string $input
	 */
	public static function sanitize($input, $to_encoding = 'UTF-8')
	{
		$input = trim($input);
		$input = htmlentities($input, ENT_QUOTES, $to_encoding, false);
		return $input;
	}


	/**
	 * sanitize user inputs
	 *
	 * @param array $array
	 *
	 * @return array $sanitized_array
	 */
	public static function sanitizeArray($array)
	{
		$sanitized_array = array();
		if(is_array($array)) {
			foreach($array as $key => $value) {
				if(is_array($value)){
					$sanitized_array[Utils::sanitize($key)] = Utils::sanitizeArray($value);
				} else {
					$sanitized_array[Utils::sanitize($key)] = Utils::sanitize($value);
				}
			}
		} else {
			$sanitized_array[] = Utils::sanitize($array);
		}
		return $sanitized_array;
	}


	/**
	 * displays a string previously sanitized
	 *
	 * @param string $string
	 */
	public static function displayEscapedString($string)
	{
		return html_entity_decode($string, ENT_QUOTES, 'UTF-8');
	}

	/**
	 * replace \n by <br>
	 *
	 * @param str $str
	 *
	 * @return str $str
	 */
	public static function n2br($str)
	{
		$str = str_replace("\n", "<br>", $str);
		return $str;
	}


	/**
	 * replace <br> by \n
	 *
	 * @param str $str
	 *
	 * @return str $str
	 */
	public static function br2n($str)
	{
		$str = str_replace("<br>", "\n", $str);
		$str = str_replace("<br />", "\n", $str);
		return $str;
	}


	/**
	 * check if a date is empty
	 *
	 * @param string $date
	 *
	 * @return bool
	 */
	public static function dateEmpty($date)
	{
		if(empty($date) || $date == '0000-00-00' || $date == '00/00/0000') {
			return true;
		}
		return false;
	}

	/**
	 * transforms a ISO formatted date to FR formatted date
	 *
	 * @param string $date
	 *
	 * @return string $date_fr
	 */
	public static function date2Fr($date)
	{
		if( ! empty($date)) {
			$array_date = self::splitDatetime($date, 'iso');
			if($array_date !== FALSE) {
				$date = $array_date['d'] . "/" . $array_date['m'] . "/" . $array_date['Y'];
			}
		}
		return $date;
	}


	/**
	 * split a date into an array
	 *
	 * @param string $datetime, the date with or without a time
	 * @param string $format, the input format : iso|fr
	 *
	 * @return array $arrayDatetime(Y,m,d,h)
	 */
	public static function splitDatetime($datetime, $format)
	{
		$arrayDatetime = array();
		if( ! empty($datetime)) {
			if($format == 'iso') {
				$arrayDatetime['Y'] = substr($datetime, 0, 4);
				$arrayDatetime['m'] = substr($datetime, 5, 2);
				$arrayDatetime['d'] = substr($datetime, 8, 2);
				if(strlen($datetime) > 11) {
					$arrayDatetime['h'] = substr($datetime, 11, 8);
				}
			} elseif($format == 'fr') {
				$arrayDatetime['Y'] = substr($datetime, 6, 4);
				$arrayDatetime['m'] = substr($datetime, 3, 2);
				$arrayDatetime['d'] = substr($datetime, 0, 2);
				if(strlen($datetime) > 11) {
					$arrayDatetime['h'] = substr($datetime, 11, 8);
				}
			} else {
				return false;
			}
		} else {
			return false;
		}
		return $arrayDatetime;
	}


	/**
	 * transforms a FR formatted date to ISO formatted date
	 *
	 * @param string $date
	 *
	 * @return string $date
	 */
	public static function date2ISO($date)
	{
		$array_date = self::splitDatetime($date, 'fr');
		if($array_date !== FALSE) {
			$date = $array_date['Y'] . "-" . $array_date['m'] . "-" . $array_date['d'];
		}
		return $date;
	}


	/**
	 * check if a date is a valid date
	 *
	 * @param string $date
	 * @param string $format : iso|fr
	 *
	 * @return bool
	 */
	public static function checkDateValidity($date, $format)
	{
		$array = self::splitDatetime($date, $format);
		return checkdate($array['m'], $array['d'], $array['Y']);
	}


	/**
	 * check validity of an email
	 *
	 * @param str $email
	 *
	 * @return bool
	 */
	public static function checkEmailValidity($email)
	{
		if (eregi("^[a-z0-9_\+-]+(\.[a-z0-9_\+-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*\.([a-z]{2,6})$", $email)){
			return true;
		}
		return false;
	}


	/**
	 * dump an item
	 *
	 * @param mixed $dump
	 *
	 * @return echo
	 */
	public static function dump($dump)
	{
		// the style is set here because sometimes the css is
		// not always loaded when the function is called
		echo '<pre style="background-color: #DDD; margin: 5px; padding: 5px 7px; ">';
		var_dump($dump);
		echo '</pre>';
	}



	/**
	 * autoload class
	 *
	 * @param str $classname
	 *
	 * @return void
	 */
	public static function loadClass($classname)
	{
		// exclude Swiftmailer library
		if(substr($classname, 0, 5) !== 'Swift') {
			require_once LOCAL_PATH . 'classes/' . $classname.'.class.php';
		}
	}


	/**
	 * display pagination on lists
	 *
	 * @param int $num_rows, total number of resultats for query (w/out limit)
	 * @param int $current, current page number
	 * @param str $more_params, parameters to pass in the url
	 *
	 * @return echo
	 */
	public static function displayPagination($num_rows, $current, $more_params = '')
	{
		$buffer = '';
		$total_num_pages = ceil($num_rows / NB_RECORDS);
		// if the is a parameter to pass in the url, put it here
		$add_more = '';
		if( ! empty($more_params)) {
			$add_more = $more_params;
		}
		$buffer = '<div class="pull-right paginate">
			<form method="post" name="formPagination" action="?tpl=list' . $add_more . '">
			<span class="nb_results">' . $num_rows . ' résultats</span>';

		// display pagination only if there is more than one page of result
		if($total_num_pages > 1) {

			$min_page = 1; // first page to display for browsing
			$max_page = $total_num_pages; // last page to display for browsing

			$previous_page = $current - 1;
			if($previous_page < $min_page) {
				$previous_page = $min_page;
			}
			$next_page = $current + 1;
			if($next_page > $max_page) {
				$next_page = $max_page;
			}


			if($current == $min_page) {
				$buffer .= '<img src="images/pagination_start_off.png" alt="|&lt;">
					<img src="images/pagination_prev_off.png" alt="&lt;&lt;">';
			} else {
				$buffer .= '<a href="?tpl=list&p=1' . $add_more . '"><img src="images/pagination_start.png" alt="|&lt;"></a>
					<a href="?tpl=list&p=' . $previous_page . $add_more . '"><img src="images/pagination_prev.png" alt="&lt;&lt;"></a>';
			}
			$buffer .= ' <input type="text" name="p" value="' . $current . '" class="input_page_number" autocomplete="off" >
				sur ' . $max_page . ' ';
			if($current == $max_page) {
				$buffer .= '<img src="images/pagination_next_off.png" alt="&gt;&gt;">
					<img src="images/pagination_end_off.png" alt="&gt;|">';
			} else {
				$buffer .= '<a href="?tpl=list&p=' . $next_page . $add_more . '"><img src="images/pagination_next.png" alt="&gt;&gt;"></a>
					<a href="?tpl=list&p=' . $max_page . $add_more . '"><img src="images/pagination_end.png" alt="&gt;|"></a>';
			}
		}
		$buffer .= '</form></div>';
		echo $buffer;
	}


	/**
	 * displays a message block
	 *
	 * @param string $message
	 * @param string $type : error|warning|info|success
	 *
	 * @return string $buffer
	 */
	public static function displayMessage($message, $type)
	{
		$buffer = '<div class=" alert alert-' . $type . '">' . $message . '</div>';
		return $buffer;
	}


	/**
	 * shorten a text without cutting the words
	 *
	 * @param string $text, the text to shorten
	 * @param int $length, the number of words to return
	 * @param string $suffix, the suffix to put after the shortened string
	 *
	 * @return string $shortened_text
	 */
	public static function shorten($text, $length = 10, $suffix = '...')
	{
		$shortened_text = '';
		$tmp = explode(' ', $text, $length + 1);
		array_pop($tmp);
		$shortened_text = implode(' ', $tmp);
		return $shortened_text . $suffix;
	}


	/**
	 * protect (escape) a data (ie post or get)
	 *
	 * @param string $string
	 *
	 * @return string $escaped_string
	 */
	public static function escapeString($string)
	{
		$escaped_string = htmlentities($string, ENT_QUOTES, 'UTF-8');
		$escaped_string = trim($escaped_string);
		$escaped_string = str_replace("#", "", $escaped_string);
		return $escaped_string;
	}


	/**
	 * format a number to display as money or percentage
	 *
	 * @param float|int $number
	 *
	 * @return float $formatted_number
	 */
	public static function formatNumber($number)
	{
		$formatted_number = number_format($number, 2, ',', ' ');
		return $formatted_number;
	}


	/**
	 * format a number to display as money
	 *
	 * @param float|int $number
	 *
	 * @return float $formatted_number
	 */
	public static function displayMoney($number)
	{
		$formatted_number = Utils::formatNumber($number);
		$formatted_number .= "&euro;";
		return $formatted_number;
	}


	/**
	 * format a number to display as percentage
	 *
	 * @param float|int $number
	 *
	 * @return float $percentage
	 */
	public static function displayPercentage($number, $signed = false)
	{
		$percentage = Utils::formatNumber($number);
		if($signed) {
			if($percentage > 0) {
				$percentage = "+" . $percentage;
			}
		}
		$percentage .= "%";
		return $percentage;
	}


	/**
	 * get the extension from a filename, based on the final dot
	 *
	 * @param str $filename
	 *
	 * @return str $ext
	 */
	public static function getExtension($filename)
	{
		$pos = strrpos($filename, '.');
		if($pos === FALSE) {
			return '';
		} else {
			$ext = substr($filename, $pos, strlen($filename));
			return $ext;
		}
	}


	/**
	 * log stuff
	 *
	 * @param mixed $element
	 */
	public static function log($element)
	{
		$buffer = "\n---------------------------------------------------------\n"
			. date('Y-m-d H:i:s') . "\n";
		if(is_array($element) || is_object($element)) {
			foreach($element as $key => $value) {
				$buffer .= $key . " => " . $value . "\n";
			}
		} else {
			$buffer .= $element;
		}
		$fileClass = new Files();
		$log_file = LOCAL_PATH . 'files/log_' . date('Ym');
		$fileClass->chmodFile($log_file, READ_WRITE);
		$fileClass->writeFile($log_file, $buffer);
		$fileClass->chmodFile($log_file, NONE);
	}


	/**
	 * log connections
	 *
	 * @param mixed $element
	 */
	public static function connectionLogs($info)
	{
		$buffer = "\n" . date('Y-m-d H:i:s') . $info;
		$fileClass = new Files();
		$log_file = LOCAL_PATH . 'files/connection_logs_' . date('Ym');
		$fileClass->chmodFile($log_file, READ_WRITE);
		$fileClass->writeFile($log_file, $buffer);
		$fileClass->chmodFile($log_file, NONE);
	}


	/**
	 * ucfirst with mb_
	 * (omg i can't believe i have to do this, this is so fucked)
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public static function mb_ucfirst($string)
	{
		return mb_strtoupper(mb_substr($string, 0, 1, 'UTF-8'), 'UTF-8').mb_strtolower(mb_substr($string, 1), 'UTF-8');
	}



	/**
	 * backup the db OR just some tables
	 * TODO : change mysql stuff by pdo
	 * @credits David Walsh <http://davidwalsh.name/backup-mysql-database-php>
	 *
	 * @param string $tables	tables to backup
	 *
	 * @return
	 */
	public static function backup_tables($tables = '*')
	{
		$buffer = '';
		$link = mysqli_connect(DB_HOST, DB_USER, DB_PWD);
		mysqli_select_db($link, DB_NAME);

		//get all of the tables
		if($tables == '*')
		{
			$tables = array();
			$result = mysqli_query($link, 'SHOW TABLES');
			while($row = mysqli_fetch_row($result))
			{
				$tables[] = $row[0];
			}
		}
		else
		{
			$tables = is_array($tables) ? $tables : explode(',',$tables);
		}

		//cycle through
		foreach($tables as $table)
		{
			$result = mysqli_query($link, 'SELECT * FROM '.$table);
			$num_fields = mysqli_num_fields($result);

			$buffer.= 'DROP TABLE IF EXISTS '.$table.';';
			$row2 = mysqli_fetch_row(mysqli_query($link, 'SHOW CREATE TABLE '.$table));
			$buffer.= "\n\n".$row2[1].";\n\n";

			for ($i = 0; $i < $num_fields; $i++)
			{
				while($row = mysqli_fetch_row($result))
				{
					$buffer.= 'INSERT INTO '.$table.' VALUES(';
					for($j=0; $j<$num_fields; $j++)
					{
						$row[$j] = addslashes($row[$j]);
						$row[$j] = preg_replace("/\\n/","\\\\n",$row[$j]);
						if (isset($row[$j])) { $buffer.= '"'.$row[$j].'"' ; } else { $buffer.= '""'; }
						if ($j<($num_fields-1)) { $buffer.= ','; }
					}
					$buffer.= ");\n";
				}
			}
			$buffer.="\n\n\n";
		}

		//save file
		$files = new Files();
		$randomString = self::generateRandomString();
		$filename = 'db-backup_' . date('Ymd-Hi') . "_" . $randomString . '.sql';
		$files->writeFile(LOCAL_PATH . 'files/' . $filename, $buffer);
		$files->chmodFile(LOCAL_PATH . 'files/' . $filename);
	}


	/**
	 * create a password
	 *
	 * @param int $num, number of characters for password ; default 8
	 *
	 * @return str $password, the password
	 */
	public static function generatePwd($num = 8)
	{
		$password = '';
		// some chars are not used to prevent wrong interpretation (ie 0 and O, l and 1)
		// numbers are doubled, special chars are tripled
		$permitted = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'L', 'M',
					'N', 'P', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
					'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'j', 'k', 'm', 'n',
					'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
					'1', '2', '3', '4', '5', '6', '7', '8', '9',
					'1', '2', '3', '4', '5', '6', '7', '8', '9',
					'@', '?', '!', '-', '_', '$',
					'@', '?', '!', '-', '_', '$',
					'@', '?', '!', '-', '_', '$',
			);
		shuffle($permitted);
		$rand_keys = array_rand ($permitted, $num);
		foreach($rand_keys as $value) {
			$password .= $permitted[$value];
		}
		return $password;
	}


	/**
	 * convert a string encoding to another
	 *
	 * @param string $string, the string to convert
	 * @param string $to_encoding, the new encoding
	 * @param string $from_encoding, the original encoding
	 *
	 * @return string $string, the encoded string
	 */
	public static function convertEncoding($string, $to_encoding, $from_encoding)
	{
		if( ! empty($from_encoding) && ! empty($to_encoding)
			&& $from_encoding != $to_encoding) {
			$string = mb_convert_encoding($string, $to_encoding, $from_encoding);
		}
		return $string;
	}


	public static function updatePwd()
	{
		$dao_users = new DaoUsers();
		$users = $dao_users->getList(array(), 0);
		foreach ($users['results'] as $user) {
			$dao_users->updateUserPwd($user);
		}
	}

	/**
	 * hash a password
	 *
	 * @param string $salt, the used salt
	 * @param string $pwd, the password to hash
	 *
	 * @return string $hashed_pwd
	 */
	public static function hashPwd($salt, $pwd)
	{
		$hashed_salt = hash("sha512", $salt);
		$hashed_pwd = hash("sha512", $hashed_salt . $pwd);
		return $hashed_pwd;
	}


	/**
	 * generate a login token in session to prevent CSRF attacks
	 *
	 * @return void
	 */
	public static function generateLoginToken()
	{
		$token = md5(uniqid(rand(), true));
		$_SESSION['login_token'] = $token;
		$_SESSION['login_token_time'] = time();
	}


	/**
	 * generate a token in session to prevent CSRF attacks
	 *
	 * @param string $prefix, a prefix for variable name
	 *
	 * @return void
	 */
	public static function generateCsrfToken()
	{
		$token = md5(uniqid(rand(), true));
		return $token;
	}


	/**
	 * generate a 40 chars length random string
	 *
	 * @return string
	 */
	public static function generateRandomString()
	{
		$random = sha1(uniqid() . microtime());
		return $random;
	}


	/**
	 * check if a token is valid
	 *
	 * @param string $token
	 *
	 * @return boolean
	 */
	public static function isValidToken($token)
	{
		$session = new Session();
		$session_token = $session->getSessionData('token');
		if (! isset($token)
			|| empty($session_token)
			|| $token != $session_token) {
			return false;
		} else {
			return true;
		}
	}


	/**
	 * check if a login token is valid and not too old
	 *
	 * @param string $token
	 *
	 * @return boolean
	 */
	public static function isValidLoginToken($token)
	{
		if ( ! isset($_SESSION['login_token'])
			|| ! isset($_SESSION['login_token_time'])
			|| ! isset($token)
			|| $token != $_SESSION['login_token']
			|| (time() - $_SESSION['login_token_time']) > 300) {
			return false;
		} else {
			return true;
		}
	}


	/**
	 * fetch remote user IP
	 *
	 * @return string $ip
	 */
	public static function getIp()
	{
		return $_SERVER['REMOTE_ADDR'];
	}


	/**
	 * fetch user agent
	 *
	 * @return string
	 */
	public static function getUserAgent()
	{
		return $_SERVER['HTTP_USER_AGENT'];
	}


}
?>