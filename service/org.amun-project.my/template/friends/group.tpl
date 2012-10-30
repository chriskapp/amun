
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
			<li><a href="<?php echo $page->url . '/friends?filterBy=groupId&filterOp=equals&filterValue=' . $group['id']; ?>"><?php echo $group['title']; ?></a></li>
			<?php endforeach; ?>
			<li><a href="<?php echo $page->url . '/friends?filterBy=groupId&filterOp=equals&filterValue=0'; ?>">Uncategorized</a></li>
		</ul>
	</div>

	<div class="span10">

		<table class="table">
		<colgroup>
			<col width="*" />
			<col width="200" />
			<col width="100" />
		</colgroup>
		<thead>
		<tr>
			<th>Name</th>
			<th>Date</th>
			<th>Options</th>
		</tr>
		</thead>
		<tbody>
		<?php if(count($groups)): ?>
		<?php foreach($groups as $group): ?>
		<tr>
			<td><?php echo $group->title; ?></td>
			<td><?php echo $group->getDate()->setTimezone($user->timezone)->format($registry['core.format_datetime']); ?></td>
			<td>
				<input class="btn" type="button" onclick="amun.services.my.friendsGroupRemove(<?php echo $group->id . ',\'' . $groupUrl . '\''; ?>, this)" value="Remove" />
			</td>
		</tr>
		<?php endforeach; ?>
		<?php else: ?>
		<tr>
			<td colspan="3">No groups available</td>
		</tr>
		<?php endif; ?>
		</tbody>
		</table>

		<form class="form-inline" method="post" id="group_form" action="<?php echo $groupUrl; ?>">
			<input type="text" name="title" id="title" placeholder="Add group" />
			<input class="btn btn-primary" type="submit" value="Add" />
		</form>

	</div>

	<hr />

</div>

<script type="text/javascript">
amun.services.my.loadFriendGroupAdd('group_form');
</script>


