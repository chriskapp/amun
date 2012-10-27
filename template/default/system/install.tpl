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

			$('#status').html(title);

			$.post(psx_url + '/install.php/install/' + path, data, function(response){

				if(response.success)
				{
					$('#console').append('[OK] ' + step.title + "\n");

					loadStep();
				}
				else
				{
					// if something went wrong add the step wich failed to the
					// stack
					steps.unshift(step);

					$('#console').append('[FAILED] ' + step.title + "\n" + response.msg + "\n");

					$('#status').html('An error occured. Click <a href="#" onclick="$(\'#consoleWindow\').fadeIn();">here</a> to see the logs');

					$('#submitButton').val('Retry');
				}

			}, 'json');
		}
		else
		{
			$('#status').html('Installation of amun was successful!');

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
			loadStep();

			$('#status').fadeIn();

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
			$('#administratorName').attr('class', 'invalid');
			$('#administratorName').focus();

			$('#nameError').html('Invalid length min 3 and max 32 signs. Must contain only a-z, A-Z, 0-9');
			$('#nameError').fadeIn();

			return false;
		}
		else
		{
			$('#administratorName').removeAttr('class');

			$('#nameError').fadeOut();

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
			$('#administratorPw').attr('class', 'invalid');
			$('#administratorPw').focus();

			$('#pwError').html('Invalid length min ' + config.min_pw_length + ' and max ' + config.max_pw_length + ' signs. Must contain ' + config.pw_alpha_count + ' alpha, ' + config.pw_numeric_count + ' numeric and ' + config.pw_special_count + ' special signs');
			$('#pwError').fadeIn();

			return false;
		}
		else
		{
			$('#administratorPw').removeAttr('class');

			$('#pwError').fadeOut();

			return true;
		}
	}

	function validateEmail()
	{
		var pattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;

		if(!pattern.test($('#administratorEmail').val()))
		{
			$('#administratorEmail').attr('class', 'invalid');
			$('#administratorEmail').focus();

			$('#emailError').html('Must be a valid Email format "user@domain.tld"');
			$('#emailError').fadeIn();

			return false;
		}
		else
		{
			$('#administratorEmail').removeAttr('class');

			$('#emailError').fadeOut();

			return true;
		}
	}

	function validateTitle()
	{
		if($('#settingsTitle').val().length < 3 || $('#settingsTitle').val().length > 64)
		{
			$('#settingsTitle').attr('class', 'invalid');
			$('#settingsTitle').focus();

			$('#titleError').html('Invalid length min 3 and max 64 signs');
			$('#titleError').fadeIn();

			return false;
		}
		else
		{
			$('#settingsTitle').removeAttr('class');

			$('#titleError').fadeOut();

			return true;
		}
	}

	function validateSubTitle()
	{
		if($('#settingsSubTitle').val().length > 128)
		{
			$('#settingsSubTitle').attr('class', 'invalid');
			$('#settingsSubTitle').focus();

			$('#subTitleError').html('Invalid length max 128 signs');
			$('#subTitleError').fadeIn();

			return false;
		}
		else
		{
			$('#settingsSubTitle').removeAttr('class');

			$('#subTitleError').fadeOut();

			return true;
		}
	}


	addStep('setupCheckRequirements', 'Check requirements ...');
	addStep('setupCreateTables', 'Create tables ...');
	addStep('setupInsertData', 'Insert data ...');
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
	addStep('setupInstallService', 'Install services ...');
	addStep('setupInstallSample', 'Install samples ...');
	</script>
</head>
<body>


<div id="consoleWindow" style="display:none;">
	<textarea readonly="readonly" id="console"></textarea>
	<p><input type="button" value="Close" onclick="$('#consoleWindow').fadeOut();" /></p>
</div>

<div class="container">
	<div class="alert alert-info" id="status" style="display:none;"></div>
	<fieldset>
		<legend>Administrator</legend>
		<p>
			<label for="administratorName">Name</label>
			<input type="text" id="administratorName" name="administratorName" value="<?php echo $administratorName; ?>" onchange="validateName();" />
			<p class="alert alert-error" id="nameError" style="display:none;"></p>
		</p>
		<p>
			<label for="administratorPw">Password</label>
			<input type="password" id="administratorPw" name="administratorPw" value="<?php echo $administratorPw; ?>" onchange="validatePw();" />
			<p class="alert alert-error" id="pwError" style="display:none;"></p>
		</p>
		<p>
			<label for="administratorEmail">Email</label>
			<input type="text" id="administratorEmail" name="administratorEmail" value="<?php echo $administratorEmail; ?>" onchange="validateEmail();" />
			<p class="alert alert-error" id="emailError" style="display:none;"></p>
		</p>
	</fieldset>

	<fieldset>
		<legend>Settings</legend>
		<p>
			<label for="settingsTitle">Title</label>
			<input type="text" id="settingsTitle" name="settingsTitle" value="<?php echo $settingsTitle; ?>" onchange="validateTitle();" />
			<p class="alert alert-error" id="titleError" style="display:none;"></p>
		</p>
		<p>
			<label for="settingsSubTitle">Sub Title</label>
			<input type="text" id="settingsSubTitle" name="settingsSubTitle" value="<?php echo $settingsSubTitle; ?>" onchange="validateSubTitle();" />
			<p class="alert alert-error" id="subTitleError" style="display:none;"></p>
		</p>
	</fieldset>

	<p>
		<input class="btn btn-primary" type="button" id="submitButton" onclick="submitForm();" value="Install" />
	</p>
</div>

</body>
</html>