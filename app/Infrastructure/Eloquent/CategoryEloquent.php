<?php

namespace App\Infrastructure\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CategoryEloquent extends Model
{
    protected $table = 'categories';

    protected $fillable = ['name', 'description'];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function units(): BelongsToMany
    {
        return $this->belongsToMany(UnitEloquent::class, 'category_unit', 'category_id', 'unit_id');
    }
}
