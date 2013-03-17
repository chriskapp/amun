
<nav>
	<ul>
		<?php foreach($navigation as $item): ?>
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
