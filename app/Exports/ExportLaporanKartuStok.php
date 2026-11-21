<?php

namespace App\Exports;

use App\Exports\Concerns\GayaLaporanExcel;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;

class ExportLaporanKartuStok implements FromCollection, WithHeadings, WithCustomStartCell, WithColumnWidths, WithColumnFormatting, WithEvents
{
    use GayaLaporanExcel;

    public $query, $varian, $tanggalAwal, $tanggalAkhir;

    public function __construct($query, $varian, $tanggalAwal, $tanggalAkhir)
    {
        $this->query = $query;
        $this->varian = $varian;
        $this->tanggalAwal = $tanggalAwal;
        $this->tanggalAkhir = $tanggalAkhir;
    }

    private function labelJenis($jenis): string
    {
        return [
            'in'         => 'Masuk',
            'out'        => 'Keluar',
            'retur'      => 'Retur',
            'adjustment' => 'Penyesuaian',
        ][$jenis] ?? ucfirst((string) $jenis);
    }

    public function collection()
    {
        $namaProduk = $this->varian->produk->nama_produk . ' ' . $this->varian->nama_varian;

        return $this->query->values()->map(function ($item, $i) use ($namaProduk) {
            return [
                $i + 1,
                $namaProduk,
                $this->varian->nomor_sku,
                Carbon::parse($item->created_at)->format('d-m-Y H:i'),
                $this->labelJenis($item->jenis_transaksi),
                (int) ($item->jumlah_masuk ?? 0),
                (int) ($item->jumlah_keluar ?? 0),
                (int) $item->stok_akhir,
                $item->petugas,
            ];
        });
    }

    public function headings(): array
    {
        return ['No', 'Produk', 'Nomor SKU', 'Tanggal', 'Jenis', 'Jumlah Masuk', 'Jumlah Keluar', 'Stok Akhir', 'Petugas'];
    }

    public function startCell(): string
    {
        return 'A4';
    }

    public function columnWidths(): array
    {
        return ['A' => 6, 'B' => 34, 'C' => 16, 'D' => 18, 'E' => 16, 'F' => 14, 'G' => 14, 'H' => 12, 'I' => 20];
    }

    public function columnFormats(): array
    {
        $format = '#,##0;-#,##0;"-"';

        return ['F' => $format, 'G' => $format, 'H' => '#,##0'];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $this->terapkanGaya(
                    $event,
                    'I',
                    'KARTU STOK ' . strtoupper($this->varian->produk->nama_produk . ' ' . $this->varian->nama_varian),
                    $this->teksPeriode($this->tanggalAwal, $this->tanggalAkhir),
                    $this->query->count(),
                    ['F', 'G']
                );
            },
        ];
    }
}
