
<?php if(count($options) > 0): ?>
	<div class="amun-options">
		<ul class="nav nav-tabs">
			<?php foreach($options as $option): ?>
			<li><a href="<?php echo $option['href']; ?>"><?php echo $option['name']; ?></a></li>
			<?php endforeach; ?>
		</ul>
	</div>
<?php endif; ?>

<div class="amun-service-pipe">
	<?php if(!empty($recordPipe)): ?>
		<div class="amun-service-pipe-entry">
			<div class="amun-service-pipe-content"><?php echo $recordPipe->getContent(); ?></div>
			<p class="muted">
				by
				<a href="<?php echo $recordPipe->authorProfileUrl; ?>" rel="author"><?php echo $recordPipe->authorName; ?></a>
				last modified on
				<time datetime="<?php echo $recordPipe->getDate()->format(DateTime::ATOM); ?>"><?php echo $recordPipe->getDate()->setTimezone($user->timezone)->format($registry['core.format_datetime']); ?></time>
			</p>
		</div>
	<?php endif; ?>
</div>
