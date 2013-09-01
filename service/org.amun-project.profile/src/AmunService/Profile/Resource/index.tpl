
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

	<div class="col-md-2 vcard hidden-phone">
		<img class="photo" src="<?php echo $account->thumbnailUrl; ?>" width="48" height="48" />
		<dl>
			<dt>Name</dt>
			<dd><a href="<?php echo $account->profileUrl; ?>" rel="me" class="nickname url uid"><?php echo $account->name; ?></a></dd>

			<dt>Karma</dt>
			<dd><?php echo $account->getKarma(); ?></dd>

			<dt>Registered on</dt>
			<dd><?php echo $account->getDate()->setTimezone($user->getTimezone())->format('Y-m-d'); ?></dd>

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

	<div class="col-md-10 amun-service-profile-activity">
		<ul class="media-list">
			<?php foreach($activities as $activity): ?>
			<li class="media amun-service-profile-activity-entry" id="activity-<?php echo $activity->id; ?>">
				<a class="pull-left" href="<?php echo $activity->authorProfileUrl; ?>">
					<img class="media-object" src="<?php echo $activity->authorThumbnailUrl; ?>" width="48" height="48" alt="avatar" />
				</a>
				<div class="media-body">
					<h4 class="media-heading"><a href="<?php echo $activity->authorProfileUrl; ?>"><?php echo $activity->authorName; ?></a></h4>
					<div class="amun-service-profile-activity-summary"><?php echo $activity->summary; ?></div>
					<p class="muted">
						created on
						<time datetime="<?php echo $activity->getDate()->format(DateTime::ATOM); ?>"><?php echo $activity->getDate()->setTimezone($user->getTimezone())->format($registry['core.format_datetime']); ?></time>
					</p>
				</div>
			</li>
			<?php endforeach; ?>
		</ul>
	</div>
</div>

<div class="row">
	<div class="col-md-12">
	<?php if($pagingActivities->getPages() > 1): ?>
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
	<?php endif; ?>
	</div>
</div>

<div class="row">
	<div class="col-md-12">
		<p><span class="muted">Last updated on <?php echo $account->getUpdated()->format($registry['core.format_date']); ?></span></p>
	</div>
</div>

</div>

