<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PortPon extends Model
{
    protected $table = 'portpon';
    protected $primaryKey = 'id_port';

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $fillable = ['nomor_port', 'tipe_kartu', 'status', 'id_olt', 'id_odp'];

    public function olt()
    {
        return $this->belongsTo(Olt::class, 'id_olt', 'id_olt');
    }

    public function odp()
    {
        return $this->belongsTo(Odp::class, 'id_odp', 'id_odp');
    }
}
