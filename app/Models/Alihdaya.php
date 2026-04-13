<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alihdaya extends Model
{
    use HasFactory;

    protected $table = 'alihdayas'; // nama tabel di database

    // Kolom yang bisa diisi massal
    protected $fillable = [
        'nama',
        'jabatan',
        'foto',
    ];

    /**
     * Jika ingin relasi ke penilaian
     */
    public function penilaian()
    {
        return $this->hasMany(Penilaian::class, 'pegawai_id'); // sesuaikan kolom foreign key
    }
}
