<?php

namespace App\Livewire\Procurement;

use Livewire\Component;
use App\Models\{
    Procurement,
    Category,
    Division,
    ClusterCommittee,
    VenueSpecific,
    ProvinceHuc,
    EndUser,
    FundSource
};

class ViewPage extends Component
{
    public $showModal = false;
    public $showTable = false; // your table toggle used in the view
    public $form = [];

    // Data your view uses:
    public $categories = [];
    public $divisions = [];
    public $clusterCommittees = [];
    public $venueSpecifics = [];
    public $venueProvinces = [];
    public $endUsers = [];
    public $fundSources = [];

    protected $listeners = ['open-procurement-view' => 'open'];

    public function open($procID)
    { 
        $procurement = Procurement::where('procID', $procID)->firstOrFail();
        $this->form = $procurement->toArray();

        // Load lookup/reference data
        $this->categories = Category::with(['categoryType', 'bacType'])->get();
        $this->divisions = Division::all();
        $this->clusterCommittees = ClusterCommittee::all();
        $this->venueSpecifics = VenueSpecific::all();
        $this->venueProvinces = ProvinceHuc::all();
        $this->endUsers = EndUser::all();
        $this->fundSources = FundSource::all();

        $this->showModal = true;
    }

    public function render()
    {
        return view('livewire.procurement.view');
    }
}


