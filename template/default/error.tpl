<!DOCTYPE html>
<html>
<head>
	<title>Exception</title>
	<meta charset="UTF-8" />
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta http-equiv="X-XRDS-Location" content="<?php echo $url; ?>api/meta/xrds" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<link rel="stylesheet" href="http://127.0.0.1/projects/amun/public/index.php/api/asset/css?services=default|page" type="text/css" />
</head>
<body>

<header class="amun-header">
	<div class="container">
		<h1><a href="<?php echo $url; ?>">Error</a></h1>
	</div>
</header>

<div class="amun-nav">
	<div class="container">
		<nav>
			<ul class="nav nav-pills">
				<li class="active"><a href="<?php echo $url; ?>">Home</a></li>
			</ul>
			<div class="clearfix"></div>
		</nav>
		<div class="amun-path">
			<ol class="breadcrumb">
				<li><a href="<?php echo $url; ?>">Error</a></li>
			</ol>
		</div>
	</div>
</div>

<div class="amun-body">
	<div class="container">
		<div class="amun-service-page">
			<div class="amun-service-page-entry">
				<div class="amun-service-page-content">
					<h1>Internal Server Error</h1>
					<p><?php echo $message; ?></p>
					<?php if(!empty($trace)): ?>
						<p><pre><?php echo $trace; ?></pre></p>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</div>

<footer class="amun-footer">
	<div class="container">
		<?php include($location . '/inc/footer.tpl'); ?>
	</div>
</footer>


</body>
</html>
