<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fab extends Model
{
    protected $table = 'fab';
    protected $primaryKey = 'id_fab';

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $fillable = [
        'kode_fab', 'nama_pelanggan', 'nik', 'foto', 'no_hp', 'alamat',
        'latitude', 'longitude', 'status', 'id_area', 'id_paket',
        'id_user', 'id_penginput',
    ];

    public function area()
    {
        return $this->belongsTo(Area::class, 'id_area', 'id_area');
    }

    public function paket()
    {
        return $this->belongsTo(Paket::class, 'id_paket', 'id_paket');
    }

    // Sales yang menangani FAB ini
    public function sales()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    // User yang menginput data FAB ini
    public function penginput()
    {
        return $this->belongsTo(User::class, 'id_penginput', 'id_user');
    }

    public function baa()
    {
        return $this->hasMany(Baa::class, 'id_fab', 'id_fab');
    }
}
