
<p>
proudly powered by <a href="http://amun.phpsx.org">amun</a> <?php echo \Amun\Base::getVersion(); ?><br />
rendering time <?php echo $render; ?> seconds and <?php echo $sql->getCount(); ?> sql queries<br />
</p>


<div id="amun-form-window" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="amun-form-window-label" aria-hidden="true">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
		<h3 id="amun-form-window-label">Form</h3>
	</div>
	<div class="modal-body">
		<div id="amun-form-window-body">
			<div id="amun-form-window-response"></div>
			<div id="amun-form-window-form"></div>
			<div id="amun-form-window-preview" class="amun-preview"></div>
		</div>
	</div>
	<div class="modal-footer" id="amun-form-window-buttons"></div>
</div>
