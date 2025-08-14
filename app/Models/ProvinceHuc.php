<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProvinceHuc extends Model
{
    use HasFactory;

    protected $fillable = ['province_huc', 'slug', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function procurements()
    {
        return $this->hasMany(Procurement::class);
    }
}
