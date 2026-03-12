<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\MenuItemModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class MenuController extends BaseController
{
    protected MenuItemModel $menuItemModel;
    protected $helpers = ['form', 'url'];

    public function initController(RequestInterface $request, ResponseInterface $response, $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->menuItemModel = model(MenuItemModel::class);
    }

    /**
     * List menu tree and show add form.
     */
    public function index(): string|RedirectResponse
    {
        $tree = $this->menuItemModel->getMenuTree();
        $flatParents = $this->getFlatParentsForSelect($tree);
        $errors = session()->getFlashdata('errors') ?? [];
        $growlSuccess = session()->getFlashdata('growl_success');
        return view('admin/menu/index', [
            'menuTree'     => $tree,
            'parentOptions' => $flatParents,
            'errors'       => $errors,
            'growl_success' => $growlSuccess,
        ]);
    }

    /**
     * Add new menu item (POST).
     */
    public function add(): RedirectResponse
    {
        $rules = [
            'title' => 'required|min_length[1]|max_length[128]',
            'url'   => 'required|max_length[255]',
            'parent_id' => 'permit_empty|integer',
            'sort_order' => 'permit_empty|integer',
        ];
        $messages = [
            'title' => [
                'required'   => 'The title is required.',
                'min_length' => 'The title must be at least 1 character.',
                'max_length'  => 'The title must not exceed 128 characters.',
            ],
        ];
        if (! $this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $parentId = $this->request->getPost('parent_id');
        $data = [
            'title'      => $this->request->getPost('title'),
            'url'        => $this->request->getPost('url') ?: null,
            'parent_id'  => $parentId !== '' && $parentId !== null ? (int) $parentId : null,
            'sort_order' => (int) ($this->request->getPost('sort_order') ?: 0),
        ];
        $this->menuItemModel->insert($data);
        return redirect()->to('/admin/menu')->with('growl_success', 'Menu item added successfully.');
    }

    /**
     * Build flat list of (id, title with indent) for parent dropdown.
     *
     * @param array<int, array> $tree
     * @param int $level
     * @return array<int, array{id: int, label: string}>
     */
    private function getFlatParentsForSelect(array $tree, int $level = 0): array
    {
        $out = [];
        if ($level === 0) {
            $out[0] = ['id' => null, 'label' => '-- Root (no parent) --'];
        }
        foreach ($tree as $item) {
            $indent = str_repeat('　', $level);
            $out[$item['id']] = ['id' => (int) $item['id'], 'label' => $indent . $item['title']];
            if (! empty($item['children'])) {
                $childOptions = $this->getFlatParentsForSelect($item['children'], $level + 1);
                foreach ($childOptions as $k => $v) {
                    if ($k !== 0) {
                        $out[$k] = $v;
                    }
                }
            }
        }
        return $out;
    }
}
