
<?php if(count($options) > 0): ?>
	<div class="amun-options">
		<ul class="nav nav-tabs">
			<?php foreach($options as $option): ?>
			<li><a href="<?php echo $option['href']; ?>"><?php echo $option['name']; ?></a></li>
			<?php endforeach; ?>
		</ul>
	</div>
<?php endif; ?>

<div class="amun-service-tracker">

	<table>
	<colgroup>
		<col width="*" />
		<col width="100" />
		<col width="100" />
		<col width="250" />
	</colgroup>
	<tr>
		<th>Title</th>
		<th>Seeder</th>
		<th>Leecher</th>
		<th>Date</th>
	</tr>
	<?php foreach($resultTracker->entry as $record): ?>
	<tr class="amun-service-tracker-entry" id="thread-<?php echo $record->id; ?>">
		<td>
			<h2><a href="<?php echo $record->getUrl(); ?>"><?php echo $record->title; ?></a></h2>
			<span class="small">
				by <a href="<?php echo $record->authorProfileUrl; ?>"><?php echo $record->authorName; ?></a>
				on <time datetime="<?php echo $record->getDate()->format(DateTime::ATOM); ?>"><?php echo $record->getDate()->setTimezone($user->timezone)->format($registry['core.format_datetime']); ?></time>
			</span>
		</td>
		<td><?php echo $record->seeder; ?></td>
		<td><?php echo $record->leecher; ?></td>
		<td><time datetime="<?php echo $record->getDate()->format(DateTime::ATOM); ?>"><?php echo $record->getDate()->setTimezone($user->timezone)->format($registry['core.format_datetime']); ?></time></td>
	</tr>
	<?php endforeach; ?>
	</table>

	<?php if($pagingTracker->getPages() > 1): ?>
	<hr />
	<div class="pagination pagination-centered">
		<ul>
			<li><a href="<?php echo $pagingTracker->getFirstUrl(); ?>">First</a></li>
			<li><a href="<?php echo $pagingTracker->getPrevUrl(); ?>">Previous</a></li>
			<li><span><?php echo $pagingTracker->getPage(); ?> of <?php echo $pagingTracker->getPages(); ?></span></li>
			<li><a href="<?php echo $pagingTracker->getNextUrl(); ?>">Next</a></li>
			<li><a href="<?php echo $pagingTracker->getLastUrl(); ?>">Last</a></li>
		</ul>
	</div>
	<?php endif; ?>

</div>





