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
 * import a file
 *
 * @since	1.0
 * @author	Marie Kuntz / Lézard Rouge <mariek@lezard-rouge.fr>
 */

?>
<h1><img src="images/file_upload.png" alt="Upload" class="icon"><?php echo $page_title; ?></h1>

<?php echo $message; ?>

<?php echo $page_legend; ?>

<form method="post" action="<?php echo $page_src; ?>" enctype="multipart/form-data">
<input type="hidden" name="tpl" value="<?php echo $tpl; ?>">

<p><label class="inline" for="imported_file"></label>
	<input type="file" name="imported_file" id="imported_file" size="30">
</p>

<p><input type="submit" name="submitform" value="Ok" class="btn btn-info">
<?php if(! empty($link_cancel)): ?>
	<input type="button" class="btn" name="annul" value="Annuler" onclick="<?php echo $link_cancel; ?>">
<?php endif; ?>
</p>
</form>