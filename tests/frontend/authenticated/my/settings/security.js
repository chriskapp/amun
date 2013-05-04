
testCase('my/settings/security', {

	view: function(){
		Assert.object(window.amun.user);
		Assert.equals(1, window.amun.user.id);
		Assert.equals('test', window.amun.user.name);

		Assert.triggerNext();
	},

	changePasswordCorrect: function(){
		document.getElementById('current_password').value = 'test123';
		document.getElementById('new_password').value = 'test123';
		document.getElementById('verify_password').value = 'test123';
		document.getElementsByTagName('form')[0].submit();
	},

	testChangePasswordCorrect: function(){
		Assert.exists('.alert-success');
		Assert.triggerNext();
	},

	changePasswordWrongCurrentPassword: function(){
		document.getElementById('current_password').value = 'test1234';
		document.getElementById('new_password').value = 'test123';
		document.getElementById('verify_password').value = 'test123';
		document.getElementsByTagName('form')[0].submit();
	},

	testChangePasswordWrongCurrentPassword: function(){
		Assert.exists('.alert-error');
		Assert.triggerNext();
	},

	changePasswordWrongCurrentComplexity: function(){
		document.getElementById('current_password').value = 'test';
		document.getElementById('new_password').value = 'test123';
		document.getElementById('verify_password').value = 'test123';
		document.getElementsByTagName('form')[0].submit();
	},

	testChangePasswordWrongCurrentComplexity: function(){
		Assert.exists('.alert-error');
		Assert.triggerNext();
	},

	changePasswordNotMatch: function(){
		document.getElementById('current_password').value = 'test123';
		document.getElementById('new_password').value = 'test1234';
		document.getElementById('verify_password').value = 'test123';
		document.getElementsByTagName('form')[0].submit();
	},

	testChangePasswordNotMatch: function(){
		Assert.exists('.alert-error');
		Assert.triggerNext();
	},

	changePasswordWrongComplexity: function(){
		document.getElementById('current_password').value = 'test123';
		document.getElementById('new_password').value = 'test';
		document.getElementById('verify_password').value = 'test';
		document.getElementsByTagName('form')[0].submit();
	},

	testChangePasswordWrongComplexity: function(){
		Assert.exists('.alert-error');
		Assert.triggerNext();
	}

});

