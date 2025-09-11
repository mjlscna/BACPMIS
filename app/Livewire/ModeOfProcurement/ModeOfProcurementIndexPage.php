<?php

namespace App\Livewire\ModeOfProcurement;

use Livewire\Component;
use App\Models\BidModeOfProcurement;
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
        // Fetch modes with related procurement and modeOfProcurement
        $modes = BidModeOfProcurement::with(['procurement', 'modeOfProcurement'])
            ->whereHas('procurement', function ($q) {
                $q->where('pr_number', 'like', '%' . $this->search . '%')
                    ->orWhere('procurement_program_project', 'like', '%' . $this->search . '%');
            })
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
            'modes' => $modes,
            'procurements' => $procurements,
        ]);
    }


}
