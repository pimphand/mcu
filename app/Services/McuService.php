<?php

namespace App\Services;
use App\Models\Client;
use App\Models\Contract;

class McuService
{
    public function mcuIn($clientId, $contractId)
    {
        $client = (new Client)->find($clientId);
        $contract = (new Contract)->where(['id' => $contractId, 'client_id' => $client->id])->first();
        \Session::put('client_id', $client->id);
        \Session::put('client_code', $client->code);
        \Session::put('contract_id', $contract->id);
        \Session::put('contract_code', $contract->code);
        \Session::put('mcu_in', true);
    }

    public function mcuOut()
    {
        \Session::remove('client_id');
        \Session::remove('client_code');
        \Session::remove('contract_id');
        \Session::remove('contract_code');
        \Session::remove('mcu_in');
        return true;
    }
}