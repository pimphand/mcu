<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    private UserService $userService;
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $users = $this->userService->paginate($request->get('limit', 10));

        return view('pages.user.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::whereIn('level', [1, 2])->get();
        return view('pages.user.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'role_id' => 'required|exists:roles,id',
            'name' => 'required',
            'password' => 'required',
            'username' => 'required|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'is_active' => 'between:0,1',
        ]);

        $this->userService->create($data);

        return redirect()->route('user.index')->with('success', 'Data berhasil simpan');
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
    public function edit(int $id)
    {
        $roles = Role::whereIn('level', [1, 2])->get();
        $user = $this->userService->find($id);
        return view('pages.user.edit', compact('roles', 'user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            'role_id' => 'required|exists:roles,id',
            'name' => 'required',
            'username' => 'required|unique:users,username,' . $id . ',id',
            'email' => 'required|email|unique:users,email,' . $id . ',id',
            'is_active' => 'between:0,1',
            'password' => '',
        ]);

        $this->userService->update($data, $id);

        return redirect()->route('user.index')->with('success', 'Data berhasil simpan');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (!$this->userService->delete($id)) {
            return redirect()->route('user.index')->with('error', 'Data gagal dihapus');
        }
        return redirect()->route('user.index')->with('success', 'Data berhasil dihapus');
    }
}
