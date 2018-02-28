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
 * TEMPLATE
 * del confirmation
 *
 * @since	1.0
 * @author	Marie Kuntz / Lézard Rouge <mariek@lezard-rouge.fr>
 */
extract($data);
if($empty): ?>
<script type="text/javascript">
	jQuery(document).ready(function($) {
		parent.location.reload();
		parent.$.fancybox.close();
	});
</script>
<?php else: ?>
<script type="text/javascript">
   function CloseBox() {
      if(typeof(parent.$.fancybox) == 'function') {
      	parent.$.fancybox.close();
      }
   }
</script>
<form action="<?php echo $url_form; ?>" method="post">
	<input type="hidden" name="<?php echo $input_hidden_name; ?>" value="<?php echo $input_hidden_value; ?>">
<?php
// complementary fields, like an hidden field
if(isset($supplement)):
	echo $supplement;
endif; ?>
<div id="tconfirm">
	<p>Êtes-vous sûr de vouloir supprimer <?php echo $item_to_delete; ?> ? <br />
	<?php echo $value_to_delete; ?></p>
<?php
// optional additionnal infos
if(isset($infos) && !empty($infos)): ?>
	<div class="alert"><?php echo $infos; ?></div>
<?php endif; ?>
	<input type="submit" name="submitform" value="Oui" class="btn btn-info"> <input type="button" class="btn" name="cancel" value="Non" onclick="javascript:CloseBox();">
	<input type="hidden" name="<?php echo TOKEN_PREFIX; ?>token" value="<?php echo $session->getSessionData('token'); ?>">
</div>
</form>
<?php endif; ?>