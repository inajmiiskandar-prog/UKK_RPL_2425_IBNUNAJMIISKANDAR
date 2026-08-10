<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Olt extends Model
{
    protected $table = 'olt';
    protected $primaryKey = 'id_olt';

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $fillable = [
        'kode_olt', 'nama_olt', 'lokasi', 'latitude', 'longitude',
        'id_pop', 'ip_olt', 'username_olt', 'password_olt', 'foto_olt',
    ];

    public function pop()
    {
        return $this->belongsTo(Pop::class, 'id_pop', 'id_pop');
    }

    public function odp()
    {
        return $this->hasMany(Odp::class, 'id_olt', 'id_olt');
    }

    public function odps()
    {
        return $this->hasMany(Odp::class, 'id_olt', 'id_olt');
    }

    public function portpon()
    {
        return $this->hasMany(PortPon::class, 'id_olt', 'id_olt');
    }

    public function portpons()
    {
        return $this->hasMany(PortPon::class, 'id_olt', 'id_olt');
    }
}
