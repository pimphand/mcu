<?php

namespace App\Services;

use App\Models\Department;
use Illuminate\Support\Facades\Auth;

class DepartmentService
{
    const PREFIX = 'D';
    private Department $department;
    public function __construct()
    {
        $this->department = new Department;
    }

    public function query()
    {
        return $this->department->query();
    }

    public function find(int $id)
    {
        return $this->department->find($id);
    }

    public function paginate(int $limit = 10)
    {
        $query = $this->department->query();
        if ($search = request()->get('search')) {
            $query = $query->where(function ($qb) use ($search) {
                $qb->orWhere('name', 'like', '%' . $search . '%');
                $qb->orWhere('code', 'like', '%' . $search . '%');
            });
        }
        $query = $query->where('client_id', Auth::user()->client_id);
        return $query->latest()->paginate($limit)->withQueryString();
    }

    public function create(array $data)
    {
        $sequence = $this->department->count();
        $data['code'] = sprintf('%s-%s', self::PREFIX, str_pad($sequence + 1, 5, '0', STR_PAD_LEFT));
        $data['client_id'] = Auth::user()->client_id;
        return $this->department->create($data);
    }

    public function update(array $data, $id)
    {
        $data['client_id'] = Auth::user()->client_id;
        return $this->department->where(['id' => $id])->update($data);
    }

    public function delete($id)
    {
        return $this->department->where('id', $id)->delete();
    }
}