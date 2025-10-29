<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Mop extends Model
{
    use HasFactory;

    protected $fillable = [
        'procurable_type', // Automatically set by Laravel (e.g., App\Models\Procurement)
        'procurable_id',   // Automatically set by Laravel (e.g., Procurement->procID or PrItem->prItemID)
        'mode_of_procurement_id',
        'mode_order',
        'uid', // Unique identifier for this specific MOP instance
    ];

    /**
     * Get the parent procurable model (Procurement or PrItem).
     */
    public function procurable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the details of the Mode of Procurement (e.g., Bidding, SVP).
     */
    public function modeDetails(): BelongsTo
    {
        return $this->belongsTo(ModeOfProcurement::class, 'mode_of_procurement_id');
    }

    /**
     * Get the bid schedules associated with this MOP instance (for Bidding, etc.).
     * NOTE: Assumes bid_schedules table has a 'mop_id' foreign key. Add migration if needed.
     */
    public function bidSchedules(): HasMany
    {
        // Adjust foreign key if necessary
        return $this->hasMany(BidSchedule::class, 'mop_id');
    }

    /**
     * Get the NTF bid schedules associated with this MOP instance (for Negotiated Procurement).
     * NOTE: Assumes ntf_bid_schedules table has a 'mop_id' foreign key. Add migration if needed.
     */
    public function ntfBidSchedules(): HasMany
    {
        // Adjust foreign key if necessary
        return $this->hasMany(NtfBidSchedule::class, 'mop_id');
    }

    /**
     * Get the SVP details associated with this MOP instance (for SVP).
     * NOTE: Assumes pr_svps table has a 'mop_id' foreign key. Add migration if needed.
     */
    public function svpDetails(): HasMany // Should likely be HasOne if only one SVP entry per Mop
    {
        // Adjust foreign key if necessary
        return $this->hasMany(PrSvp::class, 'mop_id');
    }

    // Add similar relationships for other schedule types if needed
}
