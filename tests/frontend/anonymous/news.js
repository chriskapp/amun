
testCase('news', {

	view: function(){
		Assert.object(window.amun.user);
		Assert.equals(2, window.amun.user.id);
		Assert.equals('Anonymous', window.amun.user.name);
	}

});

