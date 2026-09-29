<?php

/**
 * Controller untuk Penilaian KPI
 *
 * Fungsi utama:
 * - Self Assessment: Karyawan menilai diri sendiri
 * - Penilaian Atasan: Atasan menilai bawahan
 * - Auto-calculate skor akhir saat keduanya selesai
 *
 * Menu: PENILAIAN -> Penilaian KPI
 */

namespace App\Http\Controllers\Kpi;

use App\Http\Controllers\Controller;
use App\Models\KpiAssessment;
use App\Models\KpiAssessmentScore;
use App\Models\KpiPeriod;
use App\Models\KpiSoftSkill;
use App\Models\KpiHardSkill;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KpiAssessmentController extends Controller
{
    /**
     * Halaman utama Penilaian KPI
     * - Karyawan biasa: form self assessment
     * - Atasan: lihat daftar bawahan + form penilaian
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Auto-create periode untuk bulan berjalan jika belum ada
        $activePeriod = $this->getOrCreateCurrentPeriod();

        // Cek apakah user ini adalah atasan (punya bawahan)
        $hasBawahan = in_array($user->role, ['ATASAN', 'ADMIN'], true)
            && $user->bawahan()->whereHas('kpiAssessments', function ($query) use ($activePeriod) {
                $query->where('kpi_period_id', $activePeriod->id)
                    ->where('status', 'menunggu_review');
            })->exists();

        // Ambil daftar bawahan jika user adalah atasan
        $bawahans = [];
        if ($hasBawahan) {
            $bawahans = $user->bawahan()->whereHas('kpiAssessments', function ($query) use ($activePeriod) {
                $query->where('kpi_period_id', $activePeriod->id)
                    ->where('status', 'menunggu_review');
            })->with(['atasan', 'kpiAssessments' => function ($query) use ($activePeriod) {
                $query->where('kpi_period_id', $activePeriod->id);
            }])->get();
        }

        // Ambil data self assessment user ini di periode aktif
        $myAssessment = null;
        $myScores = collect();
        if ($activePeriod) {
            $myAssessment = KpiAssessment::where('user_id', $user->id_user)
                                       ->where('kpi_period_id', $activePeriod->id)
                                       ->with(['scores' => function($q) {
                                           $q->where('penilai_type', 'karyawan');
                                       }])
                                       ->first();
            $myScores = $myAssessment ? $myAssessment->scores : collect();
        }

        return view('kpi.assessment.index', compact(
            'user', 'hasBawahan', 'bawahans', 'myAssessment', 'myScores', 'activePeriod'
        ));
    }

    /**
     * Helper: Ambil atau buat periode untuk bulan berjalan
     */
    private function getOrCreateCurrentPeriod()
    {
        $monthNames = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
            '04' => 'April', '05' => 'Mei', '06' => 'Juni',
            '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
            '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
        ];

        $month = now()->format('m');
        $year = now()->format('Y');
        $nama = "KPI {$monthNames[$month]} {$year}";

        $period = KpiPeriod::where('nama', $nama)->first();

        if (!$period) {
            $period = KpiPeriod::create([
                'nama' => $nama,
                'tanggal_mulai' => now()->startOfMonth(),
                'tanggal_selesai' => now()->endOfMonth(),
                'status' => 'aktif',
            ]);
        }

        return $period;
    }

    /**
     * Form self assessment untuk user yang login
     * Menampilkan form untuk menilai diri sendiri
     * Hard skill difilter berdasarkan divisi/jabatan user
     */
    public function selfAssessment(Request $request)
    {
        $user = auth()->user();

        // Cek apakah sudah ada assessment di bulan ini
        $period = $this->getOrCreateCurrentPeriod();
        $assessment = KpiAssessment::where('user_id', $user->id_user)
                                  ->where('kpi_period_id', $period->id)
                                  ->first();

        if (!$assessment) {
            $assessment = KpiAssessment::firstOrCreate(
                [
                    'user_id' => $user->id_user,
                    'kpi_period_id' => $period->id,
                ],
                [
                    'atasan_id' => $user->atasan_id,
                    'status' => 'pending',
                ]
            );
        }

        // Ambil semua indikator soft skill (generic untuk semua)
        $softSkills = KpiSoftSkill::orderBy('nama_indikator')->get();

        // Ambil indikator hard skill berdasarkan divisi/jabatan user
        $hardSkills = collect();
        if ($user->divisi && $user->jabatan) {
            $hardSkills = KpiHardSkill::where('divisi', $user->divisi)
                                     ->where('jabatan', $user->jabatan)
                                     ->orderBy('kpi')
                                     ->get();
        }

        // Ambil skor existing
        $selfScores = $assessment ? $assessment->selfScores()->get()->keyBy('skill_id') : collect();

        return view('kpi.assessment.self-assessment', compact(
            'assessment', 'softSkills', 'hardSkills', 'selfScores'
        ));
    }

    /**
     * Simpan self assessment
     */
    public function storeSelfAssessment(Request $request, KpiAssessment $assessment)
    {
        // Validasi: pastikan assessment ini milik user yang login
        // User model menggunakan id_user sebagai primary key, bukan id
        if ($assessment->user_id !== auth()->user()->id_user) {
            abort(403, 'Anda tidak memiliki akses ke penilaian ini!');
        }

        $request->validate([
            'scores' => 'required|array',
            'scores.*' => 'nullable|array',
            'scores.*.*' => 'required|integer|min:1|max:100',
            'notes' => 'nullable|array',
            'notes.*.*' => 'nullable|string|max:500',
        ], [
            'scores.required' => 'Minimal satu indikator harus dinilai!',
            'scores.*.min' => 'Skor minimal adalah 1!',
            'scores.*.max' => 'Skor maksimal adalah 100!',
        ]);

        try {
            DB::beginTransaction();

            // Simpan skor baru
            $scoresByType = $request->input('scores', []);
            if (isset($scoresByType['soft_skill']) || isset($scoresByType['hard_skill'])) {
                $typedScores = $scoresByType;
            } else {
                $typedScores = ['soft_skill' => $scoresByType];
            }

            foreach ($typedScores as $skillType => $scores) {
                foreach ($scores ?? [] as $skillId => $skor) {
                    $skillIdInt = (int) $skillId;

                    KpiAssessmentScore::updateOrCreate(
                    [
                        'kpi_assessment_id' => $assessment->id,
                        'skill_type' => $skillType,
                        'skill_id' => $skillIdInt,
                        'penilai_type' => 'karyawan',
                    ],
                    [
                        'skor' => $skor,
                        'catatan' => $request->input("notes.{$skillType}.{$skillId}"),
                    ]
                    );
                }
            }

            // New self submissions wait for supervisor review.
            $assessment->update(['status' => 'menunggu_review']);

            \App\Models\ActivityLog::log(
                'KPI_SELF_ASSESSMENT_DONE',
                "Self assessment selesai untuk: {$assessment->user->nama}",
                auth()->user()->id_user
            );

            DB::commit();

            return redirect()->route('kpi.assessment.index')
                           ->with('success', 'Self Assessment berhasil disimpan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                           ->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    /**
     * Form penilaian atasan untuk satu bawahan
     * Menampilkan form untuk atasan menilai salah satu bawahannya
     * Hard skill difilter berdasarkan divisi/jabatan bawahan
     */
    public function atasanAssessment(Request $request, KpiAssessment $assessment)
    {
        $user = auth()->user();

        // Validasi: pastikan user ini adalah atasan dari assessment ini
        $isAtasan = $assessment->atasan_id === $user->id_user
                     || ($user->role === 'ATASAN' || $user->role === 'ADMIN');

        if (!$isAtasan) {
            return redirect()->route('kpi.assessment.index')
                           ->with('error', 'Anda bukan atasan dari karyawan ini!');
        }

        // Ambil bawahan untuk filter hard skill
        $bawahan = $assessment->user;

        // Ambil semua indikator soft skill (generic untuk semua)
        $softSkills = KpiSoftSkill::orderBy('nama_indikator')->get();

        // Ambil indikator hard skill berdasarkan divisi/jabatan bawahan
        $hardSkills = collect();
        if ($bawahan->divisi && $bawahan->jabatan) {
            $hardSkills = KpiHardSkill::where('divisi', $bawahan->divisi)
                                     ->where('jabatan', $bawahan->jabatan)
                                     ->orderBy('kpi')
                                     ->get();
        }

        // Ambil skor existing dari atasan
        $atasanScores = $assessment->atasanScores()->get()->keyBy('skill_id');

        return view('kpi.assessment.atasan-assessment', compact(
            'assessment', 'softSkills', 'hardSkills', 'atasanScores'
        ));
    }

    /**
     * Simpan penilaian atasan
     */
    public function storeAtasanAssessment(Request $request, KpiAssessment $assessment)
    {
        $user = auth()->user();

        // Validasi akses
        $isAtasan = $assessment->atasan_id === $user->id_user
                     || ($user->role === 'ATASAN' || $user->role === 'ADMIN');

        if (!$isAtasan) {
            abort(403, 'Anda tidak memiliki akses!');
        }

        $request->validate([
            'scores' => 'required|array',
            'scores.*' => 'required|integer|min:1|max:100',
            'notes' => 'nullable|array',
            'notes.*.*' => 'nullable|string|max:500',
        ], [
            'scores.required' => 'Minimal satu indikator harus dinilai!',
        ]);

        try {
            DB::beginTransaction();

            // Simpan skor baru
            foreach ($request->input('scores', []) as $skillType => $scores) {
                foreach ($scores ?? [] as $skillId => $skor) {
                    KpiAssessmentScore::updateOrCreate(
                        [
                            'kpi_assessment_id' => $assessment->id,
                            'skill_type' => $skillType,
                            'skill_id' => (int) $skillId,
                            'penilai_type' => 'atasan',
                        ],
                        [
                            'skor' => $skor,
                            'catatan' => $request->input("notes.{$skillType}.{$skillId}"),
                        ]
                    );
                }
            }

            // Update status assessment (juga calculate skor akhir)
            $assessment->updateStatus();

            \App\Models\ActivityLog::log(
                'KPI_ATASAN_ASSESSMENT_DONE',
                "Penilaian atasan selesai untuk: {$assessment->user->nama}",
                auth()->user()->id_user
            );

            DB::commit();

            return redirect()->route('kpi.assessment.index')
                           ->with('success', 'Penilaian berhasil disimpan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                           ->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    /**
     * Detail assessment (view only, setelah selesai)
     */
    public function show(KpiAssessment $assessment)
    {
        $assessment->load(['user', 'atasan', 'scores.skill']);

        // Kelompokkan skor berdasarkan tipe
        $selfScores = $assessment->scores->where('penilai_type', 'karyawan');
        $atasanScores = $assessment->scores->where('penilai_type', 'atasan');

        return view('kpi.assessment.show', compact(
            'assessment', 'selfScores', 'atasanScores'
        ));
    }

    /**
     * Histori KPI per user
     * Dikelompokkan berdasarkan bulan dari created_at
     */
    public function history(Request $request)
    {
        // Ambil user yang akan dilihat (default: user yang login)
        $targetUserId = $request->user_id ?? auth()->user()->id_user;

        // Admin bisa lihat user lain
        if ($targetUserId != auth()->user()->id_user && auth()->user()->role !== 'ADMIN') {
            $targetUserId = auth()->user()->id_user;
        }

        $targetUser = User::findOrFail($targetUserId);

        // Ambil daftar periode untuk filter
        $periods = KpiPeriod::orderByDesc('tanggal_mulai')->get();

        // Filter berdasarkan periode jika dipilih
        $selectedPeriodId = $request->period_id;

        // Ambil semua assessment user ini
        $query = KpiAssessment::where('user_id', $targetUserId)
                              ->with(['scores', 'period']);

        if ($selectedPeriodId) {
            $query->where('kpi_period_id', $selectedPeriodId);
        }

        $assessments = $query->orderByDesc('created_at')->get();

        // Selected period object untuk dropdown
        $selectedPeriod = $selectedPeriodId ? $periods->find($selectedPeriodId) : null;

        // Siapkan data untuk chart tren
        // Label: nama periode dari period->nama
        $chartLabels = $assessments->map(function($a) {
            return $a->period?->nama ?? $a->created_at->format('M Y');
        })->toArray();
        $chartScores = $assessments->pluck('skor_akhir')->toArray();

        // Semua user untuk filter (hanya admin)
        $allUsers = [];
        if (auth()->user()->role === 'ADMIN') {
            $allUsers = User::where('status', true)
                           ->orderBy('nama')
                           ->get();
        }

        return view('kpi.assessment.history', compact(
            'targetUser', 'assessments', 'chartLabels', 'chartScores', 'allUsers',
            'periods', 'selectedPeriod'
        ));
    }

    /**
     * Rekap semua KPI (halaman admin)
     * Tampilkan semua assessment, difilter berdasarkan periode
     */
    public function recap(Request $request)
    {
        // Ambil daftar periode untuk filter
        $periods = KpiPeriod::orderByDesc('tanggal_mulai')->get();

        // Filter berdasarkan periode jika dipilih
        $selectedPeriodId = $request->period_id;

        // Ambil semua assessment
        $query = KpiAssessment::with(['user', 'atasan', 'period']);

        if ($selectedPeriodId) {
            $query->where('kpi_period_id', $selectedPeriodId);
        }

        $assessments = $query->orderByDesc('created_at')->get();

        // Selected period object untuk dropdown
        $selectedPeriod = $selectedPeriodId ? $periods->find($selectedPeriodId) : null;

        return view('kpi.assessment.recap', compact(
            'assessments', 'periods', 'selectedPeriod'
        ));
    }

    // =====================================================
    // WIZARD PENILAIAN KPI (4 STEP)
    // =====================================================

    /**
     * Wizard Step 1: Pilih Periode & Karyawan
     *
     * Ada dua mode:
     * 1. Review mode (ada assessment_id): Atasan mereview assessment bawahan
     * 2. Legacy mode (tanpa assessment_id): Membuat assessment baru (untuk backward compatibility)
     */
    public function wizardStep1(Request $request)
    {
        $assessmentId = $request->input('assessment_id');

        if ($assessmentId) {
            // Mode 1: Review mode - Atasan mereview assessment bawahan
            $assessment = KpiAssessment::with('user')->findOrFail($assessmentId);
            $this->authorizeAtasanReview($assessment);

            if ($assessment->status !== 'menunggu_review') {
                return redirect()->route('kpi.assessment.index')
                    ->with('error', 'Assessment ini belum menunggu review atasan.');
            }

            return view('kpi.assessment.wizard-step1', [
                'assessment' => $assessment,
                'wizardData' => [],
                'reviewMode' => true,
            ]);
        }

        // Mode 2: Legacy mode - Panggil wizard legacy untuk buat assessment baru
        return $this->legacyWizardStep1($request);
    }

    private function authorizeAtasanReview(KpiAssessment $assessment): void
    {
        $user = auth()->user();
        if ($user->role !== 'ADMIN' && !($user->role === 'ATASAN' && $assessment->atasan_id === $user->id_user)) {
            abort(403, 'Anda tidak memiliki akses review assessment ini!');
        }
    }

    /**
     * Legacy method body is retained below for route compatibility.
     */
    private function legacyWizardStep1(Request $request)
    {
        $user = auth()->user();
        $isAdmin = $user->role === 'ADMIN';
        $isAtasan = $user->role === 'ATASAN';

        // Ambil daftar karyawan
        // Admin & Atasan bisa pilih bawahan, Karyawan biasa hanya bisa untuk diri sendiri
        if ($isAdmin) {
            $employees = User::where('status', true)->orderBy('nama')->get();
        } elseif ($isAtasan) {
            // Ambil bawahan langsung
            $employees = $user->bawahan()->where('status', true)->orderBy('nama')->get();
            // Tambahkan diri sendiri
            $employees = $employees->push($user)->sortBy('nama')->values();
        } else {
            // Karyawan biasa hanya bisa menilai diri sendiri
            $employees = collect([$user]);
        }

        // Ambil data session jika ada (langkah sebelumnya)
        $wizardData = session('kpi_wizard', []);

        return view('kpi.assessment.wizard-step1', compact('employees', 'wizardData'));
    }

    /**
     * Wizard Step 1 POST: Simpan data periode & karyawan ke session
     *
     * Ada dua mode:
     * 1. Review mode (ada assessment_id): Simpan ke session, redirect ke step2
     * 2. Legacy mode (tanpa assessment_id): Redirect ke self-assessment untuk buat assessment baru
     */
    public function wizardPostStep1(Request $request)
    {
        if ($request->filled('assessment_id')) {
            // Mode 1: Review mode - Simpan assessment_id dan redirect ke wizard step 2
            $assessment = KpiAssessment::with(['user', 'period'])->findOrFail($request->assessment_id);
            $this->authorizeAtasanReview($assessment);

            if ($assessment->status !== 'menunggu_review') {
                return redirect()->route('kpi.assessment.index')
                    ->with('error', 'Assessment ini belum menunggu review atasan.');
            }

            session(['kpi_wizard' => [
                'assessment_id' => $assessment->id,
                'period_id' => $assessment->kpi_period_id,
                'period_nama' => $assessment->period?->nama,
                'user_id' => $assessment->user_id,
                'user_nama' => $assessment->user->nama,
                'atasan_id' => auth()->user()->id_user,
            ]]);

            return redirect()->route('kpi.assessment.wizard.step2');
        }

        // Mode 2: Legacy mode - Wizard ini sekarang HANYA untuk review.
        // Untuk buat assessment baru, redirect ke self-assessment page
        \Log::info('WIZARD STEP 1 POST - Legacy mode detected, redirecting to self-assessment', [
            'user_id' => auth()->user()->id_user ?? 'null',
            'role' => auth()->user()->role ?? 'null',
        ]);

        return redirect()->route('kpi.assessment.self')
            ->with('info', 'Untuk membuat penilaian baru, gunakan menu Self-Assessment.');
    }

    /**
     * Helper: Generate nama periode
     */
    private function getPeriodNama(string $month, string $year): string
    {
        $monthNames = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
            '04' => 'April', '05' => 'Mei', '06' => 'Juni',
            '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
            '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
        ];
        $monthName = $monthNames[$month] ?? $month;
        return "KPI {$monthName} {$year}";
    }

    /**
     * Wizard Step 2: Soft Skill
     */
    public function wizardStep2()
    {
        \Log::info('WIZARD STEP 2 - Loading', [
            'session_kpi_wizard' => session('kpi_wizard'),
        ]);

        $wizardData = session('kpi_wizard');
        if (!$wizardData || !isset($wizardData['period_id'], $wizardData['assessment_id'])) {
            \Log::warning('WIZARD STEP 2 - No review session, redirecting back');
            return redirect()->route('kpi.assessment.index')->with('error', 'Mohon pilih assessment yang menunggu review.');
        }

        $softSkills = KpiSoftSkill::orderBy('nama_indikator')->get();

        if ($softSkills->isEmpty()) {
            return redirect()->back()->with('error', 'Belum ada indikator Soft Skill. Hubungi admin!');
        }

        $assessment = KpiAssessment::with('selfScores')->findOrFail($wizardData['assessment_id']);
        $selfScores = $assessment->selfScores->keyBy('skill_id');

        return view('kpi.assessment.wizard-step2', compact('wizardData', 'softSkills', 'assessment', 'selfScores'));
    }

    /**
     * Wizard Step 2 POST: Simpan soft skill scores ke session
     */
    public function wizardPostStep2(Request $request)
    {
        // Validasi: nilai harus dari skala dropdown (20, 40, 60, 80, 100)
        $validScores = [20, 40, 60, 80, 100];

        $request->validate([
            'scores' => 'required|array',
            'scores.*' => 'required|integer|in:' . implode(',', $validScores),
        ], [
            'scores.required' => 'Minimal satu indikator harus dinilai!',
            'scores.*.in' => 'Nilai harus dipilih dari daftar yang tersedia!',
        ]);

        $wizardData = session('kpi_wizard', []);
        $wizardData['atasan_soft_skills'] = $request->scores;
        $wizardData['atasan_soft_notes'] = $request->notes ?? [];
        session(['kpi_wizard' => $wizardData]);

        return redirect()->route('kpi.assessment.wizard.step3');
    }

    /**
     * Wizard Step 3: Hard Skill
     * Filter berdasarkan divisi & jabatan karyawan yang dinilai
     */
    public function wizardStep3()
    {
        $wizardData = session('kpi_wizard');
        if (!$wizardData || !isset($wizardData['period_id'], $wizardData['assessment_id'])) {
            return redirect()->route('kpi.assessment.index')->with('error', 'Mohon pilih assessment yang menunggu review.');
        }

        // Ambil data karyawan yang dinilai
        $employee = User::find($wizardData['user_id']);
        $employeeDivisi = $employee->divisi ?? null;
        $employeeJabatan = $employee->jabatan ?? null;

        // Jika divisi atau jabatan kosong, tampilkan pesan error dengan link ke edit profil
        if (empty($employeeDivisi) || empty($employeeJabatan)) {
            $wizardData['atasan_hard_skills'] = [];
            $wizardData['atasan_hard_notes'] = [];
            session(['kpi_wizard' => $wizardData]);
            return redirect()->route('kpi.assessment.wizard.step4');
        }

        // Filter hard skill berdasarkan divisi DAN jabatan karyawan
        $hardSkills = KpiHardSkill::where('divisi', $employeeDivisi)
                                  ->where('jabatan', $employeeJabatan)
                                  ->orderBy('kpi')
                                  ->get();

        if ($hardSkills->isEmpty()) {
            $wizardData['atasan_hard_skills'] = [];
            $wizardData['atasan_hard_notes'] = [];
            session(['kpi_wizard' => $wizardData]);
            return redirect()->route('kpi.assessment.wizard.step4');
        }

        $assessment = KpiAssessment::with('selfScores')->findOrFail($wizardData['assessment_id']);
        $selfScores = $assessment->selfScores->keyBy('skill_id');

        return view('kpi.assessment.wizard-step3', [
            'wizardData' => $wizardData,
            'hardSkills' => $hardSkills,
            'missingPosition' => false,
            'employeeId' => null,
            'employeeName' => null,
            'assessment' => $assessment,
            'selfScores' => $selfScores,
        ]);
    }

    /**
     * Wizard Step 3 POST: Simpan hard skill scores ke session
     */
    public function wizardPostStep3(Request $request)
    {
        // Validasi: nilai harus dari skala dropdown (20, 40, 60, 80, 100)
        $validScores = [20, 40, 60, 80, 100];

        // Handle skip button jika tidak ada hard skill untuk divisi/jabatan ini
        if ($request->input('skip') == '1') {
            $wizardData = session('kpi_wizard', []);
            $wizardData['atasan_hard_skills'] = [];
            $wizardData['atasan_hard_notes'] = [];
            session(['kpi_wizard' => $wizardData]);
            return redirect()->route('kpi.assessment.wizard.step4');
        }

        // Validasi khusus untuk step 3 - hanya jika ada hard skills yang harus dinilai
        $wizardData = session('kpi_wizard', []);
        $employee = User::find($wizardData['user_id'] ?? null);

        // Cek apakah ada hard skills untuk divisi/jabatan ini
        if ($employee && $employee->divisi && $employee->jabatan) {
            $hardSkillsCount = KpiHardSkill::where('divisi', $employee->divisi)
                                          ->where('jabatan', $employee->jabatan)
                                          ->count();

            if ($hardSkillsCount > 0) {
                $request->validate([
                    'scores' => 'required|array',
                    'scores.*' => 'required|integer|in:' . implode(',', $validScores),
                ], [
                    'scores.required' => 'Minimal satu indikator harus dinilai!',
                    'scores.*.in' => 'Nilai harus dipilih dari daftar yang tersedia!',
                ]);
            }
        }

        $wizardData['atasan_hard_skills'] = $request->scores ?? [];
        $wizardData['atasan_hard_notes'] = $request->notes ?? [];
        session(['kpi_wizard' => $wizardData]);

        return redirect()->route('kpi.assessment.wizard.step4');
    }

    /**
     * Wizard Step 4: Review & Submit
     * Filter hard skill berdasarkan divisi/jabatan karyawan yang dinilai
     */
    public function wizardStep4()
    {
        $wizardData = session('kpi_wizard');
        if (!$wizardData || !isset($wizardData['period_id'], $wizardData['assessment_id'])) {
            return redirect()->route('kpi.assessment.index')->with('error', 'Mohon pilih assessment yang menunggu review.');
        }

        // Ambil data untuk review
        $softSkills = KpiSoftSkill::orderBy('nama_indikator')->get();

        // Filter hard skill berdasarkan divisi/jabatan karyawan
        $employee = User::find($wizardData['user_id']);
        if ($employee && $employee->divisi && $employee->jabatan) {
            $hardSkills = KpiHardSkill::where('divisi', $employee->divisi)
                                      ->where('jabatan', $employee->jabatan)
                                      ->orderBy('kpi')
                                      ->get();
        } else {
            $hardSkills = collect(); // Empty collection jika divisi/jabatan kosong
        }

        $assessment = KpiAssessment::with(['selfScores', 'atasanScores'])->findOrFail($wizardData['assessment_id']);
        return view('kpi.assessment.wizard-step4', compact('wizardData', 'softSkills', 'hardSkills', 'assessment'));
    }

    /**
     * Wizard Submit: Simpan semua data ke database
     */
    public function wizardSubmit(Request $request)
    {
        $wizardData = session('kpi_wizard');
        if (!$wizardData || !isset($wizardData['period_id'])) {
            return redirect()->route('kpi.assessment.create')->with('error', 'Sesi wizard expired. Mohon mulai ulang!');
        }

        try {
            DB::beginTransaction();

            if (isset($wizardData['assessment_id'])) {
                $assessment = KpiAssessment::findOrFail($wizardData['assessment_id']);
                $this->authorizeAtasanReview($assessment);

                foreach ([
                    'soft_skill' => ['scores' => $wizardData['atasan_soft_skills'] ?? [], 'notes' => $wizardData['atasan_soft_notes'] ?? []],
                    'hard_skill' => ['scores' => $wizardData['atasan_hard_skills'] ?? [], 'notes' => $wizardData['atasan_hard_notes'] ?? []],
                ] as $skillType => $skillData) {
                    foreach ($skillData['scores'] as $skillId => $skor) {
                        KpiAssessmentScore::updateOrCreate(
                            [
                                'kpi_assessment_id' => $assessment->id,
                                'skill_type' => $skillType,
                                'skill_id' => (int) $skillId,
                                'penilai_type' => 'atasan',
                            ],
                            [
                                'skor' => (int) $skor,
                                'catatan' => $skillData['notes'][$skillId] ?? null,
                            ]
                        );
                    }
                }

                $assessment->updateStatus();
                DB::commit();
                session()->forget('kpi_wizard');

                return redirect()->route('kpi.assessment.index')
                    ->with('success', 'Review penilaian berhasil disimpan!');
            }

            // Buat assessment baru dengan kpi_period_id
            $assessment = KpiAssessment::create([
                'user_id' => $wizardData['user_id'],
                'kpi_period_id' => $wizardData['period_id'],
                'atasan_id' => $wizardData['atasan_id'] ?? null,
                'status' => 'pending',
            ]);

            // JANGAN menghapus data skor lama. Tetap aman dan update/insert per indikator.
            if (isset($wizardData['soft_skills'])) {
                foreach ($wizardData['soft_skills'] as $skillId => $skor) {
                    KpiAssessmentScore::updateOrCreate(
                        [
                            'kpi_assessment_id' => $assessment->id,
                            'skill_type' => 'soft_skill',
                            'skill_id' => (int) $skillId,
                            'penilai_type' => 'atasan',
                        ],
                        [
                            'skor' => (int) $skor,
                            'catatan' => $wizardData['soft_notes'][$skillId] ?? null,
                        ]
                    );
                }
            }

            // Simpan hard skill scores
            if (isset($wizardData['hard_skills'])) {
                foreach ($wizardData['hard_skills'] as $skillId => $skor) {
                    KpiAssessmentScore::updateOrCreate(
                        [
                            'kpi_assessment_id' => $assessment->id,
                            'skill_type' => 'hard_skill',
                            'skill_id' => (int) $skillId,
                            'penilai_type' => 'atasan',
                        ],
                        [
                            'skor' => (int) $skor,
                            'catatan' => $wizardData['hard_notes'][$skillId] ?? null,
                        ]
                    );
                }
            }

            // Update status assessment
            $assessment->updateStatus();

            // Log aktivitas
            \App\Models\ActivityLog::log(
                'KPI_ASSESSMENT_CREATED',
                "Penilaian KPI wizard untuk: {$wizardData['user_nama']}",
                auth()->user()->id_user
            );

            DB::commit();

            // Hapus session wizard
            session()->forget('kpi_wizard');

            return redirect()->route('kpi.assessment.index')
                           ->with('success', 'Penilaian KPI berhasil disimpan!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                           ->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }
}
