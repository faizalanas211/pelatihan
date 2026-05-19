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
            // Ambil daftar pelatihan
            $pelatihanList = [];
            $jpList = [];
            $tahunPelatihanList = [];
            foreach ($pegawai['pelatihan'] as $p) {
                $pelatihanList[] = $p->jenis_pelatihan ?? '-';
                $jpList[] = $p->jp ?? 0;
                $tahunPelatihanList[] = $p->tahun ?? '-';
            }
            
            // Ambil daftar sertifikasi & masa berlaku
            $sertifikasiList = [];
            $masaBerlakuList = [];
            $tahunSertifikasiList = [];
            foreach ($pegawai['sertifikasi'] as $s) {
                $sertifikasiList[] = $s->jenis_sertifikasi ?? '-';
                $masaBerlakuList[] = $s->masa_berlaku ?? '-';
                // Ambil tahun dari sertifikasi (prioritas dari properti tahun, atau dari tanggal_perolehan)
                $tahunSertifikasi = $s->tahun ?? null;
                if (!$tahunSertifikasi && isset($s->tanggal_perolehan)) {
                    $tahunSertifikasi = date('Y', strtotime($s->tanggal_perolehan));
                }
                $tahunSertifikasiList[] = $tahunSertifikasi ?? '-';
            }
            
            // Ambil daftar tubel (digabung jadi satu kolom: Jenjang - Prodi - Univ)
            $tubelList = [];
            $tahunTubelList = [];
            foreach ($pegawai['tubel'] as $t) {
                // Format: Jenjang - Prodi/Jurusan - Universitas
                $jenjang = $t->jenjang ?? '';
                $prodi = $t->jurusan ?? '';
                $univ = $t->instansi ?? '';
                
                $tubelText = trim($jenjang . ' ' . $prodi . ' ' . $univ);
                $tubelText = str_replace('  ', ' ', $tubelText);
                $tubelText = trim($tubelText);
                
                if (empty($tubelText)) {
                    $tubelText = $t->nama_pelatihan ?? '-';
                }
                
                $tubelList[] = $tubelText;
                $tahunTubelList[] = $t->tahun ?? '-';
            }
            
            // Cari jumlah maksimal baris
            $maxRows = max(count($pelatihanList), count($sertifikasiList), count($tubelList));
            
            if ($maxRows == 0) {
                // Tidak ada kegiatan sama sekali
                $rows[] = [
                    'no' => $no,
                    'nama' => $pegawai['nama'],
                    'nip' => $pegawai['nip'] . ' ',
                    'pelatihan' => '-',
                    'jp' => 0,
                    'sertifikasi' => '-',
                    'masa_berlaku' => '-',
                    'tubel' => '-',
                    'tahun' => '-',
                ];
                $no++;
            } else {
                for ($i = 0; $i < $maxRows; $i++) {
                    // Prioritaskan tahun dari pelatihan, lalu sertifikasi, lalu tubel
                    $tahunValue = $tahunPelatihanList[$i] ?? '';
                    if (empty($tahunValue) || $tahunValue == '-') {
                        $tahunValue = $tahunSertifikasiList[$i] ?? '';
                    }
                    if (empty($tahunValue) || $tahunValue == '-') {
                        $tahunValue = $tahunTubelList[$i] ?? '';
                    }
                    
                    $rows[] = [
                        'no' => $i === 0 ? $no : '',
                        'nama' => $i === 0 ? $pegawai['nama'] : '',
                        'nip' => $i === 0 ? $pegawai['nip'] . ' ' : '',
                        'pelatihan' => $pelatihanList[$i] ?? '',
                        'jp' => $jpList[$i] ?? 0,
                        'sertifikasi' => $sertifikasiList[$i] ?? '',
                        'masa_berlaku' => $masaBerlakuList[$i] ?? '',
                        'tubel' => $tubelList[$i] ?? '',
                        'tahun' => $tahunValue,
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
            'JP',
            'Sertifikasi',
            'Masa Berlaku',
            'Tubel',
            'Tahun'
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Geser ke bawah 2 baris
                $sheet->insertNewRowBefore(1, 2);
                
                // Judul
                $sheet->mergeCells('A1:I1');
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
                
                // Header style (A3:I3)
                $sheet->getStyle('A3:I3')->applyFromArray([
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
                $highestColumn = 'I'; // Karena kolom sampai I
                
                // Format NIP sebagai teks
                if ($highestRow >= 4) {
                    $sheet->getStyle('C4:C' . $highestRow)->getNumberFormat()->setFormatCode('@');
                    for ($row = 4; $row <= $highestRow; $row++) {
                        $cell = $sheet->getCell('C' . $row);
                        $value = $cell->getValue();
                        if ($value && $value != '-') {
                            $cell->setValueExplicit((string)$value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                        }
                    }
                }
                
                // Wrap text untuk kolom Sertifikasi (F), Masa Berlaku (G), Tubel (H)
                if ($highestRow >= 4) {
                    $sheet->getStyle('F4:F' . $highestRow)->getAlignment()->setWrapText(true);
                    $sheet->getStyle('G4:G' . $highestRow)->getAlignment()->setWrapText(true);
                    $sheet->getStyle('H4:H' . $highestRow)->getAlignment()->setWrapText(true);
                }
                
                // Merge cell untuk nama dan nip yang sama
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
                    $sheet->getStyle('A4:I' . $highestRow)->applyFromArray([
                        'alignment' => [
                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        ],
                    ]);
                    
                    // Align left untuk kolom tertentu
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
                        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                    ]);
                    $sheet->getStyle('F4:F' . $highestRow)->applyFromArray([
                        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT, 'wrap' => true],
                    ]);
                    $sheet->getStyle('G4:G' . $highestRow)->applyFromArray([
                        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT, 'wrap' => true],
                    ]);
                    $sheet->getStyle('H4:H' . $highestRow)->applyFromArray([
                        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT, 'wrap' => true],
                    ]);
                    $sheet->getStyle('I4:I' . $highestRow)->applyFromArray([
                        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                    ]);
                    
                    // Atur tinggi baris
                    for ($row = 4; $row <= $highestRow; $row++) {
                        $sheet->getRowDimension($row)->setRowHeight(-1);
                    }
                }
                
                // Border
                $sheet->getStyle("A3:{$highestColumn}{$highestRow}")
                    ->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => ['rgb' => '000000'],
                            ],
                        ],
                    ]);
                
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