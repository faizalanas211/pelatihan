<?php
namespace App\Imports;

use App\Models\Alihdaya;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class AlihDayaImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        dd($row);
        return new Alihdaya([
            'nama' => $row['nama'],
            'jabatan' => $row['jabatan'],
        ]);
    }
}
