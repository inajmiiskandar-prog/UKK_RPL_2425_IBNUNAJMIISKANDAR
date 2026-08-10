<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\PortPon;
use App\Models\Olt;
use App\Models\Odp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PortPonController extends Controller
{
    public function index(Request $request)
    {
        $query = PortPon::with(['olt.pop.area', 'odp']);

        if ($request->has('search') && $request->search) {
            $query->where('nomor_port', 'like', '%' . $request->search . '%')
                  ->orWhere('tipe_kartu', 'like', '%' . $request->search . '%');
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('olt') && $request->olt) {
            $query->where('id_olt', $request->olt);
        }

        $ports = $query->orderBy('id_olt')->orderBy('nomor_port')->paginate(10);
        $ports->appends($request->all());
        $olts = Olt::with('pop.area')->orderBy('kode_olt')->get();

        return view('master.portpon.index', compact('ports', 'olts'));
    }

    public function create()
    {
        $olts = Olt::with('pop.area')->orderBy('kode_olt')->get();
        $odps = Odp::with('olt.pop.area')->orderBy('kode_odp')->get();
        return view('master.portpon.create', compact('olts', 'odps'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nomor_port' => 'required|integer|min:1',
            'tipe_kartu' => 'required|string|max:50',
            'status' => 'required|in:TERSEDIA,TERPASANG,RUSAK',
            'id_olt' => 'required|exists:olt,id_olt',
            'id_odp' => 'nullable|exists:odp,id_odp',
        ], [
            'nomor_port.required' => 'Nomor Port wajib diisi!',
            'tipe_kartu.required' => 'Tipe Kartu wajib diisi!',
            'status.required' => 'Status wajib dipilih!',
            'id_olt.required' => 'OLT wajib dipilih!',
        ]);

        // Check duplicate
        $exists = PortPon::where('id_olt', $request->id_olt)
                         ->where('nomor_port', $request->nomor_port)
                         ->exists();
        if ($exists) {
            return redirect()->back()->withInput()->with('error', 'Port dengan nomor yang sama sudah ada di OLT ini!');
        }

        try {
            DB::beginTransaction();
            PortPon::create([
                'nomor_port' => $request->nomor_port,
                'tipe_kartu' => $request->tipe_kartu,
                'status' => $request->status,
                'id_olt' => $request->id_olt,
                'id_odp' => $request->id_odp,
            ]);

            \App\Models\ActivityLog::log('PORTPON_CREATED', "Menambah Port PON: {$request->nomor_port}", auth()->id());
            DB::commit();

            return redirect()->route('masterdata.port-pon.index')->with('success', 'Port PON berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan Port PON: ' . $e->getMessage());
        }
    }

    public function show(PortPon $portPon)
    {
        $portPon->load(['olt.pop.area', 'odp']);
        return view('master.portpon.show', compact('portPon'));
    }

    public function edit(PortPon $portPon)
    {
        $olts = Olt::with('pop.area')->orderBy('kode_olt')->get();
        $odps = Odp::with('olt.pop.area')->orderBy('kode_odp')->get();
        return view('master.portpon.edit', compact('portPon', 'olts', 'odps'));
    }

    public function update(Request $request, PortPon $portPon)
    {
        $request->validate([
            'nomor_port' => 'required|integer|min:1',
            'tipe_kartu' => 'required|string|max:50',
            'status' => 'required|in:TERSEDIA,TERPASANG,RUSAK',
            'id_olt' => 'required|exists:olt,id_olt',
            'id_odp' => 'nullable|exists:odp,id_odp',
        ]);

        // Check duplicate
        $exists = PortPon::where('id_olt', $request->id_olt)
                         ->where('nomor_port', $request->nomor_port)
                         ->where('id_port', '!=', $portPon->id_port)
                         ->exists();
        if ($exists) {
            return redirect()->back()->withInput()->with('error', 'Port dengan nomor yang sama sudah ada di OLT ini!');
        }

        try {
            DB::beginTransaction();
            $portPon->update([
                'nomor_port' => $request->nomor_port,
                'tipe_kartu' => $request->tipe_kartu,
                'status' => $request->status,
                'id_olt' => $request->id_olt,
                'id_odp' => $request->id_odp ?: null,
            ]);

            DB::commit();

            return redirect()->route('masterdata.port-pon.index')->with('success', 'Port PON berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui Port PON: ' . $e->getMessage());
        }
    }

    public function destroy(PortPon $portPon)
    {
        try {
            DB::beginTransaction();
            $nomor = $portPon->nomor_port;
            $portPon->delete();

            \App\Models\ActivityLog::log('PORTPON_DELETED', "Menghapus Port PON: {$nomor}", auth()->id());
            DB::commit();

            return redirect()->route('masterdata.port-pon.index')->with('success', 'Port PON berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus Port PON: ' . $e->getMessage());
        }
    }
}
