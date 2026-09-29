<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AreaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Area::query();

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($searchQuery) use ($search) {
                $searchQuery->where('nama_area', 'like', "%{$search}%")
                    ->orWhere('kode_area', 'like', "%{$search}%");
            });
        }

        $areas = $query->orderBy('kode_area')->paginate(10);
        $areas->appends($request->all());

        return view('master.area.index', compact('areas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('master.area.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_area' => 'required|string|max:100',
            'keterangan' => 'nullable|string|max:255',
        ], [
            'nama_area.required' => 'Nama area wajib diisi!',
        ]);

        try {
            DB::beginTransaction();

            // Generate kode_area
            $lastArea = Area::orderBy('id_area', 'desc')->first();
            $lastNumber = $lastArea ? (int) substr($lastArea->kode_area, 2) : 0;
            $kodeArea = 'AR' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);

            Area::create([
                'kode_area' => $kodeArea,
                'nama_area' => $request->nama_area,
                'keterangan' => $request->keterangan,
            ]);

            // Log activity
            \App\Models\ActivityLog::log('AREA_CREATED', "Menambah area: {$request->nama_area}", auth()->id());

            DB::commit();

            return redirect()->route('masterdata.area.index')->with('success', 'Area berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan area: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Area $area)
    {
        $area->load(['pops', 'fabs']);
        return view('master.area.show', compact('area'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Area $area)
    {
        return view('master.area.edit', compact('area'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Area $area)
    {
        $request->validate([
            'nama_area' => 'required|string|max:100',
            'keterangan' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $area->update([
                'nama_area' => $request->nama_area,
                'keterangan' => $request->keterangan,
            ]);

            \App\Models\ActivityLog::log('AREA_UPDATED', "Mengubah area: {$area->nama_area}", auth()->id());

            DB::commit();

            return redirect()->route('masterdata.area.index')->with('success', 'Area berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui area: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Area $area)
    {
        try {
            DB::beginTransaction();

            // Check if area has related data
            if ($area->pops()->count() > 0 || $area->fabs()->count() > 0) {
                return redirect()->back()->with('error', 'Area tidak dapat dihapus karena masih memiliki data terkait (POP/Pelanggan)!');
            }

            $namaArea = $area->nama_area;
            $area->delete();

            \App\Models\ActivityLog::log('AREA_DELETED', "Menghapus area: {$namaArea}", auth()->id());

            DB::commit();

            return redirect()->route('masterdata.area.index')->with('success', 'Area berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus area: ' . $e->getMessage());
        }
    }
}
