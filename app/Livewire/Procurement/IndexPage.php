<?php

namespace App\Livewire\Procurement;

use App\Models\Category;
use App\Models\ClusterCommittee;
use App\Models\Division;
use App\Models\EndUser;
use App\Models\FundSource;
use App\Models\ProvinceHuc;
use App\Models\Supplier;
use App\Models\VenueSpecific;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Procurement;

class IndexPage extends Component
{
    use WithPagination;

    public $perPage = 10;
    public $search = '';
    public $showModal = false;
    public $selectedProcurement;
    public $categories;
    public $divisions;
    public $clusterCommittees;
    public $venueSpecifics;
    public $venueProvinces;
    public $endUsers;
    public $fundSources;

    public $showEarlyPrompt = false;
    public $early = null;
    public $form = [];
    public function promptEarlyProcurement()
    {
        $this->showEarlyPrompt = true;
    }


    public function confirmEarly($isEarly)
    {
        $this->early = $isEarly;
        $this->showEarlyPrompt = false;

        // Navigate to create route with query parameter (optional)
        return redirect()->route('procurements.create', ['early' => $isEarly ? 1 : 0]);
    }


    public function render()
    {
        $query = Procurement::query()->latest();

        if (!empty($this->search)) {
            $searchTerm = '%' . $this->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('pr_number', 'like', $searchTerm)
                    ->orWhere('procurement_program_project', 'like', $searchTerm);
            });
        }

        return view('livewire.procurement.index', [
            'procurements' => $query->paginate($this->perPage),
        ]);
    }

}
