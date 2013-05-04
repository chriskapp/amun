
testCase('news', {

	view: function(){
		Assert.object(window.amun.user);
		Assert.equals(1, window.amun.user.id);
		Assert.equals('test', window.amun.user.name);

		Assert.triggerNext();
	},

	testAddNews: function(baseUrl){
		// call show form
		amun.services.news.showForm(baseUrl + 'api/news/form?format=json&method=create&pageId=7');

		// wait for the form
		Assert.waitFor('#amun-form-window-form form', function(){
			// set title
			$('#afw-title').val('foobar');

			// enter form
			var editor = ace.edit('afw-text');
			editor.getSession().setValue('foobar');

			// submitting the form triggers a reload therefore our next test is
			// triggered automatically
			$('#amun-form-window-form form').submit();
		});
	},

	testNewsAddedContent: function(){
		// check added news
		Assert.equals('foobar', $('#news-2').find('h2').text().trim());
		Assert.equals('foobar', $('#news-2').find('.amun-service-news-text').text().trim());

		Assert.triggerNext();
	}

});

