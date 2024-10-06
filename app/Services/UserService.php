<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Contract;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserService
{
    private User $user;
    public function __construct()
    {
        $this->user = new User;
    }

    public function query()
    {
        return $this->user->query()->with('role');
    }

    public function find(int $id)
    {
        return $this->user->find($id);
    }

    public function paginate(int $limit = 10)
    {
        $user = Auth::user();
        $query = $this->user->query()->with('role');
        if ($search = request()->get('search')) {
            $query = $query->where(function ($qb) use ($search) {
                $qb->orWhere('name', 'like', '%' . $search . '%');
                $qb->orWhere('username', 'like', '%' . $search . '%');
                $qb->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        if ($user->role?->level == RoleService::LEVEL_CLIENT) {
            $query->where('client_id', $user->client_id);
        }

        if ($user->role?->level == RoleService::LEVEL_EMPLOYEE || $user->role?->level == RoleService::LEVEL_PARTICIPANT) {
            $query->where('id', $user->id);
        }
        return $query->latest()->paginate($limit)->withQueryString();
    }

    public function create(array $data)
    {
        $data['password'] = bcrypt($data['password']);
        return $this->user->create($data);
    }

    public function update(array $data, $id)
    {
        if ($data['password']) {
            $data['password'] = bcrypt($data['password']);
        }
        return $this->user->where(['id' => $id])->update($data);
    }

    public function delete($id)
    {
        return $this->user->where('id', $id)->delete();
    }

    public function getClient()
    {
        return $this->user->with('client')->find(Auth::user()->id);
    }
}