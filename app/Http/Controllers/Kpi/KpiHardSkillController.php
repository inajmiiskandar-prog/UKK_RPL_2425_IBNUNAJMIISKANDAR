<?php

/**
 * Controller untuk mengelola indikator Hard Skill KPI
 * CRUD standar untuk master data indikator hard skill
 *
 * Menu: Master Data -> Hard Skill
 */

namespace App\Http\Controllers\Kpi;

use App\Http\Controllers\Controller;
use App\Models\KpiHardSkill;
use App\Helpers\KodeGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KpiHardSkillController extends Controller
{
    /**
     * Menampilkan daftar semua indikator hard skill
     */
    public function index(Request $request)
    {
        $query = KpiHardSkill::query();

        // Filter search
        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('kode', 'like', "%{$search}%")
                  ->orWhere('kpi', 'like', "%{$search}%")
                  ->orWhere('divisi', 'like', "%{$search}%")
                  ->orWhere('jabatan', 'like', "%{$search}%");
            });
        }

        // Filter divisi
        if ($request->has('divisi') && $request->divisi) {
            $query->where('divisi', $request->divisi);
        }

        // Ambil daftar divisi unik untuk dropdown filter
        $divisiList = KpiHardSkill::whereNotNull('divisi')
                                  ->where('divisi', '!=', '')
                                  ->distinct()
                                  ->orderBy('divisi')
                                  ->pluck('divisi');

        $hardSkills = $query->orderBy('kode')->paginate(10);
        $hardSkills->appends($request->all());

        return view('kpi.hard-skill.index', compact('hardSkills', 'divisiList'));
    }

    /**
     * Form tambah hard skill baru - redirect ke index (modal sudah di index)
     */
    public function create()
    {
        return redirect()->route('kpi.hard-skill.index');
    }

    /**
     * Simpan hard skill baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'divisi' => 'required|string|max:100',
            'jabatan' => 'required|string|max:100',
            'responsibilities' => 'required|string',
            'kpi' => 'required|string|max:255',
            'target' => 'nullable|string|max:50',
            'weight' => 'nullable|numeric|min:0|max:100',
        ], [
            'divisi.required' => 'Divisi wajib diisi!',
            'jabatan.required' => 'Jabatan wajib diisi!',
            'responsibilities.required' => 'Responsibilities wajib diisi!',
            'kpi.required' => 'KPI (nama indikator) wajib diisi!',
        ]);

        try {
            DB::beginTransaction();

            // Generate kode dengan logika isi celah
            $kode = KodeGenerator::generateWithGapFilling('kpi_hard_skills', 'kode', 'HS');

            KpiHardSkill::create([
                'kode' => $kode,
                // Kolom lama (nama_indikator, deskripsi) tetap ada di schema DB
                // Diisi sama dengan kpi untuk backward compatibility
                'nama_indikator' => $request->kpi,
                'divisi' => $request->divisi,
                'jabatan' => $request->jabatan,
                'responsibilities' => $request->responsibilities,
                'kpi' => $request->kpi,
                'target' => $request->target ?? '100%',
                'weight' => $request->weight ?? 0,
            ]);

            \App\Models\ActivityLog::log(
                'KPI_HARD_SKILL_CREATED',
                "Menambah Hard Skill: {$request->kpi} ({$request->divisi} - {$request->jabatan})",
                auth()->user()->id_user
            );

            DB::commit();

            return redirect()->route('kpi.hard-skill.index')
                           ->with('success', 'Hard Skill berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                           ->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    /**
     * Detail hard skill
     */
    public function show(KpiHardSkill $hardSkill)
    {
        return view('kpi.hard-skill.show', compact('hardSkill'));
    }

    /**
     * Form edit hard skill - redirect ke index (modal sudah di index)
     */
    public function edit(KpiHardSkill $hardSkill)
    {
        return redirect()->route('kpi.hard-skill.index');
    }

    /**
     * Update hard skill
     */
    public function update(Request $request, KpiHardSkill $hardSkill)
    {
        $request->validate([
            'divisi' => 'required|string|max:100',
            'jabatan' => 'required|string|max:100',
            'responsibilities' => 'required|string',
            'kpi' => 'required|string|max:255|unique:kpi_hard_skills,kpi,' . $hardSkill->id,
            'target' => 'nullable|string|max:50',
            'weight' => 'nullable|numeric|min:0|max:100',
        ], [
            'divisi.required' => 'Divisi wajib diisi!',
            'jabatan.required' => 'Jabatan wajib diisi!',
            'responsibilities.required' => 'Responsibilities wajib diisi!',
            'kpi.required' => 'KPI (nama indikator) wajib diisi!',
            'kpi.unique' => 'KPI ini sudah ada, gunakan nama yang berbeda!',
        ]);

        try {
            DB::beginTransaction();

            $hardSkill->update([
                // Kolom lama (nama_indikator) di-sync dengan kpi
                'nama_indikator' => $request->kpi,
                'divisi' => $request->divisi,
                'jabatan' => $request->jabatan,
                'responsibilities' => $request->responsibilities,
                'kpi' => $request->kpi,
                'target' => $request->target ?? '100%',
                'weight' => $request->weight ?? 0,
            ]);

            \App\Models\ActivityLog::log(
                'KPI_HARD_SKILL_UPDATED',
                "Mengubah Hard Skill: {$request->kpi} ({$request->divisi} - {$request->jabatan})",
                auth()->user()->id_user
            );

            DB::commit();

            return redirect()->route('kpi.hard-skill.index')
                           ->with('success', 'Hard Skill berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                           ->with('error', 'Gagal memperbarui: ' . $e->getMessage());
        }
    }

    /**
     * Hapus hard skill
     */
    public function destroy(KpiHardSkill $hardSkill)
    {
        try {
            DB::beginTransaction();

            $usedCount = $hardSkill->assessmentScores()->count();
            if ($usedCount > 0) {
                return redirect()->back()
                               ->with('error', "Indikator tidak dapat dihapus karena masih digunakan di {$usedCount} penilaian!");
            }

            $nama = $hardSkill->kpi;
            $hardSkill->delete();

            \App\Models\ActivityLog::log(
                'KPI_HARD_SKILL_DELETED',
                "Menghapus Hard Skill: {$nama}",
                auth()->user()->id_user
            );

            DB::commit();

            return redirect()->route('kpi.hard-skill.index')
                           ->with('success', 'Hard Skill berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                           ->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }
}
