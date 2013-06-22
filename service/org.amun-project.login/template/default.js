/*
 * amun
 * A social content managment system based on the psx framework. For
 * the current version and informations visit <http://amun.phpsx.org>
 *
 * Copyright (c) 2010-2013 Christoph Kappestein <k42b3.x@gmail.com>
 *
 * This file is part of amun. amun is free software: you can
 * redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or any later version.
 *
 * amun is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with amun. If not, see <http://www.gnu.org/licenses/>.
 */

amun.services.login = {

	detection: function(){
		$.get(amun.config.url + 'api/login/determineLoginHandler?identity=' + $('#identity').val(), function(resp){
			resp = JSON.parse(resp);
			if (resp.icon) {
				$('#identity').css('background-image', 'url(' + resp.icon + ')');
				$('#identity').css('background-position', '95% 50%');
				$('#identity').css('background-repeat', 'no-repeat');
			} else {
				$('#identity').css('background-image', 'none');
			}

			if (resp.needPassword == '0') {
				$('#pw').css('background-color', '#eee');
				$('#pw').val('');

				if ($('#pw').is(':focus')) {
					$('#login').focus();
				}
			} else {
				$('#pw').css('background-color', '#fff');
			}
		});
	}

};


