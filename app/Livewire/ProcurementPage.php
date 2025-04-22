<?php
namespace App\Livewire;

use App\Models\BidModeOfProcurement;
use App\Models\BidSchedule;
use App\Models\Category;
use App\Models\CategoryVenue;
use App\Models\ClusterCommittee;
use App\Models\Division;
use App\Models\EndUser;
use App\Models\FundSource;
use App\Models\ModeOfProcurement;
use App\Models\Procurement;
use App\Models\Province;
use App\Models\Supplier;
use App\Models\Venue;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

class ProcurementPage extends Component
{
    use WithPagination, WithFileUploads;
    public $search = '';
    public string $procID = '';

    public $perPage = 10;
    public $showCreateModal = false; // Tracks modal state
    public $activeTab = 1; // Track the active tab

    public $tab1Data, $tab2Data, $tab3Data; // Data for each tab
    public $isCreating = false;
    public $editingId;
    public $earlyProcurement = false;
    public string $approved_ppmp = ''; // For radio buttons
    public string $otherPPMP = '';     // For the text input
    public string $app_updated = ''; // For radio buttons
    public string $otherAPP = '';     // For the text input

    public $venue_province_huc_id, $venue_specific_id, $category_venue;
    public $bid_schedules = [];

    // Form fields for procurement creation
    public $form = [
        'pr_number' => '',
        'procurement_program_project' => '',
        'date_receipt_advance' => '',
        'date_receipt_signed' => '',
        'rbac_sbac' => '',
        'dtrack_no' => '',
        'unicode' => '',
        'divisions_id' => '',
        'cluster_committees_id' => '',
        'category_id' => '',
        'venue_specific_id' => '',
        'venue_province_huc_id' => '',
        'category_venue' => '',
        'approved_ppmp' => '',
        'app_updated' => '',
        'immediate_date_needed' => '',
        'date_needed' => '',
        'end_users_id' => '',
        'early_procurement' => false,
        'fund_source_id' => '',
        'expense_class' => '',
        'abc' => '',
        'abc_50k' => '50k_or_less',
        // Tab 2 fields
        'mode_of_procurement_id' => '',
        'ib_number' => '',
        'pre_proc_conference' => '',
        'ads_post_ib' => '',
        'pre_bid_conf' => '',
        'eligibility_check' => '',
        'sub_open_bids' => '',
        'bidding_number' => '',
        'bidding_date' => '',
        'bidding_result' => '',
    ];

    protected $paginationTheme = 'tailwind';

    protected $rules = [
        'form.pr_number' => 'required|string|max:12',
        'form.procurement_program_project' => 'required|string|max:255',
        'form.date_receipt_advance' => 'nullable|date',
        'form.date_receipt_signed' => 'nullable|date',
        'form.rbac_sbac' => 'required|in:RBAC,SBAC',
        'form.dtrack_no' => 'required|string|max:12',
        'form.unicode' => 'nullable|string|max:30',
        'form.divisions_id' => 'required|exists:divisions,id',
        'form.cluster_committees_id' => 'required|exists:cluster_committees,id',
        'form.category_id' => 'required|exists:categories,id',
        'form.venue_specific_id' => 'nullable|exists:venues,id',
        'form.venue_province_huc_id' => 'nullable|exists:provinces,id',
        'form.category_venue' => 'nullable|string|max:255',
        'form.approved_ppmp' => 'required|string|max:255',
        'form.app_updated' => 'required|string|max:255',
        'form.immediate_date_needed' => 'nullable|string|max:255',
        'form.date_needed' => 'nullable|string|max:255',
        'form.end_users_id' => 'nullable|string|max:255',
        'form.early_procurement' => 'nullable|boolean',
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
        // dd('BAC' . now()->format('YmdHis') . rand(100, 999));
        $this->editingId = null;
        $this->resetForm();
        $this->showCreateModal = true;
    }

