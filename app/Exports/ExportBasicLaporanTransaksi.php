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

class ExportBasicLaporanTransaksi implements FromCollection, WithHeadings, WithCustomStartCell, WithColumnWidths, WithColumnFormatting, WithEvents
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
            return [
                $i + 1,
                Carbon::parse($item->created_at)->format('d-m-Y H:i'),
                $item->nomor_transaksi,
                $this->jenisTransaksi == 'pemasukan' ? $item->pengirim : $item->penerima,
                $item->kontak,
                $item->petugas,
                (int) $item->jumlah_barang,
                (float) $item->total_harga,
                $item->keterangan,
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
            'Petugas',
            'Jumlah Barang',
            'Total Harga (Rp)',
            'Keterangan',
        ];
    }

    public function startCell(): string
    {
        return 'A4';
    }

    public function columnWidths(): array
    {
        return ['A' => 6, 'B' => 18, 'C' => 22, 'D' => 24, 'E' => 16, 'F' => 18, 'G' => 14, 'H' => 18, 'I' => 34];
    }

    public function columnFormats(): array
    {
        return ['G' => '#,##0', 'H' => '#,##0'];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $this->terapkanGaya(
                    $event,
                    'I',
                    'LAPORAN TRANSAKSI ' . ($this->jenisTransaksi == 'pemasukan' ? 'MASUK' : 'KELUAR'),
                    $this->teksPeriode($this->tanggalAwal, $this->tanggalAkhir),
                    $this->transaksi->count(),
                    ['G', 'H']
                );
            },
        ];
    }
}
