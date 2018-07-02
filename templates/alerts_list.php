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
 * alerts list
 *
 * @since	1.0
 * @author	Marie Kuntz / Lézard Rouge <mariek@lezard-rouge.fr>
 */


$today = date("Y-m-d");
$next_days = date("Y-m-d", mktime(null, null, null, date("m"), date("d") + NEXT_DAYS, date("Y")));

?>
<h1><img src="images/alerte.png" class="icon" alt="">Alertes</h1>

<?php if(Access::userCanAccess('alert_form', false)): ?>
	<p class="btn_action"><a href="?tpl=form&alert_id=0" class="no_underline"><img src='images/add.png' alt='Ajouter' class='icon' />Ajouter</a></p>
<?php endif; ?>

<!-- filters ############################################################### -->
<form name="filter" action="" method="get" class="form-inline">
<fieldset class="filters">
  <legend class="filter_title"><img src="images/search.png" alt="" class="icon">Recherche</legend>

	<div class="control-group">
		<label for="f_name" class="control-label">Nom / prénom</label>
		<input type="text" name="f_name" id="f_name" value="<?php if(isset($criteria['f_name'])): echo $criteria['f_name']; endif; ?>" class="input-medium">

		<label for="f_keyword" class="control-label">Mot clé</label>
		<input type="text" name="f_keyword" id="f_keyword" value="<?php if(isset($criteria['f_keyword'])): echo $criteria['f_keyword']; endif; ?>" class="input-medium">

		<label for="f_done" class="control-label">Fait</label>
		<select name="f_done" class="span2">
			<option value="0"<?php if(isset($criteria['f_done']) && $criteria['f_done'] == '0'): ?> selected<?php endif; ?>>Non</option>
			<option value="1"<?php if(isset($criteria['f_done']) && $criteria['f_done'] == '1'): ?> selected<?php endif; ?>>Oui</option>
			<option value="-1"<?php if(isset($criteria['f_done']) && $criteria['f_done'] == '-1'): ?> selected<?php endif; ?>>Tous</option>
		</select>
	</div>


	<div class="control-group">

	<?php if(count($users) > 1): ?>
		<label for="f_user" class="control-label">Assigné à</label>
		<select name="f_user" id="f_user" class="span2">
			<option value="0">--</option>
		<?php foreach ($users as $user_id => $user_name): ?>
				<option value="<?php echo $user_id; ?>"<?php if(isset($criteria['f_user']) && $criteria['f_user'] == $user_id): ?> selected<?php endif; ?>>
					<?php echo $user_name; ?></option>
		<?php endforeach; ?>
		</select>
	<?php endif; ?>

		<label for="date_from" class="control-label">Entre le</label>
		<input type="date" name="f_date_from" id="f_date_from" value="<?php if(isset($criteria['f_date_from'])): echo $criteria['f_date_from']; endif; ?>" class="date input-medium">

		<label for="f_date_to" class="control-label">et le</label>
		<input type="date" name="f_date_to" id="f_date_to" value="<?php if(isset($criteria['f_date_to'])): echo $criteria['f_date_to']; endif; ?>" class="date input-medium">
	</div>

	<div class="control-group">
		<label for="" class="control-label">Filtre rapide</label>
			<a href="?f_when=nodate"><span class="badge">Sans date</span></a>
			<a href="?f_when=expired"><span class="badge badge-alert-expired">En retard</span></a>
			<a href="?f_when=today"><span class="badge badge-alert-today">Aujourd'hui</span></a>
			<a href="?f_when=nextdays"><span class="badge badge-alert-next-days">Dans les <?php echo NEXT_DAYS; ?> prochains jours</span></a>
			<a href="?f_when=later"><span class="badge badge-alert-later">Plus tard</span></a>
	</div>

	<div class="control-group">
		<label for="" class="control-label"></label>
		<input type="submit" class="btn btn-info" name="submitform" value="Filtrer">
		<input type="button" class="btn" name="reset" value="Voir tout" onclick="location.href='alerts.php';">
	</div>

</fieldset>
</form>

<?php if($alerts_list['total'] == 0): ?>
		<p>Aucune alerte.</p>
<?php else:

	$num_rows = $alerts_list['total'];

	Utils::displayPagination($num_rows, $current, $more_params_pag);
