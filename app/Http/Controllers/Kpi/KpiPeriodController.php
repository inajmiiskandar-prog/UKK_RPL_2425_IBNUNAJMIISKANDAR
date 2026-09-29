<?php

/**
 * Controller untuk mengelola Periode KPI
 * CRUD periode + action untuk generate assessments otomatis
 *
 * Menu: PENILAIAN -> Periode KPI
 */

namespace App\Http\Controllers\Kpi;

use App\Http\Controllers\Controller;
use App\Models\KpiPeriod;
use App\Models\KpiAssessment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KpiPeriodController extends Controller
{
    /**
     * Daftar semua periode KPI
     */
    public function index(Request $request)
    {
        $query = KpiPeriod::query();

        // Filter status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Filter pencarian nama periode
        if ($search = trim((string) $request->input('search'))) {
            $query->where('nama', 'like', "%{$search}%");
        }

        $periods = $query->orderByDesc('tanggal_mulai')->paginate(10);
        $periods->appends($request->all());

        return view('kpi.period.index', compact('periods'));
    }

    /**
     * Form tambah periode baru
     */
    public function create()
    {
        return view('kpi.period.create');
    }

    /**
     * Simpan periode baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'status' => 'required|in:draft,aktif,selesai',
        ], [
            'nama.required' => 'Nama Periode wajib diisi!',
            'tanggal_mulai.required' => 'Tanggal Mulai wajib diisi!',
            'tanggal_selesai.required' => 'Tanggal Selesai wajib diisi!',
            'tanggal_selesai.after_or_equal' => 'Tanggal Selesai harus setelah atau sama dengan Tanggal Mulai!',
        ]);

        try {
            DB::beginTransaction();

            // Jika status = aktif, nonaktifkan periode lain yang aktif
            if ($request->status === 'aktif') {
                KpiPeriod::where('status', 'aktif')->update(['status' => 'selesai']);
            }

            $period = KpiPeriod::create([
                'nama' => $request->nama,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
                'status' => $request->status,
            ]);

            \App\Models\ActivityLog::log(
                'KPI_PERIOD_CREATED',
                "Menambah Periode KPI: {$request->nama}",
                auth()->user()->id_user
            );

            DB::commit();

            $successMsg = 'Periode KPI berhasil ditambahkan!';
            if ($request->status === 'aktif') {
                $successMsg .= ' Periode aktif sebelumnya otomatis ditutup.';
            }

            return redirect()->route('kpi.period.index')
                           ->with('success', $successMsg);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                           ->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    /**
     * Detail periode
     */
    public function show(KpiPeriod $period)
    {
        // Load assessments dengan data user dan atasan
        $period->load(['assessments.user', 'assessments.atasan']);
        return view('kpi.period.show', compact('period'));
    }

    /**
     * Form edit periode
     */
    public function edit(KpiPeriod $period)
    {
        return view('kpi.period.edit', compact('period'));
    }

    /**
     * Update periode
     */
    public function update(Request $request, KpiPeriod $period)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'status' => 'required|in:draft,aktif,selesai',
        ]);

        try {
            DB::beginTransaction();

            // Jika status berubah ke aktif, nonaktifkan periode lain
            if ($request->status === 'aktif' && $period->status !== 'aktif') {
                KpiPeriod::where('status', 'aktif')
                         ->where('id', '!=', $period->id)
                         ->update(['status' => 'selesai']);
            }

            $period->update([
                'nama' => $request->nama,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
                'status' => $request->status,
            ]);

            \App\Models\ActivityLog::log(
                'KPI_PERIOD_UPDATED',
                "Mengubah Periode KPI: {$request->nama}",
                auth()->user()->id_user
            );

            DB::commit();

            return redirect()->route('kpi.period.index')
                           ->with('success', 'Periode KPI berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                           ->with('error', 'Gagal memperbarui: ' . $e->getMessage());
        }
    }

    /**
     * Hapus periode (hanya jika tidak ada assessment)
     */
    public function destroy(KpiPeriod $period)
    {
        try {
            DB::beginTransaction();

            // Cek apakah sudah ada assessment
            if ($period->assessments()->count() > 0) {
                return redirect()->back()
                               ->with('error', 'Periode tidak dapat dihapus karena sudah ada penilaian!');
            }

            $nama = $period->nama;
            $period->delete();

            \App\Models\ActivityLog::log(
                'KPI_PERIOD_DELETED',
                "Menghapus Periode KPI: {$nama}",
                auth()->user()->id_user
            );

            DB::commit();

            return redirect()->route('kpi.period.index')
                           ->with('success', 'Periode KPI berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                           ->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }

    /**
     * Generate assessments otomatis untuk semua user aktif
     * Dipanggil saat periode diaktifkan
     *
     * @param KpiPeriod $period
     */
    public function generateAssessments(KpiPeriod $period)
    {
        // Hanya boleh untuk periode aktif
        if ($period->status !== 'aktif') {
            return redirect()->back()
                           ->with('error', 'Hanya periode aktif yang bisa di-generate assessments!');
        }

        try {
            DB::beginTransaction();

            // Ambil semua user aktif (status = true)
            $users = User::where('status', true)->get();
            $createdCount = 0;
            $skippedCount = 0;

            foreach ($users as $user) {
                // Cek apakah sudah ada assessment untuk user ini di periode ini
                $exists = KpiAssessment::where('user_id', $user->id_user)
                                      ->where('kpi_period_id', $period->id)
                                      ->exists();

                if ($exists) {
                    $skippedCount++;
                    continue;
                }

                // Buat assessment baru
                KpiAssessment::create([
                    'user_id' => $user->id_user,
                    'kpi_period_id' => $period->id,
                    'atasan_id' => $user->atasan_id, // Auto-fill dari data karyawan
                    'status' => 'pending',
                ]);

                $createdCount++;
            }

            \App\Models\ActivityLog::log(
                'KPI_ASSESSMENTS_GENERATED',
                "Generate {$createdCount} assessment untuk periode: {$period->nama}",
                auth()->user()->id_user
            );

            DB::commit();

            $msg = "Berhasil generate {$createdCount} assessment!";
            if ($skippedCount > 0) {
                $msg .= " ({$skippedCount} sudah ada)";
            }

            return redirect()->route('kpi.period.show', $period->id)
                           ->with('success', $msg);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                           ->with('error', 'Gagal generate assessments: ' . $e->getMessage());
        }
    }

    /**
     * Aktifkan periode dan generate assessments
     * Action untuk mudah mengaktifkan periode + auto generate
     */
    public function activate(KpiPeriod $period)
    {
        try {
            DB::beginTransaction();

            // Nonaktifkan periode aktif lainnya
            KpiPeriod::where('status', 'aktif')->update(['status' => 'selesai']);

            // Aktifkan periode ini
            $period->update(['status' => 'aktif']);

            // Generate assessments untuk semua user aktif
            $users = User::where('status', true)->get();
            $createdCount = 0;

            foreach ($users as $user) {
                $exists = KpiAssessment::where('user_id', $user->id_user)
                                      ->where('kpi_period_id', $period->id)
                                      ->exists();

                if (!$exists) {
                    KpiAssessment::create([
                        'user_id' => $user->id_user,
                        'kpi_period_id' => $period->id,
                        'atasan_id' => $user->atasan_id,
                        'status' => 'pending',
                    ]);
                    $createdCount++;
                }
            }

            \App\Models\ActivityLog::log(
                'KPI_PERIOD_ACTIVATED',
                "Mengaktifkan periode: {$period->nama}, {$createdCount} assessment di-generate",
                auth()->user()->id_user
            );

            DB::commit();

            return redirect()->route('kpi.period.show', $period->id)
                           ->with('success', "Periode berhasil diaktifkan! {$createdCount} assessment di-generate.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                           ->with('error', 'Gagal mengaktifkan: ' . $e->getMessage());
        }
    }
}
