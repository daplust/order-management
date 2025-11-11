<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Food extends Model
{
    protected $table = 'food';
    
    protected $fillable = [
        'name',
        'description',
        'price',
        'type',
        'image',
        'is_available'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_available' => 'boolean'
    ];
}