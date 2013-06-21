
amun.services.php = {

	showForm: function(url){
		var win = new amun.window(url);
		win.onEditorCreate(function(editor){
			var mode = require("ace/mode/php").Mode;
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
		win.show();
	}

};
