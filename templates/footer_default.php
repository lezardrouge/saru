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
 * along with SARU. If not, see <http://www.gnu.org/licenses/>.
 * See LICENSE.TXT file for more information.
 *
 * Saru is released under dual license, AGPL and commercial license.
 * If you need a commercial license or if you don't know which licence you need,
 * please contact us at <info@saru.fr>
 *
 */

/**
 * TEMPLATE
 * footer (default template)
 *
 * @since	1.0
 * @author	Marie Kuntz / Lézard Rouge <mariek@lezard-rouge.fr>
 */
?>
		</div><!-- .span12 -->
	</div><!-- .row-fluid -->

	<div id="footer" class="row-fluid">
		<a href="http://www.saru.fr/" target="_blank" class="a-propos">À propos de Saru</a> -
		<a href="http://www.saru.fr/contact/" target="_blank" class="a-propos">Contactez-nous</a> -
		Saru v<?php echo VERSION; ?> - 2012-2014 - GNU/AGPL v3 - Lézard Rouge
	</div><!-- #footer -->

</div> <!-- .container-fluid -->

<script>
$(function() {
	$( "#quicksearch" ).autocomplete({
		source: function( request, response ) {
			$.getJSON( "search.php", {
				term: request.term
			}, response
		);
		},
		minLength: 2,
		select: function( event, ui ) {
			location.href = ui.item.src;
		}
	});
});
</script>

</body>
</html>
