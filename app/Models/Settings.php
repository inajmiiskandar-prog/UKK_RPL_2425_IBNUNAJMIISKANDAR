<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    protected $table = 'settings';
    protected $primaryKey = 'id_setting';

    const CREATED_AT = null;
    const UPDATED_AT = 'updatedAt';

    public $timestamps = false; // dikelola manual karena cuma updatedAt

    protected $fillable = ['key', 'value'];

    /**
     * Helper ambil satu setting berdasarkan key.
     * Contoh: Settings::get('app_name', 'Aplikasi Management System Pelanggan')
     */
    public static function get(string $key, ?string $default = null): ?string
    {
        return static::where('key', $key)->value('value') ?? $default;
    }
}
