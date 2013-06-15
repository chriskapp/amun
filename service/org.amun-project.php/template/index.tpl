
<?php if(count($options) > 0): ?>
	<div class="amun-options">
		<ul class="nav nav-tabs">
			<?php foreach($options as $option): ?>
			<li><a href="<?php echo $option['href']; ?>"><?php echo $option['name']; ?></a></li>
			<?php endforeach; ?>
		</ul>
	</div>
<?php endif; ?>

<div class="amun-service-php">
	<?php if(!empty($phpError)): ?>
		<?php echo $phpError; ?>
	<?php elseif(!empty($phpResponse)): ?>
		<div class="amun-service-php-entry">
			<div class="amun-service-php-content"><?php echo $phpResponse; ?></div>
			<p class="muted">
				by
				<a href="<?php echo $recordPhp->authorProfileUrl; ?>" rel="author"><?php echo $recordPhp->authorName; ?></a>
				last modified on
				<time datetime="<?php echo $recordPhp->getDate()->format(DateTime::ATOM); ?>"><?php echo $recordPhp->getDate()->setTimezone($user->getTimezone())->format($registry['core.format_datetime']); ?></time>
			</span>
		</div>
	<?php endif; ?>
</div>
