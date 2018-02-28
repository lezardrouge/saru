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
 * image manipulation functions
 *
 * @since	1.0
 * @author	Marie Kuntz / Lézard Rouge <mariek@lezard-rouge.fr>
 */

class Image
{

	/* private */



	public function __construct()
	{
		// init GD
	}


	/**
	 * upload a logo
	 *
	 * @param array $file, the $_FILES array
	 * @param str $new_name, the new name for the uploaded file
	 *
	 * @return bool $success
	 */
	public function uploadLogo($file, $new_name = '')
	{
		$path = LOCAL_PATH . "logos/";
		$mimes = array("image/jpeg","image/gif","image/png");
		$file_class = new Files();
		$result = $file_class->upload($file, $path, $mimes, $new_name);
		return $result;
	}



}

/* End of file Image.class.php */
/* Location: ./classes/Image.class.php */