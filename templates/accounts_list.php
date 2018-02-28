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
 * accounts list
 *
 * @since	1.0
 * @author	Marie Kuntz / Lézard Rouge <mariek@lezard-rouge.fr>
 */

?>
<h1><img src="images/contact.png" class="icon" alt=""><?php echo Utils::mb_ucfirst($lang->accounts); ?></h1>

<?php if(Access::userCanAccess('contact_form', false)): ?>
	<p class="btn_action"><a href="?tpl=form&account_id=0" class="no_underline"><img src='images/add.png' alt='Ajouter' class='icon' />Ajouter</a></p>
<?php endif; ?>

<?php
if(isset($message)):
	echo $message;
endif;
?>

<!-- filters ############################################################### -->
<form name="filter" action="" method="get" class="form-inline">
<fieldset class="span12 filters">
	<legend class="filter_title"><img src="images/search.png" alt="" class="icon">Recherche</legend>

	<div class="control-group">

		<label for="f_name" class="control-label">Nom</label>
		<input type="text" name="f_name" id="f_name" value="<?php if(isset($criteria['f_name'])): echo $criteria['f_name']; endif; ?>" class="input-medium">

		<label for="f_active" class="control-label">Actif</label>
		<select name="f_active" id="f_active" class='span1'>
				<option value="1"<?php if(isset($criteria['f_active']) && $criteria['f_active'] == '1'): ?> selected<?php endif; ?>>Oui</option>
				<option value="0"<?php if(isset($criteria['f_active']) && $criteria['f_active'] == '0'): ?> selected<?php endif; ?>>Non</option>
				<option value="-1"<?php if(isset($criteria['f_active']) && $criteria['f_active'] == '-1'): ?> selected<?php endif; ?>>Tous</option>
			</select>

		<input type="submit" class="btn btn-info" name="submitform" value="Filtrer" />
		<input type="button" class="btn" name="reset" value="Voir tout" onclick="location.href='accounts.php';" />
	</div>

</fieldset>
</form>

<?php if($accounts_list['total'] == 0): ?>
		<p><?php echo Utils::mb_ucfirst($lang->no_account); ?>.</p>
<?php else:

	$num_rows = $accounts_list['total'];

	Utils::displayPagination($num_rows, $current, $more_params_pag);
	?>

		<table class="table tlist">
		  <tr>
			<th class="action" colspan="3">&nbsp;</th>
			<th>#</th>
			<th>Nom <span class="inline">
				<a href="?sort=name&order=asc<?php echo $more_params; ?>"><img src="images/order-asc.png" alt="Croissant"></a>
				<a href="?sort=name&order=desc<?php echo $more_params; ?>"><img src="images/order-desc.png" alt="Décroissant"></a></span></th>
		  </tr>
<?php
		$even_odd = TRUE;

		foreach($accounts_list['results'] as $account):
			$bgcolor = ($even_odd ? "even":"odd");
?>
			<tr class="<?php echo $bgcolor; ?>">

				<td class="action">
				<?php if(Access::userCanAccess('account_del', false)): ?>
					<a href="?tpl=del&account_id=<?php echo $account->getId(); ?>&p=<?php echo $p; ?>" rel="fancybox" class="iframe fancydel del"><img src="images/del.png" alt="Del"></a>
				<?php endif; ?>
				</td>
				<td class="action">
				<?php if(Access::userCanAccess('account_form', false)): ?>
					<a href="?tpl=form&account_id=<?php echo $account->getId(); ?>&p=<?php echo $p; ?>"><img src="images/edit.png" alt="Edit"></a>
				<?php endif; ?>
				</td>
				<td class="action">
					<a href="accounts.php?tpl=choice&account_id=<?php echo $account->getId(); ?>&prov=accounts"><img src="images/checked.png" alt="<?php echo Utils::mb_ucfirst($lang->work_with_account); ?>"></a>
				</td>

				<td class="id"><?php echo $account->getId(); ?></td>
				<td><?php echo $account->getName(); ?></td>
			</tr>
<?php
			$even_odd = ! $even_odd;
		endforeach; ?>
		</table>
<?php
		Utils::displayPagination($num_rows, $current, $more_params_pag);
	endif;
?>