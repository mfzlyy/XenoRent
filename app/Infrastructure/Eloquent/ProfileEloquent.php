<?php

namespace App\Infrastructure\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileEloquent extends Model
{
    protected $table = 'profiles';

    protected $fillable = ['user_id', 'phone', 'address'];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(UserEloquent::class, 'user_id');
    }
}
