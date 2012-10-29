
<?php if(count($options) > 0): ?>
	<div class="amun-options">
		<ul class="nav nav-tabs">
			<?php foreach($options as $option): ?>
			<li><a href="<?php echo $option['href']; ?>"><?php echo $option['name']; ?></a></li>
			<?php endforeach; ?>
		</ul>
	</div>
<?php endif; ?>


<div class="amun-service-forum">

	<table class="table">
	<colgroup>
		<col width="*" />
		<col width="80" />
		<col width="250" />
	</colgroup>
	<thead>
	<tr>
		<th>Topic</th>
		<th>Replies</th>
		<th>Last post</th>
	</tr>
	</thead>
	<tbody>
	<?php foreach($resultForum->entry as $record): ?>
	<tr class="amun-service-forum-entry" id="thread-<?php echo $record->id; ?>">
		<td>
			<h3><a href="<?php echo $record->getUrl(); ?>"><?php echo $record->isSticky() ? '<strong>' . $record->title . '</strong>' : $record->title; ?></a></h3>
			<p class="muted">
				by <a href="<?php echo $record->authorProfileUrl; ?>"><?php echo $record->authorName; ?></a>
				on <time datetime="<?php echo $record->getDate()->format(DateTime::ATOM); ?>"><?php echo $record->getDate()->setTimezone($user->timezone)->format($registry['core.format_datetime']); ?></time>
			</p>
		</td>
		<td><?php echo $record->getReplyCount(); ?></td>
		<td>
			<?php $lastReply = $record->getLastReply(); ?>
			<?php if(!empty($lastReply)): ?>
			<div class="amun-service-forum-entry-lastcomment">
				by <a href="<?php echo $lastReply->authorProfileUrl; ?>"><?php echo $lastReply->authorName; ?></a>
				<br />
				on <time datetime="<?php echo $lastReply->getDate()->format(DateTime::ATOM); ?>"><?php echo $lastReply->getDate()->setTimezone($user->timezone)->format($registry['core.format_datetime']); ?></time>
			</div>
			<?php else: ?>
			-
			<?php endif; ?>
		</td>
	</tr>
	<?php endforeach; ?>
	</tbody>
	</table>

	<?php if($pagingForum->getPages() > 1): ?>
	<hr />
	<div class="pagination pagination-centered">
		<ul>
			<li><a href="<?php echo $pagingForum->getFirstUrl(); ?>">First</a></li>
			<li><a href="<?php echo $pagingForum->getPrevUrl(); ?>">Previous</a></li>
			<li><span><?php echo $pagingForum->getPage(); ?> of <?php echo $pagingForum->getPages(); ?></span></li>
			<li><a href="<?php echo $pagingForum->getNextUrl(); ?>">Next</a></li>
			<li><a href="<?php echo $pagingForum->getLastUrl(); ?>">Last</a></li>
		</ul>
	</div>
	<?php endif; ?>

</div>





