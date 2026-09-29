<?php

/**
 * Model KpiPeriod
 * Menyimpan periode penilaian KPI
 * Contoh: "Januari 2024", "Q1 2024"
 * Status: draft, aktif, selesai
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KpiPeriod extends Model
{
    protected $table = 'kpi_periods';
    protected $primaryKey = 'id';

    protected $fillable = [
        'nama',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    /**
     * Relasi ke assessment (penilaian) dalam periode ini
     */
    public function assessments(): HasMany
    {
        return $this->hasMany(KpiAssessment::class, 'kpi_period_id');
    }

    /**
     * Scope untuk mendapatkan periode yang sedang aktif
     */
    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    /**
     * Cek apakah periode ini masih bisa dinilai
     */
    public function isEditable(): bool
    {
        return $this->status === 'aktif';
    }
}
