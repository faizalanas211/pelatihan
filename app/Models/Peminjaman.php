<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Peminjaman extends Model
{
    // WAJIB: nama tabel (biar gak jadi "peminjamen")
    protected $table = 'peminjaman';

    // Kolom yang boleh diisi
    protected $fillable = [
        'nama_peminjam',
        'kelas',
        'barang_id',
        'tanggal_pinjam',
        'tanggal_kembali',
        'status',
    ];

    // Relasi ke barang
    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }
}
