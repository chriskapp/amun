
<div class="pull-right amun-header-info">
	<?php if($user->isAnonymous()): ?>
		<a href="<?php echo $url . 'login'; ?>">Sign in</a>
	<?php else: ?>
		You are logged in as <a href="<?php echo $url . 'my'; ?>"><?php echo $user->getName(); ?></a> <!--(<?php echo $user->getGroup(); ?>)-->
		| <a href="<?php echo $url . 'login/logout'; ?>">Sign out</a>
		<?php if($user->isAdministrator()): ?>| <a href="<?php echo $url . 'workbench'; ?>"><b>Workbench</b></a><?php endif; ?>
	<?php endif; ?>
</div>
<h1><a href="<?php echo $url; ?>"><?php echo $registry['core.title']; ?></a></h1>

