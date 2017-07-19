<!-- Always show the jumbo question title -->
<div class="jumbotron">
	<h1><?= esc($thread->title) ?></h1>
</div>

<!-- First Post (body only shown on page 1 of results) -->
<?php if (empty($_GET['page'])) : ?>

	<?= $thread->firstPost()->display() ?>

<?php endif ?>

<?php foreach ($thread->posts() as $post) : ?>

	<?php if ($post->id != $thread->first_post) : ?>
		<?= $post->display(); ?>
	<?php endif ?>

<?php endforeach ?>

<?= $pager->links() ?>
