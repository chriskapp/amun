
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

	<div class="span2 hidden-phone">
		<img src="<?php echo $account->thumbnailUrl; ?>" />
		<dl>
			<dt>Name</dt>
			<dd><?php echo $account->name; ?></dd>

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

	<div class="span10 amun-service-my-activity">

		<div class="row" id="activity">
			<div id="response" class="alert alert-error" style="display:none;"></div>
			<form id="activity-form-0" method="post" action="<?php echo $activityUrl; ?>" class="form-inline activity-form">
				<input type="hidden" name="parentId" value="0" />
				<input type="hidden" name="verb" id="verb" value="post" />
				<p>
					<textarea name="summary" id="summary" style="width:96%;height:64px;"></textarea>
				</p>
				<p>
					<input class="btn btn-primary" type="submit" value="Send" style="padding:4px;" />
					<select name="scope" id="scope" style="width:140px">
						<option value="0">Everyone</option>
						<?php foreach($groups as $group): ?>
						<option value="<?php echo $group['id']; ?>"><?php echo $group['title']; ?></option>
						<?php endforeach; ?>
					</select>
				</p>
			</form>
		</div>

		<?php foreach($activities as $activity): ?>
		<div class="row amun-service-my-activity-entry <?php if($activity->receiverStatus == AmunService\User\Activity\Receiver\Record::HIDDEN): ?>amun-service-my-activity-entry-hidden<?php endif; ?>" id="activity-<?php echo $activity->id; ?>">
			<?php if($activity->receiverStatus == AmunService\User\Activity\Receiver\Record::VISIBLE): ?>
				<div class="pull-right"><a class="btn" href="#" onclick="amun.services.my.setActivityStatus(<?php echo $activity->receiverId . ',\'' . $receiverUrl . '\',this'; ?>);return false;" data-status="2" title="Hides the activity on your public profile">Hide</a></div>
			<?php else: ?>
				<div class="pull-right"><a class="btn" href="#" onclick="amun.services.my.setActivityStatus(<?php echo $activity->receiverId . ',\'' . $receiverUrl . '\',this'; ?>);return false;" data-status="1" title="Shows the activity on your public profile">Show</a></div>
			<?php endif; ?>
			<img class="pull-left" src="<?php echo $activity->authorThumbnailUrl; ?>" alt="avatar" width="48" height="48" />
			<h4><a href="<?php echo $activity->authorProfileUrl; ?>"><?php echo $activity->authorName; ?></a></h4>
			<div class="amun-service-my-activity-summary"><?php echo $activity->summary; ?></div>
			<?php $comments = $activity->getComments(); ?>
			<?php if(!empty($comments)): ?>
				<p class="muted">
					created on
					<time datetime="<?php echo $activity->getDate()->format(DateTime::ATOM); ?>"><?php echo $activity->getDate()->setTimezone($user->timezone)->format($registry['core.format_datetime']); ?></time>
				</p>
				<div class="amun-service-my-activity-entry-comments" id="activity-comments-<?php echo $activity->id; ?>">
					<?php foreach($activity->getComments() as $comment): ?>
					<div class="amun-service-my-activity-entry" id="activity-<?php echo $comment->id; ?>">
						<img class="pull-left" src="<?php echo $comment->authorThumbnailUrl; ?>" alt="avatar" width="48" height="48" />
						<h4><a href="<?php echo $comment->authorProfileUrl; ?>"><?php echo $comment->authorName; ?></a></h4>
						<div class="amun-service-my-activity-summary"><?php echo $comment->summary; ?></div>
						<p class="muted">
							created on
							<time datetime="<?php echo $comment->getDate()->format(DateTime::ATOM); ?>"><?php echo $comment->getDate()->setTimezone($user->timezone)->format($registry['core.format_datetime']); ?></time>
						</p>
					</div>
					<?php endforeach; ?>
				</div>
				<div class="amun-service-my-activity-entry-reply" id="activity-reply-<?php echo $activity->id; ?>" style="display:block;">
					<form id="activity-form-<?php echo $activity->id; ?>" method="post" action="<?php echo $activityUrl; ?>" class="form-inline activity-form">
						<input type="hidden" name="parentId" value="<?php echo $activity->id; ?>" />
						<input type="hidden" name="verb" id="verb" value="post" />
						<input type="hidden" name="scope" id="scope" value="0" />
						<p>
							<textarea name="summary" placeholder="Write a comment" onfocus="$(this).css('height', '48');"></textarea>
						</p>
						<p>
							<input class="btn btn-primary" type="submit" value="Comment" />
						</p>
					</form>
				</div>
			<?php else: ?>
				<p class="muted">
					<a href="#" onclick="$('#activity-reply-<?php echo $activity->id; ?>').fadeToggle();return false;">Comment</a>
					created on
					<time datetime="<?php echo $activity->getDate()->format(DateTime::ATOM); ?>"><?php echo $activity->getDate()->setTimezone($user->timezone)->format($registry['core.format_datetime']); ?></time>
				</p>
				<div class="amun-service-my-activity-entry-comments" id="activity-comments-<?php echo $activity->id; ?>">
				</div>
				<div class="amun-service-my-activity-entry-reply" id="activity-reply-<?php echo $activity->id; ?>">
					<form id="activity-form-<?php echo $activity->id; ?>" method="post" action="<?php echo $activityUrl; ?>" class="form-inline activity-form">
						<input type="hidden" name="parentId" value="<?php echo $activity->id; ?>" />
						<input type="hidden" name="verb" id="verb" value="post" />
						<input type="hidden" name="scope" id="scope" value="0" />
						<p>
							<textarea name="summary" placeholder="Write a comment" onfocus="$(this).css('height', '48');"></textarea>
						</p>
						<p>
							<input class="btn btn-primary" type="submit" value="Comment" />
						</p>
					</form>
				</div>
			<?php endif; ?>
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

<script type="text/javascript">
$(document).ready(function(){

	$('.activity-form').each(function(){

		amun.services.my.loadSubmitActivity($(this).attr('id'));

	});

});
</script>


