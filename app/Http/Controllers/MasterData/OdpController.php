<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Odp;
use App\Models\Olt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OdpController extends Controller
{
    public function index(Request $request)
    {
        $query = Odp::with(['olt.pop.area']);

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($searchQuery) use ($search) {
                $searchQuery->where('nama_odp', 'like', "%{$search}%")
                    ->orWhere('kode_odp', 'like', "%{$search}%");
            });
        }

        if ($request->has('olt') && $request->olt) {
            $query->where('id_olt', $request->olt);
        }

        $odps = $query->orderBy('kode_odp')->paginate(10);
        $odps->appends($request->all());
        $olts = Olt::with('pop.area')->orderBy('kode_olt')->get();

        return view('master.odp.index', compact('odps', 'olts'));
    }

    public function create()
    {
        $olts = Olt::with('pop.area')->orderBy('kode_olt')->get();
        return view('master.odp.create', compact('olts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_odp' => 'required|string|max:100',
            'alamat' => 'required|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'jumlah_port' => 'nullable|integer|min:1',
            'id_olt' => 'required|exists:olt,id_olt',
        ], [
            'nama_odp.required' => 'Nama ODP wajib diisi!',
            'alamat.required' => 'Alamat wajib diisi!',
            'id_olt.required' => 'OLT wajib dipilih!',
        ]);

        try {
            DB::beginTransaction();
            $lastOdp = Odp::orderBy('id_odp', 'desc')->first();
            $lastNumber = $lastOdp ? (int) substr($lastOdp->kode_odp, 3) : 0;
            $kodeOdp = 'ODP' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);

            Odp::create([
                'kode_odp' => $kodeOdp,
                'nama_odp' => $request->nama_odp,
                'alamat' => $request->alamat,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'jumlah_port' => $request->jumlah_port,
                'stok_port' => $request->jumlah_port,
                'id_olt' => $request->id_olt,
            ]);

            \App\Models\ActivityLog::log('ODP_CREATED', "Menambah ODP: {$request->nama_odp}", auth()->id());
            DB::commit();

            return redirect()->route('masterdata.odp.index')->with('success', 'ODP berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan ODP: ' . $e->getMessage());
        }
    }

    public function show(Odp $odp)
    {
        $odp->load(['olt.pop.area', 'onts', 'portPons']);
        return view('master.odp.show', compact('odp'));
    }

    public function edit(Odp $odp)
    {
        $olts = Olt::with('pop.area')->orderBy('kode_olt')->get();
        return view('master.odp.edit', compact('odp', 'olts'));
    }

    public function update(Request $request, Odp $odp)
    {
        $request->validate([
            'nama_odp' => 'required|string|max:100',
            'alamat' => 'required|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'jumlah_port' => 'nullable|integer|min:1',
            'id_olt' => 'required|exists:olt,id_olt',
        ]);

        try {
            DB::beginTransaction();
            $odp->update([
                'nama_odp' => $request->nama_odp,
                'alamat' => $request->alamat,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'jumlah_port' => $request->jumlah_port,
                'stok_port' => $request->jumlah_port,
                'id_olt' => $request->id_olt,
            ]);

            \App\Models\ActivityLog::log('ODP_UPDATED', "Mengubah ODP: {$odp->nama_odp}", auth()->id());
            DB::commit();

            return redirect()->route('masterdata.odp.index')->with('success', 'ODP berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui ODP: ' . $e->getMessage());
        }
    }

    public function destroy(Odp $odp)
    {
        try {
            DB::beginTransaction();

            if ($odp->onts()->count() > 0 || $odp->portPons()->count() > 0) {
                return redirect()->back()->with('error', 'ODP tidak dapat dihapus karena masih memiliki data terkait!');
            }

            $namaOdp = $odp->nama_odp;
            $odp->delete();

            \App\Models\ActivityLog::log('ODP_DELETED', "Menghapus ODP: {$namaOdp}", auth()->id());
            DB::commit();

            return redirect()->route('masterdata.odp.index')->with('success', 'ODP berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus ODP: ' . $e->getMessage());
        }
    }
}
