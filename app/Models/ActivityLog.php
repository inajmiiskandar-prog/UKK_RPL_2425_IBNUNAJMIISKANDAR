<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ActivityLog extends Model
{
    protected $table = 'activity_logs';
    protected $primaryKey = 'id_log';
    public $timestamps = false;

    protected $fillable = ['type', 'description', 'id_user', 'createdAt'];

    protected $casts = [
        'createdAt' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function getCreatedAtAttribute($value)
    {
        return $value ? Carbon::parse($value) : null;
    }

    public static function log(string $type, string $description, ?int $userId = null): self
    {
        return static::create([
            'type' => $type,
            'description' => $description,
            'id_user' => $userId ?? auth()->user()->id_user,
            'createdAt' => now(),
        ]);
    }
}
