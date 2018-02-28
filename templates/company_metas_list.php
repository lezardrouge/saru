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
 * company metas list
 *
 * @since	1.0
 * @author	Marie Kuntz / Lézard Rouge <mariek@lezard-rouge.fr>
 */

?>
<h1>Metadonnées des entreprises</h1>

<p class="btn_action"><a href="?tpl=form&meta_id=0" class="no_underline"><img src='images/add.png' alt='Ajouter' class='icon' />Ajouter</a></p>

<?php if($metas_list['total'] == 0): ?>
		<p>Aucune meta.</p>
<?php else:

	$num_rows = $metas_list['total'];

	Utils::displayPagination($num_rows, $current);
	?>

		<table class="tlist tlist_small">
		  <tr>
			<th class="action" colspan="2">&nbsp;</th>
			<th>Intitulé</th>
			<th>Ordre</th>
		  </tr>
<?php
		$even_odd = TRUE;

		foreach($metas_list['results'] as $meta):
			$bgcolor = ($even_odd ? "even":"odd");
			$bg_done = (($meta->getActive() == 0)? ' meta_inactive':'');
?>
			<tr class="<?php echo $bgcolor . $bg_done; ?>">
				<td class="action"><a href="?tpl=del&meta_id=<?php echo $meta->getId(); ?>&p=<?php echo $p; ?>" rel="fancybox" class="iframe del"><img src="images/del.png" alt="Del" ></a></td>
				<td class="action"><a href="?tpl=form&meta_id=<?php echo $meta->getId(); ?>&p=<?php echo $p; ?>"><img src="images/edit.png" alt="Edit" ></a></td>

				<td><?php echo $meta->getName(); ?>
				<?php $active = $meta->getActive();
				if($active == 0):
					echo ' (inactif)';
				endif; ?></td>
				<td class="order"><?php echo $meta->getOrder(); ?></td>
			</tr>
<?php
			$even_odd = ! $even_odd;
		endforeach; ?>
		</table>
<?php
		Utils::displayPagination($num_rows, $current);
	endif;
?>