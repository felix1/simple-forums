<form action="<?= current_url() ?>" method="post">
    <?= csrf_field() ?>

    <h2 class="text-center"><?= lang('auth.forgot') ?></h2>

    <?= $notice ?>

    <br>

    <p><?= lang('Auth.forgotNote') ?></p>

    <br>

    <div class="form-group">
        <label for="email">Email Address</label>
        <input type="email" name="email" class="form-control" placeholder="<?= lang('Auth.email') ?>" required autofocus value="<?= set_value('email') ?>" >
    </div>

    <br>

    <input type="submit" class="btn btn-lg btn-primary btn-block" value="<?= lang('Auth.send') ?>">

</form>
