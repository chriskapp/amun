
testCase('news', {

	view: function(){
		Assert.object(window.amun.user);
		Assert.equals(1, window.amun.user.id);
		Assert.equals('test', window.amun.user.name);
	}

});

