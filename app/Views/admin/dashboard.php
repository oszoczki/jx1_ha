<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/admin.css') ?>">
</head>
<body class="admin-page">
    <header class="admin-header">
        <nav class="admin-nav">
            <a href="<?= base_url('admin/dashboard') ?>" class="nav-brand">Admin</a>
            <ul class="nav-menu">
                <li><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
                <li><a href="<?= base_url('admin/menu') ?>">Menu</a></li>
                <li><a href="<?= base_url('admin/login/logout') ?>">Logout</a></li>
            </ul>
            <span class="nav-user"><?= esc(session('nickname')) ?></span>
        </nav>
    </header>
    <main class="admin-main">
        <div id="growl-container"></div>
        <h1>Dashboard</h1>
        <p>Welcome, <strong><?= esc($nickname) ?></strong>. You are logged in.</p>
    </main>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
    <script src="<?= base_url('assets/js/admin.js') ?>"></script>
    <script>
    $(function() {
        var success = <?= json_encode($growl_success ?? '') ?>;
        if (success) {
            window.showGrowl(success, 'success');
        }
    });
    </script>
</body>
</html>
