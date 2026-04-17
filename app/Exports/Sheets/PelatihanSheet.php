<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PelatihanSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data->values()->map(function ($item, $i) {
            $mulai = $item->waktu_pelaksanaan ?? null;
            $selesai = $item->tanggal_selesai ?? null;

            return [
                'No' => $i + 1,
                'Nama Pelatihan' => $item->jenis_pelatihan ?? '-',
                'JP' => $item->jp ?? 0,
                'Tanggal' => ($mulai && $selesai)
                    ? $mulai . ' s/d ' . $selesai
                    : '-',
            ];
        });
    }

    public function headings(): array
    {
        return ['No', 'Nama Pelatihan', 'JP', 'Tanggal'];
    }

    public function title(): string
    {
        return 'Pelatihan';
    }
}