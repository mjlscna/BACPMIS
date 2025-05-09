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
    public $modeBidUid;
    public $earlyProcurement = false;
    public string $approved_ppmp = ''; // For radio buttons
    public string $otherPPMP = '';     // For the text input
    public string $app_updated = ''; // For radio buttons
    public string $otherAPP = '';     // For the text input

    public $venue_province_huc_id, $venue_specific_id, $category_venue;
    public $bid_schedules = [];
    public bool $showModeSelect = false;
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
        'modes' => [],
        'ib_number' => '',
        'pre_proc_conference' => '',
        'ads_post_ib' => '',
        'pre_bid_conf' => '',
        'eligibility_check' => '',
        'sub_open_bids' => '',
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
        $this->activeTab = 1;
        $this->showCreateModal = true;
    }
    public function openEditModal($id)
    {
        $this->editingId = $id;
        $procurement = Procurement::findOrFail($id);
        $this->procID = $procurement->procID;

        // Load all modes for this procurement, ordered by the latest mode (created_at or updated_at)
        $modes = BidModeOfProcurement::where('procID', $this->procID)
            ->orderByDesc('created_at')  // Order by the latest updated_at
            ->get();

        // ✅ If there's more than one mode, exclude mode_id = 1
        if ($modes->count() > 1) {
            $modes = $modes->filter(fn($mode) => $mode->mode_of_procurement_id != 1);
        }

        // Build the modes array with associated schedules
        $this->form['modes'] = $modes->map(function ($mode) {
            $schedules = BidSchedule::where('procID', $mode->procID)
                ->where('uid', $mode->uid)
                ->get()
                ->map(function ($schedule) {
                    return [
                        'ib_number' => $schedule->ib_number,
                        'pre_proc_conference' => $schedule->pre_proc_conference,
                        'ads_post_ib' => $schedule->ads_post_ib,
                        'pre_bid_conf' => $schedule->pre_bid_conf,
                        'eligibility_check' => $schedule->eligibility_check,
                        'sub_open_bids' => $schedule->sub_open_bids,
                        'bidding_number' => $schedule->bidding_number,
                        'bidding_date' => $schedule->bidding_date,
                        'bidding_result' => $schedule->bidding_result,
                    ];
                })->toArray();

            return [
                'uid' => $mode->uid,
                'mode_of_procurement_id' => $mode->mode_of_procurement_id,
                'mode_order' => $mode->mode_order,
                'bid_schedules' => $schedules,
            ];
        })->values()->toArray();


        // Populate other fields
        $this->form = array_merge($this->form, [
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
            'early_procurement' => (bool) $procurement->early_procurement,
            'fund_source_id' => $procurement->fund_source_id,
            'expense_class' => $procurement->expense_class,
            'abc' => $procurement->abc,
            'abc_50k' => $procurement->abc >= 50000 ? 'above_50k' : '50k_or_less',
        ]);

        // Handle custom approved_ppmp and app_updated
        $this->otherPPMP = '';
        if (in_array($procurement->approved_ppmp, ['Yes', 'No'])) {
            $this->form['approved_ppmp'] = $procurement->approved_ppmp;
        } else {
            $this->form['approved_ppmp'] = 'Others';
            $this->otherPPMP = $procurement->approved_ppmp;
        }

        $this->otherAPP = '';
        if (in_array($procurement->app_updated, ['Yes', 'No'])) {
            $this->form['app_updated'] = $procurement->app_updated;
            $this->otherAPP = '';
        } else {
            $this->form['app_updated'] = 'Others';
            $this->otherAPP = $procurement->app_updated;
        }

        $this->activeTab = 1;
        if (!empty($this->form['modes'][0]['mode_of_procurement_id'] ?? null)) {
            $this->activeTab = 2;
        }
        $this->showCreateModal = true;
    }


    // Close the modal and reset the form
    public function closeCreateModal()
    {
        $this->showCreateModal = false;  // Close the modal
        $this->editingId = null; // Clear the editingId
        $this->resetForm();              // Reset the form fields
    }

    // Switch the active tab
    public function switchTab($tab)
    {
        if (
            $tab == 2 &&
            !empty($this->procID)
        ) {
            $this->activeTab = 2;
        } elseif ($tab == 1) {
            $this->activeTab = 1;
        }
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

    public function updateProcurement()
    {
        try {
            // Log the value before any processing
            logger('Before update - approved_ppmp: ' . $this->form['approved_ppmp']);

            // Handle override if "Others" is selected
            if (($this->form['approved_ppmp'] ?? '') === 'Others') {
                $this->form['approved_ppmp'] = $this->otherPPMP;
            }

            // Handle 'app_updated' override if "Others" is selected
            if (($this->form['app_updated'] ?? '') === 'Others') {
                $this->form['app_updated'] = $this->otherAPP;
            }

            // Handle and format the 'abc' value
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
                'form.abc' => 'required',
            ]);

            // Handle optional fields
            $optionalFields = [
                'date_receipt_advance',
                'date_receipt_signed',
                'unicode',
                'venue_specific_id',
                'venue_province_huc_id',
                'category_venue',
                'immediate_date_needed',
                'date_needed',
                'end_users_id',
                'fund_source_id',
                'expense_class',
            ];

            foreach ($optionalFields as $field) {
                $this->form[$field] = empty($this->form[$field]) ? null : $this->form[$field];
            }

            // Log the value after processing
            logger('After update - approved_ppmp: ' . $this->form['approved_ppmp']);
        } catch (ValidationException $e) {
            LivewireAlert::title('ERROR!')
                ->error()
                ->text('Required Fields Missing!')
                ->toast()
                ->position('top-end')
                ->show();
            return;
        }

        // Update the procurement record
        Procurement::findOrFail($this->editingId)->update($this->form);
        // Restore radio button logic for 'Others'
        if (!in_array($this->form['approved_ppmp'], ['Yes', 'No'])) {
            $this->otherPPMP = $this->form['approved_ppmp'];
            $this->form['approved_ppmp'] = 'Others';
        }

        if (!in_array($this->form['app_updated'], ['Yes', 'No'])) {
            $this->otherAPP = $this->form['app_updated'];
            $this->form['app_updated'] = 'Others';
        }
        // Show success toast
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
        $this->procID = 'BAC' . now()->format('YmdHis');
        // Create the procurement record
        Procurement::create([
            'procID' => $this->procID,
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


        // Create a default mode of procurement
        BidModeOfProcurement::create([
            'procID' => $this->procID,
            'uid' => 'MOP1-1', // uid based on default mode_of_procurement_id and mode_order
            'mode_of_procurement_id' => 1,  // Default mode ID
            'mode_order' => 1,
        ]);

        $this->loadModeOfProcurement();

        // Set the active tab for the UI update
        $this->activeTab = 2;

        LivewireAlert::title('Saved!')
            ->success()
            ->toast()
            ->position('top-end')
            ->show();
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
    public function reindexBiddingNumbers()
    {
        foreach ($this->form['modes'] as $modeIndex => $mode) {
            if (isset($mode['bid_schedules']) && is_array($mode['bid_schedules'])) {
                foreach ($mode['bid_schedules'] as $bidIndex => $schedule) {
                    $this->form['modes'][$modeIndex]['bid_schedules'][$bidIndex]['bidding_number'] = $bidIndex + 1;
                }
            }
        }
    }
    public function loadModeOfProcurement()
    {
        $this->form['modes'] = BidModeOfProcurement::where('procID', $this->procID)
            ->orderBy('mode_order')
            ->get()
            ->map(function ($mode) {
                return [
                    'mode_of_procurement_id' => $mode->mode_of_procurement_id,
                    'bid_schedules' => [], // Or hydrate from DB if needed
                ];
            })
            ->values()
            ->toArray();

    }
    public function addMode()
    {
        $modeIndex = count($this->form['modes']) + 1;

        // Generate a temporary uid that will later be updated when saved
        $tempUid = "TEMP-{$modeIndex}-" . now()->timestamp;

        $newMode = [
            'uid' => $tempUid, // Add temporary uid here
            'mode_of_procurement_id' => '', // Initially empty for mode selection
            'bid_schedules' => [], // Initially empty
        ];

        // Add the new mode at the start of the array
        array_unshift($this->form['modes'], $newMode);
    }

    public function addBidSchedule($modeIndex)
    {
        // Get the current mode and its associated bid schedules
        $mode = $this->form['modes'][$modeIndex]; // This works because we're passing $loopIndex here
        $bidSchedules = $mode['bid_schedules'] ?? [];
        $bidIndex = count($bidSchedules);  // This gives the index of the new bid

        // Generate a unique modeBidUid for this bid schedule
        $modeId = $mode['mode_of_procurement_id'] ?? 'x'; // Use mode ID as part of the unique ID
        $this->modeBidUID = "mp{$modeId}-{$modeIndex}-{$bidIndex}";

        // Ensure we're appending a completely new bid schedule
        $newBidSchedule = [
            'modeproc' => $this->modeBidUid,  // Unique ID for each bid schedule
            'ib_number' => '',
            'pre_proc_conference' => null,
            'ads_post_ib' => null,
            'pre_bid_conf' => null,
            'eligibility_check' => null,
            'sub_open_bids' => null,
            'bidding_date' => null,
            'bidding_result' => '',
            'bidding_number' => $bidIndex + 1,
            'ntfNumber' => '',
            'ntfBiddingDate' => null,
            'ntfBiddingResult' => '',
            'rfqNo' => '',
            'postQualDate' => null,
            'dateReturnedOfCanvass' => null,
            'abstractOfCanvassDate' => null,
        ];

        // Append this new bid schedule to the current mode's bid schedules
        $this->form['modes'][$modeIndex]['bid_schedules'][] = $newBidSchedule;
    }

    public function storeOrUpdateTab2()
    {
        try {
            \Log::info('Form Data:', $this->form);

            // Validate form data
            $this->validate([
                'form.modes' => 'required|array',
                'form.modes.*.mode_of_procurement_id' => 'required|exists:mode_of_procurements,id',
                'form.modes.*.bid_schedules' => 'nullable|array',
                'form.modes.*.bid_schedules.*.ib_number' => 'nullable|string|max:255',
                'form.modes.*.bid_schedules.*.pre_proc_conference' => 'nullable|date',
                'form.modes.*.bid_schedules.*.ads_post_ib' => 'nullable|date',
                'form.modes.*.bid_schedules.*.pre_bid_conf' => 'nullable|date',
                'form.modes.*.bid_schedules.*.eligibility_check' => 'nullable|date',
                'form.modes.*.bid_schedules.*.sub_open_bids' => 'nullable|date',
                'form.modes.*.bid_schedules.*.bidding_number' => 'nullable|integer|min:0|max:255',
                'form.modes.*.bid_schedules.*.bidding_date' => 'nullable|date',
                'form.modes.*.bid_schedules.*.bidding_result' => 'nullable|string|max:255',
            ]);

            foreach ($this->form['modes'] as $modeIndex => $mode) {
                \Log::info("Processing Mode {$modeIndex}: ", $mode);

                $modeId = $mode['mode_of_procurement_id'];
                $modeOrder = $mode['mode_order'] ?? (BidModeOfProcurement::where('procID', $this->procID)->max('mode_order') ?? 0) + 1;

                // Prevent duplicate mode_id = 1
                if ($modeId == 1) {
                    $exists = BidModeOfProcurement::where('procID', $this->procID)
                        ->where('mode_of_procurement_id', 1)
                        ->exists();

                    if ($exists) {
                        throw new \Exception('Mode of procurement ID 1 is already added.');
                    }
                }

                \Log::info("Mode Order for Mode ID {$modeId}: {$modeOrder}");

                // Handle existing mode by checking UID
                $existingMode = !empty($mode['uid']) && !str_starts_with($mode['uid'], 'TEMP-')
                    ? BidModeOfProcurement::where('uid', $mode['uid'])->first()
                    : null;

                // If existing mode exists, only update if mode_id changes
                if ($existingMode) {
                    // If mode_id is being updated to a different value (from 1 to another mode), recalculate the UID
                    if ($existingMode->mode_of_procurement_id == 1 && $modeId != 1) {
                        \Log::info("Changing mode from 1 to {$modeId}, updating UID");
                        // Update the mode with the new mode_id and recalculate UID only when changing from mode_id 1
                        $existingMode->update([
                            'mode_of_procurement_id' => $modeId,
                            'mode_order' => $modeOrder,
                            'uid' => "MOP{$modeId}-{$modeOrder}", // Recalculate UID
                        ]);

                        // Update the UID in the form so we can track it
                        $uid = $existingMode->uid;
                        $this->form['modes'][$modeIndex]['uid'] = $uid;
                    } else {
                        // If the mode_id is not 1, just update the mode without changing the UID
                        \Log::info("Mode ID remains the same, using existing UID.");
                        $existingMode->update([
                            'mode_of_procurement_id' => $modeId,
                            'mode_order' => $modeOrder,
                        ]);
                        $uid = $existingMode->uid;
                    }
                } else {
                    // Mode doesn't exist, create a new one with a new UID
                    $uid = "MOP{$modeId}-{$modeOrder}";
                    \Log::info("Creating new mode with UID: {$uid}");

                    $existingMode = BidModeOfProcurement::create([
                        'procID' => $this->procID,
                        'uid' => $uid,
                        'mode_of_procurement_id' => $modeId,
                        'mode_order' => $modeOrder,
                    ]);

                    // Update the UID in the form for future reference
                    $this->form['modes'][$modeIndex]['uid'] = $uid;
                    $this->form['modes'][$modeIndex]['mode_order'] = $modeOrder;
                }

                // Save or update bid schedules
                if (!empty($mode['bid_schedules'])) {
                    foreach ($mode['bid_schedules'] as $bidIndex => $schedule) {
                        \Log::info("Processing Bid Schedule for Mode UID: {$uid}", $schedule); // Debug log for bid schedule

                        // Normalize date fields to null if empty
                        foreach (['pre_proc_conference', 'ads_post_ib', 'pre_bid_conf', 'eligibility_check', 'sub_open_bids', 'bidding_date'] as $field) {
                            $schedule[$field] = empty($schedule[$field]) ? null : $schedule[$field];
                        }

                        // Prepare the data to save or update
                        $data = [
                            'procID' => $this->procID,
                            'uid' => $uid,
                            'ib_number' => $schedule['ib_number'] ?? null,
                            'pre_proc_conference' => $schedule['pre_proc_conference'],
                            'ads_post_ib' => $schedule['ads_post_ib'],
                            'pre_bid_conf' => $schedule['pre_bid_conf'],
                            'eligibility_check' => $schedule['eligibility_check'],
                            'sub_open_bids' => $schedule['sub_open_bids'],
                            'bidding_number' => $schedule['bidding_number'] ?? null,
                            'bidding_date' => $schedule['bidding_date'],
                            'bidding_result' => $schedule['bidding_result'] ?? null,
                        ];

                        // Check if the schedule exists
                        $existingSchedule = BidSchedule::where('procID', $this->procID)
                            ->where('uid', $uid)
                            ->where('bidding_number', $schedule['bidding_number'])
                            ->first();

                        if ($existingSchedule) {
                            // If schedule exists, update it
                            \Log::info("Updating existing schedule for bidding number {$schedule['bidding_number']}");
                            $existingSchedule->update($data);
                        } else {
                            // If schedule doesn't exist, create it
                            \Log::info("Creating new schedule for bidding number {$schedule['bidding_number']}");
                            BidSchedule::create($data);
                        }
                    }
                }
            }

            LivewireAlert::title('Saved Successfully!')
                ->success()
                ->toast()
                ->position('top-end')
                ->show();

        } catch (ValidationException $e) {
            LivewireAlert::title('Validation Failed!')
                ->error()
                ->text($e->getMessage())
                ->toast()
                ->position('top-end')
                ->show();
        } catch (\Exception $e) {
            \Log::error('Error Saving Data: ' . $e->getMessage()); // Log error details for debugging
            LivewireAlert::title('Error Saving Data!')
                ->error()
                ->text($e->getMessage())
                ->toast()
                ->position('top-end')
                ->show();
        }
    }



    public function toggleModeSelect()
    {
        $this->showModeSelect = true;
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
            'approved_ppmp' => 'Yes',
            'app_updated' => 'Yes',
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
        $this->activeTab = 1;
        $this->editingId = null;
        $this->modeBidUid = null;
    }
    // Save data for the active tab
    public function saveTabData()
    {
        switch ($this->activeTab) {
            case 1:
                if ($this->editingId) {
                    LivewireAlert::title('Update?')
                        ->confirmButtonColor('green')
                        ->withDenyButton('red')
                        ->withConfirmButton('Update')
                        ->withDenyButton('Cancel')
                        ->timer(false)
                        ->onConfirm('updateProcurement')
                        ->show();

                } else {
                    LivewireAlert::title('Save?')
                        ->confirmButtonColor('green')
                        ->withDenyButton('red')
                        ->withConfirmButton('Save')
                        ->withDenyButton('Cancel')
                        ->timer(false)
                        ->onConfirm('createProcurement')
                        ->show();
                }
                break;
            case 2:
                $this->storeOrUpdateTab2();
            case 3:
                break;
        }

    }
    // Render the component view
    public function render()
    {
        if (!isset($this->form['modes'])) {
            $this->form['modes'] = [];
        }
        if (!isset($this->form['bid_schedules'])) {
            $this->form['bid_schedules'] = [];
        }
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
                'form' => $this->form,  // Pass the form data so it can be prefilled in the modal
            ]);
        }

        // For searching procurements (when modal is not shown)
        $query = Procurement::query()->latest();

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

