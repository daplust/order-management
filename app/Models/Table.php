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

    protected $appends = ['table_number'];

    public function getTableNumberAttribute()
    {
        return $this->number;
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function currentOrder()
    {
        return $this->hasOne(Order::class)->where('status', 'open')->latest();
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    
    public function markAsUnavailable()
    {
        $this->update(['is_available' => false]);
    }

    
    public function markAsAvailable()
    {
        $this->update(['is_available' => true]);
    }
}
