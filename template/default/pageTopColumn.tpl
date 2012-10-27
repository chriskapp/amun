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

<div class="amun-body">
	<div class="container">
		<div class="row">
			<div class="span12">
				<div class="amun-gadget">
					<?php echo $gadget->get(); ?>
				</div>
			</div>
			<div class="span12">
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
