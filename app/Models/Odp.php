<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Odp extends Model
{
    protected $table = 'odp';
    protected $primaryKey = 'id_odp';

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $fillable = [
        'kode_odp', 'nama_odp', 'alamat', 'latitude', 'longitude',
        'jumlah_port', 'stok_port', 'id_olt',
    ];

    public function olt()
    {
        return $this->belongsTo(Olt::class, 'id_olt', 'id_olt');
    }

    public function ont()
    {
        return $this->hasMany(Ont::class, 'id_odp', 'id_odp');
    }

    public function onts()
    {
        return $this->hasMany(Ont::class, 'id_odp', 'id_odp');
    }

    public function portpon()
    {
        return $this->hasMany(PortPon::class, 'id_odp', 'id_odp');
    }

    public function portpons()
    {
        return $this->hasMany(PortPon::class, 'id_odp', 'id_odp');
    }
}
