<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TubelSheet implements FromArray, WithTitle, WithEvents
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
        return 'Tugas Belajar';
    }

    public function array(): array
    {
        $exportData = [];
        
        // Row 1: Judul Utama
        $exportData[] = ['RIWAYAT PENGEMBANGAN KOMPETENSI', '', '', ''];
        
        // Row 2: Informasi Pegawai
        if ($this->pegawai) {
            $exportData[] = ['Nama Pegawai', ': ' . ($this->pegawai->nama ?? '-'), '', ''];
            $exportData[] = ['NIP', ': ' . ($this->pegawai->nip ?? '-'), '', ''];
            $exportData[] = ['Jabatan', ': ' . ($this->pegawai->jabatan ?? '-'), '', ''];
        }
        
        // Row 5: Empty row for spacing
        $exportData[] = ['', '', '', ''];
        
        // Row 6: Sub Judul
        $exportData[] = ['DATA TUGAS BELAJAR', '', '', ''];
        
        // Row 7: Headers
        $exportData[] = ['No', 'Perguruan Tinggi', 'Tanggal Tugas Belajar', 'Nomor SK'];
        
        // Row 8 onward: Data Tugas Belajar
        foreach ($this->data as $i => $item) {
            $mulai = $item->tanggal_mulai ?? null;
            $selesai = $item->tanggal_selesai ?? null;
            $tanggal = ($mulai && $selesai)
                ? date('d/m/Y', strtotime($mulai)) . ' s/d ' . date('d/m/Y', strtotime($selesai))
                : '-';
            
            $exportData[] = [
                $i + 1,
                $item->nama_pelatihan ?? '-',
                $tanggal,
                $item->no_sk_tubel ?? '-',
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
                $sheet->mergeCells('A1:D1');
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
                    $sheet->getStyle('A2:D4')->applyFromArray([
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
                $sheet->mergeCells('A' . $rowSubJudul . ':D' . $rowSubJudul);
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
                $sheet->getStyle('A' . $rowHeader . ':D' . $rowHeader)->applyFromArray([
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
                
                // Style untuk data - ALL BORDER dengan warna hitam
                $highestRow = $sheet->getHighestRow();
                if ($highestRow >= $rowDataStart) {
                    $sheet->getStyle('A' . $rowDataStart . ':D' . $highestRow)->applyFromArray([
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => '000000'], // Hitam
                            ],
                        ],
                    ]);
                    
                    // Align left untuk kolom Perguruan Tinggi (kolom B)
                    $sheet->getStyle('B' . $rowDataStart . ':B' . $highestRow)->applyFromArray([
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                    ]);
                    
                    // Align left untuk kolom Tanggal Tugas Belajar (kolom C)
                    $sheet->getStyle('C' . $rowDataStart . ':C' . $highestRow)->applyFromArray([
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                    ]);
                    
                    // Align left untuk kolom Nomor SK (kolom D)
                    $sheet->getStyle('D' . $rowDataStart . ':D' . $highestRow)->applyFromArray([
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                    ]);
                }
                
                // Auto-size columns
                foreach (range('A', 'D') as $column) {
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