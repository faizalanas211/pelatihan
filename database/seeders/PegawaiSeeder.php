<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pegawai;
use Faker\Factory as Faker;

class PegawaiSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        for ($i = 1; $i <= 73; $i++) {
            Pegawai::create([
                'nama' => $faker->name,
                'nip' => $faker->unique()->numerify('1970######'),
                'jabatan' => "Staf",
                'foto' => null,
                'tanggal_lahir' => $faker->date(),
                'is_pejabat' => 0,
                'jenis_pejabat' => null,
            ]);
        }
    }
}