    // Close the modal and reset the form
    public function closeCreateModal()
    {
        $this->showCreateModal = false;  // Close the modal
        $this->editingId = null;         // Clear the editingId
        $this->resetForm();              // Reset the form fields
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

        $this->form['abc_50k'] = $numericValue >= 50000 ? 'above_50k' : '50k_or_less';
    }
    public function updated($propertyName)
    {
        if (
            $propertyName === 'form.venue_province_huc_id' ||
            $propertyName === 'form.venue_specific_id'
        ) {
            $this->updateCategoryVenue();
        }
    }

    public function updateCategoryVenue()
    {
        if (!empty($this->form['venue_province_huc_id']) && !empty($this->form['venue_specific_id'])) {
            $venueProvince = Province::find($this->form['venue_province_huc_id']);
            $venueSpecific = Venue::find($this->form['venue_specific_id']);

            if ($venueProvince && $venueSpecific) {
                $this->form['category_venue'] = $venueSpecific->venue . ' ' . $venueProvince->province;
            }
        } else {
            $this->form['category_venue'] = null;
        }
    }

    public function saveProcurement()
    {
        try {
            $this->form['abc'] = floatval(preg_replace('/[^0-9.]/', '', $this->form['abc']));
            $this->validate();
        } catch (ValidationException $e) {
            LivewireAlert::title('ERROR!')
                ->error()
                ->text('Required Fields Missing!')
                ->toast()
                ->position('top-end')
                ->show();
            return;
        }
        if ($this->editingId) {
            // Update existing record
            LivewireAlert::title('Update?')
                ->confirmButtonColor('green')
                ->withDenyButton('red')
                ->withConfirmButton('Update')
                ->withDenyButton('Cancel')
                ->timer(3600000)  // This will overwrite the previous one.
                ->onConfirm('updateProcurement')
                ->show();

        } else {
            // Create new record
            LivewireAlert::title('Save?')
                ->confirmButtonColor('green')
                ->withDenyButton('red')
                ->withConfirmButton('Save')
                ->withDenyButton('Cancel')
                ->timer(3600000)  // This will overwrite the previous one.
                ->onConfirm('createProcurement')
                ->show();
        }


    }
    public function updateProcurement()
    {
        try {
            // Handle and format the 'abc' value to ensure it's a float
            $this->form['abc'] = floatval(preg_replace('/[^0-9.]/', '', $this->form['abc']));

            // Validate required fields
            $this->validate([
                'form.pr_number' => 'required',
                'form.procurement_program_project' => 'required',
                'form.rbac_sbac' => 'required',
                'form.dtrack_no' => 'required',
                'form.divisions_id' => 'required',
                'form.cluster_committees_id' => 'required',
                'form.category_id' => 'required',
                'form.approved_ppmp' => 'required',
                'form.app_updated' => 'required',
                'form.abc' => 'required',
            ]);

            // Validate conditionally required fields if 'others' is selected
            if ($this->form['approved_ppmp'] === 'Others') {
                $this->validate([
                    'otherPPMP' => 'required|string|max:255', // Ensure otherPPMP is provided if 'others' is selected
                ]);
            }

            if ($this->form['app_updated'] === 'Others') {
                $this->validate([
                    'otherAPP' => 'required|string|max:255', // Ensure otherAPP is provided if 'others' is selected
                ]);
            }

            // Handle optional fields (ensure they are null if not provided)
            $this->form['date_receipt_advance'] = empty($this->form['date_receipt_advance']) ? null : $this->form['date_receipt_advance'];
            $this->form['date_receipt_signed'] = empty($this->form['date_receipt_signed']) ? null : $this->form['date_receipt_signed'];
            $this->form['unicode'] = empty($this->form['unicode']) ? null : $this->form['unicode'];
            $this->form['venue_specific_id'] = empty($this->form['venue_specific_id']) ? null : $this->form['venue_specific_id'];
            $this->form['venue_province_huc_id'] = empty($this->form['venue_province_huc_id']) ? null : $this->form['venue_province_huc_id'];
            $this->form['category_venue'] = empty($this->form['category_venue']) ? null : $this->form['category_venue'];
            $this->form['immediate_date_needed'] = empty($this->form['immediate_date_needed']) ? null : $this->form['immediate_date_needed'];
            $this->form['date_needed'] = empty($this->form['date_needed']) ? null : $this->form['date_needed'];
            $this->form['end_users_id'] = empty($this->form['end_users_id']) ? null : $this->form['end_users_id'];
            $this->form['fund_source_id'] = empty($this->form['fund_source_id']) ? null : $this->form['fund_source_id'];
            $this->form['expense_class'] = empty($this->form['expense_class']) ? null : $this->form['expense_class'];

        } catch (ValidationException $e) {
            // If validation fails, show the error alert
            LivewireAlert::title('ERROR!')
                ->error()
                ->text('Required Fields Missing!')
                ->toast()
                ->position('top-end')
                ->show();
            return;
        }

        // Handle 'approved_ppmp' override if "Others" is selected
        if (($this->form['approved_ppmp'] ?? '') === 'Others') {
            $this->form['approved_ppmp'] = $this->otherPPMP;
        }

        // Handle 'app_updated' override if "Others" is selected
        if (($this->form['app_updated'] ?? '') === 'Others') {
            $this->form['app_updated'] = $this->otherAPP;
        }

        // Update the procurement record
        Procurement::findOrFail($this->editingId)->update($this->form);

        // Show success toast after updating
        LivewireAlert::title('Updated!')
            ->success()
            ->toast()
            ->position('top-end')
            ->show();
    }

