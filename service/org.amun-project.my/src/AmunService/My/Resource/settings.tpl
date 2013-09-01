
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
		<div id="response"></div>
		<div id="settings_form"></div>
	</div>

</div>

<script type="text/javascript">
amun.services.my.loadSettingsForm('settings_form', '<?php echo $formUrl; ?>');
</script>


