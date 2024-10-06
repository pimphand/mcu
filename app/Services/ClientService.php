<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ClientService
{
    const PREFIX = 'C';
    private Client $client;
    private User $user;
    public function __construct()
    {
        $this->client = new Client;
        $this->user = new User;
    }

    public function query()
    {
        return $this->client->query();
    }

    public function find(int $id)
    {
        return $this->client->find($id);
    }

    public function paginate(int $limit = 10)
    {
        $query = $this->client->query()->with('contracts');
        if ($search = request()->get('search')) {
            $query = $query->where(function ($qb) use ($search) {
                $qb->orWhere('name', 'like', '%' . $search . '%');
                $qb->orWhere('code', 'like', '%' . $search . '%');
            });
        }
        return $query->latest()->paginate($limit)->withQueryString();
    }

    public function create(array $data)
    {
        $sequence = $this->client->count();
        $data['code'] = sprintf('%s%s', self::PREFIX, str_pad($sequence + 1, 5, '0', STR_PAD_LEFT));
        $insert = $this->client->create($data);
        \Log::info("inser_client",[$insert]);
        if ($insert) {
            $user = Auth::user();
            $role = Role::where('level', RoleService::LEVEL_CLIENT)->first();
            $dataUser = [
                'name' => $insert->name,
                'username' => $insert->code,
                'email' => $insert->email,
                'password' => bcrypt($insert->code),
                'role_id' => $role->id,
                // 'client_id' => $user->client_id,
                'client_id' => $insert->id,
                'is_active' => 0
            ];
            User::create($dataUser);
        }
        return $insert;
    }

    public function update(array $data, $id)
    {
        return $this->client->where(['id' => $id])->update($data);
    }

    public function delete($id)
    {
        $checkRelation = $this->user->where(['client_id' => $id])->exists();
        if ($checkRelation) {
            return false;
        }

        return $this->client->where('id', $id)->delete();
    }
}