<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProcurementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'procID' => $this->procID,
            'pr_number' => $this->pr_number,
            'procurement_type' => $this->procurement_type,
            'procurement_program_project' => $this->procurement_program_project,
            'date_receipt' => $this->date_receipt,
            'dtrack_no' => $this->dtrack_no,
            'unicode' => $this->unicode,
            'divisions_id' => $this->divisions_id,
            'cluster_committees_id' => $this->cluster_committees_id,
            'category_id' => $this->category_id,
            'category_type_id' => $this->category_type_id,
            'bac_type_id' => $this->bac_type_id,
            'venue_specific_id' => $this->venue_specific_id,
            'venue_province_huc_id' => $this->venue_province_huc_id,
            'category_venue' => $this->category_venue,
            'approved_ppmp' => $this->approved_ppmp,
            'app_updated' => $this->app_updated,
            'immediate_date_needed' => $this->immediate_date_needed,
            'date_needed' => $this->date_needed,
            'end_users_id' => $this->end_users_id,
            'early_procurement' => $this->early_procurement,
            'fund_source_id' => $this->fund_source_id,
            'expense_class' => $this->expense_class,
            'abc' => (float) $this->abc,
            'abc_50k' => $this->abc_50k
        ];
    }
}
