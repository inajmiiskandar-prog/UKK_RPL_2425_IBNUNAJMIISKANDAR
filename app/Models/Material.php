<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    protected $table = 'material';
    protected $primaryKey = 'id_material';

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $fillable = [
        'kode_material', 'nama_material', 'stok', 'minimal_stok',
        'satuan', 'harga', 'kondisi', 'keterangan',
    ];

    protected function casts(): array
    {
        return ['harga' => 'decimal:2'];
    }

    public function baadetail()
    {
        return $this->hasMany(BaaDetail::class, 'id_material', 'id_material');
    }
}
