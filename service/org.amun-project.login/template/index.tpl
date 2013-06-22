
<div class="row amun-service-login">

	<form method="POST">
	<input type="hidden" name="redirect" value="<?php echo $redirect; ?>" />

	<div class="span8 amun-service-login-description">

		<h3>Sign in to personalize your experience</h3>

		<p>To sign in enter as identity your <strong>Email</strong> address and password or your <strong>OpenID</strong>.<br />
		<b><?php echo $registry['core.title']; ?></b> supports OpenID if you enter as identity an Google, Yahoo
		or AOL email address you dont need an password because these providers are supporting <a href="http://openid.net/">OpenID</a>.</p>

		<hr />

		<p>If you dont have an account and want to register with an Email address and password click
		on the "Register" button and follow the instructions.</p>

	</div>
	<div class="span4 amun-service-login-form">

		<h3>Login</h3>

		<?php if(isset($error)): ?>

			<div class="alert alert-error">
				<img src="<?php echo $base; ?>/img/icons/login/exclamation.png" />
				<?php echo $error; ?>
			</div>

		<?php endif; ?>

		<p id="form-identity">
			<label for="identity">Identity:</label>
			<input type="text" name="identity" id="identity" value="" maxlength="256" placeholder="Email or OpenID" />
		</p>

		<p id="form-pw">
			<label for="pw">Password:</label>
			<input type="password" name="pw" id="pw" value="" maxlength="64" placeholder="Password" />
		</p>

		<?php if(isset($captcha)): ?>
		<p>
			<label for="captcha">Captcha:</label>
			<img src="<?php echo $captcha; ?>" alt="Captcha" id="amun-service-my-register-form-captcha" /><br />
			<input type="text" name="captcha" id="captcha" value="" maxlength="64" />
		</p>
		<?php endif; ?>

		<p>
			<input class="btn btn-primary" type="submit" id="login" name="login" value="Login" />
			<input class="btn" type="submit" id="register" name="register" value="Register" />
		</p>

		<div class="help-block amun-service-login-form-help">
			<br />
			<small><a href="<?php echo $page->getUrl() . '/recover'; ?>">Can't access your account?</a></small>
		</div>

	</div>
	
	</form>

</div>

<script type="text/javascript">
$(document).ready(function(){

	$('#identity').change(amun.services.login.detection);

});
</script>


