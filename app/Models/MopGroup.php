<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MopGroup extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function procurements()
    {
        // morphedByMany(RelatedModel, name, table, foreignKey, relatedKey, parentKey, relatedModelKey)
        return $this->morphedByMany(
            Procurement::class,
            'biddable',
            'mop_group_items',
            'mop_group_id',
            'biddable_id',
            'id',
            'procID'
        );
    }

    public function prItems()
    {
        return $this->morphedByMany(
            PrItem::class,
            'biddable',
            'mop_group_items',
            'mop_group_id',
            'biddable_id',
            'id',
            'prItemID'
        );
    }
}
