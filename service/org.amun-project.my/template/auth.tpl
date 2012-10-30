
<?php if(isset($error)): ?>

	<div class="alert alert-error"><?php echo $error; ?></div>

<?php elseif(isset($verifier)): ?>

	<p>Your request was approved. Please provide the following verifier to the
	application in order to complete the authentication process.</p>

	<hr />

	<div style="text-align:center;font-size:2em;font-weight:bold;margin:8px;"><?php echo $verifier; ?></div>

<?php else: ?>

	<form method="POST" action="<?php echo $self; ?>">

	<input type="hidden" name="token" value="<?php echo $token; ?>" />

	<?php if(isset($consumerHost)): ?>

		<p>The website <strong><?php echo $consumerHost; ?></strong> tries to access your account.
		Allow only access to your account if you trust this website if you are unsure click "Deny".
		If you allow access the application can act in your name. You can revoke evertyime the
		access of the website. The following informations are provided by the website:</p>

	<?php else: ?>

		<p>An application tries to access you account. Allow only access to your account if you
		trust the application if you are unsure click "Deny". If you allow access the application
		can act in your name. You can revoke evertyime the access of the application. The following
		informations are provided by the application:</p>

	<?php endif; ?>


	<hr />

	<h5><?php echo $consumerTitle; ?></h5>

	<div class="box"><?php echo $consumerDescription; ?></div>

	<hr />

	<p>
		<input class="btn btn-primary" type="submit" name="allow" value="Allow" />
		<input class="btn" type="submit" name="deny" value="Deny" />
	</p>

	</form>

<?php endif; ?>
