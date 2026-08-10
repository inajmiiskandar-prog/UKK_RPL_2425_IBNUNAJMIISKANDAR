<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Menyimpan daftar teknisi TAMBAHAN yang ikut mengerjakan
 * satu BAA -- di luar teknisi utama (baa.id_user).
 * Satu BAA bisa punya banyak baris teknisi tambahan.
 */
class BaaTeknisi extends Model
{
    protected $table = 'baa_teknisi';
    protected $primaryKey = 'id_baa_teknisi';

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = null; // tabel ini tidak punya updatedAt

    public $timestamps = false; // dikelola manual karena cuma createdAt

    protected $fillable = ['id_baa', 'id_user'];

    public function baa()
    {
        return $this->belongsTo(Baa::class, 'id_baa', 'id_baa');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}
