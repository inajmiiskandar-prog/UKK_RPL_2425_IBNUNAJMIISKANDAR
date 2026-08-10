<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Ont;
use App\Models\Pop;
use App\Models\Odp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OntController extends Controller
{
    public function index(Request $request)
    {
        $query = Ont::with(['pop.area', 'odp']);

        if ($request->has('search') && $request->search) {
            $query->where('serial_number', 'like', '%' . $request->search . '%')
                  ->orWhere('pelanggan', 'like', '%' . $request->search . '%');
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $onts = $query->orderBy('serial_number')->paginate(10);
        $onts->appends($request->all());
        $pops = Pop::with('area')->orderBy('kode_pop')->get();

        return view('master.ont.index', compact('onts', 'pops'));
    }

    public function create()
    {
        $pops = Pop::with('area')->orderBy('kode_pop')->get();
        $odps = Odp::with('olt.pop.area')->orderBy('kode_odp')->get();
        return view('master.ont.create', compact('pops', 'odps'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'serial_number' => 'required|string|max:100|unique:ont,serial_number',
            'pelanggan' => 'required|string|max:100',
            'status' => 'required|in:TERSEDIA,TERPASANG,RUSAK',
            'id_pop' => 'required|exists:pop,id_pop',
            'id_odp' => 'nullable|exists:odp,id_odp',
        ], [
            'serial_number.required' => 'Serial Number wajib diisi!',
            'serial_number.unique' => 'Serial Number sudah terdaftar!',
            'pelanggan.required' => 'Nama Pelanggan wajib diisi!',
            'status.required' => 'Status wajib dipilih!',
            'id_pop.required' => 'POP wajib dipilih!',
        ]);

        try {
            DB::beginTransaction();
            Ont::create([
                'serial_number' => $request->serial_number,
                'pelanggan' => $request->pelanggan,
                'status' => $request->status,
                'id_pop' => $request->id_pop,
                'id_odp' => $request->id_odp ?: null,
            ]);

            \App\Models\ActivityLog::log('ONT_CREATED', "Menambah ONT: {$request->serial_number}", auth()->id());
            DB::commit();

            return redirect()->route('masterdata.ont.index')->with('success', 'ONT berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan ONT: ' . $e->getMessage());
        }
    }

    public function show(Ont $ont)
    {
        $ont->load(['pop.area', 'odp.olt']);
        return view('master.ont.show', compact('ont'));
    }

    public function edit(Ont $ont)
    {
        $pops = Pop::with('area')->orderBy('kode_pop')->get();
        $odps = Odp::with('olt.pop.area')->orderBy('kode_odp')->get();
        return view('master.ont.edit', compact('ont', 'pops', 'odps'));
    }

    public function update(Request $request, Ont $ont)
    {
        $request->validate([
            'serial_number' => 'required|string|max:100|unique:ont,serial_number,' . $ont->id_ont . ',id_ont',
            'pelanggan' => 'required|string|max:100',
            'status' => 'required|in:TERSEDIA,TERPASANG,RUSAK',
            'id_pop' => 'required|exists:pop,id_pop',
            'id_odp' => 'nullable|exists:odp,id_odp',
        ]);

        try {
            DB::beginTransaction();
            $ont->update([
                'serial_number' => $request->serial_number,
                'pelanggan' => $request->pelanggan,
                'status' => $request->status,
                'id_pop' => $request->id_pop,
                'id_odp' => $request->id_odp ?: null,
            ]);

            \App\Models\ActivityLog::log('ONT_UPDATED', "Mengubah ONT: {$ont->serial_number}", auth()->id());
            DB::commit();

            return redirect()->route('masterdata.ont.index')->with('success', 'ONT berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui ONT: ' . $e->getMessage());
        }
    }

    public function destroy(Ont $ont)
    {
        try {
            DB::beginTransaction();
            $serial = $ont->serial_number;
            $ont->delete();

            \App\Models\ActivityLog::log('ONT_DELETED', "Menghapus ONT: {$serial}", auth()->id());
            DB::commit();

            return redirect()->route('masterdata.ont.index')->with('success', 'ONT berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus ONT: ' . $e->getMessage());
        }
    }
}
