<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TubelPeserta extends Model
{
    protected $table = 'tubel_peserta';

    protected $fillable = [
        'master_pelatihan_id',
        'pegawai_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'no_sk_tubel',
        'file_sk_tubel',
    ];

    public function masterPelatihan()
    {
        return $this->belongsTo(MasterPelatihan::class);
    }

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }
}