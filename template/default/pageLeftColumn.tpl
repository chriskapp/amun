<!DOCTYPE html>
<html lang="en">
<head>
	<?php include($location . '/inc/meta.tpl'); ?>
</head>
<body>

<?php echo $htmlContent->get(Amun_Html_Content::HEADER); ?>

<header class="amun-header">
	<div class="container">
		<?php include($location . '/inc/header.tpl'); ?>
	</div>
</header>

<div class="amun-nav">
	<div class="container">
		<?php include($location . '/inc/nav.tpl'); ?>
	</div>
</div>

<div class="amun-body">
	<div class="container">
		<div class="row">
			<div class="span4">
				<?php foreach($gadget as $g): ?>
				<div class="amun-gadget">
					<h2><?php echo $g->getTitle(); ?></h2>
					<?php echo $g->getBody(); ?>
				</div>
				<?php endforeach; ?>
			</div>
			<div class="span8">
				<?php echo $content; ?>
			</div>
		</div>
	</div>
</div>

<footer class="amun-footer">
	<div class="container">
		<?php include($location . '/inc/footer.tpl'); ?>
	</div>
</footer>

<?php echo $htmlContent->get(Amun_Html_Content::FOOTER); ?>

</body>
</html>
