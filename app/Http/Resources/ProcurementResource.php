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
            'division' => new DivisionResource($this->whenLoaded('division')),
            'cluster_committees_id' => $this->cluster_committees_id,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'category_type' => new CategoryTypeResource($this->whenLoaded('categoryType')),
            'bac_type' => new BacTypeResource($this->whenLoaded('bacType')),
            'venue_specific' => new VenueSpecificResource($this->whenLoaded('venueSpecific')),
            'venue_province_huc' => new ProvinceHucResource($this->whenLoaded('venueProvincesHUC')),
            'category_venue' => $this->category_venue,
            'approved_ppmp' => $this->approved_ppmp,
            'app_updated' => $this->app_updated,
            'immediate_date_needed' => $this->immediate_date_needed,
            'date_needed' => $this->date_needed,
            'end_user' => new EndUserResource($this->whenLoaded('endUser')),
            'early_procurement' => $this->early_procurement,
            'fund_source' => new FundSourceResource($this->whenLoaded('fundSource')),
            'expense_class' => $this->expense_class,
            'abc' => (float) $this->abc,
            'abc_50k' => $this->abc_50k
        ];
    }
}
