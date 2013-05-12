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

amun.services.my = {

	loginDetection: function(){
		$.get(amun.config.url + 'api/my/determineLoginHandler?identity=' + $('#identity').val(), function(resp){
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
	},

	loadSettingsForm: function(cId, url){
		var form = new amun.form(cId, url);

		form.onError(function(msg){
			$('#response').html('<div class="alert alert-error">' + msg + '</div>');
		});

		form.onLoad(function(cId){
			var client = new amun.client(cId);

			client.beforeSubmit(function(){
				$('#' + this.getContainerId() + ' input[type=submit]').attr('disabled', 'disabled');
			});

			client.afterSubmit(function(){
				$('#' + this.getContainerId() + ' input[type=submit]').removeAttr('disabled');
			});

			client.onSuccess(function(msg){
				location.reload();
			});

			client.onError(function(msg){
				$('#response').html('<div class="alert alert-error">' + msg + '</div>');
			});

		});

		form.load();
	},

	loadSubmitActivity: function(id){
		var client = new amun.client(id);

		client.beforeSubmit(function(){
			$('#' + this.getContainerId() + ' input[type=submit]').attr('disabled', 'disabled');
		});

		client.afterSubmit(function(){
			$('#' + this.getContainerId() + ' input[type=submit]').removeAttr('disabled');
		});

		client.onSuccess(function(msg){
			// clear textfield
			$('#' + this.getContainerId() + ' textarea').val('');

			// append new post
			var url = $('#' + this.getContainerId()).attr('action');
			var params = '?count=1&fields=id,summary,date,authorThumbnailUrl,authorProfileUrl,authorName&sortBy=id&sortOrder=descending&filterBy=userId&filterOp=equals&filterValue=' + amun.user.id + '&format=json';

			$.get(url + params, function(data){
				var entry = data.entry[0];
				var html = '';

				date = amun.util.getSqlToDate(entry.date);

				if (id == 'activity-form-0') {
					html+= '<div class="row amun-service-my-activity-entry" id="activity-' + entry.id + '" style="display:none;">';
					html+= '	<img class="pull-left" src="' + entry.authorThumbnailUrl + '" alt="avatar" width="48" height="48" />';
					html+= '	<h4><a href="' + entry.authorProfileUrl + '">' + entry.authorName + '</a></h4>';
					html+= '	<div class="amun-service-my-activity-summary">' + entry.summary + '</div>';
					html+= '	<p class="muted">';
					html+= '	created on';
					html+= '	<time datetime="' + date.toGMTString() + '">' + date.toGMTString() + '</time>';
					html+= '	</p>';
					html+= '</div>';

					$('#activity').after(html);
					$('#activity-' + entry.id).fadeIn();
				} else {
					html+= '<div class="amun-service-my-activity-entry" id="activity-' + entry.id + '" style="display:none;">';
					html+= '	<img class="pull-left" src="' + entry.authorThumbnailUrl + '" alt="avatar" width="48" height="48" />';
					html+= '	<h4><a href="' + entry.authorProfileUrl + '">' + entry.authorName + '</a></h4>';
					html+= '	<div class="amun-service-my-activity-summary">' + entry.summary + '</div>';
					html+= '	<p class="muted">';
					html+= '		created on';
					html+= '		<time datetime="' + date.toGMTString() + '">' + date.toGMTString() + '</time>';
					html+= '	</p>';
					html+= '</div>';

					$('#' + id.replace(/form/, 'comments')).append(html);
					$('#activity-' + entry.id).fadeIn();
				}
			});
		});

		client.onError(function(msg){
			$('#response').fadeIn().html(msg);
		});
	},

	setActivityStatus: function(id, url, el){
		var client = new amun.client();

		client.onSuccess(function(){
			if ($(el).data('status') == 1) {
				$(el).data('status', 2);
				$(el).html('Hide');
				$(el).parent().parent().removeClass('amun-service-my-activity-entry-hidden');
			} else {
				$(el).data('status', 1);
				$(el).html('Show');
				$(el).parent().parent().addClass('amun-service-my-activity-entry-hidden');
			}
		});

		client.onError(function(msg){
			alert(msg);
		});

		client.request(url, 'PUT', {id: id, status: $(el).data('status')});
	},

	friendsDisableButtons: function(){
		if ($('input[type=checkbox]:checked').length > 0) {
			$('#groups').removeAttr('disabled');
			$('#move').removeAttr('disabled');
		} else {
			$('#groups').attr('disabled', 'disabled');
			$('#move').attr('disabled', 'disabled');
		}
	},

	moveFriendInGroup: function(url){
		var client = new amun.client();
		var groupId = parseInt($('#groups').val());
		var els = [];

		if (groupId > 0) {
			$('input[type=checkbox]:checked').each(function(){
				var el = $(this);
				var friendId = parseInt(el.val());
				els.push(el);

				client.onSuccess(function(){
					for (var i = 0; i < els.length; i++) {
						$(els[i]).parent().parent().fadeOut();
					}
				});

				client.onError(function(msg){
					alert(msg);
				});

				client.request(url, 'PUT', {id: friendId, groupId: groupId});
			});
		}

		this.friendsDisableButtons();
	},

	friendsRevokeRelation: function(id, url, el){
		var client = new amun.client();

		client.onSuccess(function(){
			$(el).parent().parent().fadeOut();
		});

		client.onError(function(msg){
			alert(msg);
		});

		client.request(url, 'DELETE', {id: id});
	},

	friendsCancelRelation: function(id, url, el){
		var client = new amun.client();

		client.onSuccess(function(){
			$(el).parent().parent().parent().fadeOut();
		});

		client.onError(function(msg){
			alert(msg);
		});

		client.request(url, 'DELETE', {id: id});
	},

	friendsAcceptRelation: function(userId, friendId, url, el){
		var client = new amun.client();

		client.onSuccess(function(){
			$(el).parent().parent().fadeOut();
		});

		client.onError(function(msg){
			alert(msg);
		});

		client.request(url, 'POST', {userId: userId, friendId: friendId});
	},

	friendsDenyRelation: function(id, url, el){
		var client = new amun.client();

		client.onSuccess(function(){
			$(el).parent().parent().fadeOut();
		});

		client.onError(function(msg){
			alert(msg);
		});

		client.request(url, 'DELETE', {id: id});
	},

	loadFriendGroupAdd: function(cId){
		var client = new amun.client(cId);

		client.onSuccess(function(){
			location.reload();
		});

		client.onError(function(msg){
			alert(msg);
		});
	},

	friendsGroupRemove: function(id, url, el){
		var client = new amun.client();

		client.onSuccess(function(){
			$(el).parent().parent().fadeOut();
		});

		client.onError(function(msg){
			alert(msg);
		});

		client.request(url, 'DELETE', {id: id});
	},

	applicationsRevokeAccess: function(id, url, el){
		var client = new amun.client();

		client.onSuccess(function(){
			$(el).parent().parent().fadeOut();
		});

		client.request(url, 'DELETE', {id: id});
	},

	connectionsRevokeAccess: function(id, url, el){
		var client = new amun.client();

		client.onSuccess(function(){
			$(el).parent().parent().fadeOut();
		});

		client.request(url, 'DELETE', {id: id});
	},

	subscriptionsRemove: function(id, url, el){
		var client = new amun.client();

		client.onSuccess(function(){
			$(el).parent().parent().fadeOut();
		});

		client.request(url, 'DELETE', {id: id});
	},

	contactsRemove: function(id, url, el){
		var client = new amun.client();

		client.onSuccess(function(){
			$(el).parent().parent().fadeOut();
		});

		client.request(url, 'DELETE', {id: id});
	},

	notificationsRemove: function(id, url, el){
		var client = new amun.client();

		client.onSuccess(function(){
			$(el).parent().parent().fadeOut();
		});

		client.request(url, 'DELETE', {id: id});
	}

};


