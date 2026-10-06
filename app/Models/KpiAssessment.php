<?php

/**
 * Model KpiAssessment
 * Menyimpan data penilaian KPI per karyawan per periode
 * Status alur baru: pending -> menunggu_review -> sudah_dicek
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KpiAssessment extends Model
{
    protected $table = 'kpi_assessments';
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'kpi_period_id',
        'atasan_id',
        'status',
        'skor_akhir',
    ];

    protected $casts = [
        'skor_akhir' => 'decimal:2',
    ];

    /**
     * Karyawan yang dinilai
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id_user');
    }

    /**
     * Periode KPI
     */
    public function period(): BelongsTo
    {
        return $this->belongsTo(KpiPeriod::class, 'kpi_period_id');
    }

    /**
     * Atasan yang menilai (nullable, bisa di-override manual)
     */
    public function atasan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'atasan_id', 'id_user');
    }

    /**
     * Semua skor penilaian (self + atasan)
     */
    public function scores(): HasMany
    {
        return $this->hasMany(KpiAssessmentScore::class, 'kpi_assessment_id');
    }

    /**
     * Skor dari self assessment (karyawan menilai diri sendiri)
     */
    public function selfScores(): HasMany
    {
        return $this->hasMany(KpiAssessmentScore::class, 'kpi_assessment_id')
                    ->where('penilai_type', 'karyawan');
    }

    /**
     * Skor dari penilaian atasan
     */
    public function atasanScores(): HasMany
    {
        return $this->hasMany(KpiAssessmentScore::class, 'kpi_assessment_id')
                    ->where('penilai_type', 'atasan');
    }

    /** Hitung skor soft skill dan hard skill secara terpisah. */
    public function calculateSkillScores(): array
    {
        $selfScores = $this->selfScores()->with('skill')->get()->keyBy(fn ($score) => $score->skill_type . ':' . $score->skill_id);
        $atasanScores = $this->atasanScores()->with('skill')->get()->keyBy(fn ($score) => $score->skill_type . ':' . $score->skill_id);
        $softScores = collect();
        $hardScores = collect();

        foreach ($selfScores as $key => $selfScore) {
            $atasanScore = $atasanScores->get($key);
            if (!$atasanScore) {
                continue;
            }

            $combinedScore = ($selfScore->skor + $atasanScore->skor) / 2;
            if ($selfScore->skill_type === 'soft_skill') {
                $softScores->push(['score' => $combinedScore]);
            } else {
                $hardScores->push([
                    'score' => $combinedScore,
                    'weight' => $selfScore->skill->weight ?? null,
                ]);
            }
        }

        // Hitung rata-rata soft skill (simple average)
        $avgSoftSkill = $softScores->count() > 0 ? $softScores->avg('score') : null;

        // Hitung weighted average untuk hard skill
        // Weight NULL/tidak ada akan di-exclude dari perhitungan
        $avgHardSkill = $this->calculateWeightedAverageHardSkill($hardScores);

        return [
            'soft_skill' => $avgSoftSkill,
            'hard_skill' => $avgHardSkill,
        ];
    }

    /**
     * Hitung dan update skor akhir
     *
     * Logika perhitungan:
     * - Soft Skill: Simple average (tanpa weight)
     * - Hard Skill: Weighted average (berdasarkan field weight di KpiHardSkill)
     * - Skor akhir: Average dari (avg_soft_skill + avg_hard_skill) / 2
     *
     * Handle division by zero untuk weighted average hard skill
     */
    public function calculateFinalScore(): void
    {
        $skillScores = $this->calculateSkillScores();
        $avgSoftSkill = $skillScores['soft_skill'];
        $avgHardSkill = $skillScores['hard_skill'];

        // Hitung skor akhir: average dari soft & hard skill
        if ($avgSoftSkill !== null && $avgHardSkill !== null) {
            // Average dari soft skill dan hard skill
            $finalScore = ($avgSoftSkill + $avgHardSkill) / 2;
        } elseif ($avgSoftSkill !== null) {
            // Hanya soft skill
            $finalScore = $avgSoftSkill;
        } elseif ($avgHardSkill !== null) {
            // Hanya hard skill
            $finalScore = $avgHardSkill;
        } else {
            // Tidak ada skor sama sekali
            $finalScore = null;
        }

        if ($finalScore !== null) {
            $this->update(['skor_akhir' => round($finalScore, 2)]);
        } else {
            $this->update(['skor_akhir' => null]);
        }
    }

    public function grade(): string
    {
        if ($this->skor_akhir === null) {
            return '-';
        }

        $score = (float) $this->skor_akhir;

        return match (true) {
            $score >= 90 => 'A',
            $score >= 75 => 'B',
            $score >= 60 => 'C',
            $score >= 45 => 'D',
            default => 'E',
        };
    }

    /**
     * Hitung weighted average untuk hard skill
     *
     * Exclude item yang weight-nya NULL/kosong
     * Handle division by zero jika semua item memiliki weight NULL
     */
    private function calculateWeightedAverageHardSkill($hardScores): ?float
    {
        if ($hardScores->isEmpty()) {
            return null;
        }

        $weightedSum = 0;
        $totalWeight = 0;

        foreach ($hardScores as $score) {
            // Ambil weight dari relasi skill
            $weight = $score['weight'];

            // Skip jika weight NULL atau tidak ada
            if ($weight === null || $weight <= 0) {
                continue;
            }

            $weightedSum += $score['score'] * $weight;
            $totalWeight += $weight;
        }

        // Handle division by zero
        if ($totalWeight <= 0) {
            // Jika semua item memiliki weight NULL/0, return null
            // Log untuk tracking
            \Log::warning('KpiAssessment: Semua item hard skill memiliki weight NULL/0, skor hard skill di-set NULL', [
                'assessment_id' => $this->id,
                'user_id' => $this->user_id,
            ]);
            return null;
        }

        return $weightedSum / $totalWeight;
    }

    /**
     * Update status berdasarkan kelengkapan penilaian
     */
    public function updateStatus(): void
    {
        $hasSelfScore = $this->selfScores()->exists();
        $hasAtasanScore = $this->atasanScores()->exists();

        if ($hasSelfScore && $hasAtasanScore) {
            $this->update(['status' => 'sudah_dicek']);
            $this->calculateFinalScore();
        } elseif ($hasSelfScore) {
            $this->update(['status' => 'menunggu_review']);
        }
    }

    /**
     * Cek apakah self assessment sudah selesai
     */
    public function isSelfDone(): bool
    {
        return in_array($this->status, ['self_done', 'atasan_done', 'selesai', 'menunggu_review', 'sudah_dicek']);
    }

    /**
     * Cek apakah penilaian atasan sudah selesai
     */
    public function isAtasanDone(): bool
    {
        return in_array($this->status, ['atasan_done', 'selesai', 'sudah_dicek']);
    }
}
