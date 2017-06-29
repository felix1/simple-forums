<h1>Recent Chatter</h1>

<br>

<?php if (! empty($threads)) : ?>

	<div class="thread-list">
		<?php foreach($threads as $thread) : ?>
			<div class="container thread">
				<div class="row">
					<div class="col">
						<p class="thread-title">
							<a href="<?= $thread->link() ?>"><?= esc($thread->title) ?></a>
							<p class="small">1 day ago by Some User</p>
						</p>
					</div>
					<div class="col-2 text-right">
						<p class="reply-count"><?= (int)$thread->post_count ?> <span><?= $thread->post_count === 1 ? 'reply' : 'replies' ?></span></p>
					</div>
				</div>
			</div>
		<?php endforeach ?>
	</div>

	<?= $pager->links() ?>

<?php else : ?>

	<div class="alert alert-warning">
		Whoa. I hear crickets. You've got to get people talking.
	</div>

<?php endif ?>
