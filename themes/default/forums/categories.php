<div class="jumbotron">
    <h1>Simple Forums</h1>
    <p class="lead">Your home for classy discussions. About whatever.</p>
</div>

<br>

<?php if (! empty($categories)) : ?>

    <?php foreach ($categories as $category): ?>

        <?php if (count($category->forums)) : ?>
            <div class="category text-center">
                <h2><?= esc($category->name) ?></h2>

                <div class="card-deck">
                    <?php $count = 0; ?>
	                <?php foreach ($category->forums as $forum) : ?>
                        <?php $count++ ?>
                        <div class="card forum" style="max-width: 30%;">
                            <div class="card-block">
                                <h4 class="card-title">
                                    <a href="<?= $forum->link() ?>"><?= esc($forum->name) ?></a>
                                </h4>

                                <br>

                                <div class="container">
                                    <div class="row">
                                        <div class="col">
                                            <p class="card-stat">
				                                <?= $forum->thread_count ?>
                                                <span>Topics</span>
                                            </p>
                                        </div>
                                        <div class="col">
                                            <p class="card-stat">
				                                <?= $forum->post_count ?>
                                                <span>Posts</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <br>

				                <?= $formatter->autoTypography($forum->description) ?>
                            </div>
                        </div>

                        <?php if ($count === 3) : ?>
                </div>
                <br>
                <div class="card-deck">
                        <?php endif ?>
	                <?php endforeach ?>
                </div>
            </div>

            <hr>
        <?php endif ?>
    <?php endforeach ?>

<?php else: ?>

    <div class="alert alert-warning">
        Damn. Nothing here. Make some categories, Admin!
    </div>

<?php endif ?>
