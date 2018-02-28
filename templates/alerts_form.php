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
 * alert add/edit form
 *
 * @since	1.0
 * @author	Marie Kuntz / Lézard Rouge <mariek@lezard-rouge.fr>
 */
?>
<div class="span5">
	<h1>Editer une alerte</h1>

	<?php if($type_tpl == "default"): ?>
	<div class="row-fluid">
		<div class="span4"><a href="alerts.php" class="no_underline"><img src="images/back.png" alt="&lt;&lt;" class="icon">Retour à la liste</a></div>
		<?php if($alert->getId() != 0): ?>
		<div class="offset4 span4"><a href="contacts.php?tpl=recap&contact_id=<?php echo $alert->getContact_id(); ?>" class="no_underline"><img src="images/recap.png" alt=">>" class="icon">Voir la fiche contact</a></div>
		<?php endif; ?>
	</div>
	<?php else: ?>
		<p><a href="javascript:CloseBox();" class="no_underline"><img src="images/cancel.png" alt="[X]" class="icon">Fermer la fenêtre</a></p>
	<?php endif; ?>

	<?php echo Utils::displayMessage("Les champs notés * sont obligatoires.", TYPE_MSG_INFO); ?>
	<?php echo $message; ?>
	<form method="post" action="alerts.php">
	<input type="hidden" name="alert_id" value="<?php echo $alert->getId(); ?>">
	<input type="hidden" name="tpl" value="<?php echo $tpl; ?>">
	<input type="hidden" name="p" value="<?php echo $p; ?>">
	<table class="tform">

<?php if(Access::userCanAccess('alert_assign', false) && count($users) > 1): ?>
		<tr>
			<td>Assigner à *</td>
			<td><select name="user_id" id="user_id">
	<?php foreach ($users as $user_id => $user_name): ?>
				<option value="<?php echo $user_id; ?>"<?php
				$alert_user_id = $alert->getUser_id();
				if((! empty($alert_user_id) && $user_id == $alert_user_id)
					|| (empty($alert_user_id) && $user_id == $current_user_id)):
					echo ' selected';
				endif;
				?>><?php echo $user_name; ?></option>
	<?php endforeach; ?>
				</select>
			</td>
		</tr>
<?php endif; ?>

		<tr>
			<td>Contact *</td>
			<td>
				<input type="text" name="contact_name" id="contact_name" value="<?php echo $alert->getContact()->getFullname(); ?>">
				<input type="hidden" name="contact_id" id="contact_id" value="<?php echo $alert->getContact_id(); ?>">
				<img src="images/information.png" id="info" class="icon info" alt="information"
					 data-original-title="Tapez 2 caractères pour voir la liste des contacts et des entreprises. Vous devez choisir une personne existante.">
			</td>
		</tr>

		<tr>
			<td>Date</td>
			<td><input type="text" name="a_date" id="m_date" value="<?php echo $alert->getDate_format(); ?>" class="date">
				<img src="images/information.png" id="infodate" class="icon info" alt="information"
					 data-original-title="Si vous n'indiquez pas de date, l'alerte sera une simple tâche."></td>
		</tr>

		<tr>
			<td>Prioritaire</td>
			<td><input type="checkbox" name="a_priority" value="1"<?php echo ($alert->getPriority() == 1 ? 'checked':''); ?>></td>
		</tr>

		<tr>
			<td>Objet</td>
			<td><textarea name="a_comment" cols="30" rows="5" style="width:300px; height:150px;"><?php echo Utils::br2n($alert->getComments()); ?></textarea></td>
		</tr>
<?php if($alert->getId() != 0) : ?>
		<tr>
			<td>Fait (ne pas me relancer)</td>
			<td><input type="checkbox" name="a_done" value="1"<?php echo ($alert->getDone() == 1 ? 'checked':''); ?>></td>
		</tr>
<?php endif; ?>
	</table>
	<input type="submit" name="submitform" value="Ok" class="btn btn-info">
<?php if($type_tpl == "default"): ?>
	<input type="button" class="btn" name="annul" value="Annuler" onclick="window.location='alerts.php?p=<?php echo $p; ?>'">
<?php else: ?>
	<input type="button" name="annul" class="btn" value="Annuler" onclick="javascript:CloseBox();">
<?php endif; ?>
	<input type="hidden" name="<?php echo TOKEN_PREFIX; ?>token" value="<?php echo $session->getSessionData('token'); ?>">
	</form>
</div>

<script type="text/javascript">
    $('.info').tooltip();
	function CloseBox() {
      if(typeof(parent.$.fancybox) == 'function') {
		parent.location.reload();
		parent.$.fancybox.close();
      }
	}
</script>