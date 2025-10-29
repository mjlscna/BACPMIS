<?php

namespace App\Livewire\ModeOfProcurement;

use App\Models\MopGroup;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use App\Models\MopItem; // Unused, but kept for context
use App\Models\MopLot; // Unused, but kept for context
use Livewire\WithPagination;

class ModeOfProcurementIndexPage extends Component
{
    use WithPagination;
    protected $paginationTheme = 'tailwind';

    public $search = '';

    public function render()
    {
        $query = MopGroup::query()
            ->with([
                // **UPDATED: Load the nested relationships to get the true mode history**
                'procurements.mops.modeDetails', // For 'perLot'
                'prItems.mops.modeDetails',      // For 'perItem'
            ]);

        // Update search logic
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('ref_number', 'like', '%' . $this->search . '%')

                    // **UPDATED: Search the true nested relationship for the mode name**
                    ->orWhereHas('procurements.mops.modeDetails', function ($subQ) {
                        $subQ->where('modeofprocurements', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('prItems.mops.modeDetails', function ($subQ) {
                        $subQ->where('modeofprocurements', 'like', '%' . $this->search . '%');
                    })

                    // Corrected: Search 'perLot' PRs via 'procurements' relationship
                    ->orWhereHas('procurements', function ($subQ) {
                        // **NOTE:** Using 'procurement_program_project' to match your CreatePage logic
                        $subQ->where('pr_number', 'like', '%' . $this->search . '%')
                            ->orWhere('procurement_program_project', 'like', '%' . $this->search . '%');
                    })

                    // Corrected: Search 'perItem' via 'prItems' relationship
                    ->orWhereHas('prItems', function ($subQ) {
                        // **NOTE:** Using 'description' to match your CreatePage logic
                        $subQ->where('description', 'like', '%' . $this . search . '%')
                            ->orWhereHas('procurement', function ($subSubQ) { // Search 'perItem' PRs
                            $subSubQ->where('pr_number', 'like', '%' . $this->search . '%');
                        });
                    });
            });
        }

        $modes = $query->latest('created_at')->paginate(10); // $modes is now a collection of MopGroup

        return view('livewire.mode-of-procurement.mode-of-procurement-index-page', [
            'modes' => $modes,
        ]);
    }
}
