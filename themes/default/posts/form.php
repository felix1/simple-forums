<h1>Say Something...</h1>

<form action="" method="post">
    <?= csrf_field() ?>

    <div class="form-group">
        <label for="title" class="form-label">Post Title</label>
        <input type="text" name="title" class="form-control" value="<?= old('title') ?>">
    </div>

</form>
