<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BACType extends Model
{
    use HasFactory;
    protected $table = 'bac_types';

    protected $fillable = [
        'abbreviation',
        'name',
        'slug',
        'is_active',
    ];
}
