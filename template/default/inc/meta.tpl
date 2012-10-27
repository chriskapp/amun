
<title><?php echo $registry['core.title']; ?> / <?php echo $page->title; ?></title>
<meta charset="UTF-8" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="X-XRDS-Location" content="<?php echo $url; ?>api/meta/xrds" />
<link href="<?php echo $base; ?>/css/bootstrap.min.css" rel="stylesheet" />
<?php echo $htmlCss . "\n"; ?>
<?php echo $htmlJs . "\n"; ?>
<?php echo $htmlContent->get(Amun_Html_Content::META); ?>

