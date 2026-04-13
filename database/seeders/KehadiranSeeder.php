<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pegawai;
use App\Models\Kehadiran;

class KehadiranSeeder extends Seeder
{
    public function run()
    {
        $targets = [
            'hadir'        => 63,
            'cuti'         => 3,
            'sakit'        => 1,
            'dinas_luar'   => 5,
            'dinas_dalam'  => 1,
        ];

        // Ambil semua pegawai dan acak
        $pegawaiList = Pegawai::inRandomOrder()->get();

        $index = 0;

        foreach ($targets as $status => $count) {
            for ($i = 0; $i < $count; $i++) {

                $pegawai = $pegawaiList[$index];

                Kehadiran::create([
                    'pegawai_id' => $pegawai->id,
                    'tanggal' => now()->toDateString(),
                    'status' => $status,
                    'jam_masuk' => ($status === 'hadir') ? now()->subMinutes(rand(1, 40))->format('H:i:s') : null,
                    'keterangan' => null,
                ]);

                $index++;
            }
        }
    }
}
