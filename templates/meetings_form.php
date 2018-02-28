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
 * meeting add/edit form
 *
 * @since	1.0
 * @author	Marie Kuntz / Lézard Rouge <mariek@lezard-rouge.fr>
 */
?>
<div class="span6">
<h1><?php echo Utils::mb_ucfirst($lang->edit_meeting); ?></h1>

<?php if($type_tpl == "default"): ?>
<div class="row-fluid">
	<div class="span4"><a href="meetings.php" class="no_underline"><img src="images/back.png" alt="&lt;&lt;" class="icon">Retour à la liste</a></div>
	<?php if($meeting->getId() != 0): ?>
	<div class="offset4 span4"><a href="contacts.php?tpl=recap&contact_id=<?php echo $meeting->getContact_id(); ?>" class="no_underline"><img src="images/recap.png" alt=">>" class="icon">Voir la fiche contact</a></div>
	<?php endif; ?>
</div>
<?php else: ?>
	<p><a href="javascript:CloseBox();" class="no_underline"><img src="images/cancel.png" alt="[X]" class="icon">Fermer la fenêtre</a></p>
<?php endif; ?>

<?php echo Utils::displayMessage("Les champs notés * sont obligatoires.", TYPE_MSG_INFO); ?>
<?php echo $message; ?>
<form method="post" enctype="multipart/form-data" action="meetings.php">
<input type="hidden" name="meeting_id" value="<?php echo $meeting->getId(); ?>">
<input type="hidden" name="tpl" value="<?php echo $tpl; ?>">
<input type="hidden" name="p" value="<?php echo $p; ?>">
<table class="tform">
	<tr>
		<td>Type de rendez-vous *</td>
		<td><select name="m_type">
			<?php foreach ($meeting_types as $type_id => $type_name): ?>
				<option value="<?php echo $type_id; ?>"<?php if($meeting->getType_id() == $type_id): ?> selected<?php endif; ?>><?php echo $type_name; ?></option>
			<?php endforeach; ?>
			</select></td>
	</tr>

	<tr>
		<td>Contact *</td>
		<td>
			<input type="text" name="contact_name" id="contact_name" value="<?php echo $meeting->getContact()->getFullname(); ?>">
			<input type="hidden" name="contact_id" id="contact_id" value="<?php echo $meeting->getContact_id(); ?>">
			<img src="images/information.png" id="info" class="icon" alt="information"
					 data-original-title="Tapez 2 caractères pour voir la liste des contacts et des entreprises. Vous devez choisir une personne existante.">
		</td>
	</tr>

	<tr>
		<td>Date *</td>
		<td><input type="text" name="m_date" id="m_date" value="<?php echo $meeting->getDate_format(); ?>" class="date"></td>
	</tr>

	<tr>
		<td>Compte-rendu</td>
		<td><textarea name="m_comment" cols="40" rows="5" class="span11" style="height:200px;"><?php echo Utils::br2n($meeting->getComments()); ?></textarea></td>
	</tr>

	<tr>
		<td>Joindre un fichier</td>
		<td><input id="attachment" type="file" name="attachment">
			<span class="help-block">Les types de fichier acceptés sont les documents
				de bureautique (xls, doc, ppt, odt, ods, odp), les fichiers texte, les pdf,
				les images et les fichiers compressés (zip, tgz, ...)</span>
		</td>
	</tr>
</table>
<input type="submit" name="submitform" value="Ok" class="btn btn-info">
<?php if($type_tpl == "default"): ?>
	<input type="button" name="annul" class="btn" value="Annuler" onclick="window.location='meetings.php?p=<?php echo $p; ?>';">
<?php else: ?>
	<input type="button" name="annul" class="btn" value="Annuler" onclick="javascript:CloseBox();">
<?php endif; ?>
<input type="hidden" name="<?php echo TOKEN_PREFIX; ?>token" value="<?php echo $session->getSessionData('token'); ?>">
</form>
</div>

<script type="text/javascript">
    $('#info').tooltip();
	function CloseBox() {
      if(typeof(parent.$.fancybox) == 'function') {
		parent.location.reload();
		parent.$.fancybox.close();
      }
	}
</script>