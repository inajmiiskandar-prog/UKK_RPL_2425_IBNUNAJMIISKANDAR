<?php

namespace App\Http\Controllers\Jaringan;

use App\Http\Controllers\Controller;
use App\Models\Fab;
use App\Models\Area;
use App\Models\Paket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FabController extends Controller
{
    public function index(Request $request)
    {
        $query = Fab::with(['area', 'paket', 'sales']);

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($searchQuery) use ($search) {
                $searchQuery->where('nama_pelanggan', 'like', "%{$search}%")
                    ->orWhere('kode_fab', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhere('no_hp', 'like', "%{$search}%")
                    ->orWhereHas('area', function ($areaQuery) use ($search) {
                        $areaQuery->where('nama_area', 'like', "%{$search}%");
                    })
                    ->orWhereHas('paket', function ($paketQuery) use ($search) {
                        $paketQuery->where('nama_paket', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('area') && $request->area) {
            $query->where('id_area', $request->area);
        }

        $fabs = $query->orderBy('createdAt', 'desc')->paginate(10);
        $fabs->appends($request->all());

        $areas = Area::orderBy('nama_area')->get();
        $pakets = Paket::orderBy('nama_paket')->get();

        return view('jaringan.fab.index', compact('fabs', 'areas', 'pakets'));
    }

    public function create()
    {
        $areas = Area::orderBy('nama_area')->get();
        $pakets = Paket::orderBy('nama_paket')->get();
        $sales = User::where('role', 'SALES')->where('status', true)->orderBy('nama')->get();

        // Auto-select sales based on logged-in user
        $selectedSales = auth()->user()->role === 'SALES' ? auth()->id() : null;

        return view('jaringan.fab.create', compact('areas', 'pakets', 'sales', 'selectedSales'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_pelanggan' => 'required|string|max:100',
            'nik' => 'required|string|max:20|unique:fab,nik',
            'no_hp' => 'required|string|max:20',
            'alamat' => 'required|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'status' => 'required|in:OPEN,AKTIF',
            'id_area' => 'required|exists:area,id_area',
            'id_paket' => 'required|exists:paket,id_paket',
            'id_user' => 'nullable',
        ], [
            'nama_pelanggan.required' => 'Nama Pelanggan wajib diisi!',
            'nik.required' => 'NIK wajib diisi!',
            'nik.unique' => 'NIK sudah terdaftar!',
            'no_hp.required' => 'No. HP wajib diisi!',
            'alamat.required' => 'Alamat wajib diisi!',
            'status.required' => 'Status wajib dipilih!',
            'id_area.required' => 'Area wajib dipilih!',
            'id_paket.required' => 'Paket wajib dipilih!',
        ]);

        try {
            DB::beginTransaction();

            // Generate kode_fab
            $lastFab = Fab::orderBy('id_fab', 'desc')->first();
            $lastNumber = $lastFab ? (int) substr($lastFab->kode_fab, 3) : 0;
            $kodeFab = 'FAB' . str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);

            // Handle foto upload
            $fotoPath = null;
            if ($request->hasFile('foto')) {
                $fotoPath = $request->file('foto')->store('fab-photos', 'public');
            }

            Fab::create([
                'kode_fab' => $kodeFab,
                'nama_pelanggan' => $request->nama_pelanggan,
                'nik' => $request->nik,
                'no_hp' => $request->no_hp,
                'alamat' => $request->alamat,
                'latitude' => $request->latitude ?: 0,
                'longitude' => $request->longitude ?: 0,
                'foto' => $fotoPath,
                'status' => $request->status,
                'id_area' => $request->id_area,
                'id_paket' => $request->id_paket,
                'id_user' => $request->id_user,
                'id_penginput' => auth()->id(),
            ]);

            \App\Models\ActivityLog::log('FAB_CREATED', "Menambah Pelanggan: {$request->nama_pelanggan}", auth()->id());
            DB::commit();

            return redirect()->route('jaringan.fab.index')->with('success', 'Pelanggan berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan Pelanggan: ' . $e->getMessage());
        }
    }

    public function show(Fab $fab)
    {
        $fab->load(['area', 'paket', 'sales', 'penginput', 'baa.teknisi']);
        return view('jaringan.fab.show', compact('fab'));
    }

    public function edit(Fab $fab)
    {
        $areas = Area::orderBy('nama_area')->get();
        $pakets = Paket::orderBy('nama_paket')->get();
        $sales = User::where('role', 'SALES')->where('status', true)->orderBy('nama')->get();

        return view('jaringan.fab.edit', compact('fab', 'areas', 'pakets', 'sales'));
    }

    public function update(Request $request, Fab $fab)
    {
        $request->validate([
            'nama_pelanggan' => 'required|string|max:100',
            'nik' => 'required|string|max:20|unique:fab,nik,' . $fab->id_fab . ',id_fab',
            'no_hp' => 'required|string|max:20',
            'alamat' => 'required|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'status' => 'required|in:OPEN,AKTIF',
            'id_area' => 'required|exists:area,id_area',
            'id_paket' => 'required|exists:paket,id_paket',
            'id_user' => 'nullable',
        ]);

        try {
            DB::beginTransaction();

            // Handle foto upload
            $fotoPath = $fab->foto;
            if ($request->hasFile('foto')) {
                // Delete old foto if exists
                if ($fab->foto && \Storage::disk('public')->exists($fab->foto)) {
                    \Storage::disk('public')->delete($fab->foto);
                }
                $fotoPath = $request->file('foto')->store('fab-photos', 'public');
            }

            $fab->update([
                'nama_pelanggan' => $request->nama_pelanggan,
                'nik' => $request->nik,
                'no_hp' => $request->no_hp,
                'alamat' => $request->alamat,
                'latitude' => $request->latitude ?: 0,
                'longitude' => $request->longitude ?: 0,
                'foto' => $fotoPath,
                'status' => $request->status,
                'id_area' => $request->id_area,
                'id_paket' => $request->id_paket,
                'id_user' => $request->id_user,
            ]);

            \App\Models\ActivityLog::log('FAB_UPDATED', "Mengubah Pelanggan: {$fab->nama_pelanggan}", auth()->id());
            DB::commit();

            return redirect()->route('jaringan.fab.index')->with('success', 'Pelanggan berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui Pelanggan: ' . $e->getMessage());
        }
    }

    public function destroy(Fab $fab)
    {
        abort_unless(auth()->user()?->role === 'ADMIN', 403);

        try {
            DB::beginTransaction();

            if ($fab->baa()->count() > 0) {
                return redirect()->back()->with('error', 'Pelanggan tidak dapat dihapus karena sudah memiliki data instalasi!');
            }

            $namaFab = $fab->nama_pelanggan;
            $fab->delete();

            \App\Models\ActivityLog::log('FAB_DELETED', "Menghapus Pelanggan: {$namaFab}", auth()->id());
            DB::commit();

            return redirect()->route('jaringan.fab.index')->with('success', 'Pelanggan berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus Pelanggan: ' . $e->getMessage());
        }
    }
}
