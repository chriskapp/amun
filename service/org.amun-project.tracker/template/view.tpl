

<?php if(count($options) > 0): ?>

<div class="amun_html_options">

	<ul>
		<?php foreach($options as $option): ?>
		<li><a href="<?php echo $option['href']; ?>"><?php echo $option['name']; ?></a></li>
		<?php endforeach; ?>
	</ul>

</div>

<?php endif; ?>


<div class="amun_service_tracker">

	<div class="amun_service_tracker_entry">
		<h2>
			<a href="<?php echo $recordTracker->getUrl(); ?>"><?php echo $recordTracker->title; ?></a>
		</h2>
		<span class="small">
			by
			<a href="<?php echo $recordTracker->authorProfileUrl; ?>" rel="author"><?php echo $recordTracker->authorName; ?></a>
			on
			<time datetime="<?php echo $recordTracker->getDate()->format(DateTime::ATOM); ?>"><?php echo $recordTracker->getDate()->setTimezone($user->timezone)->format($registry['core.format_datetime']); ?></time>
		</span>
		<dl>
			<dt>Name</dt>
			<dd><a href="<?php echo $recordTracker->getDownloadUrl(); ?>"><?php echo $recordTracker->name; ?></a></dd>
			<dt>Size</dt>
			<dd><?php echo $recordTracker->getLength(); ?></dd>
			<dt>Seeder</dt>
			<dd><?php echo $recordTracker->seeder; ?></dd>
			<dt>Leecher</dt>
			<dd><?php echo $recordTracker->leecher; ?></dd>
		</dl>
	</div>
	<hr />

</div>


<?php if($resultComments->totalResults > 0): ?>

	<div class="amun_service_comment">

		<?php foreach($resultComments->entry as $record): ?>
		<div class="amun_service_comment_entry" id="comment-<?php echo $record->id; ?>">
			<div class="amun_service_comment_avatar">
				<img src="<?php echo $record->authorThumbnailUrl; ?>" alt="avatar" />
			</div>
			<div class="amun_service_comment_content">
				<span class="small">
					by
					<a href="<?php echo $record->authorProfileUrl; ?>" rel="author"><?php echo $record->authorName; ?></a>
					on
					<time datetime="<?php echo $record->getDate()->format(DateTime::ATOM); ?>"><?php echo $record->getDate()->setTimezone($user->timezone)->format($registry['core.format_datetime']); ?></time>
				</span>
				<div class="amun_service_comment_text"><?php echo $record->text; ?></div>
			</div>
		</div>
		<hr />
		<?php endforeach; ?>

	</div>

	<?php if($pagingComments->getPages() > 1): ?>
	<div class="psx_html_paging">
		<ul>
			<li><a href="<?php echo $pagingComments->getFirstUrl(); ?>">First</a></li>
			<li><a href="<?php echo $pagingComments->getPrevUrl(); ?>">Previous</a></li>
			<li><?php echo $pagingComments->getPage(); ?> of <?php echo $pagingComments->getPages(); ?></li>
			<li><a href="<?php echo $pagingComments->getNextUrl(); ?>">Next</a></li>
			<li><a href="<?php echo $pagingComments->getLastUrl(); ?>">Last</a></li>
		</ul>
	</div>
	<hr />
	<?php endif; ?>

<?php endif; ?>


<?php if($user->hasRight('tracker_comment_add')): ?>

	<div id="response"></div>

	<div id="form"></div>

	<script type="text/javascript">
	amun.services.tracker.loadCommentForm("form", <?php echo '"' . $url . '"'; ?>);
	</script>

	<p><span class="small">Please read the <a href="<?php echo $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'help.htm'; ?>">help</a> howto properly format your content before submitting.</span></p>

<?php else: ?>

	<?php if($user->isAnonymous()): ?>

		<p>You must be logged in to post a comment.</p>

	<?php endif; ?>

<?php endif; ?>


