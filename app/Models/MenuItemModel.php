<?php

namespace App\Models;

use CodeIgniter\Model;

class MenuItemModel extends Model
{
    protected $table            = 'menu_items';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = ['parent_id', 'title', 'url', 'sort_order', 'created_at', 'updated_at'];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    /**
     * Get all menu items ordered by parent and sort_order.
     *
     * @return array<int, array>
     */
    public function getAllOrdered(): array
    {
        return $this->orderBy('parent_id', 'ASC')
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }

    /**
     * Whether the given value means "root" (no parent).
     */
    private function isRootParent($value): bool
    {
        return $value === null || $value === '' || $value === 0;
    }

    /**
     * Build hierarchical tree from flat list (recursive).
     *
     * @param array<int, array> $items Flat list of menu items
     * @param int|null          $parentId Parent ID (null = root)
     * @return array<int, array>
     */
    public function buildTree(array $items, $parentId = null): array
    {
        $branch = [];
        $wantRoot = $this->isRootParent($parentId);
        foreach ($items as $item) {
            $itemParentId = $item['parent_id'] ?? null;
            $itemIsRoot = $this->isRootParent($itemParentId);
            $match = $wantRoot
                ? $itemIsRoot
                : (!$itemIsRoot && (int) $itemParentId === (int) $parentId);
            if ($match) {
                $node = $item;
                $node['children'] = $this->buildTree($items, (int) $item['id']);
                $branch[] = $node;
            }
        }
        return $branch;
    }

    /**
     * Get menu tree for display.
     *
     * @return array<int, array>
     */
    public function getMenuTree(): array
    {
        $items = $this->getAllOrdered();
        return $this->buildTree($items, null);
    }
}
