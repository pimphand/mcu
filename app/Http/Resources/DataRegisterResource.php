<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DataRegisterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'code' => $this->code,
            'nik' => $this->nik,
            'register_date' => $this->register_date,
            'birthday' => $this->birthday,
            'gender' => $this->gender,
            'packet_name' => $this->packet_name,
            'gedung' => $this->gedung ?? "-",
            'divisi' => $this->divisi->name,
            'department' => $this->department->name,
            'perusahaan' => $this->client->name,
            'ttv' => $this->tandaVital?->selesai == 1 ? "SELESAI" : "TIDAK",
            'pemeriksaanFisik' => $this->pemeriksaanFisik?->selesai == 1 ? "SELESAI" : "TIDAK",
            'laboratorium' => $this->laboratorium?->selesai == 1 ? "SELESAI" : "TIDAK",
            'radiologi' => $this->radiologi?->selesai == 1 ? "SELESAI" : "TIDAK",
            'audiometri' => $this->audiometri?->selesai == 1 ? "SELESAI" : "TIDAK",
            'spirometri' => $this->spirometri?->selesai == 1 ? "SELESAI" : "TIDAK",
            'rectal' => $this->rectal?->selesai == 1 ? "SELESAI" : "TIDAK",
            'ekg' => $this->ekg?->selesai == 1 ? "SELESAI" : "TIDAK",
        ];
    }
}
