<?php

namespace App\Livewire\ModeOfProcurement;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use App\Models\MopItem; // This is unused in this component but kept for context
use App\Models\MopLot;
use Livewire\WithPagination;

class ModeOfProcurementIndexPage extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;

    // Use the default tailwind theme for pagination
    protected $paginationTheme = 'tailwind';

    /**
     * Reset the page number when the search term is updated.
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }

    /**
     * Render the component.
     */
    public function render()
    {
        $modesQuery = MopLot::query()
            ->with(['procurement', 'modeOfProcurement'])

            // Condition 1: Only show the latest MopLot for each procurement
            ->whereIn('id', function ($query) {
                $query->selectRaw('MAX(id)')
                    ->from('mop_lot')
                    ->groupBy('procID');
            })

            // Condition 2: Exclude if uid is 'MOP-1-1' AND it's the only record for its procurement
            ->where(function ($query) {
                // KEEP the record if its uid is NOT 'MOP-1-1'
                $query->where('uid', '!=', 'MOP-1-1')
                    // OR KEEP the record if its procurement has more than one MopLot in total
                    ->orWhere(DB::raw('(SELECT COUNT(*) FROM mop_lot sub WHERE sub.procID = mop_lot.procID)'), '>', 1);
            })

            // Your original search logic
            ->when($this->search, function ($query) {
                $searchTerm = '%' . $this->search . '%';

                $query->where(function ($subQuery) use ($searchTerm) {
                    $subQuery->whereHas('procurement', function ($procurementQuery) use ($searchTerm) {
                        $procurementQuery->where('pr_number', 'like', 'searchTerm')
                            ->orWhere('project_name', 'like', $searchTerm);
                    })
                        ->orWhereHas('modeOfProcurement', function ($modeQuery) use ($searchTerm) {
                            $modeQuery->where('name', 'like', $searchTerm);
                        });
                });
            })

            // Order the final results by the most recently created
            ->latest();

        // Paginate the results and pass them to the view
        return view('livewire.mode-of-procurement.mode-of-procurement-index-page', [
            'modes' => $modesQuery->paginate($this->perPage),
        ]);
    }
}
