<?php

/**
 * Controller untuk mengelola indikator Soft Skill KPI
 * CRUD standar untuk master data indikator soft skill
 *
 * Menu: Master Data -> Soft Skill
 */

namespace App\Http\Controllers\Kpi;

use App\Http\Controllers\Controller;
use App\Models\KpiSoftSkill;
use App\Helpers\KodeGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KpiSoftSkillController extends Controller
{
    /**
     * Menampilkan daftar semua indikator soft skill
     * Mendukung pencarian dan pagination
     */
    public function index(Request $request)
    {
        $query = KpiSoftSkill::query();

        // Filter pencarian
        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('kode', 'like', "%{$search}%")
                  ->orWhere('nama_indikator', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        $softSkills = $query->orderBy('kode')->paginate(10);
        $softSkills->appends($request->all());

        return view('kpi.soft-skill.index', compact('softSkills'));
    }

    /**
     * Menampilkan form tambah indikator soft skill baru
     */
    public function create()
    {
        return view('kpi.soft-skill.create');
    }

    /**
     * Menyimpan indikator soft skill baru ke database
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_indikator' => 'required|string|max:200',
            'deskripsi' => 'nullable|string|max:500',
        ], [
            'nama_indikator.required' => 'Nama Indikator wajib diisi!',
        ]);

        try {
            DB::beginTransaction();

            // Generate kode dengan logika isi celah
            $kode = KodeGenerator::generateWithGapFilling('kpi_soft_skills', 'kode', 'SS');

            KpiSoftSkill::create([
                'kode' => $kode,
                'nama_indikator' => $request->nama_indikator,
                'deskripsi' => $request->deskripsi,
            ]);

            // Log aktivitas untuk audit trail
            \App\Models\ActivityLog::log(
                'KPI_SOFT_SKILL_CREATED',
                "Menambah Indikator Soft Skill: {$request->nama_indikator}",
                auth()->user()->id_user
            );

            DB::commit();

            return redirect()->route('kpi.soft-skill.index')
                           ->with('success', 'Indikator Soft Skill berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                           ->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan detail satu indikator soft skill
     */
    public function show(KpiSoftSkill $softSkill)
    {
        return view('kpi.soft-skill.show', compact('softSkill'));
    }

    /**
     * Menampilkan form edit indikator soft skill
     */
    public function edit(KpiSoftSkill $softSkill)
    {
        return view('kpi.soft-skill.edit', compact('softSkill'));
    }

    /**
     * Mengupdate data indikator soft skill
     */
    public function update(Request $request, KpiSoftSkill $softSkill)
    {
        $request->validate([
            'nama_indikator' => 'required|string|max:200',
            'deskripsi' => 'nullable|string|max:500',
        ], [
            'nama_indikator.required' => 'Nama Indikator wajib diisi!',
        ]);

        try {
            DB::beginTransaction();

            $softSkill->update([
                'nama_indikator' => $request->nama_indikator,
                'deskripsi' => $request->deskripsi,
            ]);

            // Log aktivitas
            \App\Models\ActivityLog::log(
                'KPI_SOFT_SKILL_UPDATED',
                "Mengubah Indikator Soft Skill: {$request->nama_indikator}",
                auth()->user()->id_user
            );

            DB::commit();

            return redirect()->route('kpi.soft-skill.index')
                           ->with('success', 'Indikator Soft Skill berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                           ->with('error', 'Gagal memperbarui: ' . $e->getMessage());
        }
    }

    /**
     * Menghapus indikator soft skill
     * Dicek apakah masih digunakan di penilaian manapun
     */
    public function destroy(KpiSoftSkill $softSkill)
    {
        try {
            DB::beginTransaction();

            // Cek apakah masih digunakan
            $usedCount = $softSkill->assessmentScores()->count();
            if ($usedCount > 0) {
                return redirect()->back()
                               ->with('error', "Indikator tidak dapat dihapus karena masih digunakan di {$usedCount} penilaian!");
            }

            $nama = $softSkill->nama_indikator;
            $softSkill->delete();

            // Log aktivitas
            \App\Models\ActivityLog::log(
                'KPI_SOFT_SKILL_DELETED',
                "Menghapus Indikator Soft Skill: {$nama}",
                auth()->user()->id_user
            );

            DB::commit();

            return redirect()->route('kpi.soft-skill.index')
                           ->with('success', 'Indikator Soft Skill berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                           ->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }
}
