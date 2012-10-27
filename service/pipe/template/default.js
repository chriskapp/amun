
amun.services.pipe = {

	loadForm: function(cId, url){

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

				$('#response').html('<div class="alert alert-success">' + msg + '</div>');

			});

			client.onError(function(msg){

				$('#response').html('<div class="alert alert-error">' + msg + '</div>');

			});

		});

	}

};



