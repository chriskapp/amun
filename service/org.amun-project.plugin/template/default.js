
amun.services.plugin = {

	loadForm: function(cId, url)
	{
		var form = new amun.form(cId, url);

		form.onError(function(msg){

			$('#response').html(msg);

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

				location = amun.util.getParentLocation();

			});

			client.onError(function(msg){

				$('#response').html(msg);

			});


			// transform textarea
			$('#' + cId + ' textarea').each(function(){

				var ref = $(this).attr('id');

				$(this).replaceWith('<div style="height:400px;"><div id="' + ref + '" title="' + ref + '" style="position:absolute;width:920px;height:400px;margin:0 auto;border:1px solid #666;">' + $(this).html() + '</div></div>');

				var editor = ace.edit(ref);

				editor.setTheme("ace/theme/eclipse");

				var mode = require("ace/mode/html").Mode;
				editor.getSession().setMode(new mode());

				amun.store.editors[ref] = editor;

			});

		});

	},

	loadCommentForm: function(cId, url){

		var form = new amun.form(cId, url);

		form.onError(function(msg){

			$('#response').html(msg);

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

				$('#response').html(msg);

			});

			// transform textarea
			$('#' + cId + ' textarea').each(function(){

				var ref = $(this).attr('id');

				$(this).replaceWith('<div style="height:240px;"><div id="' + ref + '" title="' + ref + '" style="position:absolute;width:550px;height:230px;margin:0 auto;border:1px solid #666;">' + $(this).html() + '</div></div>');

				var editor = ace.edit(ref);

				editor.setTheme("ace/theme/eclipse");

				var mode = require("ace/mode/markdown").Mode;
				editor.getSession().setMode(new mode());

				amun.store.editors[ref] = editor;

			});

		});

	}

};


