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
 * contact meta add/edit form
 *
 * @since	1.0
 * @author	Marie Kuntz / Lézard Rouge <mariek@lezard-rouge.fr>
 */
?>
<div class="span5">
	<h1>Editer une metadonnée de contact</h1>
	<p><a href="contact_metas.php" class="no_underline"><img src="images/back.png" alt="&lt;&lt;" class="icon">Retour à la liste</a></p>
	<?php echo Utils::displayMessage("Les champs notés * sont obligatoires.", TYPE_MSG_INFO); ?>
	<?php echo $message; ?>
	<form method="post" action="contact_metas.php">
	<input type="hidden" name="meta_id" value="<?php echo $meta->getId(); ?>">
	<input type="hidden" name="tpl" value="form">
	<input type="hidden" name="p" value="<?php echo $p; ?>">
	<table class="tform">

		<tr>
			<td>Intitulé *</td>
			<td>
				<input type="text" name="meta_name" id="meta_name" value="<?php echo $meta->getName(); ?>">
			</td>
		</tr>

		<tr>
			<td>Ordre d'affichage</td>
			<td><input type="text" name="meta_order" class="input-mini" id="meta_order" value="<?php echo $meta->getOrder(); ?>"></td>
		</tr>
<?php if($meta->getId() != 0) : ?>
		<tr>
			<td>Activé</td>
			<td><input type="checkbox" name="meta_active" value="1"<?php echo ($meta->getActive() == 1 ? 'checked':''); ?>></td>
		</tr>
<?php else: ?>
		<input type="hidden" name="meta_active" value="1">
<?php endif; ?>
	</table>
	<input type="submit" name="submitform" value="Ok" class="btn btn-info"> <input type="button" name="annul" class="btn" value="Annuler" onclick="window.location='contact_metas.php?p=<?php echo $p; ?>'">
	<input type="hidden" name="<?php echo TOKEN_PREFIX; ?>token" value="<?php echo $session->getSessionData('token'); ?>">
	</form>
</div>