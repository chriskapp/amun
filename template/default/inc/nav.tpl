
<?php if($navigation instanceof \Amun\Navigation): ?>
<nav>
	<ul class="nav nav-pills">
		<?php foreach($navigation as $item): ?>
			<?php if($item['selected']): ?>
				<li class="active"><a href="<?php echo $item['href']; ?>"><?php echo $item['title']; ?></a></li>
			<?php else: ?>
				<li><a href="<?php echo $item['href']; ?>"><?php echo $item['title']; ?></a></li>
			<?php endif; ?>
		<?php endforeach; ?>
	</ul>
	<div class="clearfix"></div>
</nav>
<?php endif; ?>

<?php if($path instanceof \Amun\Path): ?>
<div class="amun-path">
	<ol class="breadcrumb">
		<?php foreach($path as $item): ?>
		<li><a href="<?php echo $item['href']; ?>"><?php echo $item['name']; ?></a></li>
		<?php endforeach; ?>
	</ol>
	
</div>
<?php endif; ?>
