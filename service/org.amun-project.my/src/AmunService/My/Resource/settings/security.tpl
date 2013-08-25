
<?php if(count($options) > 0): ?>
	<div class="amun-options">
		<ul class="nav nav-tabs">
			<?php foreach($options as $option): ?>
			<li><a href="<?php echo $option['href']; ?>"><?php echo $option['name']; ?></a></li>
			<?php endforeach; ?>
		</ul>
	</div>
<?php endif; ?>

<div class="row amun-service-my-settings">

	<div class="span2 amun-service-my-settings-nav">
		<ul class="nav nav-list">
			<li class="nav-header">Settings</li>
			<?php foreach($optionsSettings as $option): ?>
			<li><a href="<?php echo $option['href']; ?>"><?php echo $option['name']; ?></a></li>
			<?php endforeach; ?>
		</ul>
	</div>

	<div class="span10">

		<p>With this form you can change your password. Note if you have logged in via
		OpenId you are not able to change your password.</p>

		<form method="post">

		<?php if(isset($success)): ?>
			<div class="alert alert-success">You have successful changed your password</div>
		<?php elseif(isset($error)): ?>
			<div class="alert alert-error"><?php echo $error; ?></div>
		<?php endif; ?>

		<p>
			<label for="current_password">Current password</label>
			<input type="password" name="current_password" id="current_password" />
		</p>

		<p>
			<label for="new_password">New password</label>
			<input type="password" name="new_password" id="new_password" />
		</p>

		<p>
			<label for="verify_password">Verify Password</label>
			<input type="password" name="verify_password" id="verify_password" />
		</p>

		<p>
			<input class="btn btn-primary" type="submit" value="Change password" />
		</p>

		</form>

	</div>

	<hr />

</div>


