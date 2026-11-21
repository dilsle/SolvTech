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

class ExportCompletedLaporanTransaksi implements FromCollection, WithHeadings, WithCustomStartCell, WithColumnWidths, WithColumnFormatting, WithEvents
{
    use GayaLaporanExcel;

    public $transaksi, $jenisTransaksi, $tanggalAwal, $tanggalAkhir;

    public function __construct($transaksi, $jenisTransaksi, $tanggalAwal, $tanggalAkhir)
    {
        $this->transaksi = $transaksi;
        $this->jenisTransaksi = $jenisTransaksi;
        $this->tanggalAwal = $tanggalAwal;
        $this->tanggalAkhir = $tanggalAkhir;
    }

    public function collection()
    {
        return $this->transaksi->values()->map(function ($item, $i) {
            $induk = $item->transaksi;

            return [
                $i + 1,
                Carbon::parse($induk->created_at)->format('d-m-Y H:i'),
                $induk->nomor_transaksi,
                $this->jenisTransaksi == 'pemasukan' ? $induk->pengirim : $induk->penerima,
                $induk->kontak,
                $item->produk,
                $item->getAttribute('varian'),
                (int) $item->qty,
                (float) $item->harga,
                (float) $item->sub_total,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'Nomor Transaksi',
            $this->jenisTransaksi == 'pemasukan' ? 'Pengirim' : 'Penerima',
            'Kontak',
            'Produk',
            'Varian',
            'Qty',
            'Harga (Rp)',
            'Sub Total (Rp)',
        ];
    }

    public function startCell(): string
    {
        return 'A4';
    }

    public function columnWidths(): array
    {
        return ['A' => 6, 'B' => 18, 'C' => 22, 'D' => 24, 'E' => 16, 'F' => 26, 'G' => 22, 'H' => 8, 'I' => 16, 'J' => 18];
    }

    public function columnFormats(): array
    {
        return ['H' => '#,##0', 'I' => '#,##0', 'J' => '#,##0'];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $this->terapkanGaya(
                    $event,
                    'J',
                    'LAPORAN DETAIL TRANSAKSI ' . ($this->jenisTransaksi == 'pemasukan' ? 'MASUK' : 'KELUAR'),
                    $this->teksPeriode($this->tanggalAwal, $this->tanggalAkhir),
                    $this->transaksi->count(),
                    ['H', 'J']
                );
            },
        ];
    }
}
