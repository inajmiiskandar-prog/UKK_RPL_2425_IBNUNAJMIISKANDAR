<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paket extends Model
{
    protected $table = 'paket';
    protected $primaryKey = 'id_paket';

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $fillable = ['kode_paket', 'nama_paket', 'kecepatan', 'harga', 'keterangan'];

    protected function casts(): array
    {
        return ['harga' => 'decimal:2'];
    }

    public function fab()
    {
        return $this->hasMany(Fab::class, 'id_paket', 'id_paket');
    }
}
