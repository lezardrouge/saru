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
 * contacts form
 *
 * @since	1.0
 * @author	Marie Kuntz / Lézard Rouge <mariek@lezard-rouge.fr>
 */
$infos = array();
if(isset($contact_infos[$contact->getId()])):
	$infos = $contact_infos[$contact->getId()];
endif;
?>
<div class="span6">
	<h1>Editer un contact</h1>

<?php if($type_tpl == "default"): ?>
	<div class="row-fluid">
		<div class="span4">
			<a href="contacts.php" class="no_underline"><img src="images/back.png" alt="&lt;&lt;" class="icon">Retour à la liste</a>
		</div>
	<?php if(Access::userCanAccess('contact_recap', false) && $contact->getId() != 0): ?>
		<div class="offset5 span3">
			<a href="?tpl=recap&contact_id=<?php echo $contact->getId(); ?>" class="no_underline"><img src="images/recap.png" alt="Fiche" class="icon">Voir la fiche</a>
		</div>
	<?php endif; ?>
	</div>
<?php else: ?>
		<p><a href="javascript:CloseBox();" class="no_underline"><img src="images/cancel.png" alt="[X]" class="icon">Fermer la fenêtre</a></p>
<?php endif; ?>

	<?php echo Utils::displayMessage("Aucun champ n'est obligatoire. Faites attention à ne pas ajouter de contact vide.", TYPE_MSG_INFO); ?>
	<?php echo $message; ?>
	<form method="post" action="contacts.php" enctype="multipart/form-data">
	<input type="hidden" name="contact_id" value="<?php echo $contact->getId(); ?>">
	<input type="hidden" name="tpl" value="<?php echo $tpl; ?>">
	<input type="hidden" name="p" value="<?php echo $p; ?>">
	<table class="tform">
<?php $company_id = $contact->getCompany()->getId();
if(empty($company_id)) : ?>
		<tr>
			<td>Type de contact</td>
			<td><select name="c_type">
				<?php foreach ($contact_types as $type_id => $type_name): ?>
					<option value="<?php echo $type_id; ?>"<?php if($contact->getType_id() == $type_id): ?> selected<?php endif; ?>><?php echo $type_name; ?></option>
				<?php endforeach; ?>
				</select></td>
		</tr>
<?php else: ?>
		<input type="hidden" name="c_type" value="<?php echo $contact->getType_id(); ?>">
<?php endif; ?>
		<tr>
			<td>Nom</td>
			<td><input type="text" name="c_lastname" value="<?php echo $contact->getLastname(); ?>" size="30"></td>
		</tr>
		<tr>
			<td>Prénom</td>
			<td><input type="text" name="c_firstname" value="<?php echo $contact->getFirstname(); ?>" size="30"></td>
		</tr>
		<tr>
			<td>Entreprise</td>
			<td>
				<input type="text" name="c_company_name" id="c_company_name" value="<?php echo $contact->getCompany()->getName(); ?>">
				<input type="hidden" name="c_company_id" id="c_company_id" value="<?php echo $contact->getCompany()->getId(); ?>">
				<img src="images/information.png" id="info" class="icon" alt="information"
					 data-original-title="Tapez 2 caractères pour voir la liste des propositions. Si l'entreprise n'existe pas encore, elle sera créée automatiquement.">
			</td>
		</tr>
<?php foreach ($metas['results'] as $meta): ?>

		<tr>
			<td><?php echo $meta->getName(); ?></td>
			<td><input type="text" name="m_<?php echo $meta->getId(); ?>" value="<?php if(isset($infos[$meta->getId()])): echo $infos[$meta->getId()]; endif; ?>" size="30"></td>
		</tr>

<?php endforeach; ?>
		<tr>
			<td>Notes diverses</td>
			<td><textarea name="c_comment" cols="40" rows="5" style="width:300px; height:100px;"><?php echo Utils::br2n($contact->getComments()); ?></textarea></td>
		</tr>
<?php if($contact->getId() != 0) : ?>
		<tr>
			<td>Actif (non masqué)</td>
			<td><input type="checkbox" name="c_active" value="1"<?php echo ($contact->getActive() == 1 ? 'checked':''); ?>></td>
		</tr>
<?php endif; ?>
		<tr>
			<td>Joindre un fichier</td>
			<td><input id="attachment" type="file" name="attachment">
			<span class="help-block">Les types de fichier acceptés sont les documents
				de bureautique (xls, doc, ppt, odt, ods, odp), les fichiers texte, les pdf,
				les images et les fichiers compressés (zip, tgz, ...)</span></td>
		</tr>
	</table>
	<input type="submit" name="submitform" value="Ok" class="btn btn-info">

<?php if($type_tpl == "default"): ?>
	<input type="button" class="btn" name="annul" value="Annuler" onclick="window.location='contacts.php?p=<?php echo $p; ?>'">
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