
amun.services.page = {

	showForm: function(url){
		var win = new amun.window(url);
		win.addButton('Preview', 'btn', function(){
			// if we have an ace editor
			var editors = this.getClient().getEditors();
			for (var k in editors) {
				var editor = editors[k];
				var value = editor.getSession().getValue();

				$.post(amun.config.url + 'api/content/page/render', value, function(resp){
					$('#amun-form-window-preview').html(resp).fadeIn();
				});
			}
		});
		win.beforeShow(function(){
			$('#amun-form-window-preview').css('display', 'none');
		});
		win.show();
	}

};



