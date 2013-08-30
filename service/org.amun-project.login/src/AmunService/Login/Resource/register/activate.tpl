
<?php if(isset($success)): ?>

	<div class="alert alert-success">You have successful activate your account.
	You can now <a href="<?php echo $page->getUrl() . '/login'; ?>"><strong>login</strong></a> with your credentials</div>

<?php elseif(isset($error)): ?>

	<div class="alert alert-danger"><?php echo $error; ?></div>

<?php endif; ?>
