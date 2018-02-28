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

class Display
{

	function __construct() {}


	/**
	 * return the page as it must display
	 *
	 * @param int $template, the content template to fetch
	 * @param int $main_template, the template to load
	 *
	 * @return str $buffer, the html to display
	 */
	function displayPage($template, $main_template = 'default') {

		$buffer = '';
		// get header
		$header = $this->getHeader($main_template);
		// get footer
		$footer = $this->getFooter($main_template);
		// get main content
		$main_content = $this->getMainContent($template);


	}


	/**
	 * fetch the page header
	 *
	 * @param str $main_template
	 *
	 * @return str $buffer, the header
	 */
	private function getHeader($main_template) {

		$buffer = '';
		if($main_template == 'simple') {
			$file = 'templates/header_simple.php';
		} else {
			$file = 'templates/header_default.php';
		}
		$buffer = file_get_contents($file);
		if($buffer === FALSE) {
			// do something
		}

		return $buffer;

	}


	/**
	 * fetch the page footer
	 *
	 * @param str $main_template
	 *
	 * @return str $buffer, the footer
	 */
	private function getFooter($main_template) {

		$buffer = '';
		if($main_template == 'simple') {
			$file = 'templates/footer_simple.php';
		} else {
			$file = 'templates/footer_default.php';
		}
		$buffer = file_get_contents($file);
		if($buffer === FALSE) {
			// do something
		}

		return $buffer;

	}


	/**
	 * fetch the main content page
	 *
	 * @param str $template, the page to load
	 *
	 * @return str $buffer, the html content
	 */
	private function getMainContent($template) {

		$buffer = '';
		$file = 'templates/' . $template . '.php';

		$buffer = file_get_contents($file);
		if($buffer === FALSE) {
			// do something
		}

		return $buffer;

	}


}
?>