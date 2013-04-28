
amun.services.php = {

	loadForm: function(cId, url){
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
				$('#response').html('<div class="alert alert-success">' + msg + '</div>');
			});

			client.onError(function(msg){
				$('#response').html('<div class="alert alert-error">' + msg + '</div>');
			});

			// transform textarea
			$('textarea').each(function(){
				var ref = $(this).attr('id');

				$(this).replaceWith('<div style="height:400px;"><div id="' + ref + '" title="' + ref + '" style="position:absolute;width:920px;height:400px;margin:0 auto;border:1px solid #666;">' + $(this).html() + '</div></div>');

				var editor = ace.edit(ref);
				editor.setTheme("ace/theme/eclipse");

				var mode = require("ace/mode/php").Mode;
				editor.getSession().setMode(new mode());

				amun.store.editors[ref] = editor;
			});

		});

		form.load();
	}

};



