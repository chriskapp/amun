
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

		<p>A list of public remote sources wich you have subscribed. You will receive from these sources
		messages wich will be added to your activity stream.</p>

		<table class="table">
		<colgroup>
			<col width="*" />
			<col width="200" />
			<col width="100" />
		</colgroup>
		<thead>
		<tr>
			<th>Topic</th>
			<th>Date</th>
			<th>Option</th>
		</tr>
		</thead>		<tbody>
		<?php if(count($subscriptions) > 0): ?>
		<?php foreach($subscriptions as $subscription): ?>
		<tr>
			<td><a href="<?php echo $subscription->topic; ?>"><div style="overflow:hidden;width:380px;"><?php echo $subscription->topic; ?></div></a></td>
			<td><?php echo $subscription->getDate()->setTimezone($user->timezone)->format($registry['core.format_datetime']); ?></td>
			<td><input type="button" onclick="amun.services.my.subscriptionsRemove(<?php echo $subscription->id . ',\'' . $subscriptionUrl . '\''; ?>, this)" value="Remove" /></td>
		</tr>
		<?php endforeach; ?>
		<?php else: ?>
		<tr>
			<td colspan="3">No subscriptions found</td>
		</tr>
		<?php endif; ?>
		</tbody>
		</table>

		<?php if($pagingSubscriptions->getPages() > 1): ?>
		<hr />
		<div class="pagination pagination-centered">
			<ul>
				<li><a href="<?php echo $pagingSubscriptions->getFirstUrl(); ?>">First</a></li>
				<li><a href="<?php echo $pagingSubscriptions->getPrevUrl(); ?>">Previous</a></li>
				<li><span><?php echo $pagingSubscriptions->getPage(); ?> of <?php echo $pagingSubscriptions->getPages(); ?></span></li>
				<li><a href="<?php echo $pagingSubscriptions->getNextUrl(); ?>">Next</a></li>
				<li><a href="<?php echo $pagingSubscriptions->getLastUrl(); ?>">Last</a></li>
			</ul>
		</div>
		<?php endif; ?>

		<hr />

		<div id="response"></div>

		<div id="subscription_form"></div>

	</div>

	<hr />

</div>

<script type="text/javascript">
amun.services.my.loadSettingsForm('subscription_form', '<?php echo $formUrl; ?>');
</script>

