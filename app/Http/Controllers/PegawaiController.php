<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class PegawaiController extends Controller
{
    public function index(Request $request)
    {
        $query = Pegawai::with('user');

        // Pencarian berdasarkan nama atau NIP
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama', 'LIKE', "%{$search}%")
                  ->orWhere('nip', 'LIKE', "%{$search}%");
            });
        }

        // Filter berdasarkan Jabatan
        if ($request->filled('jabatan')) {
            $query->where('jabatan', $request->jabatan);
        }

        // Filter berdasarkan Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $pegawais = $query->latest()->paginate(10)->withQueryString();

        // Data statistik card
        $totalPegawai = Pegawai::count();
        $pegawaiAktif = Pegawai::where('status', 'Aktif')->count();
        $pegawaiNonaktif = Pegawai::where('status', 'Nonaktif')->count();
        $listJabatan = Pegawai::select('jabatan')->distinct()->pluck('jabatan');

        return view('pegawai.index', compact('pegawais', 'totalPegawai', 'pegawaiAktif', 'pegawaiNonaktif', 'listJabatan'));
    }

    public function create()
    {
        return view('pegawai.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'no_hp' => 'required|string|max:20',
            'alamat' => 'nullable|string',
            'nip' => 'required|string|unique:pegawais,nip',
            'jabatan' => 'required|string|max:255',
            'tanggal_masuk' => 'required|date',
            'status' => 'required|in:Aktif,Nonaktif',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        DB::beginTransaction();
        try {
            // 1. Buat akun User
            $user = User::create([
                'name' => $request->nama,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // 2. Buat data Biodata Pegawai
            Pegawai::create([
                'user_id' => $user->id,
                'nip' => $request->nip,
                'nama' => $request->nama,
                'jenis_kelamin' => $request->jenis_kelamin,
                'no_hp' => $request->no_hp,
                'alamat' => $request->alamat,
                'jabatan' => $request->jabatan,
                'tanggal_masuk' => $request->tanggal_masuk,
                'status' => $request->status,
            ]);

            DB::commit();
            return redirect()->route('pegawai.index')->with('success', 'Data pegawai dan akun berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $pegawai = Pegawai::with(['user', 'absensis' => function($q) {
            $q->latest()->limit(10);
        }, 'pengajuanIzins' => function($q) {
            $q->latest()->limit(10);
        }])->findOrFail($id);

        return view('pegawai.show', compact('pegawai'));
    }

    public function edit($id)
    {
        $pegawai = Pegawai::with('user')->findOrFail($id);
        return view('pegawai.edit', compact('pegawai'));
    }

    public function update(Request $request, $id)
    {
        $pegawai = Pegawai::findOrFail($id);

        $request->validate([
            'nama' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'no_hp' => 'required|string|max:20',
            'alamat' => 'nullable|string',
            'nip' => ['required', 'string', Rule::unique('pegawais', 'nip')->ignore($pegawai->id)],
            'jabatan' => 'required|string|max:255',
            'tanggal_masuk' => 'required|date',
            'status' => 'required|in:Aktif,Nonaktif',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($pegawai->user_id)],
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        DB::beginTransaction();
        try {
            // Update User
            if ($pegawai->user) {
                $userData = [
                    'name' => $request->nama,
                    'email' => $request->email,
                ];
                if ($request->filled('password')) {
                    $userData['password'] = Hash::make($request->password);
                }
                $pegawai->user->update($userData);
            }

            // Update Pegawai
            $pegawai->update([
                'nip' => $request->nip,
                'nama' => $request->nama,
                'jenis_kelamin' => $request->jenis_kelamin,
                'no_hp' => $request->no_hp,
                'alamat' => $request->alamat,
                'jabatan' => $request->jabatan,
                'tanggal_masuk' => $request->tanggal_masuk,
                'status' => $request->status,
            ]);

            DB::commit();
            return redirect()->route('pegawai.index')->with('success', 'Data pegawai berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $pegawai = Pegawai::findOrFail($id);

        // Ubah status menjadi Nonaktif sebagai pengganti hapus fisik
        $pegawai->update(['status' => 'Nonaktif']);
        if ($pegawai->user) {
            // Optional: Anda bisa menghapus user atau membiarkannya (namun status pegawai nonaktif mencegah login jika divalidasi)
        }

        return redirect()->route('pegawai.index')->with('success', 'Pegawai berhasil dinonaktifkan.');
    }
}