    public function createProcurement()
    {
        try {
            // Handle and format the 'abc' value to ensure it's a float
            $this->form['abc'] = floatval(preg_replace('/[^0-9.]/', '', $this->form['abc']));

            // Validate required fields
            $this->validate([
                'form.pr_number' => 'required',
                'form.procurement_program_project' => 'required',
                'form.rbac_sbac' => 'required',
                'form.dtrack_no' => 'required',
                'form.divisions_id' => 'required',
                'form.cluster_committees_id' => 'required',
                'form.category_id' => 'required',
                'form.approved_ppmp' => 'required',
                'form.app_updated' => 'required',
                'form.abc' => 'required',
            ]);

            // Validate conditionally required fields if 'others' is selected
            if ($this->form['approved_ppmp'] === 'others') {
                $this->validate([
                    'otherPPMP' => 'required|string|max:255', // Ensure otherPPMP is provided if 'others' is selected
                ]);
            }

            if ($this->form['app_updated'] === 'others') {
                $this->validate([
                    'otherAPP' => 'required|string|max:255', // Ensure otherAPP is provided if 'others' is selected
                ]);
            }

            // Handle optional fields (ensure they are null if not provided)
            $this->form['date_receipt_advance'] = empty($this->form['date_receipt_advance']) ? null : $this->form['date_receipt_advance'];
            $this->form['date_receipt_signed'] = empty($this->form['date_receipt_signed']) ? null : $this->form['date_receipt_signed'];
            $this->form['unicode'] = empty($this->form['unicode']) ? null : $this->form['unicode'];
            $this->form['venue_specific_id'] = empty($this->form['venue_specific_id']) ? null : $this->form['venue_specific_id'];
            $this->form['venue_province_huc_id'] = empty($this->form['venue_province_huc_id']) ? null : $this->form['venue_province_huc_id'];
            $this->form['category_venue'] = empty($this->form['category_venue']) ? null : $this->form['category_venue'];
            $this->form['immediate_date_needed'] = empty($this->form['immediate_date_needed']) ? null : $this->form['immediate_date_needed'];
            $this->form['date_needed'] = empty($this->form['date_needed']) ? null : $this->form['date_needed'];
            $this->form['end_users_id'] = empty($this->form['end_users_id']) ? null : $this->form['end_users_id'];
            $this->form['fund_source_id'] = empty($this->form['fund_source_id']) ? null : $this->form['fund_source_id'];
            $this->form['expense_class'] = empty($this->form['expense_class']) ? null : $this->form['expense_class'];

        } catch (ValidationException $e) {
            // If validation fails, show the error alert
            LivewireAlert::title('ERROR!')
                ->error()
                ->text('Required Fields Missing!')
                ->toast()
                ->position('top-end')
                ->show();
            return;
        }
        $this->procID = 'BAC' . now()->format('YmdHis') . rand(100, 999);
        // Create the procurement record
        Procurement::create([
            'procID' => $generatedProcID = 'BAC' . now()->format('YmdHis') . rand(100, 999),
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
            'category_venue' => $this->form['category_venue'],
            'approved_ppmp' => $this->form['approved_ppmp'] === 'Others'
                ? $this->otherPPMP
                : $this->form['approved_ppmp'],
            'app_updated' => $this->form['app_updated'] === 'Others'
                ? $this->otherAPP
                : $this->form['app_updated'],
            'immediate_date_needed' => $this->form['immediate_date_needed'],
            'date_needed' => $this->form['date_needed'],
            'end_users_id' => $this->form['end_users_id'],
            'early_procurement' => $this->form['early_procurement'],
            'fund_source_id' => $this->form['fund_source_id'],
            'expense_class' => $this->form['expense_class'],
            'abc' => $this->form['abc'],
            'abc_50k' => $this->form['abc'] >= 50000 ? 'above_50k' : '50k_or_less',
        ]);
        $this->procID = $generatedProcID;
        // Show success toast after saving
        LivewireAlert::title('Saved!')
            ->success()
            ->toast()
            ->position('top-end')
            ->show();
    }

