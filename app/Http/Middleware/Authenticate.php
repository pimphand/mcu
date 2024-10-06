<?php

namespace App\Http\Middleware;

use App\Models\Menu;
use Closure;
use Illuminate\Http\Request;

;
use Illuminate\Support\Facades\Auth;

class Authenticate
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('login.index');
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (!Auth::check()) {
            return redirect()->route('login.index')->with('error', 'Your session has been Expired, please login again.');
        }
        $user = Auth::user();
        $roleID = $user->role_id;
        if (!$roleID) {
            Auth::logout();
            return redirect()->route('login.index')->with('error', 'Access Denied, Your role has\'t been set.');
        }

        $menus = (new Menu)->getMenuByRoleID($roleID);
        \View::share(['dataMenus' => self::buildTree($menus), 'dataUser' => $user]);

        return $next($request);
    }

    private static function buildTree(array $data, $parentId = null)
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
