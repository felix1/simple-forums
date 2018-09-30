<div class="jumbotron text-center">
    <h1><?= esc($forum->name) ?></h1>
    <p class="lead"><?= esc($forum->description) ?></p>
</div>


<div class="thread-meta container">
    <div class="row">
        <div class="col">
		    <?= $forum->thread_count ?> Discussions
        </div>
        <div class="col text-center">
            <a href="#">Newest</a>
            <a href="#">Popular</a>
            <a href="#">Unanswered</a>
        </div>
        <div class="col text-right">

            <?php if ($current_user) : ?>
                <a href="<?= route_to('newPost', $forum->id) ?>" class="btn btn-primary">New Discussion</a>
            <?php else : ?>
                <a href="<?= route_to('login') ?>" class="btn btn-primary">Log in to Post</a>
            <?php endif ?>

        </div>
    </div>
</div>

<div class="thread-list">
    <?php foreach ($threads as $thread) : ?>
    <div class="container thread">
        <div class="row">
            <div class="col-1 text-center">
                <img src="<?= $thread->user->avatar(45) ?>" alt="<?= $thread->user->username ?>" class="rounded-circle">
            </div>
            <div class="col">
                <p>
                    <a href="<?= $thread->link() ?>"><?= esc($thread->title) ?></a>
                    <br><?= $thread->userSummaryLine() ?></a>
                </p>
            </div>
            <div class="col-2">
                <?=  $thread->post_count ?>
                <span>replies</span>
            </div>
        </div>
    </div>
    <?php endforeach ?>
</div>

<?php if ($pager) $pager->links() ?>
