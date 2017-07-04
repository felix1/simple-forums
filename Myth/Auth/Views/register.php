<form action="<?= current_url() ?>" id="join_form" method="post">
    <?= csrf_field() ?>

    <h2 class="text-center"><?= lang('auth.register') ?></h2>

    <?= $notice ?>

    <br>

    <div class="form-group">
        <label for="first_name"><?= lang('auth.first_name') ?></label>
        <input type="text" name="first_name" class="form-control" required autofocus value="<?= set_value('first_name') ?>">
    </div>

    <div class="form-group">
        <label for="last_name"><?= lang('auth.last_name') ?></label>
        <input type="text" name="last_name" class="form-control" required value="<?= set_value('last_name') ?>" >
    </div>

    <div class="form-group">
        <label for="email"><?= lang('auth.email') ?></label>
        <input type="email" name="email" class="form-control" required value="<?= set_value('email') ?>">
    </div>

    <br/>

    <div class="form-group">
        <label for="username"><?= lang('auth.username') ?></label>
        <input type="text" name="username" class="form-control" required value="<?= set_value('username') ?>">
    </div>

    <div class="form-group">
        <label for="password"><?= lang('auth.password') ?></label>
        <input type="password" name="password" id="password" class="form-control" required>
    </div>

    <div class="form-group">
        <label for="pass_confirm"><?= lang('auth.pass_confirm') ?></label>
        <input type="password" name="pass_confirm" id="pass-confirm" class="form-control" required>
    </div>


<!--    --><?//= $uikit->notice(lang('auth.password_strength'), 'default', false, ['class' => 'pass-strength']); ?>

    <br>

    <input class="btn btn-lg btn-primary btn-block" id="submit" name="submit" type="submit" value="<?= lang('auth.register') ?>" />

    <br/>
    <p><?= lang('auth.have_account') ?></p>

</form>
