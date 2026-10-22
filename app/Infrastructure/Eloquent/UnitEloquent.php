<?php

namespace App\Infrastructure\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class UnitEloquent extends Model
{
    use SoftDeletes;

    protected $table = 'units';

    protected $fillable = ['code', 'name', 'description', 'status'];

    protected $casts = [
        'status' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function rentals(): HasMany
    {
        return $this->hasMany(RentalEloquent::class, 'unit_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(CategoryEloquent::class, 'category_unit', 'unit_id', 'category_id');
    }
}
