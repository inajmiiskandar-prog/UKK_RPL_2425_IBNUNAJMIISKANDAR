<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Paket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaketController extends Controller
{
    public function index(Request $request)
    {
        $query = Paket::query();

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($searchQuery) use ($search) {
                $searchQuery->where('nama_paket', 'like', "%{$search}%")
                    ->orWhere('kode_paket', 'like', "%{$search}%")
                    ->orWhere('kecepatan', 'like', "%{$search}%");
            });
        }

        $pakets = $query->orderBy('kode_paket')->paginate(10);
        $pakets->appends($request->all());

        return view('master.paket.index', compact('pakets'));
    }

    public function create()
    {
        return view('master.paket.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_paket' => 'required|string|max:100',
            'kecepatan' => 'required|string|max:50',
            'harga' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string|max:255',
        ], [
            'nama_paket.required' => 'Nama Paket wajib diisi!',
            'kecepatan.required' => 'Kecepatan wajib diisi!',
            'harga.required' => 'Harga wajib diisi!',
        ]);

        try {
            DB::beginTransaction();
            $lastPaket = Paket::orderBy('id_paket', 'desc')->first();
            $lastNumber = $lastPaket ? (int) substr($lastPaket->kode_paket, 2) : 0;
            $kodePaket = 'PK' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);

            Paket::create([
                'kode_paket' => $kodePaket,
                'nama_paket' => $request->nama_paket,
                'kecepatan' => $request->kecepatan,
                'harga' => $request->harga,
                'keterangan' => $request->keterangan,
            ]);

            \App\Models\ActivityLog::log('PAKET_CREATED', "Menambah Paket: {$request->nama_paket}", auth()->id());
            DB::commit();

            return redirect()->route('masterdata.paket.index')->with('success', 'Paket berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan Paket: ' . $e->getMessage());
        }
    }

    public function show(Paket $paket)
    {
        $paket->load('fabs');
        return view('master.paket.show', compact('paket'));
    }

    public function edit(Paket $paket)
    {
        return view('master.paket.edit', compact('paket'));
    }

    public function update(Request $request, Paket $paket)
    {
        $request->validate([
            'nama_paket' => 'required|string|max:100',
            'kecepatan' => 'required|string|max:50',
            'harga' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();
            $paket->update([
                'nama_paket' => $request->nama_paket,
                'kecepatan' => $request->kecepatan,
                'harga' => $request->harga,
                'keterangan' => $request->keterangan,
            ]);

            \App\Models\ActivityLog::log('PAKET_UPDATED', "Mengubah Paket: {$paket->nama_paket}", auth()->id());
            DB::commit();

            return redirect()->route('masterdata.paket.index')->with('success', 'Paket berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui Paket: ' . $e->getMessage());
        }
    }

    public function destroy(Paket $paket)
    {
        try {
            DB::beginTransaction();

            if ($paket->fabs()->count() > 0) {
                return redirect()->back()->with('error', 'Paket tidak dapat dihapus karena masih digunakan oleh pelanggan!');
            }

            $namaPaket = $paket->nama_paket;
            $paket->delete();

            \App\Models\ActivityLog::log('PAKET_DELETED', "Menghapus Paket: {$namaPaket}", auth()->id());
            DB::commit();

            return redirect()->route('masterdata.paket.index')->with('success', 'Paket berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus Paket: ' . $e->getMessage());
        }
    }
}
