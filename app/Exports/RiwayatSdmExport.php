<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RiwayatSdmExport implements FromCollection, WithHeadings
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data)->map(function ($row, $i) {
            return [
                'No' => $i + 1,
                'Nama' => $row->nama,
                'NIP' => $row->nip,
                'Pelatihan' => $row->total_pelatihan,
                'Sertifikasi' => $row->total_sertifikasi,
                'Tubel' => $row->total_tubel,
                'JP' => $row->total_jp,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama',
            'NIP',
            'Pelatihan',
            'Sertifikasi',
            'Tubel',
            'JP'
        ];
    }
}