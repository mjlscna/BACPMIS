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
                'modeOfProcurement',
                'procurements', // Correct: Loads $mode->procurements for the 'else' block
                'prItems'       // Corrected: Loads $mode->prItems for the 'if' block
            ]);

        // Update search logic
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('ref_number', 'like', '%' . $this->search . '%')
                    ->orWhereHas('modeOfProcurement', function ($subQ) {
                        $subQ->where('modeofprocurements', 'like', '%' . $this->search . '%');
                    })
                    // Corrected: Search 'perLot' PRs via 'procurements' relationship
                    ->orWhereHas('procurements', function ($subQ) {
                        // Assumes 'procurements' table has 'pr_number' and 'project_title'
                        // Changed from 'procurement_program_project' to 'project_title' to match blade
                        $subQ->where('pr_number', 'like', '%' . $this->search . '%')
                            ->orWhere('project_title', 'like', '%' . $this->search . '%');
                    })
                    // Corrected: Search 'perItem' via 'prItems' relationship
                    ->orWhereHas('prItems', function ($subQ) {
                        // Assumes 'pr_items' table has 'item_name'
                        // Changed from 'description' to 'item_name' to match blade
                        $subQ->where('item_name', 'like', '%' . $this->search . '%')
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
