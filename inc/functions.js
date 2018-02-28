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
 */

/**
 * generic js functions
 *
 * @since	1.0
 * @author	Marie Kuntz / Lézard Rouge <mariek@lezard-rouge.fr>
 */


/**
 * get element by id
 *
 * @param {type} id
 * @returns {@exp;document@call;getElementById}
 */
function GetId(id)
{
 	return document.getElementById(id);
}


/**
 * check multi boxes
 *
 * @param {type} aBox
 * @returns {undefined}
 */
function checkBoxes(aBox) {

	if(aBox === 'D') {
		var chk_all = GetId('checkBDel');
		var grpbox = document.getElementsByName('ckb_file_del[]');
	} else if(aBox === 'S') {
		var chk_all = GetId('checkB');
		var grpbox = document.getElementsByName('ckb_file[]');
	}
	var nb_box = grpbox.length;
	var i;

	if(chk_all.checked === true) { // check all
		for (i=0; i < nb_box; i++) {
			grpbox[i].checked="checked";
		}
	} else { // check none
		for (i=0; i < nb_box; i++) {
			grpbox[i].checked="";
		}
    }
}


