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
 * contacts list
 *
 * @since	1.0
 * @author Marie Kuntz / Lézard Rouge
 */

?>
<h1><img src="images/contact.png" class="icon" alt="">Contacts</h1>

<?php if(Access::userCanAccess('contact_form', false)): ?>
	<p class="btn_action"><a href="?tpl=form&contact_id=0" class="no_underline"><img src='images/add.png' alt='Ajouter' class='icon' />Ajouter</a></p>
<?php endif; ?>

<!-- filters ############################################################### -->
<form name="filter" action="" method="get" class="form-inline">
<fieldset class="span12 filters">
  <legend class="filter_title"><img src="images/search.png" alt="" class="icon">Recherche</legend>

  <div class="control-group">
	<label for="f_name" class="control-label">Nom / prénom</label>
	<input type="text" name="f_name" id="f_name" value="<?php if(isset($criteria['f_name'])): echo $criteria['f_name']; endif; ?>" class="input-medium">

	<label for="f_company" class="control-label">Entreprise</label>
	<input type="text" name="f_company" id="f_company" value="<?php if(isset($criteria['f_company'])): echo $criteria['f_company']; endif; ?>" class="input-medium">

	<label for="f_type" class="control-label">Type</label>
	<select name="f_type" id="f_type" class="span2">
	<?php foreach ($contact_types as $type_id => $type_name): ?>
		<option value="<?php echo $type_id; ?>"<?php if(isset($criteria['f_type']) && $criteria['f_type'] == $type_id): ?> selected<?php endif; ?>><?php echo $type_name; ?></option>
	<?php endforeach; ?>
	</select>
  </div>

  <div class="control-group">
	  <label for="f_zipcity" class="control-label">Code postal / ville</label>
	  <input type="text" name="f_zipcity" id="f_zipcity" value="<?php if(isset($criteria['f_zipcity'])): echo $criteria['f_zipcity']; endif; ?>" class="input-medium">

	  <label for="f_phone" class="control-label">Téléphone</label>
	  <input type="tel" name="f_phone" id="f_phone" value="<?php if(isset($criteria['f_phone'])): echo $criteria['f_phone']; endif; ?>" class="input-medium">

	  <label for="f_email" class="control-label">Email</label>
	  <input type="text" name="f_email" id="f_email" value="<?php if(isset($criteria['f_email'])): echo $criteria['f_email']; endif; ?>" class="input-medium">

  </div>

  <div class="control-group">
	  <div class="controls">
		<label class="control-label"></label>
		<label class="checkbox" for="f_active">
			<input type="checkbox" name="f_active" id="f_active" value="0"<?php if(isset($criteria['f_active']) && $criteria['f_active'] == '0'): ?> checked<?php endif; ?>> Inclure les masqués
		</label>
	  </div>
  </div>

<?php if(MOD_CRM == 1): ?>
  <div class="control-group">
	  <label for="f_date_add_from" class="control-label">Ajout entre le</label>
	  <input type="text" name="f_date_add_from" id="f_date_add_from" value="<?php if(isset($criteria['f_date_add_from'])): echo $criteria['f_date_add_from']; endif; ?>" class="date">

	  <label for="f_date_add_to" class="control-label">et le</label>
	  <input type="text" name="f_date_add_to" id="f_date_add_to" value="<?php if(isset($criteria['f_date_add_to'])): echo $criteria['f_date_add_to']; endif; ?>" class="date">
  </div>
  <div class="control-group">
	  <label for="f_date_upd_from" class="control-label">Màj entre le</label>
	  <input type="text" name="f_date_upd_from" id="f_date_upd_from" value="<?php if(isset($criteria['f_date_upd_from'])): echo $criteria['f_date_upd_from']; endif; ?>" class="date">

	  <label for="f_date_upd_to" class="control-label">et le</label>
	  <input type="text" name="f_date_upd_to" id="f_date_upd_to" value="<?php if(isset($criteria['f_date_upd_to'])): echo $criteria['f_date_upd_to']; endif; ?>" class="date">
  </div>
<?php endif; ?>

  <div class="control-group">
	  <label for="" class="control-label"></label>
	  <input type="submit" class="btn btn-info" name="submitform" value="Filtrer">
	  <input type="button" class="btn" name="reset" value="Voir tout" onclick="location.href='contacts.php';" />
  </div>

