<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $fillable = [
        'parent_id', 'name', 'url', 'icon', 'sort_order', 'is_active',
    ];

    public function checkAvailabilityMenuPermission(int $menuID, int $roleID, string $condition): int
    {
        return self::join('menus', 'permissions.menu_id', '=', 'menus.id')
            ->where("menus.id = '{$menuID}' AND permissions.role_id = '{$roleID}' {$condition}")->count();
    }

    public function getMenuByRoleID(int $roleID): array
    {
        $query = self::select('permissions.role_id', 'menus.id as menu_id', 'menus.parent_id', 'menus.name', 'menus.url', 'menus.icon', 'permissions.id as role_permission_id', 'permissions.is_view', 'permissions.is_add', 'permissions.is_edit', 'permissions.is_delete');
        $query->leftJoin('permissions', function ($join) use ($roleID) {
            $join->on('menus.id', '=', 'permissions.menu_id');
            $join->where('permissions.role_id', $roleID);
        });

        return $query->where('menus.is_active', 1)->orderBy('menus.sort_order')->get()->toArray();
    }

    public function getMenuByUserRole(int $roleID): array
    {
        return $this->getMenuByRoleID($roleID);
    }

    public static function buildTree(array $data, $parentId = null)
    {
        $tree = array();

        foreach ($data as $node) {
            $node['slug'] = '';
            if ($node['parent_id'] == $parentId) {
                $children = self::buildTree($data, $node['menu_id']);
                if ($children) {
                    $node['sub_menu'] = $children;
                }
                $tree[$node['menu_id']] = $node;
                unset($data[$node['menu_id']]);
            }
        }

        return $tree;
    }
}
