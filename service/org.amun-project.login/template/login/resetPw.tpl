
<?php if(isset($success)): ?>

	<div class="alert alert-sccuess">You have successful reset your password.
	We have send your new password to the provided email address.</div>

<?php elseif(isset($error)): ?>

	<div class="alert alert-error"><?php echo $error; ?></div>

<?php endif; ?>
