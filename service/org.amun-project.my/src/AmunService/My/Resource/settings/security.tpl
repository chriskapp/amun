
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

	<div class="col-md-2 amun-service-my-settings-nav">
		<ul class="nav nav-stacked">
			<li><h4>Settings</h4></li>
			<?php foreach($optionsSettings as $option): ?>
			<li><a href="<?php echo $option['href']; ?>"><?php echo $option['name']; ?></a></li>
			<?php endforeach; ?>
		</ul>
	</div>

	<div class="col-md-10">

		<p>With this form you can change your password. Note if you have logged in via
		OpenId you are not able to change your password.</p>

		<form method="post">

		<?php if(isset($success)): ?>
			<div class="alert alert-success">You have successful changed your password</div>
		<?php elseif(isset($error)): ?>
			<div class="alert alert-danger"><?php echo $error; ?></div>
		<?php endif; ?>

		<div class="form-group">
			<label for="current_password">Current password</label>
			<input type="password" name="current_password" id="current_password" class="form-control" />
		</div>

		<div class="form-group">
			<label for="new_password">New password</label>
			<input type="password" name="new_password" id="new_password" class="form-control" />
		</div>

		<div class="form-group">
			<label for="verify_password">Verify Password</label>
			<input type="password" name="verify_password" id="verify_password" class="form-control" />
		</div>

		<input class="btn btn-primary" type="submit" value="Change password" />

		</form>

	</div>

</div>


