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
 * accounts form
 *
 * @since	1.0
 * @author	Marie Kuntz / Lézard Rouge <mariek@lezard-rouge.fr>
 */
$infos = array();
if(isset($account_infos[$account->getId()])):
	$infos = $account_infos[$account->getId()];
endif;
?>
<div class="span5">
	<h1><?php echo Utils::mb_ucfirst($lang->edit_account); ?></h1>
	<p><a href="accounts.php" class="no_underline"><img src="images/back.png" alt="&lt;&lt;" class="icon">Retour à la liste</a></p>
	<?php echo Utils::displayMessage("Les champs notés * sont obligatoires.", TYPE_MSG_INFO); ?>
	<?php echo $message; ?>
	<form method="post" action="accounts.php" enctype="multipart/form-data">
	<input type="hidden" name="account_id" value="<?php echo $account->getId(); ?>">
	<input type="hidden" name="tpl" value="form">
	<table class="tform">
		<tr>
			<td>Nom *</td>
			<td><input type="text" name="a_name" value="<?php echo $account->getName(); ?>" size="30"></td>
		</tr>
<?php foreach ($metas['results'] as $meta): ?>

		<tr>
			<td><?php echo $meta->getName(); ?></td>
			<td><input type="text" name="m_<?php echo $meta->getId(); ?>" value="<?php if(isset($infos[$meta->getId()])): echo $infos[$meta->getId()]; endif; ?>" size="30"></td>
		</tr>

<?php endforeach; ?>
		<tr>
			<td>Actif</td>
			<td><input type="checkbox" name="a_active" value="1"<?php echo ($account->getActive() == 1 ? ' checked':''); ?>></td>
		</tr>
	</table>
	<input type="submit" name="submitform" value="Ok" class="btn btn-info"> <input type="button" class="btn" name="annul" value="Annuler" onclick="window.location='accounts.php?p=<?php echo $p; ?>';">
	<input type="hidden" name="<?php echo TOKEN_PREFIX; ?>token" value="<?php echo $session->getSessionData('token'); ?>">
	</form>
</div>