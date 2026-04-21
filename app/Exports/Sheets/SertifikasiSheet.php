<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SertifikasiSheet implements FromArray, WithTitle, WithEvents
{
    protected $data;
    protected $pegawai;

    public function __construct($data, $pegawai)
    {
        $this->data = $data;
        $this->pegawai = $pegawai;
    }

    public function title(): string
    {
        return 'Sertifikasi';
    }

    public function array(): array
    {
        $exportData = [];
        
        // Row 1: Judul Utama
        $exportData[] = ['RIWAYAT PENGEMBANGAN KOMPETENSI', '', '', '', ''];
        
        // Row 2: Informasi Pegawai
        if ($this->pegawai) {
            $exportData[] = ['Nama Pegawai', ': ' . ($this->pegawai->nama ?? '-'), '', '', ''];
            $exportData[] = ['NIP', ': ' . ($this->pegawai->nip ?? '-'), '', '', ''];
            $exportData[] = ['Jabatan', ': ' . ($this->pegawai->jabatan ?? '-'), '', '', ''];
        }
        
        // Row 5: Empty row for spacing
        $exportData[] = ['', '', '', '', ''];
        
        // Row 6: Sub Judul
        $exportData[] = ['DATA SERTIFIKASI', '', '', '', ''];
        
        // Row 7: Headers
        $exportData[] = ['No', 'Nama Sertifikasi', 'Tanggal Sertifikasi', 'Masa Berlaku', 'Instansi Penyelenggara'];
        
        // Row 8 onward: Data Sertifikasi
        foreach ($this->data as $i => $item) {
            $mulai = $item->tanggal_mulai ?? null;
            $selesai = $item->tanggal_selesai ?? null;
            $tanggal = ($mulai && $selesai)
                ? date('d/m/Y', strtotime($mulai)) . ' s/d ' . date('d/m/Y', strtotime($selesai))
                : '-';
            
            $masaBerlaku = $item->masa_berlaku ?? '-';
            if ($masaBerlaku !== '-' && $masaBerlaku) {
                $masaBerlaku = date('d/m/Y', strtotime($masaBerlaku));
            }
            
            $exportData[] = [
                $i + 1,
                $item->jenis_sertifikasi ?? '-',
                $tanggal,
                $masaBerlaku,
                $item->instansi_penerbit ?? '-',
            ];
        }
        
        return $exportData;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;
                
                // Informasi baris
                $rowJudul = 1;
                $rowInfoPegawaiStart = 2;
                $rowInfoPegawaiEnd = $this->pegawai ? 4 : 1;
                $rowSpacing = $this->pegawai ? 5 : 2;
                $rowSubJudul = $this->pegawai ? 6 : 3;
                $rowHeader = $this->pegawai ? 7 : 4;
                $rowDataStart = $this->pegawai ? 8 : 5;
                
                // Style untuk judul utama (row 1) - HANYA BOLD, TANPA BACKGROUND
                $sheet->mergeCells('A1:E1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 16,
                        'name' => 'Calibri',
                        'color' => ['rgb' => '000000'], // Hitam
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                
                // Style untuk informasi pegawai (jika ada)
                if ($this->pegawai) {
                    $sheet->getStyle('A2:E4')->applyFromArray([
                        'font' => [
                            'size' => 11,
                            'name' => 'Calibri',
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_LEFT,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                    ]);
                    
                    // Bold untuk label informasi pegawai (kolom A)
                    $sheet->getStyle('A2:A4')->applyFromArray([
                        'font' => ['bold' => true],
                    ]);
                }
                
                // Style untuk sub judul
                $sheet->mergeCells('A' . $rowSubJudul . ':E' . $rowSubJudul);
                $sheet->getStyle('A' . $rowSubJudul)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 13,
                        'name' => 'Calibri',
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '5B9BD5'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                
                // Style untuk header
                $sheet->getStyle('A' . $rowHeader . ':E' . $rowHeader)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 11,
                        'name' => 'Calibri',
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '4472C4'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);
                
                // Style untuk data - ALL BORDER untuk semua data rows
                $highestRow = $sheet->getHighestRow();
                if ($highestRow >= $rowDataStart) {
                    // Terapkan border ke semua sel data
                    $sheet->getStyle('A' . $rowDataStart . ':E' . $highestRow)->applyFromArray([
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => '000000'], // Hitam agar lebih jelas
                            ],
                        ],
                    ]);
                    
                    // Align left untuk kolom Nama Sertifikasi (kolom B)
                    $sheet->getStyle('B' . $rowDataStart . ':B' . $highestRow)->applyFromArray([
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                    ]);
                    
                    // Align left untuk kolom Tanggal Sertifikasi (kolom C)
                    $sheet->getStyle('C' . $rowDataStart . ':C' . $highestRow)->applyFromArray([
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                    ]);
                    
                    // Align left untuk kolom Masa Berlaku (kolom D)
                    $sheet->getStyle('D' . $rowDataStart . ':D' . $highestRow)->applyFromArray([
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                    ]);
                    
                    // Align left untuk kolom Instansi Penyelenggara (kolom E)
                    $sheet->getStyle('E' . $rowDataStart . ':E' . $highestRow)->applyFromArray([
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                    ]);
                }
                
                // Tambahan: Pastikan baris kosong (spacing) tidak memiliki border
                // (tidak perlu diubah, karena hanya data rows yang kena style)
                
                // Auto-size columns
                foreach (range('A', 'E') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
                
                // Set row heights
                $sheet->getRowDimension($rowJudul)->setRowHeight(35);
                $sheet->getRowDimension($rowSubJudul)->setRowHeight(28);
                $sheet->getRowDimension($rowHeader)->setRowHeight(25);
            },
        ];
    }
}