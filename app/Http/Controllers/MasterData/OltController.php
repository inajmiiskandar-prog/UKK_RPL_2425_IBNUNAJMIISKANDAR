<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Olt;
use App\Models\Pop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OltController extends Controller
{
    public function index(Request $request)
    {
        $query = Olt::with('pop');

        if ($request->has('search') && $request->search) {
            $query->where('nama_olt', 'like', '%' . $request->search . '%')
                  ->orWhere('kode_olt', 'like', '%' . $request->search . '%')
                  ->orWhere('ip_olt', 'like', '%' . $request->search . '%');
        }

        if ($request->has('pop') && $request->pop) {
            $query->where('id_pop', $request->pop);
        }

        $olts = $query->orderBy('kode_olt')->paginate(10);
        $olts->appends($request->all());
        $pops = Pop::with('area')->orderBy('kode_pop')->get();

        return view('master.olt.index', compact('olts', 'pops'));
    }

    public function create()
    {
        $pops = Pop::with('area')->orderBy('kode_pop')->get();
        return view('master.olt.create', compact('pops'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_olt' => 'required|string|max:100',
            'lokasi' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'id_pop' => 'required|exists:pop,id_pop',
            'ip_olt' => 'nullable|string|max:50',
            'username_olt' => 'nullable|string|max:50',
            'password_olt' => 'nullable|string|max:100',
            'foto_olt' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ], [
            'nama_olt.required' => 'Nama OLT wajib diisi!',
            'id_pop.required' => 'POP wajib dipilih!',
            'foto_olt.image' => 'File foto harus berupa gambar!',
            'foto_olt.mimes' => 'Format foto harus jpeg, png, jpg, gif, atau svg!',
        ]);

        try {
            DB::beginTransaction();
            $lastOlt = Olt::orderBy('id_olt', 'desc')->first();
            $lastNumber = $lastOlt ? (int) substr($lastOlt->kode_olt, 3) : 0;
            $kodeOlt = 'OLT' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);

            // Get POP data for location
            $pop = Pop::find($request->id_pop);

            // Handle foto upload
            $fotoPath = null;
            if ($request->hasFile('foto_olt')) {
                $fotoPath = $request->file('foto_olt')->store('olt-photos', 'public');
            }

            Olt::create([
                'kode_olt' => $kodeOlt,
                'nama_olt' => $request->nama_olt,
                'lokasi' => $request->lokasi ?? ($pop ? $pop->lokasi : null),
                'latitude' => $request->latitude ?? ($pop ? $pop->latitude : null),
                'longitude' => $request->longitude ?? ($pop ? $pop->longitude : null),
                'id_pop' => $request->id_pop,
                'ip_olt' => $request->ip_olt,
                'username_olt' => $request->username_olt,
                'password_olt' => $request->password_olt,
                'foto_olt' => $fotoPath,
            ]);

            \App\Models\ActivityLog::log('OLT_CREATED', "Menambah OLT: {$request->nama_olt}", auth()->id());
            DB::commit();

            return redirect()->route('masterdata.olt.index')->with('success', 'OLT berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan OLT: ' . $e->getMessage());
        }
    }

    public function show(Olt $olt)
    {
        $olt->load(['pop.area', 'odps', 'portPons']);
        return view('master.olt.show', compact('olt'));
    }

    public function edit(Olt $olt)
    {
        $pops = Pop::with('area')->orderBy('kode_pop')->get();
        return view('master.olt.edit', compact('olt', 'pops'));
    }

    public function update(Request $request, Olt $olt)
    {
        $request->validate([
            'nama_olt' => 'required|string|max:100',
            'lokasi' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'id_pop' => 'required|exists:pop,id_pop',
            'ip_olt' => 'nullable|string|max:50',
            'username_olt' => 'nullable|string|max:50',
            'password_olt' => 'nullable|string|max:100',
            'foto_olt' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        try {
            DB::beginTransaction();

            // Get POP data for location
            $pop = Pop::find($request->id_pop);

            // Handle foto upload
            $fotoPath = $olt->foto_olt;
            if ($request->hasFile('foto_olt')) {
                // Delete old photo if exists
                if ($olt->foto_olt && \Storage::disk('public')->exists($olt->foto_olt)) {
                    \Storage::disk('public')->delete($olt->foto_olt);
                }
                $fotoPath = $request->file('foto_olt')->store('olt-photos', 'public');
            }

            $olt->update([
                'nama_olt' => $request->nama_olt,
                'lokasi' => $request->lokasi ?? ($pop ? $pop->lokasi : null),
                'latitude' => $request->latitude ?? ($pop ? $pop->latitude : null),
                'longitude' => $request->longitude ?? ($pop ? $pop->longitude : null),
                'id_pop' => $request->id_pop,
                'ip_olt' => $request->ip_olt,
                'username_olt' => $request->username_olt,
                'password_olt' => $request->password_olt,
                'foto_olt' => $fotoPath,
            ]);

            \App\Models\ActivityLog::log('OLT_UPDATED', "Mengubah OLT: {$olt->nama_olt}", auth()->id());
            DB::commit();

            return redirect()->route('masterdata.olt.index')->with('success', 'OLT berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui OLT: ' . $e->getMessage());
        }
    }

    public function destroy(Olt $olt)
    {
        try {
            DB::beginTransaction();

            if ($olt->odps()->count() > 0 || $olt->portPons()->count() > 0) {
                return redirect()->back()->with('error', 'OLT tidak dapat dihapus karena masih memiliki data terkait!');
            }

            $namaOlt = $olt->nama_olt;
            $olt->delete();

            \App\Models\ActivityLog::log('OLT_DELETED', "Menghapus OLT: {$namaOlt}", auth()->id());
            DB::commit();

            return redirect()->route('masterdata.olt.index')->with('success', 'OLT berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus OLT: ' . $e->getMessage());
        }
    }
}
