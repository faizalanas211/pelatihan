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
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class RiwayatSdmExport implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize, WithEvents
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Title for the worksheet
     */
    public function title(): string
    {
        return 'Riwayat SDM';
    }

    public function collection()
    {
        return collect($this->data)->values()->map(function ($row, $i) {
            return [
                'No' => $i + 1,
                'Nama' => $row->nama,
                'NIP' => $row->nip . ' ', // Tambahkan spasi di belakang
                'Pelatihan' => (int) $row->total_pelatihan,
                'Sertifikasi' => (int) $row->total_sertifikasi,
                'Tugas Belajar' => (int) $row->total_tubel,
                'JP' => (int) $row->total_jp,
            ];
        });
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

    /**
     * Register events to modify worksheet after creation
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Geser semua ke bawah 2 baris
                $sheet->insertNewRowBefore(1, 2);
                
                // Buat judul di row 1
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
                
                // Style untuk header (row 3)
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
                
                // Format kolom NIP (kolom C) sebagai TEXT sebelum data ditulis
                if ($highestRow >= 4) {
                    // Method 1: Set format teks
                    $sheet->getStyle('C4:C' . $highestRow)->getNumberFormat()->setFormatCode('@');
                    
                    // Method 2: Set setiap cell NIP sebagai teks
                    for ($row = 4; $row <= $highestRow; $row++) {
                        $cell = $sheet->getCell('C' . $row);
                        $value = $cell->getValue();
                        // Set cell value as string
                        $cell->setValueExplicit((string)$value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    }
                }
                
                // Style untuk data (row 4 sampai akhir)
                if ($highestRow >= 4) {
                    $sheet->getStyle('A4:G' . $highestRow)->applyFromArray([
                        'alignment' => [
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        ],
                    ]);
                    
                    // Align left untuk kolom Nama (kolom B) dan NIP (kolom C)
                    $sheet->getStyle('B4:B' . $highestRow)->applyFromArray([
                        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT],
                    ]);
                    $sheet->getStyle('C4:C' . $highestRow)->applyFromArray([
                        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT],
                    ]);
                }
                
                // Set row height
                $sheet->getRowDimension(1)->setRowHeight(30);
                $sheet->getRowDimension(3)->setRowHeight(25);

                $lastRow = $sheet->getHighestRow();
                $lastColumn = $sheet->getHighestColumn();

                // Border untuk semua data (termasuk header)
                $sheet->getStyle("A3:{$lastColumn}{$lastRow}")
                    ->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => ['rgb' => '000000'],
                            ],
                        ],
                    ]);

                // Isi cell kosong dengan background abu-abu (opsional)
                $lastColIndex = Coordinate::columnIndexFromString($lastColumn);

                for ($row = 4; $row <= $lastRow; $row++) {
                    for ($col = 1; $col <= $lastColIndex; $col++) {
                        $cell = $sheet->getCellByColumnAndRow($col, $row);
                        $value = $cell->getValue();

                        if ($value === null || $value === '') {
                            $cell->setValue('');

                            $sheet->getStyleByColumnAndRow($col, $row)->applyFromArray([
                                'fill' => [
                                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => 'FFBFBFBF'],
                                ],
                            ]);
                        }
                    }
                }
            },
        ];
    }

    /**
     * Apply styling to the worksheet
     */
    public function styles(Worksheet $sheet)
    {
        // Method ini tetap diperlukan untuk interface WithStyles
        return [];
    }
}