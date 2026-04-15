<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterPelatihan extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terhubung dengan model ini.
     * @var string
     */
    protected $table = 'master_pelatihans';

    /**
     * Atribut yang dapat diisi melalui mass assignment.
     * Kategori harus ada di sini agar tidak terblokir saat proses create/update.
     * * @var array
     */
    protected $fillable = [
        'kategori', 
        'nama_pelatihan', 
        'jp', 
        'tahun'
    ];

    /**
     * Opsional: Casting untuk memastikan tipe data konsisten
     * @var array
     */
    protected $casts = [
        'jp' => 'integer',
        'tahun' => 'integer',
    ];

    public function tubelPeserta()
    {
        return $this->hasMany(TubelPeserta::class);
    }
}