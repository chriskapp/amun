
<?php if(isset($error)): ?>

	<div class="alert alert-danger"><?php echo $error; ?></div>

<?php else: ?>

	<form method="POST" action="<?php echo $self; ?>">

	<p>The website <b><?php echo $rpHost; ?></b> tries to get informations
	from your account. If you allow access the following informations will
	be submitted:</p>

	<dl>
		<?php foreach($rpData as $k => $v): ?>
		<dt><?php echo ucfirst($k); ?></dt>
		<dd><?php echo $v; ?></dd>
		<?php endforeach; ?>
	</dl>

	<p>If you remember your decision next time you will be directly
	redirected to the relying party. You can revoke this decision everytime
	in your profile.</p>

	<input type="checkbox" name="remember" id="remember" value="1" />
	<label for="remember"><b>Remember my decision</b></label>

	<hr />

	<p>
		<input type="submit" name="allow" value="Allow" class="btn btn-primary" />
		<input type="submit" name="deny" value="Deny" class="btn btn-default" />
	</p>

	</form>

<?php endif; ?>
