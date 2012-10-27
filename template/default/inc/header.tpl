
<div class="pull-right amun-header-info">
	<?php if($user->isAnonymous()): ?>
		<a href="<?php echo $url . 'my.htm/login'; ?>">Sign in</a>
	<?php else: ?>
		You are logged in as <a href="<?php echo $url . 'my.htm'; ?>"><?php echo $user->name; ?></a> <!--(<?php echo $user->group; ?>)-->
		| <a href="<?php echo $url . 'my.htm/logout'; ?>">Sign out</a>
		<?php if($user->isAdministrator()): ?>| <a href="<?php echo $url . 'workbench'; ?>"><b>Workbench</b></a><?php endif; ?>
	<?php endif; ?>
</div>
<h1><a href="<?php echo $url; ?>"><?php echo $registry['core.title']; ?></a></h1>

<nav class="amun-nav">
	<ul>
		<?php foreach($nav as $item): ?>
			<?php if($item['selected']): ?>
				<li class="active"><a href="<?php echo $item['href']; ?>"><?php echo $item['title']; ?></a></li>
			<?php else: ?>
				<li><a href="<?php echo $item['href']; ?>"><?php echo $item['title']; ?></a></li>
			<?php endif; ?>
		<?php endforeach; ?>
	</ul>
	<br style="clear:left;" />
</nav>

<div class="amun-path">
	<p>Location: <?php foreach($path as $item): ?><a href="<?php echo $item['href']; ?>"><?php echo $item['name']; ?></a> / <?php endforeach; ?></p>
</div>
