
<title><?php echo $registry['core.title']; ?> / <?php echo $page->title; ?></title>
<meta charset="UTF-8" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="X-XRDS-Location" content="<?php echo $url; ?>api/meta/xrds" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<?php echo $htmlCss->toString() . "\n"; ?>
<?php echo $htmlJs->toString() . "\n"; ?>
<?php echo $htmlContent->get(Amun_Html_Content::META); ?>

