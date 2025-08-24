<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'amount',
        'type',       // 'income' | 'expense'
        'date',
        'description',
        // 'title' -> uklonjeno
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    // Relacije
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    
    public function scopeForAuthUser($query)
    {
        return $query->when(auth()->check(), fn ($q) => $q->where('user_id', auth()->id()));
    }
}
