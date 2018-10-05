<?php if (isset($notice) && is_array($notice) && ! empty($notice)) : ?>
	<div class="alert alert-<?= $notice['type'] ?>" role="alert">
		<button type="button" class="close" data-dismiss="alert" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		</button>
		<?= $notice['message'] ?>
	</div>
<?php elseif (session('errors')) : ?>
    <div class="alert alert-danger">
        <?php foreach (session('errors') as $error) : ?>
            <li><?= esc($error) ?></li>
        <?php endforeach ?>
    </div>
<?php else: ?>

	<div id="notice"></div>

<?php endif; ?>
