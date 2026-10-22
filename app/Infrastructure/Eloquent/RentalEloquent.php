<?php

namespace App\Infrastructure\Eloquent;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentalEloquent extends Model
{
    protected $table = 'rentals';

    protected $fillable = ['user_id', 'unit_id', 'rented_at', 'due_at', 'returned_at', 'fine_amount', 'status'];

    protected $casts = [
        'rented_at' => 'date',
        'due_at' => 'date',
        'returned_at' => 'date',
        'fine_amount' => 'integer',
        'status' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(UserEloquent::class, 'user_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(UnitEloquent::class, 'unit_id')->withTrashed();
    }

    protected function isActive(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->status === 'disewa',
        );
    }

    protected function isReturned(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->status === 'dikembalikan',
        );
    }
}
