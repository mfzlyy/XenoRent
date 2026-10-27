<?php

namespace App\Infrastructure\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserEloquent extends Model
{
    use SoftDeletes;

    protected $table = 'users';

    protected $fillable = ['name', 'email', 'password', 'role', 'is_approved'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'role' => 'string',
        'is_approved' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function profile(): HasOne
    {
        return $this->hasOne(ProfileEloquent::class, 'user_id');
    }

    public function rentals(): HasMany
    {
        return $this->hasMany(RentalEloquent::class, 'user_id');
    }
}
