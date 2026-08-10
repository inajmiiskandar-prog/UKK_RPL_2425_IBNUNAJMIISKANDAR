<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'id_user';

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $fillable = [
        'kode_user', 'nama', 'username', 'password', 'jkl', 'foto',
        'role', 'no_hp', 'email', 'status',
    ];

    protected $hidden = ['password'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'status' => 'boolean',
        ];
    }

    // Fab yang user ini jadi sales-nya
    public function fabSales()
    {
        return $this->hasMany(Fab::class, 'id_user', 'id_user');
    }

    // Fab yang user ini yang menginput datanya
    public function fabPenginput()
    {
        return $this->hasMany(Fab::class, 'id_penginput', 'id_user');
    }

    // BAA sebagai teknisi utama
    public function baa()
    {
        return $this->hasMany(Baa::class, 'id_user', 'id_user');
    }

    // BAA sebagai teknisi tambahan
    public function baaTeknisi()
    {
        return $this->hasMany(BaaTeknisi::class, 'id_user', 'id_user');
    }
}
