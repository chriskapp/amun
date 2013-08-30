<!DOCTYPE html>
<html lang="en">
<head>
	<?php include($location . '/inc/meta.tpl'); ?>
</head>
<body>

<?php echo $htmlContent->get(\Amun\Html\Content::HEADER); ?>

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
			<div class="col-md-12">
				<?php echo $content; ?>
			</div>
			<div class="col-md-12">
				<?php if($gadget->valid()): ?>
				<div class="amun-gadget">
					<?php echo $gadget->get()->getBody(); ?>
				</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>

<footer class="amun-footer">
	<div class="container">
		<?php include($location . '/inc/footer.tpl'); ?>
	</div>
</footer>

<?php echo $htmlContent->get(\Amun\Html\Content::FOOTER); ?>

</body>
</html>
