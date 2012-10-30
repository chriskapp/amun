

<?php if(count($options) > 0): ?>

<div class="amun_html_options">

	<ul>
		<?php foreach($options as $option): ?>
		<li><a href="<?php echo $option['href']; ?>"><?php echo $option['name']; ?></a></li>
		<?php endforeach; ?>
	</ul>

</div>

<?php endif; ?>


<div class="amun_service_googleproject">

	<?php if(!empty($recordProject)): ?>

		<?php if($resultCommit->totalResults > 0): ?>

			<?php foreach($resultCommit->entry as $record): ?>
			<div class="amun_service_googleproject_entry" id="commit-<?php echo $record->id; ?>">
				<h2>
					<a href="<?php echo 'http://code.google.com/p/' . $recordProject->name . '/source/detail?r=' . $record->revision; ?>"><?php echo $record->message; ?></a>
				</h2>
				<span class="small">
					by
					<a href="<?php echo $record->authorProfileUrl; ?>" rel="author"><?php echo $record->authorName; ?></a>
					on
					<time datetime="<?php echo $record->getDate()->format(DateTime::ATOM); ?>"><?php echo $record->getDate()->setTimezone($user->timezone)->format($registry['core.format_datetime']); ?></time>
				</span>
			</div>
			<?php endforeach; ?>

			<?php if($pagingCommits->getPages() > 1): ?>
			<hr />
			<div class="psx_html_paging">
				<ul>
					<li><a href="<?php echo $pagingCommits->getFirstUrl(); ?>">First</a></li>
					<li><a href="<?php echo $pagingCommits->getPrevUrl(); ?>">Previous</a></li>
					<li><?php echo $pagingCommits->getPage(); ?> of <?php echo $pagingCommits->getPages(); ?></li>
					<li><a href="<?php echo $pagingCommits->getNextUrl(); ?>">Next</a></li>
					<li><a href="<?php echo $pagingCommits->getLastUrl(); ?>">Last</a></li>
				</ul>
			</div>
			<?php endif; ?>

		<?php else: ?>

			<p>No commits made yet</p>

		<?php endif; ?>

	<?php else: ?>

		<p>Google project not configured</p>

	<?php endif; ?>

</div>




