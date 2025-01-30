<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::paginate(10);
        return view('pages.role.index', compact('roles'));
    }

    public function permission(int $roleID)
    {
        $role = Role::findOrFail($roleID)->toArray();
        $menus = (new Menu())->getMenuByRoleID($roleID);
        $menus = Menu::buildTree($menus);
        return view('pages.role.permission', compact('menus', 'role'));
    }

    public function permissionCreate(Request $request)
    {
        $data = $request->validate([
            'role_id' => 'required|integer',
            'menu_id' => 'required|integer',
            'is_view' => 'numeric|between:0,1',
            'is_add' => 'numeric|between:0,1',
            'is_edit' => 'numeric|between:0,1',
            'is_delete' => 'numeric|between:0,1',
        ]);
        $where = ['role_id' => $data['role_id'], 'menu_id' => $data['menu_id']];
        $createOrUpdate = (new Permission)->updateOrCreate($where, $data);
        return response()->json(['success' => true, 'result' => $createOrUpdate, '_token' => csrf_token()]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
