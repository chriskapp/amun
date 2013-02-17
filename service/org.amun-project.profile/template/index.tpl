
<?php if(count($options) > 0): ?>
	<div class="amun-options">
		<ul class="nav nav-tabs">
			<?php foreach($options as $option): ?>
			<li><a href="<?php echo $option['href']; ?>"><?php echo $option['name']; ?></a></li>
			<?php endforeach; ?>
		</ul>
	</div>
<?php endif; ?>

<div class="row vcard amun-service-profile">

	<div class="span2 vcard hidden-phone">
		<img class="photo" src="<?php echo $account->thumbnailUrl; ?>" />
		<dl>
			<dt>Name</dt>
			<dd><a href="<?php echo $account->profileUrl; ?>" rel="me" class="nickname url uid"><?php echo $account->name; ?></a></dd>

			<dt>Karma</dt>
			<dd><?php echo $account->getKarma(); ?></dd>

			<dt>Registered on</dt>
			<dd><?php echo $account->getDate()->setTimezone($user->timezone)->format('Y-m-d'); ?></dd>

			<?php if($account->gender != 'undisclosed'): ?>
			<dt>Gender</dt>
			<dd><?php echo ucfirst($account->gender); ?></dd>
			<?php endif; ?>

			<?php if($account->countryTitle != 'Undisclosed'): ?>
			<dt>Country</dt>
			<dd><?php echo $account->countryTitle; ?></dd>
			<?php endif; ?>

			<?php if(!empty($account->timezone)): ?>
			<dt>Timezone</dt>
			<dd><?php echo $account->timezone; ?></dd>
			<?php endif; ?>
		</dl>
	</div>

	<div class="span10 amun-service-profile-activity">
		<?php foreach($activities as $activity): ?>
		<div class="row amun-service-profile-activity-entry" id="activity-<?php echo $activity->id; ?>">
			<img class="pull-left" src="<?php echo $activity->authorThumbnailUrl; ?>" alt="avatar" width="48" height="48" />
			<h4><a href="<?php echo $activity->authorProfileUrl; ?>"><?php echo $activity->authorName; ?></a></h4>
			<div class="amun-service-profile-activity-summary"><?php echo $activity->summary; ?></div>
			<p class="muted">
				created on
				<time datetime="<?php echo $activity->getDate()->format(DateTime::ATOM); ?>"><?php echo $activity->getDate()->setTimezone($user->timezone)->format($registry['core.format_datetime']); ?></time>
			</p>
		</div>
		<div class="clearfix"></div>
		<?php endforeach; ?>
	</div>

	<?php if($pagingActivities->getPages() > 1): ?>
	<div class="span12">
		<hr />
		<div class="pagination pagination-centered">
			<ul>
				<li><a href="<?php echo $pagingActivities->getFirstUrl(); ?>">First</a></li>
				<li><a href="<?php echo $pagingActivities->getPrevUrl(); ?>">Previous</a></li>
				<li><span><?php echo $pagingActivities->getPage(); ?> of <?php echo $pagingActivities->getPages(); ?></span></li>
				<li><a href="<?php echo $pagingActivities->getNextUrl(); ?>">Next</a></li>
				<li><a href="<?php echo $pagingActivities->getLastUrl(); ?>">Last</a></li>
			</ul>
		</div>
	</div>
	<?php endif; ?>

	<div class="span12">
		<span class="muted">Last updated on <?php echo $account->getUpdated()->format($registry['core.format_date']); ?></span>
	</div>

</div>

