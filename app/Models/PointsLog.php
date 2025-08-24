<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointsLog extends Model
{
    protected $fillable = ['user_id','points','reason','meta'];
    protected $casts = ['meta'=>'array'];
    public function user(){ return $this->belongsTo(User::class); }
}
