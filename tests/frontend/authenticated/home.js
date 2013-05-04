
testCase('home', {

	view: function(){
		Assert.object(window.amun.user);
		Assert.equals(1, window.amun.user.id);
		Assert.equals('test', window.amun.user.name);

		Assert.triggerNext();
	},

	testPageEdit: function(){
		// call show form
		amun.services.page.showForm('http://127.0.0.1/projects/amun/public/index.php/api/page/form?format=json&method=update&id=1');

		// wait for the form
		Assert.waitFor('#amun-form-window-form form', function(){
			// enter form
			var editor = ace.edit('afw-content');
			editor.getSession().setValue('foobar');

			// submit form should not force a page reload since we have a custom 
			// form submit event wich sends an ajax request
			$('#amun-form-window-form form').submit();

			// wait for success div
			Assert.waitFor('.alert-success', function(){
				Assert.exists('.alert-success');
				Assert.equals('You have successful edit a page', $('.alert-success').text());

				location.reload();
			});
		});
	},

	testPageEditedContent: function(){
		Assert.equals('foobar', $('.amun-service-page-content').html());

		Assert.triggerNext();
	}

});

