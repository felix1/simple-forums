<h1>Say Something...</h1>

<?= $this->render('layouts/_notice') ?>

<?php if (isset($typeObject)) : ?>

    <form action="<?= current_url() ?>" method="post">
        <?= csrf_field() ?>

        <input type="hidden" name="postType" value="<?= get_class($typeObject) ?>">

        <?php if (isset($newThread)) : ?>
            <input type="hidden" name="newThread" value="1">
        <?php endif ?>

        <!-- Title -->
        <div class="form-group">
            <label for="title" class="form-label">Post Title</label>
            <input type="text" name="title" class="form-control" value="<?= old('title') ?>">
        </div>

        <?= $typeObject->displayForm(); ?>

        <hr>

        <div class="text-right">
            <button type="submit" class="btn btn-primary">Save Post</button>
        </div>
    </form>

<?php else: ?>

    <p class="lead">Select a post type to continue:</p>

    <div class="row">
        <!-- Post Type -->
        <div class="col-sm-4 form-group">
            <label for="type">Post Type</label>
            <select name="type" id="post-type" class="form-control">
                <option value="">Select one...</option>
                <?php foreach($types as $alias => $postType) : ?>
                    <option value="<?= $alias ?>" <?php if($alias == ($type ?? '')): ?> selected <?php endif ?>><?= ucfirst($alias) ?></option>
                <?php endforeach ?>
            </select>
        </div>
    </div>

<?php endif ?>


<?php $this->section('scripts') ?>

<script>
    $('#post-type').change(function(){
        var type = $(this).val();

        window.location='<?= current_url() ?>?type='+ type;
    });
</script>

<?php $this->endSection() ?>
