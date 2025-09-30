<?php

namespace App\Livewire\Procurements;

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


class ProcurementViewPage extends Component
{
    public $showModal = false;
    public $showTable = false;
    // Data your view uses:
    public $categories = [];
    public $divisions = [];
    public $clusterCommittees = [];
    public $venueSpecifics = [];
    public $venueProvinces = [];
    public $endUsers = [];
    public $fundSources = [];
    public $page = 1;
    public $perPage = 10;


    protected $listeners = ['open-procurement-view' => 'open'];

    public $form = [
        'procurement_type' => 'perLot', // 👈 default so it's always defined
    ];


    public function open($procID)
    {
        $procurement = Procurement::with('pr_items', 'category.categoryType', 'category.bacType')
            ->where('procID', $procID)
            ->firstOrFail();

        $this->form = $procurement->toArray();

        $this->form['category_type'] = $procurement->category?->categoryType?->category_type ?? null;
        $this->form['rbac_sbac'] = $procurement->category?->bacType?->abbreviation ?? null;

        // ✅ Normalize procurement_type
        if (!in_array($this->form['procurement_type'] ?? null, ['perItem', 'perLot'])) {
            $this->form['procurement_type'] = 'perLot';
        }

        // 🔁 Reverse items if perItem
        if ($this->form['procurement_type'] === 'perItem') {
            $this->form['items'] = $procurement->pr_items
                ->sortByDesc('id') // or prItemID if needed
                ->map(fn($item) => [
                    'prItemID' => $item->prItemID,
                    'item_no' => $item->item_no,
                    'description' => $item->description,
                    'amount' => $item->amount ?? 0,
                ])
                ->values()
                ->toArray();
        }

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
        return view('livewire.procurements.procurement-view-page');
    }
}
