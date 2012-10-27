
amun.services.comment = {

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

				$('#response').html(msg);

			});

			client.onError(function(msg){

				$('#response').html(msg);

			});

		});

	}

};

