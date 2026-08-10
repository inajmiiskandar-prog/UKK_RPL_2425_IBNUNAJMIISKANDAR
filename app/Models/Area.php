<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $table = 'area';
    protected $primaryKey = 'id_area';

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $fillable = ['kode_area', 'nama_area', 'keterangan'];

    public function pop()
    {
        return $this->hasMany(Pop::class, 'id_area', 'id_area');
    }

    public function fab()
    {
        return $this->hasMany(Fab::class, 'id_area', 'id_area');
    }

    // Alias for plural access (convention for hasMany)
    public function pops()
    {
        return $this->hasMany(Pop::class, 'id_area', 'id_area');
    }

    public function fabs()
    {
        return $this->hasMany(Fab::class, 'id_area', 'id_area');
    }
}
