
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

	<div class="col-md-2 amun-service-my-settings-nav">
		<ul class="nav nav-stacked">
			<li><h4>Settings</h4></li>
			<?php foreach($optionsSettings as $option): ?>
			<li><a href="<?php echo $option['href']; ?>"><?php echo $option['name']; ?></a></li>
			<?php endforeach; ?>
		</ul>
	</div>

	<div class="col-md-10">

		<p>A list of applications wich have access to your account. If you revoke an access the
		application has no longer access to your account. The applications using OAuth to access
		your account. More informations about OAuth at the <a href="http://oauth.net/">website</a>.</p>

		<table class="table">
		<colgroup>
			<col width="*" />
			<col width="180" />
			<col width="180" />
		</colgroup>
		<thead>
		<tr>
			<th>Title</th>
			<th>Date</th>
			<th>Option</th>
		</tr>
		</thead>
		<tbody>
		<?php if(count($applications) > 0): ?>
		<?php foreach($applications as $application): ?>
		<tr>
			<td><a href="application/settings?appId=<?php echo $application->id; ?>"><?php echo $application->apiTitle; ?></a></td>
			<td><?php echo $application->getDate()->setTimezone($user->getTimezone())->format($registry['core.format_datetime']); ?></td>
			<td><input class="btn btn-default" type="button" onclick="amun.services.my.applicationsRevokeAccess(<?php echo $application->id . ',\'' . $accessUrl . '\''; ?>, this)" value="Revoke" /></td>
		</tr>
		<?php endforeach; ?>
		<?php else: ?>
		<tr>
			<td colspan="3">No applications found</td>
		</tr>
		<?php endif; ?>
		</tbody>
		</table>

		<?php if($pagingApplications->getPages() > 1): ?>
		<hr />
		<div class="amun-pagination">
			<ul class="pagination">
				<li><a href="<?php echo $pagingApplications->getFirstUrl(); ?>">First</a></li>
				<li><a href="<?php echo $pagingApplications->getPrevUrl(); ?>">Previous</a></li>
				<li><span><?php echo $pagingApplications->getPage(); ?> of <?php echo $pagingApplications->getPages(); ?></span></li>
				<li><a href="<?php echo $pagingApplications->getNextUrl(); ?>">Next</a></li>
				<li><a href="<?php echo $pagingApplications->getLastUrl(); ?>">Last</a></li>
			</ul>
		</div>
		<?php endif; ?>

	</div>

</div>


