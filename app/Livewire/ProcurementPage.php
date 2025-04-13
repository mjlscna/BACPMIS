<?php
namespace App\Livewire;

use App\Models\Category;
use App\Models\CategoryVenue;
use App\Models\ClusterCommittee;
use App\Models\Division;
use App\Models\EndUser;
use App\Models\FundSource;
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
    public $editingId = null;


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
        'abc_50k' => '50k_or_less',
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



    public function updatedFormAbc($value)
    {
        // Remove ₱ and commas if pasted
        $cleaned = preg_replace('/[^0-9.]/', '', $value);
        $numericValue = floatval($cleaned);

        $this->form['abc_50k'] = $numericValue > 50000 ? 'above_50k' : '50k_or_less';
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

    public function saveProcurement()
    {
        $this->form['abc'] = floatval(preg_replace('/[^0-9.]/', '', $this->form['abc']));
        $this->validate();

        if ($this->editingId) {
            // Update existing record
            Procurement::findOrFail($this->editingId)->update($this->form);
            session()->flash('message', 'Procurement updated successfully!');
        } else {
            // Create new record
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
            session()->flash('message', 'Procurement created successfully!');
        }

        $this->closeCreateModal();
        $this->resetForm();
    }


    public function createProcurement()
    {
        try {
            $this->form['abc'] = floatval(preg_replace('/[^0-9.]/', '', $this->form['abc']));
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
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }


    public function editProcurement($id)
    {
        $this->editingId = $id;
        $procurement = Procurement::findOrFail($id);

        // Populate the form with the existing procurement data
        $this->form = [
            'pr_number' => $procurement->pr_number,
            'procurement_program_project' => $procurement->procurement_program_project,
            'date_receipt_advance' => $procurement->date_receipt_advance,
            'date_receipt_signed' => $procurement->date_receipt_signed,
            'rbac_sbac' => $procurement->rbac_sbac,
            'dtrack_no' => $procurement->dtrack_no,
            'unicode' => $procurement->unicode,
            'divisions_id' => $procurement->divisions_id,
            'cluster_committees_id' => $procurement->cluster_committees_id,
            'category_id' => $procurement->category_id,
            'venue_specific_id' => $procurement->venue_specific_id,
            'venue_province_huc_id' => $procurement->venue_province_huc_id,
            'fund_source_id' => $procurement->fund_source_id,
            'expense_class' => $procurement->expense_class,
            'abc' => $procurement->abc,
            'abc_50k' => $procurement->abc > 50000 ? 'above_50k' : '50k_or_less',
            'mode_of_procurement_id' => $procurement->mode_of_procurement_id,
            'ib_number' => $procurement->ib_number,
            'pre_proc_conference' => $procurement->pre_proc_conference,
            'ads_post_ib' => $procurement->ads_post_ib,
            'pre_bid_conf' => $procurement->pre_bid_conf,
            'eligibility_check' => $procurement->eligibility_check,
            'sub_open_bids' => $procurement->sub_open_bids,
            'Bids' => $procurement->Bids ?? [],
        ];

        $this->showCreateModal = true;
    }
    public function deleteProcurement($id)
    {
        try {
            $procurement = Procurement::findOrFail($id);
            $procurement->delete();

            session()->flash('message', 'Procurement deleted successfully.');
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
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
        $endUsers = EndUser::all();
        $fundSources = FundSource::all();

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
                    'endUsers',
                    'fundSources',
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

