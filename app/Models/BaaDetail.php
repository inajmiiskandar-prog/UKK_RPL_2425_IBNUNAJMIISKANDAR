<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaaDetail extends Model
{
    protected $table = 'baadetail';
    protected $primaryKey = 'id_baa_detail';

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $fillable = ['id_baa', 'id_material', 'jumlah', 'keterangan'];

    public function baa()
    {
        return $this->belongsTo(Baa::class, 'id_baa', 'id_baa');
    }

    public function material()
    {
        return $this->belongsTo(Material::class, 'id_material', 'id_material');
    }
}
