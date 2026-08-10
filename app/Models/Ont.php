<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ont extends Model
{
    protected $table = 'ont';
    protected $primaryKey = 'id_ont';

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $fillable = ['serial_number', 'pelanggan', 'status', 'id_pop', 'id_odp'];

    public function pop()
    {
        return $this->belongsTo(Pop::class, 'id_pop', 'id_pop');
    }

    public function odp()
    {
        return $this->belongsTo(Odp::class, 'id_odp', 'id_odp');
    }
}
