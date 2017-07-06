<form action="<?= current_url() ?>" method="post" id="login_form">
    <?= csrf_field() ?>

    <h2 class="text-center"><?= lang('auth.signin') ?></h2>

    <?= $notice ?>

    <br>

    <div class="form-group">
        <label for="email"><?= lang('auth.email') ?></label>
        <input type="email" name="email" class="form-control" placeholder="john.doe@example.com" required autofocus value="<?= set_value('email') ?>" >
    </div>

    <div class="form-group">
        <label for="password"><?= lang('auth.password') ?></label>
        <input type="password" name="password" class="form-control" required >
    </div>

    <div class="form-check">
        <label class="form-check-label">
            <input name="remember" class="form-check-input" type="checkbox" value="1" <?= set_checkbox('remember', 1) ?>>
	        <?= lang('auth.rememberLabel') ?>
        </label>
    </div>

    <br>

    <input type="submit" class="btn btn-lg btn-primary btn-block" id="submit" name="submit" value="<?= lang('auth.signin') ?>">

    <br/>
    <p><?= lang('auth.needAccount') ?></p>
    <p><?= lang('auth.forgotPass') ?></p>

</form>
