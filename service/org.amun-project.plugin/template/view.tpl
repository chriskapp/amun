
<?php if(count($options) > 0): ?>
	<div class="amun-options">
		<ul class="nav nav-tabs">
			<?php foreach($options as $option): ?>
			<li><a href="<?php echo $option['href']; ?>"><?php echo $option['name']; ?></a></li>
			<?php endforeach; ?>
		</ul>
	</div>
<?php endif; ?>

<div class="amun-service-plugin">

	<div class="row amun-service-plugin-entry">
		<div class="span9">
			<h2>
				<a href="<?php echo $recordPlugin->getUrl(); ?>"><?php echo $recordPlugin->title; ?></a>
			</h2>
			<p class="muted">
				by
				<a href="<?php echo $recordPlugin->authorProfileUrl; ?>" rel="author"><?php echo $recordPlugin->authorName; ?></a>
				on
				<time datetime="<?php echo $recordPlugin->getDate()->format(DateTime::ATOM); ?>"><?php echo $recordPlugin->getDate()->setTimezone($user->timezone)->format($registry['core.format_datetime']); ?></time>
			</p>
			<div class="amun-service-plugin-text"><?php echo $recordPlugin->description; ?></div>
		</div>
		<div class="span3 amun-service-plugin-meta">
			<div class="amun-service-plugin-maintainer">
				<h5>Maintainer</h5>
				<ul>
					<?php foreach($resultMaintainer as $maintainer): ?>
					<li>
						<img src="<?php echo $maintainer['authorThumbnailUrl']; ?>" />
						<a href="<?php echo $maintainer['authorProfileUrl']; ?>">
							<?php echo $maintainer['authorName']; ?>
						</a>
					</li>
					<?php endforeach; ?>
				</ul>
			</div>
			<hr />
			<div class="amun-service-plugin-release">
				<h5>Latest release</h5>
				<?php if(!empty($resultRelease)): ?>
					<ul>
						<li>
							<a href="<?php echo $resultRelease->href; ?>"><?php echo $resultRelease->version; ?></a> (<?php echo $resultRelease->status; ?>)<br />
							<p class="muted">
								by
								<a href="<?php echo $resultRelease->authorProfileUrl; ?>" rel="author"><?php echo $resultRelease->authorName; ?></a>
								on
								<?php echo $resultRelease->getDate()->setTimezone($user->timezone)->format($registry['core.format_datetime']); ?>
							</span>
						</li>
					</ul>
				<?php else: ?>
					<p>No releases available</p>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<?php if($resultComments->totalResults > 0): ?>
	<div class="amun-service-comment">
		<?php foreach($resultComments->entry as $record): ?>
		<div class="amun-service-comment-entry" id="comment-<?php echo $record->id; ?>">
			<div class="pull-left amun-service-comment-avatar">
				<img src="<?php echo $record->authorThumbnailUrl; ?>" alt="avatar" />
			</div>
			<div class="pull-left amun-service-comment-content">
				<p class="muted">
					by
					<a href="<?php echo $record->authorProfileUrl; ?>" rel="author"><?php echo $record->authorName; ?></a>
					on
					<time datetime="<?php echo $record->getDate()->format(DateTime::ATOM); ?>"><?php echo $record->getDate()->setTimezone($user->timezone)->format($registry['core.format_datetime']); ?></time>
				</span>
				<div class="amun-service-comment-text"><?php echo $record->text; ?></div>
			</div>
		</div>
		<div class="clearfix"></div>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>

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

	<?php if($user->hasRight('service_plugin_comment_add')): ?>
		<div id="response"></div>
		<div id="form"></div>

		<script type="text/javascript">
		amun.services.plugin.loadCommentForm("form", <?php echo '"' . $formUrl . '"'; ?>);
		</script>

		<p><span class="small">Please read the <a href="<?php echo $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'help.htm'; ?>">help</a> howto properly format your content before submitting.</span></p>
	<?php else: ?>
		<?php if($user->isAnonymous()): ?>
			<p>You must be logged in to post a comment.</p>
		<?php endif; ?>
	<?php endif; ?>

</div>