?>
		<table class="table tlist">
		  <tr>
			<th class="action" colspan="3">&nbsp;</th>
			<th>Date <span class="inline">
				<a href="?sort=date&order=asc<?php echo $more_params; ?>"><img src="images/order-asc.png" alt="Croissant"></a>
				<a href="?sort=date&order=desc<?php echo $more_params; ?>"><img src="images/order-desc.png" alt="Décroissant"></a></span></th>
			<th>P <span class="inline">
				<a href="?sort=priority&order=asc<?php echo $more_params; ?>"><img src="images/order-asc.png" alt="Croissant"></a>
				<a href="?sort=priority&order=desc<?php echo $more_params; ?>"><img src="images/order-desc.png" alt="Décroissant"></a></span></th>
			<th>Contact <span class="inline">
				<a href="?sort=name&order=asc<?php echo $more_params; ?>"><img src="images/order-asc.png" alt="Croissant"></a>
				<a href="?sort=name&order=desc<?php echo $more_params; ?>"><img src="images/order-desc.png" alt="Décroissant"></a></span></th>
		<?php if(count($users) > 1): ?>
			<th>Utilisateur <span class="inline">
				<a href="?sort=user&order=asc<?php echo $more_params; ?>"><img src="images/order-asc.png" alt="Croissant"></a>
				<a href="?sort=user&order=desc<?php echo $more_params; ?>"><img src="images/order-desc.png" alt="Décroissant"></a></span></th>
		<?php endif; ?>
			<th>Objet</th>
		  </tr>
<?php
		$even_odd = TRUE;

		foreach($alerts_list['results'] as $alert):
			$bgcolor = ($even_odd ? "even":"odd");
			$bg_done = (($alert->getDone() == 1)? ' alert_done':'');
			if (Utils::dateEmpty($alert->getDate())):
				$blockColor = 'alert-note';
			elseif ($alert->getDate() < $today):
				$blockColor = 'alert-expired';
			elseif($alert->getDate() == $today):
				$blockColor = 'alert-today';
			elseif($alert->getDate() < $next_days):
				$blockColor = 'alert-next-days';
			else:
				$blockColor = 'alert-later';
			endif;
?>
			<tr class="<?php echo $bgcolor . $bg_done; ?>">
				<td class="action">
				<?php if(Access::userCanAccess('alert_del', false)): ?>
					<a href="?tpl=del&alert_id=<?php echo $alert->getId(); ?>&p=<?php echo $p; ?>" rel="fancybox" class="iframe del"><img src="images/del.png" alt="Del" ></a>
				<?php endif; ?>
				</td>
				<td class="action">
				<?php if(Access::userCanAccess('alert_form', false)): ?>
					<a href="?tpl=form&alert_id=<?php echo $alert->getId(); ?>&p=<?php echo $p; ?>"><img src="images/edit.png" alt="Edit" ></a>
				<?php endif; ?>
				</td>
				<td class="action">
				<?php if(Access::userCanAccess('alert_form', false) && ($alert->getDone() == 0)): ?>
					<a href="?tpl=done&alert_id=<?php echo $alert->getId(); ?>&<?php echo TOKEN_PREFIX; ?>token=<?php echo $session->getSessionData('token'); ?>"><img src="images/checked.png" alt="Fait" title="Fait" class="icon"></a>
				<?php endif; ?>
				</td>

				<td class="date"><span class="badge badge-<?php echo $blockColor; ?>"><?php echo $alert->getDate_format(); ?></span></td>
				<td class="date"><?php if($alert->getPriority() == 1): ?>
					<img src="images/alert_p1.png" alt="priority" class="icon">
				<?php endif; ?></td>
				<td><?php if(Access::userCanAccess('contact_recap', false)): ?>
					<a href="contacts.php?tpl=recap&contact_id=<?php echo $alert->getContact_id(); ?>" class="invisible-link"><?php echo $contacts[$alert->getContact_id()]->getFullname(); ?></a>
				<?php else: ?>
					<?php echo $contacts[$alert->getContact_id()]->getFullname(); ?>
				<?php endif; ?>
				</td>
			<?php if(count($users) > 1): ?>
				<td><?php echo $alert->getUser()->getFirstname() . ' ' . $alert->getUser()->getLastname(); ?></td>
			<?php endif; ?>
				<td><?php echo $alert->getComments(); ?></td>
			</tr>
<?php
			$even_odd = ! $even_odd;
		endforeach; ?>
		</table>
<?php
		Utils::displayPagination($num_rows, $current, $more_params_pag);
	endif;
?>
