<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pop extends Model
{
    protected $table = 'pop';
    protected $primaryKey = 'id_pop';

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $fillable = ['kode_pop', 'nama_pop', 'alamat', 'latitude', 'longitude', 'id_area'];

    public function area()
    {
        return $this->belongsTo(Area::class, 'id_area', 'id_area');
    }

    public function olt()
    {
        return $this->hasMany(Olt::class, 'id_pop', 'id_pop');
    }

    public function olts()
    {
        return $this->hasMany(Olt::class, 'id_pop', 'id_pop');
    }

    public function ont()
    {
        return $this->hasMany(Ont::class, 'id_pop', 'id_pop');
    }

    public function onts()
    {
        return $this->hasMany(Ont::class, 'id_pop', 'id_pop');
    }
}
