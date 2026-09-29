<?php

/**
 * Model KpiHardSkill
 * Menyimpan daftar indikator penilaian hard skill per divisi/jabatan
 * Contoh: Penguasaan Teknis, Problem Solving, dll.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KpiHardSkill extends Model
{
    protected $table = 'kpi_hard_skills';
    protected $primaryKey = 'id';

    protected $fillable = [
        'kode',
        'nama_indikator',  // Kolom lama - tetap ada untuk backward compatibility
        'deskripsi',        // Kolom lama - tetap ada untuk backward compatibility
        // Kolom baru
        'divisi',
        'jabatan',
        'responsibilities',
        'kpi',              // Nama indikator KPI (pengganti nama_indikator)
        'target',
        'weight',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
    ];

    /**
     * Relasi ke skor penilaian yang menggunakan hard skill ini
     */
    public function assessmentScores(): HasMany
    {
        return $this->hasMany(KpiAssessmentScore::class, 'skill_id')
                    ->where('skill_type', 'hard_skill');
    }

    /**
     * Get nama indikator (prioritas ke kolom 'kpi' yang baru)
     * Fallback ke 'nama_indikator' untuk data lama
     */
    public function getNamaIndikatorAttribute(): ?string
    {
        return $this->kpi ?? $this->attributes['nama_indikator'] ?? null;
    }

    /**
     * Scope untuk filter berdasarkan divisi
     */
    public function scopeByDivisi($query, string $divisi)
    {
        return $query->where('divisi', $divisi);
    }

    /**
     * Scope untuk filter berdasarkan jabatan
     */
    public function scopeByJabatan($query, string $jabatan)
    {
        return $query->where('jabatan', $jabatan);
    }

    /**
     * Scope untuk filter berdasarkan divisi DAN jabatan
     */
    public function scopeForPosition($query, ?string $divisi, ?string $jabatan)
    {
        if ($divisi) {
            $query->where('divisi', $divisi);
        }
        if ($jabatan) {
            $query->where('jabatan', $jabatan);
        }
        return $query;
    }
}
