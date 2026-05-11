<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class RiwayatSdmExport implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize, WithEvents
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function title(): string
    {
        return 'Riwayat SDM';
    }

    public function collection()
    {
        $rows = [];
        $no = 1;
        
        foreach ($this->data as $pegawai) {
            $firstRow = true;
            
            // Gabungkan semua kegiatan pegawai
            $kegiatan = collect();
            
            // Pelatihan
            foreach ($pegawai['pelatihan'] as $p) {
                $kegiatan->push([
                    'jenis' => 'Pelatihan',
                    'nama' => $p->jenis_pelatihan ?? '-',
                    'jp' => $p->jp ?? 0,
                ]);
            }
            
            // Sertifikasi
            foreach ($pegawai['sertifikasi'] as $s) {
                $kegiatan->push([
                    'jenis' => 'Sertifikasi',
                    'nama' => $s->jenis_sertifikasi ?? '-',
                    'jp' => 0,
                ]);
            }
            
            // Tugas Belajar
            foreach ($pegawai['tubel'] as $t) {
                $kegiatan->push([
                    'jenis' => 'Tubel',
                    'nama' => $t->nama_pelatihan ?? '-',
                    'jp' => 0,
                ]);
            }
            
            if ($kegiatan->isEmpty()) {
                $rows[] = [
                    'no' => $no,
                    'nama' => $pegawai['nama'],
                    'nip' => $pegawai['nip'] . ' ',
                    'pelatihan' => '-',
                    'sertifikasi' => '-',
                    'tubel' => '-',
                    'jp' => 0,
                ];
                $no++;
            } else {
                foreach ($kegiatan as $index => $item) {
                    $rows[] = [
                        'no' => $index === 0 ? $no : '',
                        'nama' => $index === 0 ? $pegawai['nama'] : '',
                        'nip' => $index === 0 ? $pegawai['nip'] . ' ' : '',
                        'pelatihan' => $item['jenis'] === 'Pelatihan' ? $item['nama'] : '',
                        'sertifikasi' => $item['jenis'] === 'Sertifikasi' ? $item['nama'] : '',
                        'tubel' => $item['jenis'] === 'Tubel' ? $item['nama'] : '',
                        'jp' => $item['jp'],
                    ];
                }
                $no++;
            }
        }
        
        return collect($rows);
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama',
            'NIP',
            'Pelatihan',
            'Sertifikasi',
            'Tubel',
            'JP'
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Geser ke bawah 2 baris untuk judul
                $sheet->insertNewRowBefore(1, 2);
                
                // Judul
                $sheet->mergeCells('A1:G1');
                $sheet->setCellValue('A1', 'LAPORAN PENGEMBANGAN KOMPETENSI PEGAWAI BALAI BAHASA JAWA TENGAH');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                        'name' => 'Calibri',
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                ]);
                
                // Header style
                $sheet->getStyle('A3:G3')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 11,
                        'name' => 'Calibri',
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '4472C4'],
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                ]);
                
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                
                // Format NIP sebagai teks
                if ($highestRow >= 4) {
                    for ($row = 4; $row <= $highestRow; $row++) {
                        $cell = $sheet->getCell('C' . $row);
                        $value = $cell->getValue();
                        if ($value && $value != '-') {
                            $cell->setValueExplicit((string)$value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                        }
                    }
                }
                
                // Merge cell untuk nama dan nip yang sama (multiple rows per pegawai)
                $currentNama = '';
                $startRow = 4;
                
                for ($row = 4; $row <= $highestRow + 1; $row++) {
                    $namaCell = $sheet->getCell('B' . $row);
                    $namaValue = $namaCell->getValue();
                    
                    if ($namaValue !== $currentNama) {
                        if ($startRow < $row) {
                            $sheet->mergeCells("B{$startRow}:B" . ($row - 1));
                            $sheet->mergeCells("C{$startRow}:C" . ($row - 1));
                            $sheet->getStyle("B{$startRow}:C" . ($row - 1))->applyFromArray([
                                'alignment' => [
                                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                                ],
                            ]);
                        }
                        $currentNama = $namaValue;
                        $startRow = $row;
                    }
                }
                
                // Style data
                if ($highestRow >= 4) {
                    $sheet->getStyle('A4:G' . $highestRow)->applyFromArray([
                        'alignment' => [
                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        ],
                    ]);
                    
                    // Rata kiri untuk kolom tertentu
                    $sheet->getStyle('B4:B' . $highestRow)->applyFromArray([
                        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT],
                    ]);
                    $sheet->getStyle('C4:C' . $highestRow)->applyFromArray([
                        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT],
                    ]);
                    $sheet->getStyle('D4:D' . $highestRow)->applyFromArray([
                        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT],
                    ]);
                    $sheet->getStyle('E4:E' . $highestRow)->applyFromArray([
                        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT],
                    ]);
                    $sheet->getStyle('F4:F' . $highestRow)->applyFromArray([
                        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT],
                    ]);
                    $sheet->getStyle('G4:G' . $highestRow)->applyFromArray([
                        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                    ]);
                }
                
                // Border semua data
                $sheet->getStyle("A3:{$highestColumn}{$highestRow}")
                    ->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => ['rgb' => '000000'],
                            ],
                        ],
                    ]);
                
                // Row height
                $sheet->getRowDimension(1)->setRowHeight(30);
                $sheet->getRowDimension(3)->setRowHeight(25);
            },
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [];
    }
}