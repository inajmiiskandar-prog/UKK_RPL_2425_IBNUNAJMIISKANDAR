<?php

/**
 * Model KpiSoftSkill
 * Menyimpan daftar indikator penilaian soft skill
 * Contoh: Komunikasi, Kerja Tim, Disiplin, dll.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KpiSoftSkill extends Model
{
    protected $table = 'kpi_soft_skills';
    protected $primaryKey = 'id';

    protected $fillable = [
        'kode',
        'nama_indikator',
        'deskripsi',
    ];

    /**
     * Relasi ke skor penilaian yang menggunakan soft skill ini
     */
    public function assessmentScores(): HasMany
    {
        return $this->hasMany(KpiAssessmentScore::class, 'skill_id')
                    ->where('skill_type', 'soft_skill');
    }
}
