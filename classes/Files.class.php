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
 * files manipulation functions
 *
 * @since	1.0
 * @author	Marie Kuntz / Lézard Rouge <mariek@lezard-rouge.fr>
 */

class Files
{

	public $csv_sep = ';'; // csv separator
	public $csv_eol = "\n"; // csv end of line
	public $default_dir = 'files/'; // default directory for imported/exported files
	public $attachment_dir = 'attachments/'; // directory for attachments
	/* encoding supported and checked for csv import */
	public $supported_encoding = array(
		 "utf-8", "iso-8859-1", "iso-8859-15", "windows-1251", "windows-1252", "ASCII",
	);
	/* name of csv template for import */
	public $csv_template = 'modele_import_donnees.csv';
	/* accepted mimes for CSV */
	public $csv_mimes = array("text/csv", "text/comma-separated-values", "application/vnd.ms-excel");
	/* accepted mimes for attachments */
	public $attachment_mimes = array(
			'text/plain',
			"text/csv", "text/comma-separated-values", "application/vnd.ms-excel", 'text/x-comma-separated-values',
			'application/octet-stream',
			'application/x-csv', 'text/x-csv', 'application/csv',
			'application/excel', 'application/vnd.msexcel', 'application/vnd.ms-excel',
			'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
			'application/vnd.oasis.opendocument.spreadsheet',
			'application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip',
			'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','application/msword',
			'application/excel', 'application/vnd.ms-excel', 'application/msexcel',
			'application/vnd.oasis.opendocument.spreadsheet', 'application/vnd.oasis.opendocument.text', 'application/vnd.oasis.opendocument.presentation',
			'application/powerpoint', 'application/vnd.ms-powerpoint',
			'application/vnd.openxmlformats-officedocument.presentationml.presentation',
			'text/richtext','text/rtf',
			'application/pdf', 'application/x-download','message/rfc822',
			'application/x-zip', 'application/zip', 'application/x-zip-compressed',
			'application/x-gtar', 'application/x-gzip', 'application/x-tar','application/x-tar', 'application/x-gzip-compressed',
			'image/bmp', 'image/x-windows-bmp',
			"image/gif", "image/jpg", "image/jpeg", "image/png", 'image/x-png',
			'application/vnd.mindjet.mindmanager', 'application/xmind', 'application/x-freemind', 'application/freemind', 'application/x-freeplane'
		);


	public function __construct()
	{
	}


	/**
	 * open a file
	 *
	 * @param string $file, the file including path
	 * @param string $mode, the mode to use to open
	 *
	 * @return mixed boolean|resource
	 */
	public function openFile($file, $mode = 'w+b')
	{
		$handle = fopen($file, $mode);
		if($handle === false) {
			return false;
		}
		return $handle;
	}


	/**
	 * write in a file
	 *
	 * @param string $file, the filename with the path
	 * @param string $buffer, the text to write
	 *
	 * @return bool
	 */
	public function writeFile($file, $buffer, $mode = 'a+b')
	{
		$handle = $this->openFile($file, $mode);
		if($handle === false) {
			return false;
		}
		$res = fwrite($handle, $buffer);
		fclose($handle);
		if($res === false) {
			return false;
		}
		return true;
	}


	/**
	 * generate the csv template file for importing contacts
	 *
	 * @return bool $res
	 */
	public function generateCsvTemplate()
	{
		$buffer = $this->generateCsvHeader();
		// write file
		$res = $this->writeFile($this->default_dir . $this->csv_template, $buffer, 'wb');
		return $res;
	}


	/**
	 * generate the first line for a CSV export
	 *
	 * @param bool $include_type, if the type must be included
	 *
	 * @return string $buffer
	 */
	public function generateCsvHeader($include_type = false)
	{
		// fetch metas for companies
		$dao_company_metas = new DaoCompanyMetas();
		$company_metas = $dao_company_metas->getCompanyMetas(array('f_active' => 1), null, 0);
		// fetch metas for contacts
		$dao_contacts_metas = new DaoContactMetas();
		$contact_metas = $dao_contacts_metas->getContactMetas(array('f_active' => 1), null, 0);
		// create buffer
		$buffer = '';
		if($include_type) {
			$buffer .= '"Type"' . $this->csv_sep;
		}
		$buffer .= '"Entreprise"' . $this->csv_sep;
		foreach ($company_metas['results'] as $meta) {
			$buffer .= '"' . Utils::convertEncoding(Utils::displayEscapedString($meta->getName()), 'ISO-8859-15', 'UTF-8') . '"' . $this->csv_sep;
		}
		$buffer .= '"Notes entreprise"' . $this->csv_sep
				. '"Nom"' . $this->csv_sep . '"' . Utils::convertEncoding('Prénom', 'ISO-8859-15', 'UTF-8') . '"' . $this->csv_sep;
		foreach ($contact_metas['results'] as $meta) {
			$buffer .= '"' . Utils::convertEncoding(Utils::displayEscapedString($meta->getName()), 'ISO-8859-15', 'UTF-8') . '"' . $this->csv_sep;
		}
		$buffer .= '"Notes contact"'
				. $this->csv_eol;
		return $buffer;
	}


