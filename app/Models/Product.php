<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'description',
    ];

    /**
     * This gets all the users who have this product in their wishlist.
     * @return BelongsToMany
     */
    public function wishedByUser(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'wishlist_items')
            ->withTimestamps();

    }
}
