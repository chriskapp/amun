
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
	<div class="amun-service-news-entry">
		<h2>
			<a href="<?php echo $recordNews->getUrl(); ?>"><?php echo $recordNews->title; ?></a>
		</h2>
		<p class="muted">
			by
			<a href="<?php echo $recordNews->authorProfileUrl; ?>" rel="author"><?php echo $recordNews->authorName; ?></a>
			on
			<time datetime="<?php echo $recordNews->getDate()->format(DateTime::ATOM); ?>"><?php echo $recordNews->getDate()->setTimezone($user->timezone)->format($registry['core.format_datetime']); ?></time>
		</p>
		<div class="amun-service-news-text"><?php echo $recordNews->text; ?></div>
	</div>
	<hr />

	<div class="amun-service-comment">
	<?php if($resultComments->totalResults > 0): ?>
		<?php foreach($resultComments->entry as $record): ?>
		<div class="amun-service-comment-entry" id="comment-<?php echo $record->id; ?>">
			<img class="pull-left" src="<?php echo $record->authorThumbnailUrl; ?>" alt="avatar" width="48" height="48" />
			<p class="muted">
				by
				<a href="<?php echo $record->authorProfileUrl; ?>" rel="author"><?php echo $record->authorName; ?></a>
				on
				<time datetime="<?php echo $record->getDate()->format(DateTime::ATOM); ?>"><?php echo $record->getDate()->setTimezone($user->timezone)->format($registry['core.format_datetime']); ?></time>
			</p>
			<div class="amun-service-comment-text"><?php echo $record->text; ?></div>
		</div>
		<?php endforeach; ?>
	<?php endif; ?>
	</div>

	<?php if($pagingComments->getPages() > 1): ?>
	<hr />
	<div class="pagination pagination-centered">
		<ul>
			<li><a href="<?php echo $pagingComments->getFirstUrl(); ?>">First</a></li>
			<li><a href="<?php echo $pagingComments->getPrevUrl(); ?>">Previous</a></li>
			<li><span><?php echo $pagingComments->getPage(); ?> of <?php echo $pagingComments->getPages(); ?></span></li>
			<li><a href="<?php echo $pagingComments->getNextUrl(); ?>">Next</a></li>
			<li><a href="<?php echo $pagingComments->getLastUrl(); ?>">Last</a></li>
		</ul>
	</div>
	<?php endif; ?>

	<?php if($user->hasRight('news_comment_add')): ?>
		<div id="response"></div>
		<div id="form"></div>
		<div id="preview" class="amun-preview"></div>

		<script type="text/javascript">
		amun.services.news.loadCommentForm("form", <?php echo '"' . $formUrl . '"'; ?>);
		</script>

		<p class="muted">Please read the <a href="<?php echo $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'help.htm'; ?>">help</a> howto properly format your content before submitting.</p>
	<?php else: ?>
		<?php if($user->isAnonymous()): ?>
			<p>You must be logged in to post a comment.</p>
		<?php endif; ?>
	<?php endif; ?>
</div>

