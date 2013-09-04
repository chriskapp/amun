
<?php if(count($options) > 0): ?>
	<div class="amun-options">
		<ul class="nav nav-tabs">
			<?php foreach($options as $option): ?>
			<li><a href="<?php echo $option['href']; ?>"><?php echo $option['name']; ?></a></li>
			<?php endforeach; ?>
		</ul>
	</div>
<?php endif; ?>

<div class="amun-service-news">
	<?php foreach($resultNews->entry as $record): ?>
	<div class="amun-service-news-entry" id="news-<?php echo $record->id; ?>">
		<h2>
			<a href="<?php echo $record->getUrl(); ?>"><?php echo $record->title; ?></a>
		</h2>
		<p class="muted">
			by
			<a href="<?php echo $record->authorProfileUrl; ?>" rel="author"><?php echo $record->authorName; ?></a>
			on
			<time datetime="<?php echo $record->getDate()->format(DateTime::ATOM); ?>"><?php echo $record->getDate()->setTimezone($user->getTimezone())->format($registry['core.format_datetime']); ?></time>
		</p>
		<div class="amun-service-news-text"><?php echo $record->text; ?></div>
	</div>
	<hr />
	<?php endforeach; ?>

	<?php if($pagingNews->getPages() > 1): ?>
	<hr />
	<div class="amun-pagination">
		<ul class="pagination">
			<li><a href="<?php echo $pagingNews->getFirstUrl(); ?>">First</a></li>
			<li><a href="<?php echo $pagingNews->getPrevUrl(); ?>">Previous</a></li>
			<li><span><?php echo $pagingNews->getPage(); ?> of <?php echo $pagingNews->getPages(); ?></span></li>
			<li><a href="<?php echo $pagingNews->getNextUrl(); ?>">Next</a></li>
			<li><a href="<?php echo $pagingNews->getLastUrl(); ?>">Last</a></li>
		</ul>
	</div>
	<?php endif; ?>
</div>
