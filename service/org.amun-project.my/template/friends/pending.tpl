
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
			<col width="60" />
			<col width="*" />
			<col width="200" />
			<col width="160" />
		</colgroup>
		<thead>
		<tr>
			<th></th>
			<th>Name</th>
			<th>Date</th>
			<th>Options</th>
		</tr>
		</thead>
		<tbody>
		<?php if(count($requests) > 0): ?>
		<?php foreach($requests as $request): ?>
		<tr>
			<td><img src="<?php echo $request->friendThumbnailUrl; ?>" width="48" height="48" /></td>
			<td><h4><a href="<?php echo $request->friendProfileUrl; ?>"><?php echo $request->friendName; ?></a></h4></td>
			<td><?php echo $request->getDate()->setTimezone($user->timezone)->format($registry['core.format_datetime']); ?></td>
			<td>
				<input class="btn" type="button" onclick="amun.services.my.friendsCancelRelation(<?php echo $request->id . ',\'' . $pendingUrl . '\''; ?>, this)" value="Cancel" />
			</td>
		</tr>
		<?php endforeach; ?>
		<?php else: ?>
		<tr>
			<td colspan="4">No pending requests available</td>
		</tr>
		<?php endif; ?>
		</tbody>
		</table>

	</div>

</div>

