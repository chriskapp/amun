<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title id="title">Amun (<?php echo Amun_Base::getVersion(); ?>) installation</title>
	<link href="<?php echo $base; ?>/css/bootstrap.min.css" rel="stylesheet" />
	<link rel="stylesheet" type="text/css" href="<?php echo $base; ?>/css/install.css" />
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js"></script>
	<script type="text/javascript">
	var psx_url = '<?php echo $config['psx_url']; ?>';
	var length;
	var steps = [];
	var step;

	function addStep(path, title, callback)
	{
		steps.push({

			path: path,
			title: title,
			callback: callback

		});
	}

	function loadStep()
	{
		if(steps.length > 0)
		{
			step = steps.shift()

			var path  = step.path;
			var title = step.title;
			var cb    = step.callback;
			var data  = typeof(cb) != 'undefined' ? cb.call(this) : {};

			$('#progressStatus').html(title);

			$.post(psx_url + '/install.php/' + path, data, function(response){

				if(response.success)
				{
					var per = steps.length * 100 / length;

					$('#console').append('[OK] ' + step.title + "\n");
					$('#progressBar').css('width', (100 - per) + '%');

					loadStep();
				}
				else
				{
					// if something went wrong add the step wich failed to the
					// stack
					steps.unshift(step);

					$('#console').append('[FAILED] ' + step.title + "\n" + response.msg + "\n");
					$('#progressStatus').html('An error occured. Click <a href="#" onclick="$(\'#consoleWindow\').slideDown();">here</a> to see the logs');

					$('#submitButton').val('Retry');
				}

			}, 'json');
		}
		else
		{
			$('#progressStatus').html('Installation of amun was successful!');

			// redirect after one second
			window.setTimeout(function(){

				window.location.href = psx_url;

			}, 1000);
		}
	}

	function submitForm()
	{
		if(validateName() && validatePw() && validateEmail() && validateTitle() && validateSubTitle())
		{
			$('#console').html('');
			$('.progress').fadeIn();

			length = steps.length;

			loadStep();

			return true;
		}
		else
		{
			return false;
		}
	}

	function validateName()
	{
		var pattern = /^[a-zA-Z0-9\.]{3,32}$/;

		if(!pattern.test($('#administratorName').val()))
		{
			$('#administratorName').focus();

			$('#nameError').parents('.control-group').removeClass('success').addClass('error');
			$('#nameError').html('Invalid length min 3 and max 32 signs. Must contain only a-z, A-Z, 0-9');

			return false;
		}
		else
		{
			$('#nameError').parents('.control-group').removeClass('error').addClass('success');
			$('#nameError').html('');

			return true;
		}
	}

	function validatePw()
	{
		var config = {
			min_pw_length: 6,
			max_pw_length: 128,
			pw_alpha_count: 4,
			pw_numeric_count: 2,
			pw_special_count: 0
		};

		var pw = $('#administratorPw').val();
		var isValid = true;

		if(pw.length < config.min_pw_length || pw.length > config.max_pw_length)
		{
			isValid = false;
		}

		var alpha   = 0;
		var numeric = 0;
		var special = 0;

		for(var i = 0; i < pw.length; i++)
		{
			var charCode = pw.charCodeAt(i);

			if((charCode >= 0x41 && charCode <= 0x5A) || (charCode >= 0x61 && charCode <= 0x7A))
			{
				alpha++;
			}
			else if(charCode >= 0x30 && charCode <= 0x39)
			{
				numeric++;
			}
			else
			{
				special++;
			}
		}

		if(alpha < config.pw_alpha_count)
		{
			isValid = false;
		}

		if(numeric < config.pw_numeric_count)
		{
			isValid = false;
		}

		if(special < config.pw_special_count)
		{
			isValid = false;
		}

		if(!isValid)
		{
			$('#administratorPw').focus();

			$('#pwError').parents('.control-group').removeClass('success').addClass('error');
			$('#pwError').html('Invalid length min ' + config.min_pw_length + ' and max ' + config.max_pw_length + ' signs. Must contain ' + config.pw_alpha_count + ' alpha, ' + config.pw_numeric_count + ' numeric and ' + config.pw_special_count + ' special signs');

			return false;
		}
		else
		{
			$('#pwError').parents('.control-group').removeClass('error').addClass('success');
			$('#pwError').html('');

			return true;
		}
	}

	function validateEmail()
	{
		var pattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;

		if(!pattern.test($('#administratorEmail').val()))
		{
			$('#administratorEmail').focus();

			$('#emailError').parents('.control-group').removeClass('success').addClass('error');
			$('#emailError').html('Must be a valid Email format "user@domain.tld"');

			return false;
		}
		else
		{
			$('#emailError').parents('.control-group').removeClass('error').addClass('success');
			$('#emailError').html('');

			return true;
		}
	}

	function validateTitle()
	{
		if($('#settingsTitle').val().length < 3 || $('#settingsTitle').val().length > 64)
		{
			$('#settingsTitle').focus();

			$('#titleError').parents('.control-group').removeClass('success').addClass('error');
			$('#titleError').html('Invalid length min 3 and max 64 signs');

			return false;
		}
		else
		{
			$('#titleError').parents('.control-group').removeClass('error').addClass('success');
			$('#titleError').html('');

			return true;
		}
	}

	function validateSubTitle()
	{
		if($('#settingsSubTitle').val().length > 128)
		{
			$('#settingsSubTitle').focus();

			$('#subTitleError').parents('.control-group').removeClass('success').addClass('error');
			$('#subTitleError').html('Invalid length max 128 signs');

			return false;
		}
		else
		{
			$('#subTitleError').parents('.control-group').removeClass('error').addClass('success');
			$('#subTitleError').html('');

			return true;
		}
	}

	addStep('setupCheckRequirements', 'Check requirements ...');
	addStep('setupCreateTables', 'Create tables ...');
	addStep('setupInsertData', 'Insert data ...');
	addStep('setupInstallService', 'Install services ...');
	addStep('setupInsertRegistry', 'Insert registry ...', function(){
		return {
			title: $('#settingsTitle').val(),
			subTitle: $('#settingsSubTitle').val()
		};
	});
	addStep('setupInsertGroup', 'Insert group ...');
	addStep('setupInsertAdmin', 'Insert admin ...', function(){
		return {
			name: $('#administratorName').val(),
			pw: $('#administratorPw').val(),
			email: $('#administratorEmail').val()
		};
	});
	addStep('setupInsertApi', 'Insert api ...');
	addStep('setupInstallSample', 'Install samples ...');
	</script>
