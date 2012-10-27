
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

	<div class="amun-service-plugin-search">
		<form method="get" class="form-inline">
			<input type="hidden" name="filterOp" value="contains" />
			<select name="filterBy">
				<option value="title">Title</option>
				<option value="description">Description</option>
				<option value="authorName">Author</option>
			</select>
			<input type="search" name="filterValue" value="" placeholder="Search ..." />
			<input class="btn btn-primary" type="submit" value="Search" />
		</form>
	</div>

	<?php foreach($resultPlugin->entry as $record): ?>
	<div class="row amun-service-plugin-entry" id="plugin-<?php echo $record->id; ?>">
		<div class="span9">
			<h2>
				<a href="<?php echo $record->getUrl(); ?>"><?php echo $record->title; ?></a>
			</h2>
			<p class="muted">
				by
				<a href="<?php echo $record->authorProfileUrl; ?>" rel="author"><?php echo $record->authorName; ?></a>
				on
				<time datetime="<?php echo $record->getDate()->format(DateTime::ATOM); ?>"><?php echo $record->getDate()->setTimezone($user->timezone)->format($registry['core.format_datetime']); ?></time>
			</p>
			<div class="amun_service_plugin_text"><?php echo $record->description; ?></div>
		</div>
		<div class="span3">
			<?php $latestRelease = $record->getLatestRelease(); ?>
			<?php if(!empty($latestRelease)): ?>
			<dl>
				<dt>Version:</dt>
				<dd><a href="<?php echo $latestRelease->href; ?>"><?php echo $latestRelease->version; ?></a> (<?php echo $latestRelease->status; ?>)</dd>
				<dt>Last updated:</dt>
				<dd><?php echo $latestRelease->getDate()->setTimezone($user->timezone)->format($registry['core.format_datetime']); ?></dd>
			</dl>
			<?php endif; ?>
		</div>
	</div>
	<?php endforeach; ?>

	<?php if($pagingPlugin->getPages() > 1): ?>
	<hr />
	<div class="pagination pagination-centered">
		<ul>
			<li><a href="<?php echo $pagingPlugin->getFirstUrl(); ?>">First</a></li>
			<li><a href="<?php echo $pagingPlugin->getPrevUrl(); ?>">Previous</a></li>
			<li><span><?php echo $pagingPlugin->getPage(); ?> of <?php echo $pagingPlugin->getPages(); ?></span></li>
			<li><a href="<?php echo $pagingPlugin->getNextUrl(); ?>">Next</a></li>
			<li><a href="<?php echo $pagingPlugin->getLastUrl(); ?>">Last</a></li>
		</ul>
	</div>
	<?php endif; ?>

</div>





