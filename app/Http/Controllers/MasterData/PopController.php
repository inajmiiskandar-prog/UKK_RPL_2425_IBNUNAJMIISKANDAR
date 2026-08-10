<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Pop;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PopController extends Controller
{
    public function index(Request $request)
    {
        $query = Pop::with('area');

        if ($request->has('search') && $request->search) {
            $query->where('nama_pop', 'like', '%' . $request->search . '%')
                  ->orWhere('kode_pop', 'like', '%' . $request->search . '%')
                  ->orWhere('alamat', 'like', '%' . $request->search . '%');
        }

        if ($request->has('area') && $request->area) {
            $query->where('id_area', $request->area);
        }

        $pops = $query->orderBy('kode_pop')->paginate(10);
        $pops->appends($request->all());
        $areas = Area::orderBy('nama_area')->get();

        return view('master.pop.index', compact('pops', 'areas'));
    }

    public function create()
    {
        $areas = Area::orderBy('nama_area')->get();
        return view('master.pop.create', compact('areas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_pop' => 'required|string|max:100',
            'alamat' => 'required|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'id_area' => 'required|exists:area,id_area',
        ], [
            'nama_pop.required' => 'Nama POP wajib diisi!',
            'alamat.required' => 'Alamat wajib diisi!',
            'id_area.required' => 'Area wajib dipilih!',
        ]);

        try {
            DB::beginTransaction();
            $lastPop = Pop::orderBy('id_pop', 'desc')->first();
            $lastNumber = $lastPop ? (int) substr($lastPop->kode_pop, 3) : 0;
            $kodePop = 'POP' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);

            Pop::create([
                'kode_pop' => $kodePop,
                'nama_pop' => $request->nama_pop,
                'alamat' => $request->alamat,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'id_area' => $request->id_area,
            ]);

            \App\Models\ActivityLog::log('POP_CREATED', "Menambah POP: {$request->nama_pop}", auth()->id());
            DB::commit();

            return redirect()->route('masterdata.pop.index')->with('success', 'POP berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan POP: ' . $e->getMessage());
        }
    }

    public function show(Pop $pop)
    {
        $pop->load(['area', 'olts', 'onts']);
        return view('master.pop.show', compact('pop'));
    }

    public function edit(Pop $pop)
    {
        $areas = Area::orderBy('nama_area')->get();
        return view('master.pop.edit', compact('pop', 'areas'));
    }

    public function update(Request $request, Pop $pop)
    {
        $request->validate([
            'nama_pop' => 'required|string|max:100',
            'alamat' => 'required|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'id_area' => 'required|exists:area,id_area',
        ]);

        try {
            DB::beginTransaction();
            $pop->update([
                'nama_pop' => $request->nama_pop,
                'alamat' => $request->alamat,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'id_area' => $request->id_area,
            ]);

            \App\Models\ActivityLog::log('POP_UPDATED', "Mengubah POP: {$pop->nama_pop}", auth()->id());
            DB::commit();

            return redirect()->route('masterdata.pop.index')->with('success', 'POP berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui POP: ' . $e->getMessage());
        }
    }

    public function destroy(Pop $pop)
    {
        try {
            DB::beginTransaction();

            if ($pop->olts()->count() > 0 || $pop->onts()->count() > 0) {
                return redirect()->back()->with('error', 'POP tidak dapat dihapus karena masih memiliki data terkait!');
            }

            $namaPop = $pop->nama_pop;
            $pop->delete();

            \App\Models\ActivityLog::log('POP_DELETED', "Menghapus POP: {$namaPop}", auth()->id());
            DB::commit();

            return redirect()->route('masterdata.pop.index')->with('success', 'POP berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus POP: ' . $e->getMessage());
        }
    }
}
