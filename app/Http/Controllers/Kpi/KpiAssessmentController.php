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

        // Cek apakah user ini punya bawahan langsung (untuk show/hide tab review)
        $hasBawahan = $user->bawahan()->whereHas('kpiAssessments', function ($query) use ($activePeriod) {
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

        // Final assessments are immutable after supervisor review.
        if (in_array($assessment->status, ['sudah_dicek', 'selesai'], true)) {
            return back()->with('error', 'Penilaian ini sudah dicek atasan dan tidak dapat diubah.');
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

        // Validasi: ADMIN atau user yang id_user = assessment->atasan_id
        $canReview = $user->role === 'ADMIN' || $assessment->atasan_id === $user->id_user;

        if (!$canReview) {
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

        // Validasi: ADMIN atau user yang id_user = assessment->atasan_id
        $canReview = $user->role === 'ADMIN' || $assessment->atasan_id === $user->id_user;

        if (!$canReview) {
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
     * Histori KPI
     * - ADMIN: melihat semua assessment (default). Parameter user_id mempersempit ke satu karyawan.
     * - Non-ADMIN: melihat assessment diri sendiri + bawahan langsung.
     *   Parameter user_id hanya boleh mempersempit dalam batas tersebut; diabaikan jika di luar batas.
     */
    public function history(Request $request)
    {
        $user = auth()->user();
        $periods = KpiPeriod::orderByDesc('tanggal_mulai')->get();

        // Parse filter inputs
        $periodInput = $request->query('period_id');
        $periodId = is_scalar($periodInput)
            ? filter_var($periodInput, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
            : false;
        $selectedPeriod = $periodId ? $periods->firstWhere('id', (int) $periodId) : null;
        $selectedPeriodId = $selectedPeriod?->id;

        $pageSizeInput = $request->query('per_page');
        $requestedPageSize = is_scalar($pageSizeInput)
            ? filter_var($pageSizeInput, FILTER_VALIDATE_INT)
            : false;
        $perPage = in_array($requestedPageSize, [10, 25, 50, 100], true) ? $requestedPageSize : 10;

        // =====================================================
        // TENTUKAN USER SCOPE BERDASARKAN HAK AKSES
        // =====================================================
        $requestedUserId = $request->user_id;
        $intUserId = $requestedUserId && is_scalar($requestedUserId)
            ? filter_var($requestedUserId, FILTER_VALIDATE_INT)
            : null;

        if ($user->role === 'ADMIN') {
            // ADMIN: lihat SEMUA user (termasuk nonaktif), filter hanya untuk dropdown
            $allUserIds = User::pluck('id_user');
            $targetUser = null; // Default: admin melihat banyak user
            if ($intUserId && $allUserIds->contains($intUserId)) {
                $allUserIds = collect([$intUserId]);
                $targetUser = User::find($intUserId); // Admin filter ke satu user
            }
            // Dropdown hanya tampilkan user aktif
            $allUsers = User::where('status', true)->orderBy('nama')->get();
        } else {
            // Non-ADMIN: diri sendiri + SEMUA bawahan langsung (termasuk nonaktif)
            $bawahanIds = $user->bawahan()->pluck('id_user');
            $allUserIds = $bawahanIds->push($user->id_user);

            // user_id param hanya boleh mempersempit dalam batas akses
            if ($intUserId) {
                // Jika user_id dalam scope, filter ke user tersebut
                if ($allUserIds->contains($intUserId)) {
                    $allUserIds = collect([$intUserId]);
                }
                // Jika di luar scope,abaikan saja (tidak error, tidak bocor data)
            }

            // Multi-user mode: $targetUser = null jika scope > 1 user (tanpa filter spesifik)
            // Single-user mode: $targetUser terisi jika filter user_id atau scope hanya 1
            if ($allUserIds->count() === 1) {
                $targetUser = User::find($allUserIds->first());
            } else {
                $targetUser = null;
            }
            $allUsers = collect(); // Non-admin tidak perlu dropdown semua user
        }

        // =====================================================
        // QUERY ASSESSMENTS
        // =====================================================
        $query = KpiAssessment::whereIn('user_id', $allUserIds);
        if ($selectedPeriodId !== null) {
            $query->where('kpi_period_id', $selectedPeriodId);
        }

        // Chart data: skor akhir per periode
        $chartAssessments = (clone $query)
            ->select(['id', 'kpi_period_id', 'skor_akhir', 'created_at'])
            ->with('period:id,nama')
            ->orderByDesc('created_at')
            ->get();

        // Paginated assessments dengan eager loading untuk hindari N+1
        $assessments = $query
            ->with(['user', 'period', 'atasan', 'scores.skill'])
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();

        // Hitung skor soft/hard skill untuk display
        $scoreRecords = $assessments->getCollection()->flatMap(fn (KpiAssessment $assessment) => $assessment->scores);
        foreach (['soft_skill', 'hard_skill'] as $skillType) {
            $scoresForType = new \Illuminate\Database\Eloquent\Collection(
                $scoreRecords->where('skill_type', $skillType)->values()->all()
            );
            $scoresForType->load('skill');
        }

        $assessments->each(function (KpiAssessment $assessment) {
            $skillScores = $assessment->calculateSkillScores();
            $assessment->setAttribute('history_soft_skill_score', $skillScores['soft_skill']);
            $assessment->setAttribute('history_hard_skill_score', $skillScores['hard_skill']);
        });

        // Chart labels dan scores
        $chartLabels = $chartAssessments->map(fn ($a) => $a->period?->nama ?? $a->created_at->format('M Y'))->toArray();
        $chartScores = $chartAssessments->pluck('skor_akhir')->toArray();

        return view('kpi.assessment.history', compact(
            'targetUser', 'assessments', 'chartLabels', 'chartScores', 'allUsers',
            'periods', 'selectedPeriod', 'perPage'
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
            $assessment = KpiAssessment::with(['user', 'period'])->findOrFail($assessmentId);
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
        // Akses review: ADMIN atau user yang id_user = assessment->atasan_id
        if ($user->role !== 'ADMIN' && $user->id_user !== $assessment->atasan_id) {
            abort(403, 'Anda tidak memiliki akses review assessment ini!');
        }
    }

    /**
     * Legacy method body is retained below for route compatibility.
     * NOTE: Legacy mode sekarang mengalihkan ke self-assessment, tapi view tetap dirender
     * dengan dropdown karyawan (Step 1 baru) untuk menampilkan status self-assessment.
     */
    private function legacyWizardStep1(Request $request)
    {
        $user = auth()->user();

        // Admin bisa pilih semua karyawan, user lain hanya bawahan langsung + diri sendiri
        if ($user->role === 'ADMIN') {
            $employeesQuery = User::where('status', true)->orderBy('nama');
        } else {
            // Ambil bawahan langsung + diri sendiri
            $employeesQuery = User::where('status', true)
                ->where(function ($q) use ($user) {
                    $q->where('atasan_id', $user->id_user)
                      ->orWhere('id_user', $user->id_user);
                })
                ->orderBy('nama');
        }

        // Eager load relasi untuk hindari N+1
        $employees = $employeesQuery->with('atasan')->get();

        // Cek apakah user punya bawahan (lebih dari 1 pilihan karyawan)
        $hasBawahan = $employees->count() > 1;

        // Ambil data session jika ada (langkah sebelumnya)
        $wizardData = session('kpi_wizard', []);

        // Ambil periode aktif untuk filter status
        $periodValue = old('period_value', $wizardData['period_value'] ?? '');
        $activePeriod = $this->getOrCreateCurrentPeriod();

        // Hitung status self-assessment untuk SEMUA karyawan dalam SATU query
        // Ini menghindari N+1 problem
        $userIds = $employees->pluck('id_user')->toArray();
        $periodId = $activePeriod->id ?? null;

        $assessmentStatuses = [];
        if (!empty($userIds) && $periodId) {
            $assessments = KpiAssessment::whereIn('user_id', $userIds)
                ->where('kpi_period_id', $periodId)
                ->with(['selfScores' => function ($q) {
                    $q->select('kpi_assessment_id'); // Hanya perlu exists, tidak perlu data lengkap
                }])
                ->get()
                ->keyBy('user_id');

            foreach ($userIds as $userId) {
                $assessment = $assessments->get($userId);
                if (!$assessment) {
                    // Tidak ada assessment sama sekali = "belum mengisi"
                    $assessmentStatuses[$userId] = [
                        'status' => 'no_record',
                        'badge' => 'Belum mengisi Self-Assessment',
                        'disabled' => true,
                    ];
                } elseif ($assessment->status === 'menunggu_review') {
                    // Self-assessment sudah, menunggu review = bisa dipilih
                    $assessmentStatuses[$userId] = [
                        'status' => 'menunggu_review',
                        'badge' => 'Menunggu Review',
                        'disabled' => false,
                    ];
                } elseif (in_array($assessment->status, ['sudah_dicek', 'selesai'])) {
                    // Sudah dinilai atasan = disabled
                    $assessmentStatuses[$userId] = [
                        'status' => 'done',
                        'badge' => 'Sudah dinilai',
                        'disabled' => true,
                    ];
                } else {
                    // pending, self_done, dll = belum mengisi lengkap (disabled)
                    $assessmentStatuses[$userId] = [
                        'status' => 'pending',
                        'badge' => 'Belum mengisi Self-Assessment',
                        'disabled' => true,
                    ];
                }
            }
        } else {
            // Tidak ada periode atau userIds kosong - semua disabled
            foreach ($userIds as $userId) {
                $assessmentStatuses[$userId] = [
                    'status' => 'no_period',
                    'badge' => 'Belum mengisi Self-Assessment',
                    'disabled' => true,
                ];
            }
        }

        // Encode ke JSON untuk JavaScript
        $employeeData = $employees->map(function ($emp) use ($assessmentStatuses, $user) {
            // Avatar: gunakan inisial nama
            $initials = strtoupper(substr($emp->nama ?? 'U', 0, 2));
            $isCurrentUser = $emp->id_user === $user->id_user;

            return [
                'id_user' => $emp->id_user,
                'nama' => $emp->nama ?? '-',
                'nik' => $emp->nik ?? '-',
                'divisi' => $emp->divisi ?? '-',
                'jabatan' => $emp->jabatan ?? '-',
                'initials' => $initials,
                'is_current_user' => $isCurrentUser,
                'status' => $assessmentStatuses[$emp->id_user]['status'] ?? 'unknown',
                'badge' => $assessmentStatuses[$emp->id_user]['badge'] ?? '-',
                'disabled' => $assessmentStatuses[$emp->id_user]['disabled'] ?? true,
            ];
        });

        return view('kpi.assessment.wizard-step1', [
            'employees' => $employees,
            'employeeData' => $employeeData,
            'wizardData' => $wizardData,
            'hasBawahan' => $hasBawahan,
            'periodValue' => $periodValue,
            'activePeriod' => $activePeriod,
        ]);
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

        // Apply dropdown validation only when the submitted period exists.
        $periodValue = $request->input('period_value');
        if ($periodValue && preg_match('/^\d{4}-\d{2}$/', $periodValue)) {
            [$year, $month] = explode('-', $periodValue);
            $period = KpiPeriod::whereYear('tanggal_mulai', $year)
                ->whereMonth('tanggal_mulai', $month)
                ->first();

            if ($period) {
                $user = auth()->user();
                $selectedUserId = (int) $request->input('user_id');
                $validUserQuery = User::where('id_user', $selectedUserId)
                    ->where('status', true);

                if ($user->role !== 'ADMIN') {
                    $validUserQuery->where(function ($query) use ($user) {
                        $query->where('atasan_id', $user->id_user)
                            ->orWhere('id_user', $user->id_user);
                    });
                }

                if (!$validUserQuery->exists()) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Karyawan yang dipilih tidak valid atau di luar hak akses Anda!');
                }

                $assessment = KpiAssessment::where('user_id', $selectedUserId)
                    ->where('kpi_period_id', $period->id)
                    ->first();

                if (!$assessment) {
                    $error = 'Karyawan ini belum mengisi Self-Assessment.';
                } elseif (in_array($assessment->status, ['sudah_dicek', 'selesai'], true)) {
                    $error = 'Karyawan ini sudah dinilai oleh atasan.';
                } elseif ($assessment->status !== 'menunggu_review') {
                    $error = 'Karyawan ini belum menyelesaikan Self-Assessment.';
                } else {
                    $error = null;
                }

                if ($error) {
                    return redirect()->back()->withInput()->with('error', $error);
                }
            }
        }

        // Legacy mode: redirect ke self-assessment
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