	/**
	 * general function to upload a file
	 *
	 * @param array $file, $_FILES array
	 * @param string $path, the destination path
	 * @param array $mimes, the mime types permitted
	 * @param str $new_name, the new name for the uploaded file
	 *
	 * @return array $result (success, filename, message, old_filename)
	 */
	public function upload($file, $path, $mimes, $new_name = '')
	{
		$result = array('filename' => '', 'success' => false, 'message' => '', 'old_filename' => $file["name"]);
		// check the file is ok & the mime type is correct & the path is set up
		if( ! empty($file["tmp_name"])
			&& $file["error"] == 0
			&& in_array($file["type"], $mimes)
			&&  ! empty($path))
		{
			if(! empty($new_name)) {
				// retrieve extension
				$ext = Utils::getExtension($file["name"]);
				$filename = Utils::escapeFilename($new_name . $ext);
			} else {
				$filename = Utils::escapeFilename($file["name"]);
			}
			if(move_uploaded_file($file["tmp_name"], $path . $filename)) {
				$result['success'] = true;
				$result['filename'] = $filename;
			} else {
				$result['message'] = 'Impossible de déplacer dans le dossier ' . $path;
			}
		} else {
			Utils::log('file error: ' . $file["error"] . "\n"
					. 'mime: ' . $file["type"] . "\n"
					. 'file tmp name: ' . $file["tmp_name"] . "\n"
					. 'path: ' . $path);
			if($file["error"] != 0) {
				$result['message'] = $file["error"];
			}
			if( ! in_array($file["type"], $mimes)) {
				$result['message'] .= 'Le type de fichier que vous voulez importer n\'est pas accepté.';
			}
			if(empty($path)) {
				$result['message'] .= 'Le chemin indiqué est invalide.';
			}
		}
		return $result;
	}


	/**
	 * upload a csv file
	 *
	 * @param array $uploaded_file, $_FILES array
	 *
	 * @return bool
	 */
	public function uploadCsv($uploaded_file)
	{
		$path = LOCAL_PATH . $this->default_dir;
		$random = Utils::generateRandomString();
		$new_name = 'import_contacts_' . date('YmdHis') . "_" . $random;
		$result = $this->upload($uploaded_file, $path, $this->csv_mimes, $new_name);
		return $result;
	}


	/**
	 * upload an attachment file
	 *
	 * @param int $account_id, the account to attach the file
	 * @param array $uploaded_file, $_FILES array
	 *
	 * @return boolean
	 */
	public function uploadAttachment($account_id, $uploaded_file)
	{
		$path = LOCAL_PATH . $this->attachment_dir . '/a' . $account_id . '/';
		$this->checkPath($path);
		$new_name = 'attachment_' . time();
		$result = $this->upload($uploaded_file, $path, $this->attachment_mimes, $new_name);
		return $result;
	}


	/**
	 * check if a file exists
	 *
	 * @param string $filepath, the complete file path & name
	 *
	 * @return boolean
	 */
	public function checkFileExists($filepath)
	{
		if(file_exists($filepath)) {
			return true;
		}
		return false;
	}


	/**
	 * delete a file
	 *
	 * @param str $filename
	 */
	public function deleteFile($filename)
	{
		$res = false;
		if(file_exists($filename)) {
			$res = unlink($filename);
		}
		return $res;
	}


	/**
	 * get mime type of a file
	 *
	 * @param string $file, path & name to file
	 *
	 * @return string mime
	 */
	public function getMimeType($file)
	{
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		return finfo_file($finfo, $file);
	}


	/**
	 * change permissions on a file
	 *
	 * @param string $file	the file path & name ; must be accessible via the server's filesystem (no remote file)
	 * @param string $perms	permissions ; possible values (constants) : READ_WRITE|READ|WRITE|NONE
	 */
	public function chmodFile($file, $perms)
	{
		if(file_exists($file)) {
			switch ($perms) {
				case READ_WRITE:
					chmod($file, 0660);
					break;
				case READ:
					chmod($file, 0440);
					break;
				case WRITE:
					chmod($file, 0330);
					break;
				case NONE:
				default:
					chmod($file, 0000);
					break;
			}
		}
	}


	/**
	 * zip files
	 *
	 * @param string $zipname	the zip file name
	 * @param array $files		array of files to add to zip
	 *
	 * @return boolean
	 */
	public function zipFile($zipname, $files)
	{
		$zip = new ZipArchive();
		if ($zip->open( LOCAL_PATH . 'files/' . $zipname, ZipArchive::CREATE) === true) {
			$i = 0;
			foreach($files as $filename)
			{
				if(file_exists(LOCAL_PATH . 'files/' . $filename)) {
					$zip->addFile(LOCAL_PATH . 'files/' . $filename, $filename);
					$i++;
				} else {
					Utils::log("Creating zip : file not found : " . $filename);
				}
			}
			$zip->close();
			Utils::log($i . ' files added');
			return true;
		} else {
			Utils::log("Error : impossible to create " . $zipname);
			return false;
		}
	}


	/**
	 * create a directory if does not exist
	 *
	 * @param str $path
	 */
	private function checkPath($path)
	{
		if( ! file_exists($path)) {
			mkdir($path, 0770, true);
		}
	}


}

/* End of file Files.class.php */
/* Location: ./classes/Files.class.php */
