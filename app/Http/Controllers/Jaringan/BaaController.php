<?php

namespace App\Http\Controllers\Jaringan;

use App\Http\Controllers\Controller;
use App\Models\Baa;
use App\Models\BaaDetail;
use App\Models\BaaTeknisi;
use App\Models\Fab;
use App\Models\Olt;
use App\Models\Ont;
use App\Models\Odp;
use App\Models\Material;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BaaController extends Controller
{
    public function index(Request $request)
    {
        $query = Baa::with(['fab', 'teknisi']);

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($searchQuery) use ($search) {
                $searchQuery->where('kode_baa', 'like', "%{$search}%")
                    ->orWhereHas('fab', function ($fabQuery) use ($search) {
                        $fabQuery->where('nama_pelanggan', 'like', "%{$search}%")
                            ->orWhere('kode_fab', 'like', "%{$search}%");
                    })
                    ->orWhereHas('teknisi', function ($teknisiQuery) use ($search) {
                        $teknisiQuery->where('nama', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $baas = $query->orderBy('tanggal_instalasi', 'desc')->paginate(10);
        $baas->appends($request->all());

        return view('jaringan.baa.index', compact('baas'));
    }

    public function create()
    {
        // Get FABs that don't have completed BAA
        $fabs = Fab::with(['area', 'paket'])
            ->where(function($query) {
                $query->where('status', 'OPEN')
                    ->orWhere(function($q) {
                        $q->where('status', 'AKTIF')
                          ->whereDoesntHave('baa', function($subQ) {
                              $subQ->where('status', 'SELESAI');
                          });
                    });
            })
            ->orderBy('nama_pelanggan')
            ->get();
        $olts = Olt::with('pop.area')->orderBy('kode_olt')->get();
        $onts = Ont::with('pop.area')
            ->where(function($query) {
                $query->where('status', 'TERSEDIA')
                    ->orWhere('status', 'RUSAK');
            })
            ->orderBy('serial_number')
            ->get();
        $odps = Odp::with('olt.pop.area')->orderBy('kode_odp')->get();
        $materials = Material::where('kondisi', 'BAIK')->where('stok', '>', 0)->whereColumn('stok', '>=', 'minimal_stok')->orderBy('nama_material')->get();
        // Teknisi: jika login sebagai TEKNISI, hanya tampilkan dirinya sendiri
        $teknisis = User::where('role', 'TEKNISI')
            ->where('status', true)
            ->when(auth()->user()->role === 'TEKNISI', function($query) {
                $query->where('id_user', auth()->id());
            })
            ->orderBy('nama')
            ->get();

        return view('jaringan.baa.create', compact('fabs', 'olts', 'onts', 'odps', 'materials', 'teknisis'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_fab' => 'required|exists:fab,id_fab',
            'tanggal_instalasi' => 'required|date',
            'status' => 'required|in:SELESAI,PENDING,PROGRES',
            'catatan' => 'nullable|string',
            'id_olt' => 'required|exists:olt,id_olt',
            'id_ont' => 'required|exists:ont,id_ont',
            'id_odp' => 'required|exists:odp,id_odp',
            'port_olt' => 'required|integer|min:1',
            'port_odp' => 'nullable|integer|min:1',
            'ping_ms' => 'nullable|numeric',
            'rx_power_dbm' => 'nullable|numeric',
            'tx_power_dbm' => 'nullable|numeric',
            'speed_download' => 'nullable|string|max:20',
            'speed_upload' => 'nullable|string|max:20',
            'id_user' => 'required|exists:users,id_user',
            'teknisi_ids' => 'nullable|array',
            'teknisi_ids.*' => 'exists:users,id_user',
            'material_ids' => 'nullable|array',
            'material_ids.*' => 'exists:material,id_material',
            'jumlahs' => 'nullable|array',
            'keterangans' => 'nullable|array',
        ], [
            'id_fab.required' => 'Pelanggan wajib dipilih!',
            'tanggal_instalasi.required' => 'Tanggal Instalasi wajib diisi!',
            'id_olt.required' => 'OLT wajib dipilih!',
            'id_ont.required' => 'ONT wajib dipilih!',
            'id_odp.required' => 'ODP wajib dipilih!',
            'port_olt.required' => 'Port OLT wajib diisi!',
            'id_user.required' => 'Teknisi Utama wajib dipilih!',
        ]);

        try {
            DB::beginTransaction();

            // Generate kode_baa
            $lastBaa = Baa::orderBy('id_baa', 'desc')->first();
            $lastNumber = $lastBaa ? (int) substr($lastBaa->kode_baa, 3) : 0;
            $kodeBaa = 'BAA' . str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);

            $baa = Baa::create([
                'kode_baa' => $kodeBaa,
                'tanggal_instalasi' => $request->tanggal_instalasi,
                'status' => $request->status,
                'catatan' => $request->catatan,
                'id_fab' => $request->id_fab,
                'id_user' => $request->id_user,
                'id_olt' => $request->id_olt,
                'id_ont' => $request->id_ont,
                'id_odp' => $request->id_odp,
                'port_olt' => $request->port_olt,
                'port_odp' => $request->port_odp,
                'ping_ms' => $request->ping_ms,
                'rx_power_dbm' => $request->rx_power_dbm,
                'tx_power_dbm' => $request->tx_power_dbm,
                'speed_download' => $request->speed_download,
                'speed_upload' => $request->speed_upload,
            ]);

            // Update status FAB to AKTIF
            Fab::where('id_fab', $request->id_fab)->update(['status' => 'AKTIF']);

            // Update ONT to TERPASANG
            Ont::where('id_ont', $request->id_ont)->update(['status' => 'TERPASANG']);

            // Save teknisi tambahan
            if ($request->teknisi_ids) {
                foreach ($request->teknisi_ids as $teknisiId) {
                    BaaTeknisi::create([
                        'id_baa' => $baa->id_baa,
                        'id_user' => $teknisiId,
                    ]);
                }
            }

            // Save material details
            if ($request->material_ids) {
                foreach ($request->material_ids as $index => $materialId) {
                    if ($materialId && isset($request->jumlahs[$index])) {
                        $jumlah = $request->jumlahs[$index];
                        $material = Material::find($materialId);

                        // Check if stock is sufficient
                        if ($material && $material->stok < $jumlah) {
                            DB::rollBack();
                            return redirect()->back()
                                ->withInput()
                                ->with('error', "Stok {$material->nama_material} tidak mencukupi! Stok tersedia: {$material->stok} {$material->satuan}");
                        }

                        BaaDetail::create([
                            'id_baa' => $baa->id_baa,
                            'id_material' => $materialId,
                            'jumlah' => $jumlah,
                            'keterangan' => $request->keterangans[$index] ?? null,
                        ]);

                        // Reduce material stock
                        Material::where('id_material', $materialId)->decrement('stok', $jumlah);
                    }
                }
            }

            \App\Models\ActivityLog::log('BAA_CREATED', "Menambah Instalasi BAA: {$kodeBaa}", auth()->id());
            DB::commit();

            return redirect()->route('jaringan.baa.index')->with('success', 'BAA berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan BAA: ' . $e->getMessage());
        }
    }

    public function show(Baa $baa)
    {
        $baa->load(['fab.area', 'fab.paket', 'teknisi', 'olt.pop.area', 'ont.pop', 'odp.olt', 'details.material']);
        return view('jaringan.baa.show', compact('baa'));
    }

    public function edit(Baa $baa)
    {
        // Get FABs - include current FAB and FABs without completed BAA
        $fabs = Fab::with(['area', 'paket'])
            ->where('id_fab', $baa->id_fab)
            ->orWhere(function($query) {
                $query->where('status', 'OPEN')
                    ->orWhere(function($q) {
                        $q->where('status', 'AKTIF')
                          ->whereDoesntHave('baa', function($subQ) {
                              $subQ->where('status', 'SELESAI');
                          });
                    });
            })
            ->orderBy('nama_pelanggan')
            ->get();
        $olts = Olt::with('pop.area')->orderBy('kode_olt')->get();
        $onts = Ont::with('pop.area')
            ->where(function($query) use ($baa) {
                $query->where('status', 'TERSEDIA')
                    ->orWhere('status', 'RUSAK')
                    ->orWhere('id_ont', $baa->id_ont);
            })
            ->orderBy('serial_number')
            ->get();
        $odps = Odp::with('olt.pop.area')->orderBy('kode_odp')->get();
        $materials = Material::where('kondisi', 'BAIK')->orderBy('nama_material')->get();
        // Teknisi: jika login sebagai TEKNISI, hanya tampilkan dirinya sendiri (atau teknisi yang sudah dipilih di BAA ini)
        $teknisis = User::where('role', 'TEKNISI')
            ->where('status', true)
            ->where(function($query) use ($baa) {
                $query->where('id_user', $baa->id_user); // Include teknisi utama yang sudah dipilih
                if ($baa->teknisiTambahan->isNotEmpty()) {
                    $query->orWhereIn('id_user', $baa->teknisiTambahan->pluck('id_user'));
                }
            })
            ->when(auth()->user()->role === 'TEKNISI', function($query) {
                $query->where('id_user', auth()->id());
            })
            ->orderBy('nama')
            ->get();

        $baa->load(['details', 'teknisiTambahan']);

        return view('jaringan.baa.edit', compact('baa', 'fabs', 'olts', 'onts', 'odps', 'materials', 'teknisis'));
    }

    public function update(Request $request, Baa $baa)
    {
        $request->validate([
            'tanggal_instalasi' => 'required|date',
            'status' => 'required|in:SELESAI,PENDING,PROGRES',
            'catatan' => 'nullable|string',
            'id_olt' => 'required|exists:olt,id_olt',
            'id_ont' => 'required|exists:ont,id_ont',
            'id_odp' => 'required|exists:odp,id_odp',
            'port_olt' => 'required|integer|min:1',
            'port_odp' => 'nullable|integer|min:1',
            'ping_ms' => 'nullable|numeric',
            'rx_power_dbm' => 'nullable|numeric',
            'tx_power_dbm' => 'nullable|numeric',
            'speed_download' => 'nullable|string|max:20',
            'speed_upload' => 'nullable|string|max:20',
            'id_user' => 'required|exists:users,id_user',
            'teknisi_ids' => 'nullable|array',
            'teknisi_ids.*' => 'exists:users,id_user',
        ]);

        try {
            DB::beginTransaction();

            $baa->update([
                'tanggal_instalasi' => $request->tanggal_instalasi,
                'status' => $request->status,
                'catatan' => $request->catatan,
                'id_olt' => $request->id_olt,
                'id_ont' => $request->id_ont,
                'id_odp' => $request->id_odp,
                'port_olt' => $request->port_olt,
                'port_odp' => $request->port_odp,
                'ping_ms' => $request->ping_ms,
                'rx_power_dbm' => $request->rx_power_dbm,
                'tx_power_dbm' => $request->tx_power_dbm,
                'speed_download' => $request->speed_download,
                'speed_upload' => $request->speed_upload,
                'id_user' => $request->id_user,
            ]);

            // Update teknisi tambahan
            $baa->teknisiTambahan()->delete();
            if ($request->teknisi_ids) {
                foreach ($request->teknisi_ids as $teknisiId) {
                    BaaTeknisi::create([
                        'id_baa' => $baa->id_baa,
                        'id_user' => $teknisiId,
                    ]);
                }
            }

            \App\Models\ActivityLog::log('BAA_UPDATED', "Mengubah BAA: {$baa->kode_baa}", auth()->id());
            DB::commit();

            return redirect()->route('jaringan.baa.index')->with('success', 'BAA berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui BAA: ' . $e->getMessage());
        }
    }

    public function destroy(Baa $baa)
    {
        abort_unless(auth()->user()?->role === 'ADMIN', 403);

        try {
            DB::beginTransaction();

            // Restore FAB status
            Fab::where('id_fab', $baa->id_fab)->update(['status' => 'OPEN']);

            // Restore ONT status
            Ont::where('id_ont', $baa->id_ont)->update(['status' => 'TERSDIA']);

            // Restore material stock
            foreach ($baa->details as $detail) {
                Material::where('id_material', $detail->id_material)->increment('stok', $detail->jumlah);
            }

            // Delete details and teknisi
            $baa->details()->delete();
            $baa->teknisiTambahan()->delete();

            $kodeBaa = $baa->kode_baa;
            $baa->delete();

            \App\Models\ActivityLog::log('BAA_DELETED', "Menghapus BAA: {$kodeBaa}", auth()->id());
            DB::commit();

            return redirect()->route('jaringan.baa.index')->with('success', 'BAA berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus BAA: ' . $e->getMessage());
        }
    }
}
