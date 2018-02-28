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
 * company form
 *
 * @since	1.0
 * @author	Marie Kuntz / Lézard Rouge <mariek@lezard-rouge.fr>
 */
$infos = array();
if(isset($company_infos[$company->getId()])):
	$infos = $company_infos[$company->getId()];
endif;
?>
<form method="post" action="companies.php" id="theform" enctype="multipart/form-data">

<div class="span6">
	<h1>Editer une entreprise</h1>

	<div class="row-fluid">
		<div class="span4">
			<a href="companies.php" class="no_underline"><img src="images/back.png" alt="&lt;&lt;" class="icon">Retour à la liste</a>
		</div>
<?php if(Access::userCanAccess('company_recap', false) && $company->getId() != 0): ?>
		<div class="span4 pull-right">
			<a href="?tpl=recap&company_id=<?php echo $company->getId(); ?>" class="no_underline"><img src="images/recap.png" alt="Fiche" class="icon">Voir la fiche</a>
		</div>
<?php endif; ?>
	</div>
	<p></p>


	<?php echo Utils::displayMessage("Les champs notés * sont obligatoires.", TYPE_MSG_INFO); ?>
	<?php echo $message; ?>
	<input type="hidden" name="company_id" value="<?php echo $company->getId(); ?>">
	<input type="hidden" name="tpl" value="form">

	<table class="tform">
		<tr>
			<td>Type d'entreprise</td>
			<td><select name="c_type">
				<?php foreach ($contact_types as $type_id => $type_name): ?>
					<option value="<?php echo $type_id; ?>"<?php if($company->getType_id() == $type_id): ?> selected<?php endif; ?>><?php echo $type_name; ?></option>
				<?php endforeach; ?>
				</select></td>
		</tr>
		<tr>
			<td>Nom de l'entreprise *</td>
			<td>
				<input type="text" name="c_name" id="c_name" value="<?php echo $company->getName(); ?>">
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
			<td><textarea name="c_comment" cols="40" rows="5" style="width:300px; height:100px;"><?php echo Utils::br2n($company->getComments()); ?></textarea></td>
		</tr>
<?php if($company->getId() != 0) : ?>
		<tr>
			<td>Actif (non masqué)</td>
			<td><input type="checkbox" name="c_active" value="1"<?php echo ($company->getActive() == 1 ? 'checked':''); ?>></td>
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
	<input type="button" class="btn" name="annul" value="Annuler" onclick="window.location='companies.php?p=<?php echo $p; ?>'">
</div>

<?php if(Access::userCanAccess('contact_form', false)): ?>

	<?php
	$company_id = $company->getId();
	if($company_id == 0): ?>
	<div class="span6">
		<h2>Ajouter un contact</h2>
		<?php echo Utils::displayMessage("Vous pouvez ajouter un nouveau contact
			qui sera automatiquement rattaché à cette entreprise. Entrez au moins
			un nom ou un prénom, sinon le contact ne sera pas créé.", TYPE_MSG_INFO); ?>
		<table class="tform">
			<tr>
				<td>Nom</td>
				<td><input type="text" name="ctc_lastname" value="<?php echo $contact->getLastname(); ?>" size="30"></td>
			</tr>
			<tr>
				<td>Prénom</td>
				<td><input type="text" name="ctc_firstname" value="<?php echo $contact->getFirstname(); ?>" size="30"></td>
			</tr>
		<?php foreach ($c_metas['results'] as $meta): ?>

			<tr>
				<td><?php echo $meta->getName(); ?></td>
				<td><input type="text" name="ctc_m_<?php echo $meta->getId(); ?>" value="" size="30"></td>
			</tr>

		<?php endforeach; ?>
			<tr>
				<td>Notes diverses</td>
				<td><textarea name="ctc_comment" cols="40" rows="5" style="width:300px; height:100px;"><?php echo Utils::br2n($contact->getComments()); ?></textarea></td>
			</tr>
		</table>
	</div>
	<?php endif; ?>
<?php endif; ?>

<input type="hidden" name="<?php echo TOKEN_PREFIX; ?>token" value="<?php echo $session->getSessionData('token'); ?>">

</form>

<script type="text/javascript">
	$("#forcesubmit").click(function() {
		$("#theform").append('<input type="hidden" name="force" value="1">').submit();
	});
</script>