</head>
<body>

<div class="container">
	<h1>Amun Installation</h1>
	<div class="progress" style="display:none">
		<div id="progressStatus"></div>
		<div id="progressBar" class="bar" style="width:0%;"></div>
	</div>
	<form class="form-horizontal">
	<fieldset>
		<legend>Administrator</legend>
		<div class="control-group">
			<label class="control-label" for="administratorName">Name</label>
			<div class="controls">
				<input type="text" id="administratorName" name="administratorName" value="<?php echo $administratorName; ?>" onchange="validateName();" />
				<span class="help-block" id="nameError"></span>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="administratorPw">Password</label>
			<div class="controls">
				<input type="password" id="administratorPw" name="administratorPw" value="<?php echo $administratorPw; ?>" onchange="validatePw();" />
				<span class="help-block" id="pwError"></span>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="administratorEmail">Email</label>
			<div class="controls">
				<input type="text" id="administratorEmail" name="administratorEmail" value="<?php echo $administratorEmail; ?>" onchange="validateEmail();" />
				<span class="help-block" id="emailError"></span>
			</div>
		</div>
	</fieldset>

	<fieldset>
		<legend>Settings</legend>
		<div class="control-group">
			<label class="control-label" for="settingsTitle">Title</label>
			<div class="controls">
				<input type="text" id="settingsTitle" name="settingsTitle" value="<?php echo $settingsTitle; ?>" onchange="validateTitle();" />
				<span class="help-block" id="titleError"></span>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="settingsSubTitle">Sub Title</label>
			<div class="controls">
				<input type="text" id="settingsSubTitle" name="settingsSubTitle" value="<?php echo $settingsSubTitle; ?>" onchange="validateSubTitle();" />
				<span class="help-block" id="subTitleError"></span>
			</div>
		</div>
	</fieldset>
	</form>

	<div id="consoleWindow" style="display:none;">
		<fieldset>
			<legend>Console</legend>
			<textarea id="console"></textarea>
			<p><input class="btn" type="button" value="Close" onclick="$('#consoleWindow').fadeOut();" /></p>
		</fieldset>
	</div>

	<p>
		<input class="btn btn-primary pull-right" type="button" id="submitButton" onclick="submitForm();" value="Install" />
	</p>
</div>

</body>
</html>