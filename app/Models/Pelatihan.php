<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pelatihan extends Model
{
    use HasFactory;
    
    protected $table = 'pelatihan';
    
    protected $fillable = [
        'nama', 
        'penyelenggara', 
        'tempat', 
        'tanggal_mulai', 
        'tanggal_selesai', 
        'status', 
        'created_by'
    ];
    
    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];
    
    public function peserta()
    {
        return $this->hasMany(PesertaPelatihan::class, 'pelatihan_id');
    }
    
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}