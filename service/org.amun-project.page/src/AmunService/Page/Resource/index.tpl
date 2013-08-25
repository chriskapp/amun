
<?php if(count($options) > 0): ?>
	<div class="amun-options">
		<ul class="nav nav-tabs">
			<?php foreach($options as $option): ?>
			<li><a href="<?php echo $option['href']; ?>"><?php echo $option['name']; ?></a></li>
			<?php endforeach; ?>
		</ul>
	</div>
<?php endif; ?>

<div class="amun-service-page">
	<?php if(!empty($recordPage)): ?>
		<div class="amun-service-page-entry">
			<div class="amun-service-page-content"><?php echo $recordPage->content; ?></div>
			<p class="muted">
				by
				<a href="<?php echo $recordPage->authorProfileUrl; ?>" rel="author"><?php echo $recordPage->authorName; ?></a>
				last modified on
				<time datetime="<?php echo $recordPage->getDate()->format(DateTime::ATOM); ?>"><?php echo $recordPage->getDate()->setTimezone($user->getTimezone())->format($registry['core.format_datetime']); ?></time>
			</p>
		</div>
	<?php endif; ?>
</div>