</fieldset>
</form>

<?php if($contacts_list['total'] == 0): ?>
		<p>Aucun contact.</p>
<?php else:
		if(Access::userCanAccess('export_search_contact', false)):
?>
		<input type="button" class="btn btn-success" name="export" value="Exporter la liste" onclick="location.href='import_export.php?tpl=export_search_contacts<?php echo $more_params; ?>'">
	<?php endif; ?>
<?php
	$num_rows = $contacts_list['total'];
	Utils::displayPagination($num_rows, $current, $more_params_pag);
?>
		<table class="tlist">
		  <tr>
			<th class="action" colspan="3">&nbsp;</th>
			<th>Nom prénom <span class="inline">
					<a href="?sort=name&order=asc<?php echo $more_params; ?>"><img src="images/order-asc.png" alt="Croissant"></a>
					<a href="?sort=name&order=desc<?php echo $more_params; ?>"><img src="images/order-desc.png" alt="Décroissant"></a></span></th>
			<th>Email</th>
			<th>Téléphone</th>
			<th>cp / ville</th>
			<th>Entreprise <span class="inline">
					<a href="?sort=company&order=asc<?php echo $more_params; ?>"><img src="images/order-asc.png" alt="Croissant"></a>
					<a href="?sort=company&order=desc<?php echo $more_params; ?>"><img src="images/order-desc.png" alt="Décroissant"></a></span></th>
		  </tr>
<?php
		$even_odd = TRUE;

		foreach($contacts_list['results'] as $contact):
			$bgcolor = ($even_odd ? "even":"odd");
			$bg_done = (($contact->getActive() == 0)? ' alert_done':'');
?>
			<tr class="<?php echo $bgcolor . $bg_done; ?>">
				<td class="action">
				<?php if(Access::userCanAccess('contact_del', false)): ?>
					<a href="?tpl=del&contact_id=<?php echo $contact->getId(); ?>&p=<?php echo $p; ?>" rel="fancybox" class="iframe fancydel del"><img src="images/del.png" alt="Del"></a>
				<?php endif; ?>
				</td>
				<td class="action">
				<?php if(Access::userCanAccess('contact_form', false)): ?>
					<a href="?tpl=form&contact_id=<?php echo $contact->getId(); ?>&p=<?php echo $p; ?>"><img src="images/edit.png" alt="Edit"></a>
				<?php endif; ?>
				</td>
				<td class="action">
				<?php if(Access::userCanAccess('contact_recap', false)): ?>
					<a href="?tpl=recap&contact_id=<?php echo $contact->getId(); ?>"><img src="images/recap.png" alt="Fiche" /></a>
				<?php endif; ?>
				</td>

				<td><a href="?tpl=recap&contact_id=<?php echo $contact->getId(); ?>" class="invisible-link"><?php echo $contact->getLastname(); ?> <?php echo $contact->getFirstname(); ?></a></td>
				<td>
				<?php if(isset($contact_infos[$contact->getId()][4])): ?>
					<a href='mailto:<?php echo $contact_infos[$contact->getId()][4]; ?>'><?php echo $contact_infos[$contact->getId()][4]; ?></a>
				<?php endif; ?></td>
				<td>
					<?php if(isset($contact_infos[$contact->getId()][2])): echo $contact_infos[$contact->getId()][2]; ?><br><?php endif; ?>
					<?php if(isset($contact_infos[$contact->getId()][3])): echo $contact_infos[$contact->getId()][3]; endif; ?>
				</td>
				<td><?php if(isset($contact_infos[$contact->getId()][7])): ?>
					<?php echo $contact_infos[$contact->getId()][7]; ?>
				<?php endif; ?>
				<?php if(isset($contact_infos[$contact->getId()][8])): ?>
					<?php echo $contact_infos[$contact->getId()][8]; ?>
				<?php endif; ?>
				</td>
				<td><?php echo $contact->getCompany()->getName(); ?></td>
			</tr>
<?php
			$even_odd = ! $even_odd;
		endforeach; ?>
		</table>
<?php
		Utils::displayPagination($num_rows, $current, $more_params_pag);
	endif;
?>