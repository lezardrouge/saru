<?php
/**
 * SARU
 * organize your contacts
 *
 * Copyright (c) 2012-2014 Marie Kuntz - Lezard Rouge
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
 * reset password page
 *
 * @since 2.0
 * @author Marie Kuntz / Lézard Rouge
 */

if( ! defined('LOCAL_PATH')) { exit; }
?>
<div class="row-fluid">
	<div id="login" class="span4 offset4">
		<h1><img src="images/password.png" class="icon" alt="Mot de passe">Réinitialiser le mot de passe</h1>

		<?php
		if(isset($message)):
			echo $message;
		endif;
		?>
		<form name="loginform" method="post" action="login.php">
			<input type="hidden" name="action" value="reset">
			<input type="hidden" name="t" value="<?php echo $token; ?>">
			<input type="hidden" name="l" value="<?php echo $login; ?>">

			<p class="alert alert-info">Indiquez un nouveau mot de passe d'au moins 8 caractères.
				Vous pouvez utiliser les lettres de l'alphabet (majuscules et minuscules),
				les chiffres et les caractères spéciaux suivants : -_@!?$ (pas d'espace).</p>
			<p><input type="input" autocomplete="off" name="password" id="password" class="" placeholder="Nouveau mot de passe"></p>
			<p><input type="submit" name="submitform" value="Enregistrer le nouveau mot de passe" class="btn btn-info"></p>
		</form>

	</div>
</div>