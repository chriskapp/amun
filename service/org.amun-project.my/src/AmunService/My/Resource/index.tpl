
<?php if(count($options) > 0): ?>
	<div class="amun-options">
		<ul class="nav nav-tabs">
			<?php foreach($options as $option): ?>
			<li><a href="<?php echo $option['href']; ?>"><?php echo $option['name']; ?></a></li>
			<?php endforeach; ?>
		</ul>
	</div>
<?php endif; ?>

<div class="row amun-service-my">

	<div class="col-md-2 hidden-phone">
		<img src="<?php echo $account->thumbnailUrl; ?>" width="48" height="48" />
		<dl>
			<dt>Name</dt>
			<dd><?php echo $account->name; ?></dd>

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

	<div class="col-md-10 amun-service-my-activity">

		<div id="activity">
			<div id="response" class="alert alert-danger" style="display:none;"></div>
			<form id="activity-form-0" method="post" action="<?php echo $activityUrl; ?>" class="form-inline activity-form">
				<input type="hidden" name="parentId" value="0" />
				<input type="hidden" name="verb" id="verb" value="post" />
				<div>
					<textarea name="summary" id="summary" rows="3" class="form-control"></textarea>
				</div>
				<div class="form-group">
					<input class="btn btn-primary" type="submit" value="Send" />
					<select name="scope" id="scope" style="width:140px" class="form-control">
						<option value="0">Everyone</option>
						<?php foreach($groups as $group): ?>
						<option value="<?php echo $group['id']; ?>"><?php echo $group['title']; ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</form>
		</div>

		<hr />

		<ul class="media-list">
			<?php foreach($activities as $activity): ?>
			<li class="media amun-service-my-activity-entry <?php if($activity->receiverStatus == AmunService\User\Activity\Receiver\Record::HIDDEN): ?>amun-service-my-activity-entry-hidden<?php endif; ?>" id="activity-<?php echo $activity->id; ?>">

				<!--
				<?php if($activity->receiverStatus == AmunService\User\Activity\Receiver\Record::VISIBLE): ?>
				<div class="pull-right"><a class="btn btn-default" href="#" onclick="amun.services.my.setActivityStatus(<?php echo $activity->receiverId . ',\'' . $receiverUrl . '\',this'; ?>);return false;" data-status="2" title="Hides the activity on your public profile">Hide</a></div>
				<?php else: ?>
				<div class="pull-right"><a class="btn btn-default" href="#" onclick="amun.services.my.setActivityStatus(<?php echo $activity->receiverId . ',\'' . $receiverUrl . '\',this'; ?>);return false;" data-status="1" title="Shows the activity on your public profile">Show</a></div>
				<?php endif; ?>
				-->

				<a class="pull-left" href="<?php echo $activity->authorProfileUrl; ?>">
					<img class="media-object" src="<?php echo $activity->authorThumbnailUrl; ?>" width="48" height="48" alt="avatar" />
				</a>
				<div class="media-body">
					<h4 class="media-heading"><a href="<?php echo $activity->authorProfileUrl; ?>"><?php echo $activity->authorName; ?></a></h4>
					<div class="amun-service-my-activity-summary"><?php echo $activity->summary; ?></div>

					<?php $comments = $activity->getComments(); ?>
					<?php if(!empty($comments)): ?>
						<p class="muted">
							created on
							<time datetime="<?php echo $activity->getDate()->format(DateTime::ATOM); ?>"><?php echo $activity->getDate()->setTimezone($user->getTimezone())->format($registry['core.format_datetime']); ?></time>
						</p>

						<ul class="media-list amun-service-my-activity-entry-comments" id="activity-comments-<?php echo $activity->id; ?>">
							<?php foreach($comments as $comment): ?>
							<li class="media amun-service-my-activity-entry" id="activity-<?php echo $comment->id; ?>">
								<a class="pull-left" href="<?php echo $comment->authorProfileUrl; ?>">
									<img class="media-object" src="<?php echo $comment->authorThumbnailUrl; ?>" width="48" height="48" alt="avatar" />
								</a>
								<div class="media-body">
									<h4 class="media-heading"><a href="<?php echo $comment->authorProfileUrl; ?>"><?php echo $comment->authorName; ?></a></h4>
									<div class="amun-service-my-activity-summary"><?php echo $comment->summary; ?></div>
									<p class="muted">
										created on
										<time datetime="<?php echo $activity->getDate()->format(DateTime::ATOM); ?>"><?php echo $activity->getDate()->setTimezone($user->getTimezone())->format($registry['core.format_datetime']); ?></time>
									</p>
								</div>
							</li>
							<?php endforeach; ?>
						</ul>

						<div class="amun-service-my-activity-entry-reply" id="activity-reply-<?php echo $activity->id; ?>" style="display:block;">
							<form id="activity-form-<?php echo $activity->id; ?>" method="post" action="<?php echo $activityUrl; ?>" class="form-inline activity-form">
								<input type="hidden" name="parentId" value="<?php echo $activity->id; ?>" />
								<input type="hidden" name="verb" id="verb" value="post" />
								<input type="hidden" name="scope" id="scope" value="0" />
								<div>
									<textarea name="summary" placeholder="Write a comment" rows="1" onfocus="$(this).attr('rows', '2');" class="form-control"></textarea>
								</div>
								<div class="form-group">
									<input class="btn btn-primary" type="submit" value="Comment" />
								</div>
							</form>
						</div>
					<?php else: ?>
						<p class="muted">
							<a href="#" onclick="$('#activity-reply-<?php echo $activity->id; ?>').fadeToggle();return false;">Comment</a>
							created on
							<time datetime="<?php echo $activity->getDate()->format(DateTime::ATOM); ?>"><?php echo $activity->getDate()->setTimezone($user->getTimezone())->format($registry['core.format_datetime']); ?></time>
						</p>

						<ul class="media-list" class="amun-service-my-activity-entry-comments" id="activity-comments-<?php echo $activity->id; ?>">
						</ul>

						<div class="amun-service-my-activity-entry-reply" id="activity-reply-<?php echo $activity->id; ?>" style="display:none;">
							<form id="activity-form-<?php echo $activity->id; ?>" method="post" action="<?php echo $activityUrl; ?>" class="form-inline activity-form">
								<input type="hidden" name="parentId" value="<?php echo $activity->id; ?>" />
								<input type="hidden" name="verb" id="verb" value="post" />
								<input type="hidden" name="scope" id="scope" value="0" />
								<p>
									<textarea name="summary" placeholder="Write a comment" onfocus="$(this).css('height', '68');" class="form-control"></textarea>
								</p>
								<p>
									<input class="btn btn-primary" type="submit" value="Comment" />
								</p>
							</form>
						</div>
					<?php endif; ?>
				</div>
			</li>
			<?php endforeach; ?>
		</ul>

	</div>
</div>

<div class="row">
	<?php if($pagingActivities->getPages() > 1): ?>
	<div class="col-md-12">
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
</div>

<div class="row">
	<div class="col-md-12">
		<span class="muted">Last updated on <?php echo $account->getUpdated()->format($registry['core.format_date']); ?></span>
	</div>
</div>

<script type="text/javascript">
$(document).ready(function(){

	$('.activity-form').each(function(){

		amun.services.my.loadSubmitActivity($(this).attr('id'));

	});

});
</script>
