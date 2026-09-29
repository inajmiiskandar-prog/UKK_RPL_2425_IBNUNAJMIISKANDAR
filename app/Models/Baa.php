<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Baa extends Model
{
    protected $table = 'baa';
    protected $primaryKey = 'id_baa';

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $casts = [
        'tanggal_instalasi' => 'datetime',
    ];

    protected $fillable = [
        'kode_baa', 'tanggal_instalasi', 'status', 'catatan', 'foto_instalasi',
        'id_fab', 'id_user', 'id_olt', 'id_ont', 'id_odp',
        'ping_ms', 'port_odp', 'port_olt', 'rx_power_dbm',
        'speed_download', 'speed_upload', 'tx_power_dbm',
    ];

    public function fab()
    {
        return $this->belongsTo(Fab::class, 'id_fab', 'id_fab');
    }

    // Teknisi utama
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    // Alias untuk teknisi utama (untuk kompatibilitas controller)
    public function teknisi()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function olt()
    {
        return $this->belongsTo(Olt::class, 'id_olt', 'id_olt');
    }

    public function ont()
    {
        return $this->belongsTo(Ont::class, 'id_ont', 'id_ont');
    }

    public function odp()
    {
        return $this->belongsTo(Odp::class, 'id_odp', 'id_odp');
    }

    // Alias untuk details
    public function details()
    {
        return $this->hasMany(BaaDetail::class, 'id_baa', 'id_baa');
    }

    public function baadetail()
    {
        return $this->hasMany(BaaDetail::class, 'id_baa', 'id_baa');
    }

    // Teknisi tambahan (selain teknisi utama di atas)
    public function teknisiTambahan()
    {
        return $this->hasMany(BaaTeknisi::class, 'id_baa', 'id_baa');
    }
}
