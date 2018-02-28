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
 * user form
 *
 * @since	1.0
 * @author	Marie Kuntz / Lézard Rouge <mariek@lezard-rouge.fr>
 */

?>
<form method="post" action="users.php">
<div class="row-fluid">
	<div class="span5">
	<h1>Editer un utilisateur</h1>
	<p><a href="users.php" class="no_underline"><img src="images/back.png" alt="&lt;&lt;" class="icon">Retour à la liste</a></p>
	<?php echo Utils::displayMessage("Les champs notés * sont obligatoires.", TYPE_MSG_INFO); ?>
	<?php echo $message; ?>
	<input type="hidden" name="user_id" value="<?php echo $user->getId(); ?>">
	<input type="hidden" name="tpl" value="form">
	<input type="hidden" name="p" value="<?php echo $p; ?>">
	<table class="tform">
		<tr>
			<td>Nom *</td>
			<td><input type="text" name="u_lastname" value="<?php echo $user->getLastname(); ?>" size="30">
			</td>
		</tr>
		<tr>
			<td>Prénom *</td>
			<td><input type="text" name="u_firstname" value="<?php echo $user->getFirstname(); ?>" size="30">
			</td>
		</tr>
		<tr>
			<td>Identifiant *</td>
			<td><input type="text" name="u_login" id="u_login" value="<?php echo $user->getLogin(); ?>" size="30">
				<input type="hidden" name="u_currentlogin" id="u_currentlogin" value="<?php echo $user->getLogin(); ?>">
				<a href="#" data-toggle="tooltip" data-placement="right" class="tip"
				   title="L'identifiant doit être unique (ne doit pas déjà exister). Indiquez au moins 6 caractères,
				   alphanumérique et .-_@ acceptés, pas d'espace. En cas de modification, vous devez
				   re-saisir le mot de passe."><img src="images/information.png" alt="tip" class="tip"></a><br>
				<div class="alert-danger ponctual-alert" id="alert-login-mod">Par mesure de sécurité, si vous modifiez
					l'identifiant, vous devez re-saisir le mot de
					passe. Vous pouvez changer le mot de passe.</div>
			</td>
		</tr>
		<tr>
			<td>Mot de passe</td>
			<td><input type="text" name="u_pwd" id="u_pwd" size="30">
				<a href="#" data-toggle="tooltip" data-placement="right" class="tip" title="Obligatoire si création.
				   En modification de l'utilisateur, laissez vide pour ne pas modifier le mot de passe.
				Entre 8 et 20 caractères, alphanumérique, caractères spéciaux acceptés : -_@!?$ (pas d'espace)."><img src="images/information.png" alt="tip" class="tip"></a>
				<a href="#" id="genpasswd" data-toggle="tooltip" data-placement="right" class="tip" title="Cliquez pour générer un mot de passe"><img src="images/refresh.png" alt="tip" class="tip"></a>
			</td>
		</tr>
		<tr>
			<td>Email *</td>
			<td><input type="text" name="u_email" value="<?php echo $user->getEmail(); ?>" size="30">
			</td>
		</tr>
	</table>
</div>
</div>

<!-- ###########################################################################
	permissions -->
<div class="row-fluid">
	<div class="span5">
		<h2>Droits d'accès</h2>
		<?php echo Utils::displayMessage("Cochez la case correspondante pour donner un droit d'accès à la fonctionnalité.", TYPE_MSG_INFO); ?>
		<table class="tform">
		<?php foreach($components as $component): ?>
			<tr><td><input type="checkbox" name="access[]" id="access_<?php echo $component->getId(); ?>" value="<?php echo $component->getId(); ?>"<?php
			if(in_array($component->getId(), $user->getAccess_perms())): ?> checked <?php endif; ?>>
			<label for="access_<?php echo $component->getId(); ?>"><?php echo $component->getDescription(); ?></label></td></tr>
		<?php endforeach; ?>
		</table>
	</div>

<!-- ###########################################################################
	account access -->

	<div class="span5 offset1">
		<h2><?php echo Utils::mb_ucfirst($lang->account_access); ?></h2>
		<?php echo Utils::displayMessage(Utils::mb_ucfirst($lang->check_account), TYPE_MSG_INFO); ?>
		<table class="tform">
		<?php foreach($accounts['results'] as $account): ?>
			<tr><td><input type="checkbox" name="account[]" id="account_<?php echo $account->getId(); ?>" value="<?php echo $account->getId(); ?>"<?php
			if(in_array($account->getId(), $user->getAccount_perms())): ?> checked <?php endif; ?>>
			<label for="account_<?php echo $account->getId(); ?>"><?php echo $account->getName(); ?></label></td></tr>
		<?php endforeach; ?>
		</table>
	</div>

</div>

<input type="submit" name="submitform" value="Ok" class="btn btn-info">
<input type="button" name="annul" class="btn" value="Annuler" onclick="window.location='users.php'">
<input type="hidden" name="<?php echo TOKEN_PREFIX; ?>token" value="<?php echo $session->getSessionData('token'); ?>">

</form>

<script type="text/javascript">
    $('.tip').tooltip();
	$('#genpasswd').click(function(event){
		$.post("users.php?tpl=pwd",
			{} ,
			function(result) {
				$("#u_pwd").val(result);
			});
		event.preventDefault();
	});
	$('#u_login').blur(function(){
		$new = $('#u_login').val();
		$current = $('#u_currentlogin').val();
		if($current !== '' && $current !== $new) {
			$('#alert-login-mod').show();
		}
	});

</script>
