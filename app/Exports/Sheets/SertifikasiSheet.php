<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SertifikasiSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data->map(function ($item, $i) {
            return [
                'No' => $i + 1,
                'Nama Sertifikasi' => $item->jenis_sertifikasi ?? '-',
                'Tanggal' => $item->tanggal ?? '-',
            ];
        });
    }

    public function headings(): array
    {
        return ['No', 'Nama Sertifikasi', 'Tanggal'];
    }

    public function title(): string
    {
        return 'Sertifikasi';
    }
}