<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/admin.css') ?>">
</head>
<body class="login-page">
    <div class="login-container">
        <h1>Admin Login</h1>
        <?php if (! empty($errors)): ?>
            <div class="alert alert-error" role="alert">
                <ul class="error-list">
                    <?php foreach ($errors as $field => $message): ?>
                        <li><?= esc($message) ?></li>
                    <?php endforeach ?>
                </ul>
            </div>
        <?php endif ?>
        <?= form_open('/admin/login/process', ['class' => 'login-form']) ?>
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="nickname">Nickname</label>
                <input type="text" name="nickname" id="nickname" value="<?= esc(old('nickname')) ?>"
                       class="form-control" autocomplete="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" class="form-control"
                       autocomplete="current-password" required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        <?= form_close() ?>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
    <script src="<?= base_url('assets/js/admin.js') ?>"></script>
</body>
</html>
