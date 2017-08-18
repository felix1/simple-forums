<?php if (isset($notice) && ! empty($notice)) : ?>
	<div class="alert alert-<?= $notice['type'] ?>" role="alert">
		<button type="button" class="close" data-dismiss="alert" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		</button>
		<?= $notice['message'] ?>
	</div>
<?php else: ?>

	<div id="notice"></div>

<?php endif; ?>
