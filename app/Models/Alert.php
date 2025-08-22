<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    protected $fillable = [
        'user_id', 'type', 'title', 'message', 'meta', 'read_at',
    ];

    protected $casts = [
        'meta'    => 'array',
        'read_at' => 'datetime',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
