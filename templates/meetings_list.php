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
 * meetings list
 *
 * @since	1.0
 * @author	Marie Kuntz / Lézard Rouge <mariek@lezard-rouge.fr>
 */

?>
<h1><img src="images/calendar.png" class="icon" alt=""><?php echo Utils::mb_ucfirst($lang->meetings); ?></h1>

<?php if(Access::userCanAccess('meeting_form', false)): ?>
	<p class="btn_action"><a href="?tpl=form&meeting_id=0" class="no_underline"><img src='images/add.png' alt='Ajouter' class='icon' />Ajouter</a></p>
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
	<select name="f_type" class="span2">
	<?php foreach ($meeting_types as $type_id => $type_name): ?>
		<option value="<?php echo $type_id; ?>"<?php if(isset($criteria['f_type']) && $criteria['f_type'] == $type_id): ?> selected<?php endif; ?>><?php echo $type_name; ?></option>
	<?php endforeach; ?>
	</select>
  </div>

  <div class="control-group">
	<label for="f_keyword" class="control-label">Mot clé</label>
	<input type="text" name="f_keyword" id="f_keyword" value="<?php if(isset($criteria['f_keyword'])): echo $criteria['f_keyword']; endif; ?>" class="input-medium">

	<label for="date_from" class="control-label">Entre le</label>
	<input type="date" name="f_date_from" id="f_date_from" value="<?php if(isset($criteria['f_date_from'])): echo $criteria['f_date_from']; endif; ?>" class="input-medium">

	<label for="f_date_to" class="control-label">et le</label>
	<input type="date" name="f_date_to" id="f_date_to" value="<?php if(isset($criteria['f_date_to'])): echo $criteria['f_date_to']; endif; ?>" class="input-medium">

  </div>

  <div class="control-group">
	  <label for="" class="control-label"></label>
	  <input type="submit" class="btn btn-info" name="submitform" value="Filtrer">
	  <input type="button" class="btn" name="reset" value="Voir tout" onclick="location.href='meetings.php';">
  </div>

</fieldset>
</form>

<?php if($meetings_list['total'] == 0): ?>
	<p><?php echo Utils::mb_ucfirst($lang->no_meeting); ?>.</p>
<?php else:

	$num_rows = $meetings_list['total'];

	Utils::displayPagination($num_rows, $current, $more_params_pag);
	?>

		<table class="table tlist">
		  <tr>
			<th class="action" colspan="2">&nbsp;</th>
			<th>Date <span class="inline">
				<a href="?sort=date&order=asc<?php echo $more_params; ?>"><img src="images/order-asc.png" alt="Croissant"></a>
				<a href="?sort=date&order=desc<?php echo $more_params; ?>"><img src="images/order-desc.png" alt="Décroissant"></a></span></th>
			<th>Nom <span class="inline">
				<a href="?sort=name&order=asc<?php echo $more_params; ?>"><img src="images/order-asc.png" alt="Croissant"></a>
				<a href="?sort=name&order=desc<?php echo $more_params; ?>"><img src="images/order-desc.png" alt="Décroissant"></a></span></th>
			<th>Entreprise <span class="inline">
				<a href="?sort=company&order=asc<?php echo $more_params; ?>"><img src="images/order-asc.png" alt="Croissant"></a>
				<a href="?sort=company&order=desc<?php echo $more_params; ?>"><img src="images/order-desc.png" alt="Décroissant"></a></span></th>
			<th>Compte-rendu</th>
		  </tr>
<?php
		$even_odd = TRUE;

		foreach($meetings_list['results'] as $meeting):
			$bgcolor = ($even_odd ? "even":"odd");
?>
			<tr class="<?php echo $bgcolor; ?>">
				<td class="action">
				<?php if(Access::userCanAccess('meeting_del', false)): ?>
					<a href="?tpl=del&meeting_id=<?php echo $meeting->getId(); ?>&p=<?php echo $p; ?>" rel="fancybox" class="iframe del"><img src="images/del.png" alt="Del" ></a>
				<?php endif; ?>
				</td>
				<td class="action">
				<?php if(Access::userCanAccess('meeting_form', false)): ?>
					<a href="?tpl=form&meeting_id=<?php echo $meeting->getId(); ?>&p=<?php echo $p; ?>"><img src="images/edit.png" alt="Edit" ></a>
				<?php endif; ?>
				</td>

				<td class="date"><?php echo $meeting->getDate_format(); ?></td>
				<td class="nom">
				<?php if(isset($contacts[$meeting->getContact_id()])): ?>
					<?php if(Access::userCanAccess('contact_recap', false)): ?>
						<a href="contacts.php?tpl=recap&contact_id=<?php echo $meeting->getContact_id(); ?>" class="invisible-link"><?php echo $contacts[$meeting->getContact_id()]->getLastname(); ?> <?php echo $contacts[$meeting->getContact_id()]->getFirstname(); ?></a>
					<?php else: ?>
						<?php echo $contacts[$meeting->getContact_id()]->getLastname(); ?> <?php echo $contacts[$meeting->getContact_id()]->getFirstname(); ?>
					<?php endif; ?>
				<?php endif; ?>
				</td>
				<td class="nom">
				<?php if(isset($contacts[$meeting->getContact_id()])): ?>
					<?php if(Access::userCanAccess('company_recap', false)): ?>
						<a href="companies.php?tpl=recap&company_id=<?php echo $contacts[$meeting->getContact_id()]->getCompany()->getId(); ?>" class="invisible-link"><?php echo $contacts[$meeting->getContact_id()]->getCompany()->getName(); ?></a>
					<?php else: ?>
						<?php echo $contacts[$meeting->getContact_id()]->getCompany()->getName(); ?>
					<?php endif; ?>
				<?php endif; ?></td>
				<td><img src="images/meeting_type_<?php echo $meeting->getType_id(); ?>.png" alt="<?php echo $meeting->getType_name(); ?>" title="<?php echo $meeting->getType_name(); ?>" class="icon">
					<?php echo $meeting->getComments(); ?></td>
			</tr>
<?php
			$even_odd = ! $even_odd;
		endforeach; ?>
		</table>
<?php
		Utils::displayPagination($num_rows, $current, $more_params_pag);
	endif;
?>