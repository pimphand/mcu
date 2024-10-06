<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\Participant;

class ContractService
{
    const PREFIX = 'KT';
    private Contract $contract;
    private Participant $participant;
    public function __construct()
    {
        $this->contract = new Contract;
        $this->participant = new Participant;
    }

    public function query()
    {
        return $this->contract->query();
    }

    public function find(int $id)
    {
        return $this->contract->find($id);
    }

    public function paginate(int $limit = 10)
    {
        $query = $this->contract->query();
        if ($search = request()->get('search')) {
            $query = $query->where(function ($qb) use ($search) {
                $qb->orWhere('name', 'like', '%' . $search . '%');
                $qb->orWhere('code', 'like', '%' . $search . '%');
            });
        }
        if ($clientId = request()->get('client_id')) {
            $query->orWhere('client_id', $clientId);
        }
        return $query->latest()->paginate($limit)->withQueryString();
    }

    public function create(array $data)
    {
        $sequence = $this->contract->count();
        $data['code'] = sprintf('%s%s', self::PREFIX, str_pad($sequence + 1, 5, '0', STR_PAD_LEFT));
        return $this->contract->create($data);
    }

    public function update(array $data, $id)
    {
        return $this->contract->where(['id' => $id])->update($data);
    }

    public function delete($id)
    {
        $checkRelation = $this->participant->where(['contract_id' => $id])->exists();
        if ($checkRelation) {
            return false;
        }

        return $this->contract->where('id', $id)->delete();
    }
}