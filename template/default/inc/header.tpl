
<div class="pull-right amun-header-info">
	<?php if($user->isAnonymous()): ?>
		<a href="<?php echo $url . 'my/login'; ?>">Sign in</a>
	<?php else: ?>
		You are logged in as <a href="<?php echo $url . 'my'; ?>"><?php echo $user->name; ?></a> <!--(<?php echo $user->group; ?>)-->
		| <a href="<?php echo $url . 'my/logout'; ?>">Sign out</a>
		<?php if($user->isAdministrator()): ?>| <a href="<?php echo $url . 'workbench'; ?>"><b>Workbench</b></a><?php endif; ?>
	<?php endif; ?>
</div>
<h1><a href="<?php echo $url; ?>"><?php echo $registry['core.title']; ?></a></h1>

