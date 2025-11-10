<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Table extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'number',
        'capacity',
        'is_available',
        'description'
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'capacity' => 'integer'
    ];

    /**
     * Get the orders for the table
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Scope a query to only include available tables
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    /**
     * Mark the table as unavailable
     */
    public function markAsUnavailable()
    {
        $this->update(['is_available' => false]);
    }

    /**
     * Mark the table as available
     */
    public function markAsAvailable()
    {
        $this->update(['is_available' => true]);
    }
}
