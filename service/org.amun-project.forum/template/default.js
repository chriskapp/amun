
amun.services.forum = {

	showForm: function(url){
		var win = new amun.window(url);
		win.addButton('Preview', 'btn', function(){
			// if we have an ace editor
			var editors = this.getClient().getEditors();
			for (var k in editors) {
				var editor = editors[k];
				var value = editor.getSession().getValue();

				$.post(amun.config.url + 'api/content/page/render?markdown=1', value, function(resp){
					$('#amun-form-window-preview').html(resp).fadeIn();
				});
			}
		});
		win.beforeShow(function(){
			$('#amun-form-window-preview').css('display', 'none');
		});
		win.onSuccess(function(){
			setTimeout('location.reload()', 600);
		});
		win.show();
	},

	loadCommentForm: function(cId, url){
		var form = new amun.form(cId, url);
		var client;

		form.addButton('Preview', 'btn', function(){
			// if we have an ace editor
			var editors = client.getEditors();
			for (var k in editors) {
				var editor = editors[k];
				var value = editor.getSession().getValue();

				$.post(amun.config.url + 'api/content/page/render?markdown=1&oembed=1', value, function(resp){
					$('#preview').html(resp).fadeIn();
				});
			}
		});

		form.onError(function(msg){
			$('#response').html('<div class="alert alert-error">' + msg + '</div>');
		});

		form.onLoad(function(cId){
			client = new amun.client(cId);

			client.beforeSubmit(function(){
				$('#' + this.getContainerId() + ' input[type=submit]').attr('disabled', 'disabled');
			});

			client.afterSubmit(function(){
				$('#' + this.getContainerId() + ' input[type=submit]').removeAttr('disabled');
			});

			client.onSuccess(function(msg){
				// fade out preview
				$('#preview').fadeOut();

				// clear textfield
				var editors = client.getEditors();
				for (var ref in editors) {
					editors[ref].getSession().getDocument().setValue('');
				}

				// append new post
				var url = $('#' + this.getContainerId()).attr('action');
				var params = '?count=1&fields=id,text,date,authorThumbnailUrl,authorProfileUrl,authorName&sortBy=id&sortOrder=descending&filterBy=userId&filterOp=equals&filterValue=' + amun.user.id + '&format=json';

				$.get(url + params, function(data){
					var entry = data.entry[0];
					var html = '';

					date = amun.util.getSqlToDate(entry.date);

					html+= '<div class="amun-service-comment-entry" id="comment-' + entry.id + '" style="display:none;">';
					html+= '	<img class="pull-left" src="' + entry.authorThumbnailUrl + '" alt="avatar" width="48" height="48" />';
					html+= '	<p class="muted">';
					html+= '	by';
					html+= '	<a href="' + entry.authorProfileUrl + '" rel="author">' + entry.authorName + '</a>';
					html+= '	on';
					html+= '	<time datetime="' + date.toGMTString() + '">' + date.toGMTString() + '</time>';
					html+= '	</p>';
					html+= '	<div class="amun-service-comment-text">' + entry.text + '</div>';
					html+= '</div>';

					$('.amun-service-comment').append(html);
					$('#comment-' + entry.id).fadeIn();
				});
			});

			client.onError(function(msg){
				$('#response').html('<div class="alert alert-error">' + msg + '</div>');
			});

			// transform textarea
			$('#' + cId + ' textarea').each(function(){
				var ref = $(this).attr('id');
				var name = $(this).attr('name');

				$(this).replaceWith('<div style="height:240px;"><div id="' + ref + '" title="' + ref + '" data-name="' + name + '" style="position:absolute;width:550px;height:230px;margin:0 auto;border:1px solid #666;">' + $(this).html() + '</div></div>');

				var editor = ace.edit(ref);
				editor.setTheme("ace/theme/eclipse");

				var mode = require("ace/mode/markdown").Mode;
				editor.getSession().setMode(new mode());

				client.addEditor(ref, editor);
			});

		});

		form.load();
	},

	setSticky: function(id, sticky, url, el){
		var client = new amun.client();

		client.onSuccess(function(){
			location.reload();
		});

		client.onError(function(msg){
			alert(msg);
		});

		client.request(url, 'PUT', {id: id, sticky: sticky});
	},

	setClosed: function(id, closed, url, el){
		var client = new amun.client();

		client.onSuccess(function(){
			location.reload();
		});

		client.onError(function(msg){
			alert(msg);
		});

		client.request(url, 'PUT', {id: id, closed: closed});
	}

};

