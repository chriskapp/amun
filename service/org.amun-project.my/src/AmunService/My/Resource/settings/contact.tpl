
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

		<p>A list of contacts wich can be used by the system to contact you. After adding an
		contact you will receive further informations howto activate the contact. If the system
		has often problems to send messages to a specific account it may get disabled.</p>

		<table class="table">
		<colgroup>
			<col width="*" />
			<col width="200" />
			<col width="100" />
		</colgroup>
		<thead>
		<tr>
			<th>Value</th>
			<th>Type</th>
			<th>Option</th>
		</tr>
		</thead>		<tbody>
		<?php if(count($contacts) > 0): ?>
		<?php foreach($contacts as $contact): ?>
		<tr>
			<?php if($contact->status == AmunService_My_Contact_Record::UNCHECKED): ?>
			<td><div style="overflow:hidden;width:380px;color:#999;" data-type="<?php echo $contact->type; ?>"><?php echo $contact->getValue(); ?></div></td>
			<?php else: ?>
			<td><div style="overflow:hidden;width:380px;" data-type="<?php echo $contact->type; ?>"><?php echo $contact->getValue(); ?></div></td>
			<?php endif; ?>
			<td><?php echo $contact->getDate()->setTimezone($user->getTimezone())->format($registry['core.format_datetime']); ?></td>
			<td><input type="button" onclick="amun.services.my.contactsRemove(<?php echo $contact->id . ',\'' . $contactUrl . '\''; ?>, this)" value="Remove" /></td>
		</tr>
		<?php endforeach; ?>
		<?php else: ?>
		<tr>
			<td colspan="3">No contacts found</td>
		</tr>
		<?php endif; ?>
		</tbody>
		</table>

		<?php if($pagingContacts->getPages() > 1): ?>
		<hr />
		<div class="amun-pagination">
			<ul class="pagination">
				<li><a href="<?php echo $pagingContacts->getFirstUrl(); ?>">First</a></li>
				<li><a href="<?php echo $pagingContacts->getPrevUrl(); ?>">Previous</a></li>
				<li><span><?php echo $pagingContacts->getPage(); ?> of <?php echo $pagingContacts->getPages(); ?></span></li>
				<li><a href="<?php echo $pagingContacts->getNextUrl(); ?>">Next</a></li>
				<li><a href="<?php echo $pagingContacts->getLastUrl(); ?>">Last</a></li>
			</ul>
		</div>
		<?php endif; ?>

		<hr />

		<div id="response"></div>

		<div id="contact_form"></div>

	</div>

	<hr />

</div>

<script type="text/javascript">
amun.services.my.loadSettingsForm('contact_form', '<?php echo $formUrl; ?>');
</script>

