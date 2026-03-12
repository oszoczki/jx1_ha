<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Management</title>
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
        <h1>Menu Management</h1>
        <?php if ($growl_success): ?>
            <script>window._growlSuccess = <?= json_encode($growl_success) ?>;</script>
        <?php endif ?>
        <?php if (! empty($errors)): ?>
            <div class="alert alert-error" role="alert">
                <ul class="error-list">
                    <?php foreach ($errors as $field => $message): ?>
                        <li><?= esc($message) ?></li>
                    <?php endforeach ?>
                </ul>
            </div>
        <?php endif ?>
        <section class="menu-form-section">
            <h2>Add menu item</h2>
            <?= form_open('/admin/menu/add', ['class' => 'menu-form']) ?>
                <?= csrf_field() ?>
                <div class="form-group">
                    <label for="title">Title *</label>
                    <input type="text" name="title" id="title" value="<?= esc(old('title')) ?>"
                           class="form-control" required maxlength="128">
                </div>
                <div class="form-group">
                    <label for="url">URL *</label>
                    <input type="text" name="url" id="url" value="<?= esc(old('url')) ?>"
                           class="form-control" maxlength="255" placeholder="/page or https://..." required>
                </div>
                <div class="form-group">
                    <label for="parent_id">Parent</label>
                    <select name="parent_id" id="parent_id" class="form-control">
                        <?php foreach ($parentOptions as $opt): ?>
                            <option value="<?= $opt['id'] ?? '' ?>" <?= (string)old('parent_id') === (string)($opt['id'] ?? '') ? 'selected' : '' ?>>
                                <?= esc($opt['label']) ?>
                            </option>
                        <?php endforeach ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="sort_order">Sort order</label>
                    <input type="number" name="sort_order" id="sort_order" value="<?= esc(old('sort_order', '0')) ?>"
                           class="form-control" min="0" step="1">
                </div>
                <button type="submit" class="btn btn-primary">Add menu item</button>
            <?= form_close() ?>
        </section>
        <section class="menu-tree-section">
            <h2>Current menu structure</h2>
            <div class="menu-tree">
                <?php if (empty($menuTree)): ?>
                    <p class="text-muted">No menu items yet. Add one above.</p>
                <?php else: ?>
                    <?= $this->setData(['treeNodes' => $menuTree])->include('admin/menu/_tree') ?>
                <?php endif ?>
            </div>
        </section>
    </main>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
    <script src="<?= base_url('assets/js/admin.js') ?>"></script>
    <?php if (! empty($growl_success)): ?>
    <script>window.showGrowl(window._growlSuccess, 'success');</script>
    <?php endif ?>
</body>
</html>
