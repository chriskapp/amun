
<?php if(count($options) > 0): ?>
	<div class="amun-options">
		<ul class="nav nav-tabs">
			<?php foreach($options as $option): ?>
			<li><a href="<?php echo $option['href']; ?>"><?php echo $option['name']; ?></a></li>
			<?php endforeach; ?>
		</ul>
	</div>
<?php endif; ?>

<div class="row amun-service-my-settings">

	<div class="span2 amun-service-my-settings-nav">
		<ul class="nav nav-list">
			<li class="nav-header">Settings</li>
			<?php foreach($optionsSettings as $option): ?>
			<li><a href="<?php echo $option['href']; ?>"><?php echo $option['name']; ?></a></li>
			<?php endforeach; ?>
		</ul>
	</div>

	<div class="span10">

		<p>If you want receive notifications about a specific event you have to bind a contact to
		an service. You will receive only notifications if the contact is verified.</p>

		<table class="table">
		<colgroup>
			<col width="120" />
			<col width="*" />
			<col width="200" />
			<col width="100" />
		</colgroup>
		<thead>
		<tr>
			<th>Title</th>
			<th>Date</th>
			<th>Option</th>
		</tr>
		</thead>		<tbody>
		<?php if(count($notifications) > 0): ?>
		<?php foreach($notifications as $notification): ?>
		<tr>
			<td><?php echo $notification->serviceTitle; ?></td>
			<td><?php echo $notification->contactValue; ?></td>
			<td><?php echo $notification->getDate()->setTimezone($user->getTimezone())->format($registry['core.format_datetime']); ?></td>
			<td><input type="button" onclick="amun.services.my.notificationsRemove(<?php echo $notification->id . ',\'' . $notifyUrl . '\''; ?>, this)" value="Remove" /></td>
		</tr>
		<?php endforeach; ?>
		<?php else: ?>
		<tr>
			<td colspan="3">No notifications found</td>
		</tr>
		<?php endif; ?>
		</tbody>
		</table>

		<?php if($pagingNotifications->getPages() > 1): ?>
		<hr />
		<div class="pagination pagination-centered">
			<ul>
				<li><a href="<?php echo $pagingNotifications->getFirstUrl(); ?>">First</a></li>
				<li><a href="<?php echo $pagingNotifications->getPrevUrl(); ?>">Previous</a></li>
				<li><span><?php echo $pagingNotifications->getPage(); ?> of <?php echo $pagingNotifications->getPages(); ?></span></li>
				<li><a href="<?php echo $pagingNotifications->getNextUrl(); ?>">Next</a></li>
				<li><a href="<?php echo $pagingNotifications->getLastUrl(); ?>">Last</a></li>
			</ul>
		</div>
		<?php endif; ?>

		<hr />

		<div id="response"></div>

		<div id="notification_form"></div>

	</div>

	<hr />

</div>

<script type="text/javascript">
amun.services.my.loadSettingsForm('notification_form', '<?php echo $formUrl; ?>');
</script>

