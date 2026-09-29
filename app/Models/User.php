<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'id_user';

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $fillable = [
        'kode_user', 'kode_karyawan', 'nama', 'name', 'username', 'password', 'jkl', 'foto',
        'role', 'no_hp', 'email', 'status',
        // Kolom baru untuk KPI employee
        'nik', 'divisi', 'jabatan', 'atasan_id', 'alamat', 'tanggal_masuk', 'no_telp', 'join_date', 'employee_status',
    ];

    protected $hidden = ['password'];

    public function getNameAttribute(): ?string
    {
        return $this->attributes['nama'] ?? null;
    }

    public function setNameAttribute($value): void
    {
        $this->attributes['nama'] = $value;
    }

    public function getIdAttribute(): ?int
    {
        return $this->attributes['id_user'] ?? null;
    }

    protected $casts = [
        'password' => 'hashed',
        'status' => 'boolean',
    ];

    // Fab yang user ini jadi sales-nya
    public function fabSales()
    {
        return $this->hasMany(Fab::class, 'id_user', 'id_user');
    }

    public function fabsSales()
    {
        return $this->hasMany(Fab::class, 'id_user', 'id_user');
    }

    // Fab yang user ini yang menginput datanya
    public function fabPenginput()
    {
        return $this->hasMany(Fab::class, 'id_penginput', 'id_user');
    }

    // BAA sebagai teknisi utama
    public function baa()
    {
        return $this->hasMany(Baa::class, 'id_user', 'id_user');
    }

    public function baas()
    {
        return $this->hasMany(Baa::class, 'id_user', 'id_user');
    }

    // BAA sebagai teknisi tambahan
    public function baaTeknisi()
    {
        return $this->hasMany(BaaTeknisi::class, 'id_user', 'id_user');
    }

    // =====================================================
    // Relasi KPI - Atasan & Bawahan
    // =====================================================

    /**
     * Atasan langsung dari karyawan ini
     * foreign key: users.atasan_id -> users.id_user
     */
    public function atasan()
    {
        return $this->belongsTo(User::class, 'atasan_id', 'id_user');
    }

    /**
     * Bawahan langsung dari karyawan ini
     * foreign key: users.atasan_id -> users.id_user
     */
    public function bawahan()
    {
        return $this->hasMany(User::class, 'atasan_id', 'id_user');
    }

    /**
     * Penilaian KPI dimana user ini yang dinilai
     */
    public function kpiAssessments()
    {
        return $this->hasMany(KpiAssessment::class, 'user_id', 'id_user');
    }

    /**
     * Penilaian KPI dimana user ini adalah atasan (yang menilai bawahan)
     */
    public function kpiAssessmentsAsAtasan()
    {
        return $this->hasMany(KpiAssessment::class, 'atasan_id', 'id_user');
    }
}
