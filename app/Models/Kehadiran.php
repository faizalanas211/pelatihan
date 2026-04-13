<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kehadiran extends Model
{
    protected $table = 'kehadiran';

    protected $fillable = [
        'pegawai_id',   // nanti isi dari UID fingerprint
        'tanggal',      // otomatis diambil dari timestamp
        'status',       // bisa 'Hadir' default untuk scan
        'jam_masuk',    // waktu scan
        'keterangan',   // optional, bisa kosong
    ];

    public $timestamps = true; // created_at & updated_at

    // Relasi ke Pegawai
    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }

    /**
     * Helper untuk format tanggal & jam dari timestamp fingerprint
     */
    public static function fromFingerprint($pegawai_id, $timestamp)
    {
        return [
            'pegawai_id' => $pegawai_id,
            'tanggal'    => date('Y-m-d', strtotime($timestamp)),
            'status'     => 'Hadir', // default
            'jam_masuk'  => date('H:i:s', strtotime($timestamp)),
            'keterangan' => null,
        ];
    }
}
