<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class TubelSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize
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
                'Program' => $item->nama_pelatihan ?? '-',
                'Tanggal Mulai' => $item->tanggal_mulai,
                'Tanggal Selesai' => $item->tanggal_selesai,
            ];
        });
    }

    public function headings(): array
    {
        return ['No', 'Program', 'Tanggal Mulai', 'Tanggal Selesai'];
    }

    public function title(): string
    {
        return 'Tubel';
    }
}