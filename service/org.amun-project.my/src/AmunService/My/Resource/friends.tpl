
<?php if(count($options) > 0): ?>
	<div class="amun-options">
		<ul class="nav nav-tabs">
			<?php foreach($options as $option): ?>
			<li><a href="<?php echo $option['href']; ?>"><?php echo $option['name']; ?></a></li>
			<?php endforeach; ?>
		</ul>
	</div>
<?php endif; ?>

<div class="row amun-service-my-friend">

	<div class="span2 amun-service-my-friend-nav">
		<ul class="nav nav-list">
			<li class="nav-header">General</li>
			<?php foreach($optionsFriends as $option): ?>
			<li><a href="<?php echo $option['href']; ?>"><?php echo $option['name']; ?></a></li>
			<?php endforeach; ?>
			<li class="nav-header">Groups</li>
			<?php foreach($groupList as $group): ?>
			<li><a href="<?php echo $page->getUrl() . '/friends?filterBy=groupId&filterOp=equals&filterValue=' . $group['id']; ?>"><?php echo $group['title']; ?></a></li>
			<?php endforeach; ?>
			<li><a href="<?php echo $page->getUrl() . '/friends?filterBy=groupId&filterOp=equals&filterValue=0'; ?>">Uncategorized</a></li>
		</ul>
	</div>

	<div class="span10">

		<form class="form-inline" method="post" action="<?php echo $self; ?>">
			<input type="search" name="search" id="search">
			<input type="submit" class="btn btn-primary" value="Search" />
		</form>

		<table class="table">
		<colgroup>
			<col width="25" />
			<col width="60" />
			<col width="*" />
			<col width="200" />
			<col width="100" />
		</colgroup>
		<thead>
		<tr>
			<th></th>
			<th></th>
			<th>Name</th>
			<th>Date</th>
			<th>Options</th>
		</tr>
		</thead>
		<tbody>
		<?php if(count($friends) > 0): ?>
		<?php foreach($friends as $friend): ?>
		<tr>
			<td><input type="checkbox" name="friend_<?php echo $friend->id; ?>" id="friend_<?php echo $friend->id; ?>" value="<?php echo $friend->id; ?>" onchange="amun.services.my.friendsDisableButtons()" /></td>
			<td><label for="friend_<?php echo $friend->id; ?>"><img src="<?php echo $friend->friendThumbnailUrl; ?>" width="48" height="48" /></label></td>
			<td><h4><a href="<?php echo $friend->friendProfileUrl; ?>"><?php echo $friend->friendName; ?></a></h4></td>
			<td><?php echo $friend->getDate()->setTimezone($user->getTimezone())->format($registry['core.format_datetime']); ?></td>
			<?php if($friend->friendId == $user->getId()): ?>
			<td><input class="btn" type="button" disabled="disabled" value="Remove" /></td>
			<?php else: ?>
			<td><input class="btn" type="button" onclick="amun.services.my.friendsRevokeRelation(<?php echo $friend->id . ',\'' . $friendUrl . '\''; ?>, this)" value="Remove" /></td>
			<?php endif; ?>
		</tr>
		<?php endforeach; ?>
		<?php else: ?>
		<tr>
			<td colspan="5">No friends available</td>
		</tr>
		<?php endif; ?>
		</tbody>
		</table>

		<form class="form-inline" action="#">
			<select name="groups" id="groups" disabled="disabled">
				<option value="0">Select a group</option>
				<?php foreach($groupList as $group): ?>
				<option value="<?php echo $group['id']; ?>"><?php echo $group['title']; ?></option>
				<?php endforeach; ?>
			</select>
			<input class="btn" type="button" id="move" disabled="disabled" value="Move" onclick="amun.services.my.moveFriendInGroup('<?php echo $friendUrl; ?>')" />
		</form>

		<?php if($pagingFriends->getPages() > 1): ?>
		<hr />
		<div class="pagination pagination-centered">
			<ul>
				<li><a href="<?php echo $pagingFriends->getFirstUrl(); ?>">First</a></li>
				<li><a href="<?php echo $pagingFriends->getPrevUrl(); ?>">Previous</a></li>
				<li><span><?php echo $pagingFriends->getPage(); ?> of <?php echo $pagingFriends->getPages(); ?></span></li>
				<li><a href="<?php echo $pagingFriends->getNextUrl(); ?>">Next</a></li>
				<li><a href="<?php echo $pagingFriends->getLastUrl(); ?>">Last</a></li>
			</ul>
		</div>
		<?php endif; ?>

	</div>

	<hr />

</div>

