<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EndUser extends Model
{
    use HasFactory;

    protected $fillable = ['endusers', 'slug', 'is_active'];

    public function procurements()
    {
        return $this->hasMany(Procurement::class);
    }

}
