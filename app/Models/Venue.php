<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venue extends Model
{
    use HasFactory;
    protected $fillable = ['venue', 'slug', 'is_active'];

    public function procurements()
    {
        return $this->hasMany(Procurement::class);
    }

}
