
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

		<p>A list of OpenID connections wich you have permanently allowed or
		disallowed. If you revoke a connection you will be asked again whether you want connect to the website.
		More informations about OpenId at the <a href="http://openid.net/">website</a>.</p>

		<table class="table">
		<colgroup>
			<col width="*" />
			<col width="180" />
			<col width="70" />
		</colgroup>
		<thead>
		<tr>
			<th>Url</th>
			<th>Date</th>
			<th>Option</th>
		</tr>
		</thead>
		<tbody>
		<?php if(count($connections) > 0): ?>
		<?php foreach($connections as $connection): ?>
		<tr>
		<td>
			<td><a href="http://<?php echo $connection->returnTo; ?>"><?php echo $connection->returnTo; ?></a></td>
			<td><?php echo $connection->getDate()->setTimezone($user->getTimezone())->format($registry['core.format_datetime']); ?></td>
			<td><input type="button" onclick="amun.services.my.connectionsRevokeAccess(<?php echo $connection->id . ',\'' . $accessUrl . '\''; ?>, this)" value="Revoke" /></td>
		</tr>
		<?php endforeach; ?>
		<?php else: ?>
		<tr>
			<td colspan="3">No connections found</td>
		</tr>
		<?php endif; ?>
		</tbody>
		</table>

		<?php if($pagingConnections->getPages() > 1): ?>
		<hr />
		<div class="amun-pagination">
			<ul class="pagination">
				<li><a href="<?php echo $pagingConnections->getFirstUrl(); ?>">First</a></li>
				<li><a href="<?php echo $pagingConnections->getPrevUrl(); ?>">Previous</a></li>
				<li><span><?php echo $pagingConnections->getPage(); ?> of <?php echo $pagingConnections->getPages(); ?></span></li>
				<li><a href="<?php echo $pagingConnections->getNextUrl(); ?>">Next</a></li>
				<li><a href="<?php echo $pagingConnections->getLastUrl(); ?>">Last</a></li>
			</ul>
		</div>
		<?php endif; ?>

	</div>

</div>

