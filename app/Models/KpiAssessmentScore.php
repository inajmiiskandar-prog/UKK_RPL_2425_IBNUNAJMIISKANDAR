<?php

/**
 * Model KpiAssessmentScore
 * Menyimpan skor per indikator per penilai
 * Skill_type: soft_skill atau hard_skill
 * Penilai_type: karyawan (self assessment) atau atasan (penilaian atasan)
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KpiAssessmentScore extends Model
{
    protected $table = 'kpi_assessment_scores';
    protected $primaryKey = 'id';

    protected $fillable = [
        'kpi_assessment_id',
        'skill_type',
        'skill_id',
        'penilai_type',
        'skor',
        'catatan',
    ];

    protected $casts = [
        'skor' => 'integer',
    ];

    /**
     * Assessment (orang tua) dari skor ini
     */
    public function assessment(): BelongsTo
    {
        return $this->belongsTo(KpiAssessment::class, 'kpi_assessment_id');
    }

    /**
     * Relasi polymorphic untuk skill (soft skill atau hard skill)
     * Bergantung pada skill_type
     */
    public function skill()
    {
        if ($this->skill_type === 'soft_skill') {
            return $this->belongsTo(KpiSoftSkill::class, 'skill_id');
        }
        return $this->belongsTo(KpiHardSkill::class, 'skill_id');
    }

    /**
     * Scope untuk soft skill saja
     */
    public function scopeSoftSkill($query)
    {
        return $query->where('skill_type', 'soft_skill');
    }

    /**
     * Scope untuk hard skill saja
     */
    public function scopeHardSkill($query)
    {
        return $query->where('skill_type', 'hard_skill');
    }

    /**
     * Scope untuk self assessment (karyawan)
     */
    public function scopeSelfAssessment($query)
    {
        return $query->where('penilai_type', 'karyawan');
    }

    /**
     * Scope untuk penilaian atasan
     */
    public function scopeByAtasan($query)
    {
        return $query->where('penilai_type', 'atasan');
    }
}