    public function editProcurement($id)
    {
        $this->editingId = $id;
        $procurement = Procurement::findOrFail($id);
        $this->procID = $procurement->procID;

        $latestBidProcurement = BidModeOfProcurement::where('procID', $this->procID)
            ->latest('created_at')
            ->first();

        $bidSchedules = BidSchedule::where('procID', $this->procID)->get();

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
            'venue_specific_id' => $procurement->venue_specific_id ?? '',
            'venue_province_huc_id' => $procurement->venue_province_huc_id ?? '',
            'category_venue' => $procurement->category_venue,
            'immediate_date_needed' => $procurement->immediate_date_needed,
            'date_needed' => $procurement->date_needed,
            'end_users_id' => $procurement->end_users_id,
            'early_procurement' => $procurement->early_procurement,
            'fund_source_id' => $procurement->fund_source_id,
            'expense_class' => $procurement->expense_class,
            'abc' => $procurement->abc,
            'abc_50k' => $procurement->abc >= 50000 ? 'above_50k' : '50k_or_less',
            // Get Mode of Procurement from BidModeOfProcurement
            'mode_of_procurement_id' => $latestBidProcurement
                ? $latestBidProcurement->mode_of_procurement_id
                : '',
            // Convert the collection to an array for bid_schedules
            'bid_schedules' => $bidSchedules->toArray(), // Here you convert the collection to an array
        ];

        // Handle custom values for approved_ppmp and app_updated
        if (in_array($procurement->approved_ppmp, ['Yes', 'No'])) {
            $this->form['approved_ppmp'] = $procurement->approved_ppmp;
            $this->otherPPMP = ''; // Clear otherPPMP when not 'others'
        } else {
            $this->form['approved_ppmp'] = 'Others';
            $this->otherPPMP = $procurement->approved_ppmp; // Set custom value for 'others'
        }

        if (in_array($procurement->app_updated, ['Yes', 'No'])) {
            $this->form['app_updated'] = $procurement->app_updated;
            $this->otherAPP = ''; // Clear otherAPP when not 'others'
        } else {
            $this->form['app_updated'] = 'Others';
            $this->otherAPP = $procurement->app_updated; // Set custom value for 'others'
        }

