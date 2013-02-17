
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

		<form method="post">
			<h3><?php echo $application->apiTitle; ?></h3>

			<?php if($application->apiStatus == AmunService_Oauth_Record::CLOSED): ?>
			<p><b>Note this application was disabled by the website administrator.</b></p>
			<?php endif; ?>

			<p><?php echo $application->apiDescription; ?></p>

			<?php if(!empty($application->apiUrl)): ?>
			<dl>
				<dt>Website</dt>
				<dd><a href="<?php echo $application->apiUrl; ?>"><?php echo $application->apiUrl; ?></a></dd>
			</dl>
			<?php endif; ?>
		
			<h4><a href="#expand-rights" onclick="$('.amun-service-my-app-rights').slideToggle();">Rights (+)</a></h4>

			<div class="amun-service-my-app-rights">
				<ul>
					<?php foreach($userRights as $right): ?>
						<li>
							<label for="right-<?php echo $right['rightId']; ?>" class="checkbox inline" style="white-space:nowrap;">
							<?php if(in_array($right['rightId'], $appRights)): ?>
							<input checked="checked" type="checkbox" name="right-<?php echo $right['rightId']; ?>" id="right-<?php echo $right['rightId']; ?>" value="1" /> <?php echo $right['rightDescription']; ?>
							<?php else: ?>
							<input type="checkbox" name="right-<?php echo $right['rightId']; ?>" id="right-<?php echo $right['rightId']; ?>" value="1" /> <?php echo $right['rightDescription']; ?>
							<?php endif; ?>
							</label>
						</li>
					<?php endforeach; ?>
				</ul>
				<hr />
			</div>

			<br />

			<p>
				<input class="btn btn-primary" type="submit" onclick="" value="Save" />
			</p>
		</form>

	</div>

</div>

<script type="text/javascript">
if (window.location.hash == '#expand-rights') {
	$('.amun-service-my-app-rights').slideToggle();
}
</script>
