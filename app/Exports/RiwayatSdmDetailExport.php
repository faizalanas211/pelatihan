<?php

namespace App\Exports;

use App\Exports\Sheets\PelatihanSheet;
use App\Exports\Sheets\SertifikasiSheet;
use App\Exports\Sheets\TubelSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class RiwayatSdmDetailExport implements WithMultipleSheets
{
    protected $pegawai, $pelatihan, $sertifikasi, $tubel;

    public function __construct($pegawai, $pelatihan, $sertifikasi, $tubel)
    {
        $this->pegawai = $pegawai;
        $this->pelatihan = $pelatihan;
        $this->sertifikasi = $sertifikasi;
        $this->tubel = $tubel;
    }

    public function sheets(): array
    {
        return [
            new PelatihanSheet($this->pelatihan),
            new SertifikasiSheet($this->sertifikasi),
            new TubelSheet($this->tubel),
        ];
    }
}