        $this->showCreateModal = true;
    }


    public function confirmDelete($id)
    {
        // Show confirmation alert
        LivewireAlert::title('Are you sure you want to delete this item?')
            ->warning()
            ->withConfirmButton()
            ->confirmButtonText('Confirm')
            ->confirmButtonColor('red')
            ->withCancelButton()
            ->cancelButtonText('Cancel')
            ->position('center')
            ->onConfirm('deleteProcurement', ['id' => $id]) // Passing the ID to the deleteProcurement method
            ->show();
    }

    public function deleteProcurement($id)
    {
        // Retrieve the procurement by ID and soft delete it
        Procurement::where('id', '=', $id)->delete();


        // Show success alert
        LivewireAlert::title('Item deleted successfully!')
            ->success()
            ->toast()
            ->position('top-end')
            ->show();
    }
    // Tab-2

    public function removeBidSchedule($index)
    {
        unset($this->form['bid_schedules'][$index]);
        // Re-index the array after removal to prevent gaps in the index
        $this->form['bid_schedules'] = array_values($this->form['bid_schedules']);
    }
    public function addBidSchedule()
    {
        $this->form['bid_schedules'][] = [
            'ib_number' => '',
            'pre_proc_conference' => null,
            'ads_post_ib' => null,
            'pre_bid_conf' => null,
            'eligibility_check' => null,
            'sub_open_bids' => null,
            'bidding_number' => '',
            'bidding_date' => null,
            'bidding_result' => '',
        ];
    }



    public function saveTab2()
    {
        try {
            // Log the form data for debugging
            \Log::info('Form Data: ' . json_encode($this->form));

            // Validate inputs
            $this->validate([
                'form.mode_of_procurement_id' => 'required|exists:mode_of_procurements,id',
                'form.bid_schedules' => 'array', // Ensure bid_schedules is an array
                'form.bid_schedules.*.ib_number' => 'nullable|string|max:255', // Validate each bid schedule field
                'form.bid_schedules.*.pre_proc_conference' => 'nullable|date',
                'form.bid_schedules.*.ads_post_ib' => 'nullable|date',
                'form.bid_schedules.*.pre_bid_conf' => 'nullable|date',
                'form.bid_schedules.*.eligibility_check' => 'nullable|date',
                'form.bid_schedules.*.sub_open_bids' => 'nullable|date',
                'form.bid_schedules.*.bidding_number' => 'nullable|integer', // Validate as integer
                'form.bid_schedules.*.bidding_date' => 'nullable|date',
                'form.bid_schedules.*.bidding_result' => 'nullable|string|max:255',
            ]);

            // Save Mode of Procurement (ensure it updates or creates)
            BidModeOfProcurement::updateOrCreate(
                ['procID' => $this->procID], // Look for existing record
                ['mode_of_procurement_id' => $this->form['mode_of_procurement_id']] // Update this field
            );

            // Log before saving bid schedules
            \Log::info('Saving Bid Schedules: ', $this->form['bid_schedules']);

            // Save Bid Schedules with updateOrCreate for each schedule
            foreach ($this->form['bid_schedules'] as $schedule) {
                // Ensure bidding_number is an integer
                $schedule['bidding_number'] = (int) $schedule['bidding_number'];

                \Log::info('Saving or Updating bid schedule: ', $schedule); // Log each schedule

                BidSchedule::updateOrCreate(
                    ['procID' => $this->procID, 'bidding_number' => $schedule['bidding_number']], // Match by unique fields
                    [
                        'ib_number' => $schedule['ib_number'] ?? null,
                        'pre_proc_conference' => $schedule['pre_proc_conference'] ?? null,
                        'ads_post_ib' => $schedule['ads_post_ib'] ?? null,
                        'pre_bid_conf' => $schedule['pre_bid_conf'] ?? null,
                        'eligibility_check' => $schedule['eligibility_check'] ?? null,
                        'sub_open_bids' => $schedule['sub_open_bids'] ?? null,
                        'bidding_date' => $schedule['bidding_date'] ?? null,
                        'bidding_result' => $schedule['bidding_result'] ?? null,
                    ]
                );
            }

            // Success message
            LivewireAlert::title('Saved Successfully!')
                ->success()
                ->toast()
                ->position('top-end')
                ->show();

        } catch (ValidationException $e) {
            // Handle validation errors
            \Log::error('Validation Error: ' . $e->getMessage());
            LivewireAlert::title('Validation Error!')
                ->error()
                ->toast()
                ->position('top-end')
                ->show();
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Error saving procurement data: ' . $e->getMessage());

            LivewireAlert::title('Error Saving Tab 2!')
                ->error()
                ->toast()
                ->position('top-end')
                ->show();
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
            'rbac_sbac' => '',
            'dtrack_no' => '',
            'unicode' => '',
            'divisions_id' => '',
            'cluster_committees_id' => '',
            'category_id' => '',
            'venue_specific_id' => '',
            'venue_province_huc_id' => '',
            'category_venue' => '',
            'approved_ppmp' => '',
            'app_updated' => '',
            'immediate_date_needed' => '',
            'date_needed' => '',
            'end_users_id' => '',
            'early_procurement' => false,
            'fund_source_id' => '',
            'expense_class' => '',
            'abc' => '',
            'abc_50k' => '50k_or_less',
            // Tab 2
            'mode_of_procurement_id' => '',
            'ib_number' => '',
            'pre_proc_conference' => '',
            'ads_post_ib' => '',
            'pre_bid_conf' => '',
            'eligibility_check' => '',
            'sub_open_bids' => '',
            'bidding_number' => '',
            'bidding_date' => '',
            'bidding_result' => '',
        ];
    }
    // Save data for the active tab
    public function saveTabData()
    {
        switch ($this->activeTab) {
            case 1:
                $this->saveProcurement();
                break;
            case 2:
                $this->saveTab2();
                break;
            case 3:
                break;
        }

    }
    // Render the component view
    public function render()
    {
        // Fetching required data for dropdowns and selects
        $divisions = Division::all();
        $suppliers = Supplier::all();
        $categories = Category::all();
        $clusterCommittees = ClusterCommittee::all();
        $venueSpecifics = Venue::all();
        $venueProvinces = Province::all();
        $categoryVenues = CategoryVenue::all();
        $endUsers = EndUser::all();
        $fundSources = FundSource::all();
        $modeOfProcurements = ModeOfProcurement::all();

        // Fetching BidSchedules for the current procurement
        $bidSchedules = BidSchedule::where('procID', $this->procID)->get();

        if ($this->showCreateModal) {
            return view('livewire.procurement.procurement-modal', [
                'divisions' => $divisions,
                'suppliers' => $suppliers,
                'categories' => $categories,
                'clusterCommittees' => $clusterCommittees,
                'venueSpecifics' => $venueSpecifics,
                'venueProvinces' => $venueProvinces,
                'categoryVenues' => $categoryVenues,
                'endUsers' => $endUsers,
                'fundSources' => $fundSources,
                'modeOfProcurements' => $modeOfProcurements,
                'bidSchedules' => $bidSchedules, // Pass bid schedules here if needed for display
                'form' => $this->form,  // Pass the form data so it can be prefilled in the modal
            ]);
        }

        // For searching procurements (when modal is not shown)
        $query = Procurement::query();

        if ($this->search) {
            $query->where('pr_number', 'like', '%' . $this->search . '%')
                ->orWhere('procurement_program_project', 'like', '%' . $this->search . '%');
        }

        // Return the view for the procurement page with paginated results
        return view('livewire.procurement-page', [
            'procurements' => $query->paginate($this->perPage),
        ]);
    }

}

