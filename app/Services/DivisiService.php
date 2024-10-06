<?php

namespace App\Services;

use App\Models\Divisi;
use Illuminate\Support\Facades\Auth;

class DivisiService
{
    const PREFIX = 'DV';
    private Divisi $divisi;
    public function __construct()
    {
        $this->divisi = new Divisi;
    }

    public function query()
    {
        return $this->divisi->query();
    }

    public function find(int $id)
    {
        return $this->divisi->find($id);
    }

    public function paginate(int $limit = 10)
    {
        $query = $this->divisi->query();
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
        $sequence = $this->divisi->count();
        $data['code'] = sprintf('%s-%s', self::PREFIX, str_pad($sequence + 1, 5, '0', STR_PAD_LEFT));
        $data['client_id'] = Auth::user()->client_id;
        return $this->divisi->create($data);
    }

    public function update(array $data, $id)
    {
        $data['client_id'] = Auth::user()->client_id;
        return $this->divisi->where(['id' => $id])->update($data);
    }

    public function delete($id)
    {
        return $this->divisi->where('id', $id)->delete();
    }
}