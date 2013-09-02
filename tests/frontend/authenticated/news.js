
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

			// set text
			var editor = ace.edit('afw-text');
			editor.getSession().setValue('foobar');

			// submitting the form triggers a reload therefore our next test is
			// triggered automatically
			$('#amun-form-window-form form').submit();
		});
	},

	testAddedNews: function(){
		// check added news
		Assert.equals('foobar', $('.amun-service-news-entry:first').find('h2').text().trim());
		Assert.equals('foobar', $('.amun-service-news-entry:first').find('.amun-service-news-text').text().trim());

		window.location = $('.amun-service-news-entry:first').find('h2 a').attr('href');
	},

	testAddComment: function(){
		// wait for the form
		Assert.waitFor('#form form', function(){
			// set comment
			var editor = ace.edit('text');
			editor.getSession().setValue('foobar');

			$('#form form').submit();

			Assert.triggerNext();
		});
	},

	testAddedComment: function(){
		// wait for the form
		Assert.waitFor('.amun-service-comment-entry', function(){
			Assert.equals('foobar', $('.amun-service-comment-entry:last').find('.amun-service-comment-text').text().trim());

			Assert.triggerNext();
		});
	}

});

