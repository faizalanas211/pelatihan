<?php

namespace App\Imports;

use App\Models\Pegawai;
use App\Models\Kehadiran;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class KehadiranImport implements ToModel, WithHeadingRow
{
    private function clean($value)
    {
        // Jika nilai adalah serial number Excel (float)
        if (is_numeric($value) && floor($value) != $value) {
            // Biarkan sebagai numeric, akan diproses di fungsi convertExcelTime
            return $value;
        }
        
        $value = strtolower($value);
        $value = preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', $value);
        $value = trim($value);
        $value = preg_replace('/\s+/', '', $value);

        return $value;
    }

    /**
     * Convert Excel serial time to HH:MM:SS format
     */
    private function convertExcelTime($excelTime)
    {
        try {
            // Method 1: Using PhpSpreadsheet Date class
            if (class_exists('PhpOffice\PhpSpreadsheet\Shared\Date')) {
                $timestamp = Date::excelToTimestamp($excelTime);
                return date('H:i:s', $timestamp);
            }
            
            // Method 2: Manual calculation
            // Excel stores time as fraction of a day (1 = 24 hours)
            $seconds = round(($excelTime - floor($excelTime)) * 86400); // 86400 seconds in a day
            
            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            $seconds = $seconds % 60;
            
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Check if value is Excel serial time
     */
    private function isExcelTime($value)
    {
        // Excel time serial numbers are floats between 0 and 1 (or slightly above for times > 24:00)
        if (!is_numeric($value)) {
            return false;
        }
        
        $floatValue = (float) $value;
        $fraction = $floatValue - floor($floatValue);
        
        // Check if it's a time value (fraction between 0 and 1, exclusive)
        return ($fraction > 0 && $fraction < 1) || $floatValue == 0;
    }

    public function model(array $row)
    {
        $nip = trim($row['id_pegawai'] ?? $row['nip'] ?? '');
        $raw = $this->clean($row['masuk'] ?? $row['jam_masuk'] ?? '');
        
        if (empty($nip)) {
            return null;
        }

        $pegawai = Pegawai::where('nip', $nip)->first();
        if (!$pegawai) return null;

        // --- 1. CEK APAKAH INI EXCEL SERIAL TIME ---
        if ($this->isExcelTime($raw)) {
            $jamMasuk = $this->convertExcelTime($raw);
            
            if ($jamMasuk) {
                return new Kehadiran([
                    'pegawai_id' => $pegawai->id,
                    'tanggal'    => now()->format('Y-m-d'),
                    'status'     => 'hadir',
                    'jam_masuk'  => $jamMasuk,
                    'keterangan' => 'Imported from Excel',
                ]);
            }
        }

        // --- 2. MAP STATUS KHUSUS ---
        $statusMap = [
            'dl' => 'dinas_luar',
            'dd' => 'dinas_dalam',
            'dinasluar' => 'dinas_luar',
            'dinasdalam' => 'dinas_dalam',
            'cuti' => 'cuti',
            'sakit' => 'sakit',
            'izin' => 'izin',
        ];

        if (array_key_exists($raw, $statusMap)) {
            return new Kehadiran([
                'pegawai_id' => $pegawai->id,
                'tanggal'    => now()->format('Y-m-d'),
                'status'     => $statusMap[$raw],
                'jam_masuk'  => null,
                'keterangan' => null,
            ]);
        }

        // --- 3. JAM FORMAT HH:MM:SS ---
        if (preg_match('/^[0-9]{2}:[0-9]{2}:[0-9]{2}$/', $raw)) {
            return new Kehadiran([
                'pegawai_id' => $pegawai->id,
                'tanggal'    => now()->format('Y-m-d'),
                'status'     => 'hadir',
                'jam_masuk'  => $raw,
                'keterangan' => null,
            ]);
        }

        // --- 4. JAM FORMAT HH:MM atau H.MM atau HH.MM ---
        if (preg_match('/^[0-9]{1,2}[:.][0-9]{2}$/', $raw)) {
            $jam = str_replace('.', ':', $raw);
            [$h, $m] = explode(':', $jam);
            $jamFinal = sprintf('%02d:%02d:00', $h, $m);

            return new Kehadiran([
                'pegawai_id' => $pegawai->id,
                'tanggal'    => now()->format('Y-m-d'),
                'status'     => 'hadir',
                'jam_masuk'  => $jamFinal,
                'keterangan' => null,
            ]);
        }

        // --- 5. CEK APAKAH INI TIME DENGAN FORMAT LAIN ---
        // Format seperti "8:30 AM", "14.45", "15:30:00.000" dll
        if (preg_match('/^(\d{1,2})[:.](\d{2})([:.]\d{2})?(\s*[APap][Mm])?$/', $raw, $matches)) {
            $hour = (int)$matches[1];
            $minute = (int)$matches[2];
            
            // Handle AM/PM
            if (isset($matches[4])) {
                $ampm = strtoupper(trim($matches[4]));
                if ($ampm == 'PM' && $hour < 12) {
                    $hour += 12;
                } elseif ($ampm == 'AM' && $hour == 12) {
                    $hour = 0;
                }
            }
            
            $jamFinal = sprintf('%02d:%02d:00', $hour, $minute);
            
            return new Kehadiran([
                'pegawai_id' => $pegawai->id,
                'tanggal'    => now()->format('Y-m-d'),
                'status'     => 'hadir',
                'jam_masuk'  => $jamFinal,
                'keterangan' => null,
            ]);
        }

        // --- 6. KETIKA BUKAN JAM = default ke alpha ---
        // return new Kehadiran([
        //     'pegawai_id' => $pegawai->id,
        //     'tanggal'    => now()->format('Y-m-d'),
        //     'status'     => 'alpha',
        //     'jam_masuk'  => null,
        //     'keterangan' => $raw,
        // ]);
    }

    /**
     * Optional: Format the Excel column as date/time before reading
     * This can be done in the controller instead
     */
    public function getCsvSettings(): array
    {
        return [
            'input_encoding' => 'UTF-8',
        ];
    }
}