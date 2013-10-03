
<div class="amun-service-search">

	<form method="get" class="form-inline" role="form">
		<div class="form-group">
			<label class="sr-only" for="search">Search</label>
			<input type="search" class="form-control" name="search" id="search" placeholder="Search ..." />
		</div>
		<button type="submit" class="btn btn-default">Search</button>
	</form>

	<hr />

	<?php if(isset($resultSearch)): ?>
		<?php if(count($resultSearch) > 0): ?>
		<ul>
			<?php foreach($resultSearch as $record): ?>
			<li>
				<h4><a href="<?php echo $record['url']; ?>"><?php echo $record['title']; ?></a></h4>
				<div><?php echo $record['content']; ?></div>
				<p class="muted">
					Last modified on 
					<time datetime="<?php echo $record['date']->format(DateTime::ATOM); ?>"><?php echo $record['date']->setTimezone($user->getTimezone())->format($registry['core.format_datetime']); ?></time>
				</p>
			</li>
			<?php endforeach; ?>
		</ul>
		<?php else: ?>
		<p>Found no results</p>
		<?php endif; ?>

		<?php if($pagingSearch->getPages() > 1): ?>
		<hr />
		<div class="amun-pagination">
			<ul class="pagination">
				<li><a href="<?php echo $pagingSearch->getFirstUrl(); ?>">First</a></li>
				<li><a href="<?php echo $pagingSearch->getPrevUrl(); ?>">Previous</a></li>
				<li><span><?php echo $pagingSearch->getPage(); ?> of <?php echo $pagingSearch->getPages(); ?></span></li>
				<li><a href="<?php echo $pagingSearch->getNextUrl(); ?>">Next</a></li>
				<li><a href="<?php echo $pagingSearch->getLastUrl(); ?>">Last</a></li>
			</ul>
		</div>
		<?php endif; ?>
	<?php endif; ?>

</div>





