
<?php if(isset($error)): ?>

	<div class="alert alert-danger"><?php echo $error; ?></div>

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

	<h3><?php echo $consumerTitle; ?></h3>

	<div class="box"><?php echo $consumerDescription; ?></div>

	<hr />

	<h4><a href="#" onclick="$('.amun-service-login-app-rights').slideToggle();">Rights (+)</a></h4>

	<div class="amun-service-login-app-rights">
		<ul>
			<?php foreach($userRights as $right): ?>
				<li>
					<label for="right-<?php echo $right['rightId']; ?>" class="checkbox inline" style="white-space:nowrap;">
					<input checked="checked" type="checkbox" name="right-<?php echo $right['rightId']; ?>" id="right-<?php echo $right['rightId']; ?>" value="1" /> <?php echo $right['rightDescription']; ?>
					</label>
				</li>
			<?php endforeach; ?>
		</ul>
		<hr />
	</div>

	<br />

	<p>
		<input class="btn btn-primary" type="submit" name="allow" value="Allow" />
		<input class="btn" type="submit" name="deny" value="Deny" />
	</p>

	</form>

<?php endif; ?>
