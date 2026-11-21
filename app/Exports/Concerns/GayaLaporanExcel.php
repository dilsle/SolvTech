<?php

namespace App\Exports\Concerns;

use Carbon\Carbon;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

trait GayaLaporanExcel
{
    protected function teksPeriode($awal, $akhir): string
    {
        if (!$awal || !$akhir) {
            return 'Periode: semua tanggal';
        }

        return 'Periode: ' . Carbon::parse($awal)->locale('id')->translatedFormat('d F Y')
            . ' s/d ' . Carbon::parse($akhir)->locale('id')->translatedFormat('d F Y');
    }

    protected function terapkanGaya(
        AfterSheet $event,
        string $kolomAkhir,
        string $judul,
        string $periode,
        int $jumlahData,
        array $kolomTotal = []
    ): void {
        $sheet = $event->sheet->getDelegate();
        $barisHeader = 4;
        $barisAkhirData = $barisHeader + $jumlahData;
        $adaTotal = $jumlahData > 0 && !empty($kolomTotal);
        $barisAkhir = $adaTotal ? $barisAkhirData + 1 : $barisAkhirData;

        // Judul, periode, dan waktu cetak
        $teksAtas = [
            1 => $judul,
            2 => $periode,
            3 => 'Dicetak: ' . now()->format('d-m-Y H:i'),
        ];
        foreach ($teksAtas as $baris => $teks) {
            $sheet->mergeCells("A{$baris}:{$kolomAkhir}{$baris}");
            $sheet->setCellValue("A{$baris}", $teks);
            $sheet->getStyle("A{$baris}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2')->getFont()->setSize(11);
        $sheet->getStyle('A3')->getFont()->setItalic(true)->setSize(9);

        // Header tabel
        $rangeHeader = "A{$barisHeader}:{$kolomAkhir}{$barisHeader}";
        $sheet->getStyle($rangeHeader)->getFont()->setBold(true);
        $sheet->getStyle($rangeHeader)->getFill()
            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFDCE6F5');
        $sheet->getStyle($rangeHeader)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);
        $sheet->getRowDimension($barisHeader)->setRowHeight(24);

        // Isi data
        if ($jumlahData > 0) {
            $sheet->getStyle("A5:{$kolomAkhir}{$barisAkhirData}")->getAlignment()
                ->setVertical(Alignment::VERTICAL_TOP)->setWrapText(true);
            $sheet->getStyle("A5:A{$barisAkhirData}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // Baris total
        if ($adaTotal) {
            $barisTotal = $barisAkhirData + 1;
            $indeksPertama = Coordinate::columnIndexFromString($kolomTotal[0]);
            $kolomLabel = Coordinate::stringFromColumnIndex($indeksPertama - 1);

            $sheet->mergeCells("A{$barisTotal}:{$kolomLabel}{$barisTotal}");
            $sheet->setCellValue("A{$barisTotal}", 'TOTAL');
            $sheet->getStyle("A{$barisTotal}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            foreach ($kolomTotal as $kolom) {
                $sheet->setCellValue("{$kolom}{$barisTotal}", "=SUM({$kolom}5:{$kolom}{$barisAkhirData})");
                $sheet->getStyle("{$kolom}{$barisTotal}")->getNumberFormat()->setFormatCode('#,##0');
            }

            $rangeTotal = "A{$barisTotal}:{$kolomAkhir}{$barisTotal}";
            $sheet->getStyle($rangeTotal)->getFont()->setBold(true);
            $sheet->getStyle($rangeTotal)->getFill()
                ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF3F5F8');
        }

        // Garis tabel
        $sheet->getStyle("A{$barisHeader}:{$kolomAkhir}{$barisAkhir}")
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Header tetap terlihat saat scroll, cetak landscape satu halaman lebar
        $sheet->freezePane('A5');
        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setFitToPage(true);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);
    }
}
