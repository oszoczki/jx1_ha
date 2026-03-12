<?php
/**
 * Recursive partial for menu tree.
 * Expects $treeNodes (array of nodes with 'children' key) and $level (int). Use unique name to avoid scope override.
 */

$treeNodes = $treeNodes ?? [];
if (! is_array($treeNodes)) {
    $treeNodes = [];
}
$level = (int) ($level ?? 0);
?>
<ul class="menu-tree-list <?= $level > 0 ? 'menu-tree-children' : '' ?>" data-level="<?= $level ?>">
<?php foreach ($treeNodes as $node): ?>
    <li class="menu-tree-item">
        <span class="menu-tree-node">
            <?= esc($node['title'] ?? '') ?>
            <?php if (! empty($node['url'])): ?>
                <small class="menu-tree-url"><?= esc($node['url']) ?></small>
            <?php endif ?>
        </span>
        <?php if (! empty($node['children'])): ?>
            <?= $this->setData(['treeNodes' => $node['children'], 'level' => $level + 1])->include('admin/menu/_tree') ?>
        <?php endif ?>
    </li>
<?php endforeach ?>
</ul>
