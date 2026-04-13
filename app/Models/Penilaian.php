<?php

namespace App\Models;
use App\Models\Alihdaya;
use Illuminate\Database\Eloquent\Model;

class Penilaian extends Model
{
    protected $fillable = [
        'pegawai_alihdaya_id',
        'bulan',
        'tahun',

        'kedisiplinan',
        'kedisiplinan_catatan',

        'tanggung_jawab',
        'tanggung_jawab_catatan',

        'sikap',
        'sikap_catatan',

        'kompetensi',
        'kompetensi_catatan',

        'penampilan',
        'penampilan_catatan',

        'komunikasi',
        'komunikasi_catatan',

        'kepatuhan_vendor',
        'kepatuhan_vendor_catatan',

        'rekomendasi',
        'rekomendasi_lain',

        'penilai_nama',
        'penilai_jabatan',
        'tanggal'
    ];
}
