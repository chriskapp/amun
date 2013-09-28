<html>
<head>
	<title>Amun (<?php echo \Amun\Base::getVersion(); ?>) Workbench</title>
	<link rel="stylesheet" type="text/css" href="http://cdn.sencha.io/ext-4.2.0-gpl/resources/css/ext-all.css">
	<!--<link rel="stylesheet" type="text/css" href="http://cdn.sencha.io/ext-4.2.0-gpl/resources/ext-theme-neptune/ext-theme-neptune-all.css">-->
	<link rel="stylesheet" type="text/css" href="<?php echo $base; ?>/css/workbench.css">
	<script type="text/javascript">
	var psx_url = '<?php echo $config['psx_url']; ?>';
	var base_url = '<?php echo $base; ?>';
	var url = '<?php echo $url; ?>';
	</script>
	<script type="text/javascript" src="http://cdn.sencha.io/ext-4.2.0-gpl/ext-all.js"></script>
	<script type="text/javascript" src="<?php echo $url; ?>api/asset/js?services=ace"></script>
	<script type="text/javascript" src="<?php echo $base; ?>/js/workbench/app.js"></script>
</head>
<body>

</body>
</html>