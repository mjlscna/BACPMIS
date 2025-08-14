<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['category', 'category_type_id', 'bac_type_id', 'slug', 'is_active'];
    public function categoryType()
    {
        return $this->belongsTo(CategoryType::class, 'category_type_id');
    }

    public function bacType()
    {
        return $this->belongsTo(BacType::class, 'bac_type_id');
    }

    public function procurements()
    {
        return $this->hasMany(Procurement::class);
    }


}
