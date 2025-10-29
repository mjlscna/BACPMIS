<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MopGroup extends Model
{
    use HasFactory;
    protected $fillable = [
        'ref_number',
        'status',
        'procurable_type',
    ];

    /**
     * CORRECTED RELATIONSHIP
     *
     * We must specify the parent key ('id' on MopGroup) and
     * the related key ('procID' on Procurement) as the 5th and 6th arguments.
     */
    public function procurements()
    {
        return $this->belongsToMany(
            Procurement::class,
            'mop_group_procurement', // pivot table
            'mop_group_id',          // foreign pivot key
            'procurement_id',        // related pivot key
            'id',                    // parent key (on MopGroup)
            'procID'                 // related key (on Procurement)
        );
    }

    /**
     * CORRECTED RELATIONSHIP
     *
     * We must specify the parent key ('id' on MopGroup) and
     * the related key ('prItemID' on PrItem) as the 5th and 6th arguments.
     */
    public function prItems()
    {
        return $this->belongsToMany(
            PrItem::class,
            'mop_group_pr_item',     // pivot table
            'mop_group_id',          // foreign pivot key
            'pr_item_id',            // related pivot key
            'id',                    // parent key (on MopGroup)
            'prItemID'               // related key (on PrItem)
        );
    }
    public function getRouteKeyName()
    {
        return 'ref_number';
    }
}
