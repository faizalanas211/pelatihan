<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Rats\Zkteco\Lib\ZKTeco;
use App\Models\Kehadiran;

class SyncFingerprint extends Command
{
    protected $signature = 'fingerprint:sync';
    protected $description = 'Sync real-time attendance from ZKTeco';

    public function handle()
    {
        $zk = new ZKTeco('192.168.1.201', 4370); // ganti IP & port alatmu
        $zk->connect();

        $this->info("Connected to fingerprint device!");

        while (true) {
            try {
                $logs = $zk->getAttendance();

                foreach ($logs as $log) {
                    // Format data pakai helper di model
                    $data = Kehadiran::fromFingerprint($log['uid'], $log['timestamp']);

                    // Simpan ke database, hindari duplikat
                    Kehadiran::updateOrCreate(
                        [
                            'pegawai_id' => $data['pegawai_id'],
                            'tanggal'    => $data['tanggal'],
                            'jam_masuk'  => $data['jam_masuk']
                        ],
                        $data
                    );
                }

                $this->info("Fingerprint data synced!");
                sleep(5); // cek tiap 5 detik
            } catch (\Exception $e) {
                $this->error("Error: " . $e->getMessage());
                sleep(5); // tunggu 5 detik lalu coba lagi
            }
        }

        $zk->disconnect();
    }
}
