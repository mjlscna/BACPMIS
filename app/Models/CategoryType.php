<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryType extends Model
{
    use HasFactory;
    protected $fillable = ['category_type', 'slug', 'is_active'];

    public function category()
    {
        return $this->hasMany(Category::class);
    }
    public function procurements()
    {
        return $this->hasMany(Procurement::class);
    }
}
