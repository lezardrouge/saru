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
 * @copyright  Copyright (c) 2012-2014 Marie Kuntz - Lezard Rouge (http://www.lezard-rouge.fr)
 * @license    GNU-AGPL v3 http://www.gnu.org/licenses/agpl.html
 * @version    1
 * @author     Marie Kuntz - Lezard Rouge SARL - www.lezard-rouge.fr - contact@lezard-rouge.fr
 */

/**
 * TEMPLATE
 * forgotten password page
 *
 * @since 2.0
 * @author Marie Kuntz / Lézard Rouge
 */

if( ! defined('LOCAL_PATH')) { exit; }
?>
<div class="row-fluid">
	<div id="login" class="span4 offset4">
		<h1><img src="images/help.png" class="icon" alt="Aide">Vous avez oublié votre mot de passe ?</h1>

		<?php
		if(isset($message)):
			echo $message;
		endif;
		?>
		<form name="loginform" method="post" action="login.php">
			<input type="hidden" name="action" value="pwd">
			<p class="alert alert-info">Renseignez votre identifiant (login) puis suivez les instructions contenues
				dans le mail qui vous sera envoyé pour réinitialiser votre mot de passe.</p>
			<p><input type="text" name="username" id="username" class="" placeholder="Identifiant" value="<?php if(isset($username)):
				echo $username;
			endif; ?>"></p>
			<input type="hidden" name="login_token" value="<?php echo $_SESSION['login_token']; ?>">
			<p><input type="submit" name="submitform" value="Envoyer le mail" class="btn btn-info"></p>
		</form>

	</div>
</div>