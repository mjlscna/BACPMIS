<?php

namespace App\Livewire\ModeOfProcurement;

use Livewire\Component;
use App\Models\MopItem;
use App\Models\MopLot;
use App\Models\Procurement;
use Livewire\WithPagination;

class ModeOfProcurementIndexPage extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;

    public $showProcurementModal = false;
    public $selectedProcurement = null;

    protected $paginationTheme = 'tailwind';

    public function render()
    {
        // Fetch MopItems
        $mopItems = MopItem::with(['procurement', 'modeOfProcurement'])
            ->whereHas('procurement', function ($q) {
                $q->where('pr_number', 'like', '%' . $this->search . '%')
                    ->orWhere('procurement_program_project', 'like', '%' . $this->search . '%');
            });

        // Fetch MopLots
        $mopLots = MopLot::with(['procurement', 'modeOfProcurement'])
            ->whereHas('procurement', function ($q) {
                $q->where('pr_number', 'like', '%' . $this->search . '%')
                    ->orWhere('procurement_program_project', 'like', '%' . $this->search . '%');
            });

        // Combine both queries using union
        $combined = $mopItems->select('id', 'procID', 'mode_of_procurement_id', 'mode_order', \DB::raw("'item' as type"))
            ->unionAll(
                $mopLots->select('id', 'procID', 'mode_of_procurement_id', 'mode_order', \DB::raw("'lot' as type"))
            )
            ->orderBy('mode_order')
            ->paginate($this->perPage);

        // Fetch procurements for modal search
        $procurements = Procurement::query()
            ->where(function ($q) {
                $q->where('pr_number', 'like', '%' . $this->search . '%')
                    ->orWhere('procurement_program_project', 'like', '%' . $this->search . '%');
            })
            ->get();

        return view('livewire.mode-of-procurement.mode-of-procurement-index-page', [
            'modes' => $combined,
            'procurements' => $procurements,
        ]);
    }
}
