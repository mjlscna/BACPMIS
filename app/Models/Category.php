<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['category', 'slug', 'is_active'];

    public function procurements()
    {
        return $this->hasMany(Procurement::class);
    }
}
