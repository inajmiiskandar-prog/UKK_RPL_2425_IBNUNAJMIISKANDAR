<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MaterialController extends Controller
{
    public function index(Request $request)
    {
        $query = Material::query();

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($searchQuery) use ($search) {
                $searchQuery->where('nama_material', 'like', "%{$search}%")
                    ->orWhere('kode_material', 'like', "%{$search}%");
            });
        }

        if ($request->has('kondisi') && $request->kondisi) {
            $query->where('kondisi', $request->kondisi);
        }

        $materials = $query->orderBy('kode_material')->paginate(10);
        $materials->appends($request->all());

        return view('master.material.index', compact('materials'));
    }

    public function create()
    {
        return view('master.material.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_material' => 'required|string|max:100',
            'stok' => 'required|integer|min:0',
            'minimal_stok' => 'required|integer|min:0',
            'satuan' => 'required|string|max:20',
            'harga' => 'required|numeric|min:0',
            'kondisi' => 'required|in:BAIK,RUSAK',
            'keterangan' => 'nullable|string|max:255',
        ], [
            'nama_material.required' => 'Nama Material wajib diisi!',
            'stok.required' => 'Stok wajib diisi!',
            'minimal_stok.required' => 'Minimal Stok wajib diisi!',
            'satuan.required' => 'Satuan wajib diisi!',
            'harga.required' => 'Harga wajib diisi!',
            'kondisi.required' => 'Kondisi wajib dipilih!',
        ]);

        try {
            DB::beginTransaction();
            $lastMaterial = Material::orderBy('id_material', 'desc')->first();
            $lastNumber = $lastMaterial ? (int) substr($lastMaterial->kode_material, 3) : 0;
            $kodeMaterial = 'MAT' . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);

            Material::create([
                'kode_material' => $kodeMaterial,
                'nama_material' => $request->nama_material,
                'stok' => $request->stok,
                'minimal_stok' => $request->minimal_stok,
                'satuan' => $request->satuan,
                'harga' => $request->harga,
                'kondisi' => $request->kondisi,
                'keterangan' => $request->keterangan,
            ]);

            \App\Models\ActivityLog::log('MATERIAL_CREATED', "Menambah Material: {$request->nama_material}", auth()->id());
            DB::commit();

            return redirect()->route('masterdata.material.index')->with('success', 'Material berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan Material: ' . $e->getMessage());
        }
    }

    public function show(Material $material)
    {
        $material->load('baaDetails.baa');
        return view('master.material.show', compact('material'));
    }

    public function edit(Material $material)
    {
        return view('master.material.edit', compact('material'));
    }

    public function update(Request $request, Material $material)
    {
        $request->validate([
            'nama_material' => 'required|string|max:100',
            'stok' => 'required|integer|min:0',
            'minimal_stok' => 'required|integer|min:0',
            'satuan' => 'required|string|max:20',
            'harga' => 'required|numeric|min:0',
            'kondisi' => 'required|in:BAIK,RUSAK',
            'keterangan' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();
            $material->update([
                'nama_material' => $request->nama_material,
                'stok' => $request->stok,
                'minimal_stok' => $request->minimal_stok,
                'satuan' => $request->satuan,
                'harga' => $request->harga,
                'kondisi' => $request->kondisi,
                'keterangan' => $request->keterangan,
            ]);

            \App\Models\ActivityLog::log('MATERIAL_UPDATED', "Mengubah Material: {$material->nama_material}", auth()->id());
            DB::commit();

            return redirect()->route('masterdata.material.index')->with('success', 'Material berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui Material: ' . $e->getMessage());
        }
    }

    public function destroy(Material $material)
    {
        try {
            DB::beginTransaction();

            if ($material->baaDetails()->count() > 0) {
                return redirect()->back()->with('error', 'Material tidak dapat dihapus karena masih digunakan di instalasi!');
            }

            $namaMaterial = $material->nama_material;
            $material->delete();

            \App\Models\ActivityLog::log('MATERIAL_DELETED', "Menghapus Material: {$namaMaterial}", auth()->id());
            DB::commit();

            return redirect()->route('masterdata.material.index')->with('success', 'Material berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus Material: ' . $e->getMessage());
        }
    }

    public function addStock(Request $request, Material $material)
    {
        $request->validate([
            'jumlah_tambah' => 'required|integer|min:1',
        ], [
            'jumlah_tambah.required' => 'Jumlah wajib diisi!',
            'jumlah_tambah.min' => 'Jumlah minimal 1!',
        ]);

        try {
            $jumlahLama = $material->stok;
            $material->increment('stok', $request->jumlah_tambah);

            \App\Models\ActivityLog::log('MATERIAL_STOCK_ADDED', "Menambah Stok {$material->nama_material}: {$jumlahLama} -> {$material->fresh()->stok}", auth()->id());

            return redirect()->back()->with('success', "Stok {$material->nama_material} berhasil ditambahkan dari {$jumlahLama} menjadi {$material->fresh()->stok}!");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menambahkan stok: ' . $e->getMessage());
        }
    }
}
