<?php

namespace App\Livewire\ModeOfProcurement;

use App\Models\MopGroup;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use App\Models\MopItem; // This is unused in this component but kept for context
use App\Models\MopLot;
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
                'procurements',
                'prItems.procurement'
            ]);

        // Update search logic
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('ref_number', 'like', '%' . $this->search . '%')
                    ->orWhereHas('modeOfProcurement', function ($subQ) {
                        $subQ->where('modeofprocurements', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('procurement', function ($subQ) { // Search 'perLot' PRs
                        $subQ->where('pr_number', 'like', '%' . $this->search . '%')
                            ->orWhere('procurement_program_project', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('prItem', function ($subQ) { // Search 'perItem' descriptions
                        $subQ->where('description', 'like', '%' . $this->search . '%')
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
