
amun.services.page = {

	showForm: function(url){
		var win = new amun.window(url);
		win.onEditorCreate(function(editor){
			var mode = require("ace/mode/html").Mode;
			editor.getSession().setMode(new mode());

			editor.commands.addCommand({
				name: 'save',
				bindKey: {win: 'Ctrl-S',  mac: 'Command-S'},
				exec: function(editor) {
					$('#amun-form-window').find('form').submit();
				},
				readOnly: true
			});
		});
		win.beforeShow(function(){
			$('#amun-form-window-preview').css('display', 'none');
		});
		win.onSuccess(function(){
			var editors = this.getClient().getEditors();
			for (var k in editors) {
				var editor = editors[k];
				var value = editor.getSession().getValue();

				$.post(amun.config.url + 'api/content/page/render', value, function(resp){
					$('.amun-service-page-content').html(resp);
				});
				break;
			}
		});
		win.show();
	}

};



