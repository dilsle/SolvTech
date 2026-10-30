<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Models\VarianProduk;
use App\Models\KartuStok;
use App\Models\TransaksiItems;
use App\Http\Requests\storeTransaksiKeluarRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class TransaksiKeluarController extends Controller
{
    public $pageTitle = 'Transaksi Keluar';
    public $jenisTransaksi = 'pengeluaran';

    public function index() {
        $penerima = request()->query('penerima');
        $tanggalAwal = request()->query('tanggal_awal');
        $tanggalAkhir = request()->query('tanggal_akhir');
        $perPage = request()->query('perPage', 5);

        $query = Transaksi::query();
        $query->orderBy('created_at', 'DESC');
        $query->where('jenis_transaksi', $this->jenisTransaksi);

        if($penerima) {
            $query->where('penerima', 'like', '%'.$penerima.'%');
        }

        if ($tanggalAwal && $tanggalAkhir) {
            $tanggalAwal = Carbon::parse($tanggalAwal)->startOfDay();
            $tanggalAkhir = Carbon::parse($tanggalAkhir)->endOfDay();
            $query->whereBetween('created_at', [$tanggalAwal, $tanggalAkhir]);
        }

        $transaksi = $query->paginate($perPage)->appends(request()->query());

        $awalGrafik = Carbon::now()->startOfMonth()->subMonths(5);
        $dataBulanan = Transaksi::where('jenis_transaksi', $this->jenisTransaksi)
            ->where('created_at', '>=', $awalGrafik)
            ->selectRaw('YEAR(created_at) as tahun, MONTH(created_at) as bulan, COUNT(*) as jumlah, SUM(total_harga) as total')
            ->groupBy('tahun', 'bulan')
            ->get()
            ->keyBy(fn ($item) => sprintf('%04d-%02d', $item->tahun, $item->bulan));

        $grafikTransaksi = ['labels' => [], 'jumlah' => [], 'total' => []];
        for ($tanggal = $awalGrafik->copy(); $tanggal->lte(Carbon::now()->startOfMonth()); $tanggal->addMonth()) {
            $kunci = $tanggal->format('Y-m');
            $data = $dataBulanan->get($kunci);

            $grafikTransaksi['labels'][] = $tanggal->locale('id')->translatedFormat('M Y');
            $grafikTransaksi['jumlah'][] = (int) ($data->jumlah ?? 0);
            $grafikTransaksi['total'][] = (float) ($data->total ?? 0);
        }

        $pageTitle = $this->pageTitle;
        return view('transaksi-keluar.index', compact('pageTitle', 'transaksi'));
    }

    public function create()
    {
        $pageTitle = $this->pageTitle;
        return view('transaksi-keluar.create', compact('pageTitle'));
    }

    public function show($nomor_transaksi)
    {
        $pageTitle = "Detail " . $this->pageTitle;
        $transaksi = Transaksi::with('items')->where('nomor_transaksi', $nomor_transaksi)->firstOrFail();
        $transaksi->formated_date = Carbon::parse($transaksi->created_at)->locale('id')->translatedFormat('l, d F Y');
        return view('transaksi-keluar.show', compact('pageTitle', 'transaksi'));
    }


    public function store(storeTransaksiKeluarRequest $request)
    {
        $items = $request->input('items', []);

        if (empty($items)) {
            throw ValidationException::withMessages([
                'items' => 'Minimal harus ada satu produk.'
            ]);
        }

        return DB::transaction(function () use ($request, $items) {

            $nomorTransaksi = Transaksi::generateNomorTransaksi(
                $this->jenisTransaksi
            );

            // Validasi stok dan siapkan pembagian batch FIFO
            $alokasiItems = [];
            $totalHarga = 0;

            foreach ($items as $item) {
                $sku = $item['nomor_sku'] ?? null;
                $qtyDiminta = (int) ($item['qty'] ?? 0);

                if (!$sku || $qtyDiminta < 1) {
                    throw ValidationException::withMessages([
                        'items' => 'SKU dan jumlah barang harus valid.'
                    ]);
                }

                $varian = VarianProduk::where('nomor_sku', $sku)
                    ->lockForUpdate()
                    ->first();

                if (!$varian) {
                    throw ValidationException::withMessages([
                        'items' => "Varian dengan SKU {$sku} tidak ditemukan."
                    ]);
                }

                // Ambil batch masuk paling lama terlebih dahulu.
                // Batch harus memiliki nomor_batch.
                $batchMasuk = DB::table('transaksi_items as ti')
                    ->join('transaksis as t', 't.id', '=', 'ti.transaksi_id')
                    ->where('t.jenis_transaksi', 'pemasukan')
                    ->where('ti.nomor_sku', $sku)
                    ->whereNotNull('ti.nomor_batch')
                    ->where('ti.nomor_batch', '<>', '')
                    ->select(
                        'ti.nomor_batch',
                        DB::raw('SUM(ti.qty) as qty_masuk'),
                        DB::raw('MIN(t.created_at) as tanggal_masuk')
                    )
                    ->groupBy('ti.nomor_batch')
                    ->orderBy('tanggal_masuk', 'asc')
                    ->orderBy('ti.nomor_batch', 'asc')
                    ->get();

                // Hitung jumlah yang sudah keluar dari setiap batch.
                $batchKeluar = DB::table('transaksi_items as ti')
                    ->join('transaksis as t', 't.id', '=', 'ti.transaksi_id')
                    ->where('t.jenis_transaksi', 'pengeluaran')
                    ->where('ti.nomor_sku', $sku)
                    ->whereNotNull('ti.nomor_batch')
                    ->select(
                        'ti.nomor_batch',
                        DB::raw('SUM(ti.qty) as qty_keluar')
                    )
                    ->groupBy('ti.nomor_batch')
                    ->pluck('qty_keluar', 'nomor_batch');

                $sisaDiminta = $qtyDiminta;

                foreach ($batchMasuk as $batch) {
                    $sudahKeluar = (int) ($batchKeluar[$batch->nomor_batch] ?? 0);
                    $stokBatch = max(0, (int) $batch->qty_masuk - $sudahKeluar);

                    if ($stokBatch === 0) {
                        continue;
                    }

                    $qtyAmbil = min($sisaDiminta, $stokBatch);

                    $alokasiItems[] = [
                        'produk' => $varian->produk->nama_produk,
                        'varian' => $varian->nama_varian,
                        'nomor_sku' => $sku,
                        'nomor_batch' => $batch->nomor_batch,
                        'qty' => $qtyAmbil,
                        'harga' => $varian->harga_varian,
                        'sub_total' => $varian->harga_varian * $qtyAmbil,
                    ];

                    $totalHarga += $varian->harga_varian * $qtyAmbil;
                    $sisaDiminta -= $qtyAmbil;

                    if ($sisaDiminta === 0) {
                        break;
                    }
                }

                if ($sisaDiminta > 0) {
                    throw ValidationException::withMessages([
                        'items' => "Stok batch untuk SKU {$sku} tidak mencukupi. Kekurangan {$sisaDiminta} barang."
                    ]);
                }
            }

            // Buat transaksi setelah semua stok berhasil divalidasi.
            $transaksi = Transaksi::create([
                'nomor_transaksi' => $nomorTransaksi,
                'jenis_transaksi' => $this->jenisTransaksi,
                'jumlah_barang' => count($items),
                'total_harga' => $totalHarga,
                'keterangan' => $request->keterangan,
                'petugas' => Auth::user()->name,
                'penerima' => $request->penerima,
                'kontak' => $request->kontak,
            ]);

            // Simpan satu detail untuk setiap batch yang digunakan.
            foreach ($alokasiItems as $alokasi) {
                $transaksi->items()->create($alokasi);
            }

            // Kurangi stok total dan catat kartu stok per SKU.
            $totalKeluarPerSku = collect($alokasiItems)
                ->groupBy('nomor_sku');

            foreach ($totalKeluarPerSku as $sku => $detailItems) {
                $qtyKeluar = $detailItems->sum('qty');

                $varian = VarianProduk::where('nomor_sku', $sku)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($varian->stok_varian < $qtyKeluar) {
                    throw ValidationException::withMessages([
                        'items' => "Stok total SKU {$sku} tidak mencukupi."
                    ]);
                }

                $varian->decrement('stok_varian', $qtyKeluar);

                KartuStok::create([
                    'nomor_transaksi' => $nomorTransaksi,
                    'jenis_transaksi' => 'out',
                    'nomor_sku' => $sku,
                    'jumlah_keluar' => $qtyKeluar,
                    'stok_akhir' => $varian->fresh()->stok_varian,
                    'petugas' => Auth::user()->name,
                ]);
            }

            return response()->json([
                'success' => true,
                'redirect_url' => route('transaksi-keluar.create'),
            ]);
        });
    }


    public function getTransaksiKeluar() {
        $search = request()->query('search');
        $transaksi = Transaksi::where('jenis_transaksi', 'pengeluaran')
            ->where('nomor_transaksi', 'like', '%' . $search . '%')
            ->get()->map(function ($q){
                return [
                    'id'   => $q->id,
                    'text' => $q->nomor_transaksi
                ];
            });

        return response()->json($transaksi);
    }

    public function getTransaksiKeluarItems($nomor_transaksi) {
        $transaksi = Transaksi::with('items', 'items.varian')->where('nomor_transaksi', $nomor_transaksi)->first();
        $transaksi->tanggal = Carbon::parse($transaksi->created_at)->locale('id')->translatedFormat('l, d F Y');
        return response()->json($transaksi);
    }
}
