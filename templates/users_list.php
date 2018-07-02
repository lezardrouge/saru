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
 * users list
 *
 * @since	1.0
 * @author	Marie Kuntz / Lézard Rouge <mariek@lezard-rouge.fr>
 */

?>
<h1><img src="images/contact.png" class="icon" alt="">Utilisateurs</h1>

<?php if(Access::userCanAccess('user_form', false)): ?>
<p class="btn_action"><a href="?tpl=form&user_id=0" class="no_underline"><img src='images/add.png' alt='Ajouter' class='icon' />Ajouter</a></p>
<?php endif; ?>


<!-- filters ############################################################### -->
<form name="filter" action="" method="get" class="form-inline">
<fieldset class="span12 filters">
	<legend class="filter_title"><img src="images/search.png" alt="" class="icon">Recherche</legend>

	<div class="control-group">

		<label for="f_name" class="control-label">Nom/prénom</label>
		<input type="text" name="f_name" id="f_name" value="<?php if(isset($criteria['f_name'])): echo $criteria['f_name']; endif; ?>" class="input-medium">

		<label for="f_login" class="control-label">Identifiant</label>
		<input type="text" name="f_login" id="f_login" value="<?php if(isset($criteria['f_login'])): echo $criteria['f_login']; endif; ?>" class="input-medium">

		<label for="f_email" class="control-label">Email</label>
		<input type="text" name="f_email" id="f_email" value="<?php if(isset($criteria['f_email'])): echo $criteria['f_email']; endif; ?>" class="input-medium">

		<input type="submit" class="btn btn-info" name="submitform" value="Filtrer" />
		<input type="button" class="btn" name="reset" value="Voir tout" onclick="location.href='users.php';" />
	</div>

</fieldset>
</form>


<?php if($users_list['total'] == 0): ?>
	<p>Aucun utilisateur.</p>
<?php else:

	$num_rows = $users_list['total'];

	Utils::displayPagination($num_rows, $current, $more_params_pag);
?>

		<table class="table tlist">
		  <tr>
			<th class="action" colspan="2">&nbsp;</th>
			<th>Nom <span class="inline">
					<a href="?sort=name&order=asc<?php echo $more_params; ?>"><img src="images/order-asc.png" alt="Croissant"></a>
					<a href="?sort=name&order=desc<?php echo $more_params; ?>"><img src="images/order-desc.png" alt="Décroissant"></a></span></th>
			<th>Login <span class="inline">
					<a href="?sort=login&order=asc<?php echo $more_params; ?>"><img src="images/order-asc.png" alt="Croissant"></a>
					<a href="?sort=login&order=desc<?php echo $more_params; ?>"><img src="images/order-desc.png" alt="Décroissant"></a></span></th>
			<th>Email</th>
			<th>Statut alertes</th>
		  </tr>
<?php
		$even_odd = TRUE;

		foreach($users_list['results'] as $user):
			$bgcolor = ($even_odd ? "even":"odd");
?>
			<tr class="<?php echo $bgcolor; ?>">
				<td class="action">
				<?php if(Access::userCanAccess('user_del', false)): ?>
					<a href="?tpl=del&user_id=<?php echo $user->getId(); ?>&p=<?php echo $p; ?>" rel="fancybox" class="iframe fancydel del"><img src="images/del.png" alt="Del"></a>
				<?php endif; ?>
				</td>
				<td class="action">
				<?php if(Access::userCanAccess('user_form', false)): ?>
					<a href="?tpl=form&user_id=<?php echo $user->getId(); ?>&p=<?php echo $p; ?>"><img src="images/edit.png" alt="Edit"></a>
				<?php endif; ?>
				</td>

				<td><?php echo $user->getLastname(); ?> <?php echo $user->getFirstname(); ?></td>
				<td><?php echo $user->getLogin(); ?></td>
				<td><?php echo $user->getEmail() ;?></td>
				<td class="action"><?php if ($user->getSend_alerts() == 0): ?>
					<img src="images/alerte_mute.png" alt="Alertes suspendues">
				<?php endif; ?>
				</td>
			</tr>
<?php
			$even_odd = ! $even_odd;
		endforeach; ?>
		</table>
<?php
		Utils::displayPagination($num_rows, $current, $more_params_pag);
	endif;
?>
