<?php
namespace App\Livewire;

use App\Models\Category;
use App\Models\CategoryVenue;
use App\Models\ClusterCommittee;
use App\Models\Division;
use App\Models\Procurement;
use App\Models\Province;
use App\Models\Supplier;
use App\Models\Venue;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

class ProcurementPage extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $perPage = 10;
    public $showCreateModal = false; // Tracks modal state
    public $activeTab = 1; // Track the active tab
    public $tab1Data, $tab2Data, $tab3Data; // Data for each tab
    public $isCreating = false;

    // Form fields for procurement creation
    public $form = [
        'pr_number' => '',
        'procurement_program_project' => '',
        'date_receipt_advance' => '',
        'date_receipt_signed' => '',
        'rbac_sbac' => 'RBAC',
        'dtrack_no' => '',
        'unicode' => '',
        'divisions_id' => null,
        'cluster_committees_id' => '',
        'category_id' => '',
        'venue_specific_id' => '',
        'venue_province_huc_id' => '',
        'fund_source_id' => '',
        'expense_class' => '',
        'abc' => '',
        'abc_50k' => '',
        'mode_of_procurement_id' => 1,
        'ib_number' => '',
        'pre_proc_conference' => '',
        'ads_post_ib' => '',
        'pre_bid_conf' => '',
        'eligibility_check' => '',
        'sub_open_bids' => '',
        'Bids' => [],
    ];

    protected $paginationTheme = 'tailwind';

    protected $rules = [
        'form.pr_number' => 'required|string|max:12',
        'form.procurement_program_project' => 'required|string|max:255',
        'form.date_receipt_advance' => 'nullable|date',
        'form.date_receipt_signed' => 'nullable|date',
        'form.rbac_sbac' => 'required|in:RBAC,SBAC',
        'form.dtrack_no' => 'nullable|string|max:12',
        'form.unicode' => 'nullable|string|max:30',
        'form.divisions_id' => 'required|exists:divisions,id',
        'form.cluster_committees_id' => 'required|exists:cluster_committees,id',
        'form.category_id' => 'required|exists:categories,id',
        'form.venue_specific_id' => 'nullable|exists:venues,id',
        'form.venue_province_huc_id' => 'nullable|exists:venues,id',
        'form.fund_source_id' => 'nullable|exists:fund_sources,id',
        'form.expense_class' => 'nullable|string|max:255',
        'form.abc' => 'required|numeric|min:0',
        'form.abc_50k' => 'nullable|string|in:50k_or_less,above_50k',
    ];

    // Toggle between showing the list of procurements and creating a new one
    public function toggleCreateForm()
    {
        $this->isCreating = !$this->isCreating;
    }

    // Open the modal for creating a procurement
    public function openCreateModal()
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    // Close the modal and reset the form
    public function closeCreateModal()
    {
        $this->resetForm();
        $this->showCreateModal = false;
    }

    // Switch the active tab
    public function switchTab($tab)
    {
        $this->activeTab = $tab;

    }

    // Save data for the active tab
    public function saveTabData()
    {
        switch ($this->activeTab) {
            case 1:
                // Validate and save data for Tab 1
                $this->validate(['form.pr_number' => 'required|string|max:12']);
                break;
            case 2:
                // Validate and save data for Tab 2
                $this->validate(['form.procurement_program_project' => 'required|string|max:255']);
                break;
            case 3:
                // Validate and save data for Tab 3
                // You can add more rules for this tab as needed
                $this->validate(['form.abc' => 'required|numeric']);
                break;
        }

        // Save the data for the active tab
        session()->flash('success', 'Tab data saved!');
    }

    // Create a new procurement record
    public function createProcurement()
    {
        $this->validate();

        Procurement::create([
            'pr_number' => $this->form['pr_number'],
            'procurement_program_project' => $this->form['procurement_program_project'],
            'date_receipt_advance' => $this->form['date_receipt_advance'],
            'date_receipt_signed' => $this->form['date_receipt_signed'],
            'rbac_sbac' => $this->form['rbac_sbac'],
            'dtrack_no' => $this->form['dtrack_no'],
            'unicode' => $this->form['unicode'],
            'divisions_id' => $this->form['divisions_id'],
            'cluster_committees_id' => $this->form['cluster_committees_id'],
            'category_id' => $this->form['category_id'],
            'venue_specific_id' => $this->form['venue_specific_id'],
            'venue_province_huc_id' => $this->form['venue_province_huc_id'],
            'fund_source_id' => $this->form['fund_source_id'],
            'expense_class' => $this->form['expense_class'],
            'abc' => $this->form['abc'],
            'abc_50k' => $this->form['abc'] > 50000 ? 'above_50k' : '50k_or_less',
        ]);

        session()->flash('success', 'Procurement record created successfully!');
        $this->closeCreateModal(); // Close modal after saving
    }

    // Reset the form fields
    private function resetForm()
    {
        $this->form = [
            'pr_number' => '',
            'procurement_program_project' => '',
            'date_receipt_advance' => '',
            'date_receipt_signed' => '',
            'rbac_sbac' => 'RBAC',
            'dtrack_no' => '',
            'unicode' => '',
            'divisions_id' => '',
            'cluster_committees_id' => '',
            'category_id' => '',
            'venue_specific_id' => '',
            'venue_province_huc_id' => '',
            'fund_source_id' => '',
            'expense_class' => '',
            'abc' => '',
            'abc_50k' => '',
            'mode_of_procurement_id' => 1,
            'ib_number' => '',
            'pre_proc_conference' => '',
            'ads_post_ib' => '',
            'pre_bid_conf' => '',
            'eligibility_check' => '',
            'sub_open_bids' => '',
            'Bids' => [],
        ];
    }

    // Render the component view
    public function render()
    {
        $divisions = Division::all();      // Fetch all divisions
        $suppliers = Supplier::all();      // Fetch all suppliers
        $categories = Category::all();     // Fetch all categories
        $clusterCommittees = ClusterCommittee::all();
        $venueSpecifics = Venue::all();
        $venueProvinces = Province::all();
        $categoryVenues = CategoryVenue::all();

        if ($this->showCreateModal) {
            return view(
                'livewire.procurement.procurement-modal',
                compact(
                    'divisions',
                    'suppliers',
                    'categories',
                    'clusterCommittees',
                    'venueSpecifics',
                    'venueProvinces',
                    'categoryVenues',
                )
            );
        }

        $query = Procurement::query();

        if ($this->search) {
            $query->where('pr_number', 'like', '%' . $this->search . '%')
                ->orWhere('procurement_program_project', 'like', '%' . $this->search . '%');
        }

        return view('livewire.procurement-page', [
            'procurements' => $query->paginate($this->perPage),
        ]);
    }
}

