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
use App\Models\NtfBidSchedule;
use App\Models\PostProcurement;
use App\Models\Procurement;
use App\Models\Province;
use App\Models\Supplier;
use App\Models\Venue;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Component;
use Livewire\WithPagination;

class ProcurementPage extends Component
{
    use WithPagination;
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

    public $hasSuccessfulBidOrNtf = false;
    public bool $hasMode5 = false;
    protected $paginationTheme = 'tailwind';
    public bool $canAccessTab2 = false;
    public bool $canAccessTab3 = false;
    public bool $viewOnly = false;


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
        'abc_50k' => '50k or less',
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

        $this->checkSuccessfulBidOrNtf();

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
    public function toggleCreateForm()
    {
        $this->isCreating = !$this->isCreating;
    }
    public function openViewModal($id)
    {
        $this->resetForm();

        $procurement = Procurement::findOrFail($id);
        $this->form = $procurement->toArray();
        $this->procID = $procurement->procID;

        $this->update2();
        $this->update3();
        $this->updateTabAccess();

        $this->viewOnly = true;

        // 🔒 Force tab 1 always on view
        $this->activeTab = 1;

        $this->showCreateModal = true;
    }




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
        $this->resetForm();

        $this->editingId = $id;
        $procurement = Procurement::findOrFail($id);
        $this->procID = $procurement->procID;

        $this->update1($procurement);
        $this->update2(); // Populates $this->form['modes']
        $this->update3();

        $this->updateTabAccess(); // Must come after update2() because it checks $this->form['modes']

        // Set active tab based on conditions
        if (!empty($this->procID) && $this->canAccessTab3) {
            $this->activeTab = 3;
        } elseif (!empty($this->procID) && $this->canAccessTab2) {
            $this->activeTab = 2;
        } else {
            $this->activeTab = 1;
        }

        $this->showCreateModal = true;
    }
    public function closeCreateModal()
    {
        $this->viewOnly = true;
        $this->showCreateModal = false;  // Close the modal
        $this->editingId = null; // Clear the editingId
        $this->resetForm();              // Reset the form fields
    }
    public function switchTab(int $tab)
    {
        switch ($tab) {
            case 1:
                $this->activeTab = 1;
                break;

            case 2:
                $hasModeSelected = !empty($this->form['modes'][0]['mode_of_procurement_id'] ?? null);
                if (!empty($this->procID) && $hasModeSelected) {
                    $this->activeTab = 2;
                }
                break;

            case 3:
                if (!empty($this->procID) && ($this->hasSuccessfulBidOrNtf || $this->hasProcurementMode(5))) {
                    $this->activeTab = 3;
                }
                break;

            default:
                // Do nothing or fallback logic
                break;
        }
    }
    protected function update1(Procurement $procurement)
    {
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
            'abc_50k' => $procurement->abc >= 50000 ? 'above 50k' : '50k or less',
        ]);

        // Handle approved_ppmp field
        $this->otherPPMP = '';
        if (in_array($procurement->approved_ppmp, ['Yes', 'No'])) {
            $this->form['approved_ppmp'] = $procurement->approved_ppmp;
        } else {
            $this->form['approved_ppmp'] = 'Others';
            $this->otherPPMP = $procurement->approved_ppmp;
        }

        // Handle app_updated field
        $this->otherAPP = '';
        if (in_array($procurement->app_updated, ['Yes', 'No'])) {
            $this->form['app_updated'] = $procurement->app_updated;
            $this->otherAPP = '';
        } else {
            $this->form['app_updated'] = 'Others';
            $this->otherAPP = $procurement->app_updated;
        }
    }
    protected function update2()
    {
        $allModes = BidModeOfProcurement::where('procID', $this->procID)->get();

        $modes = $allModes
            ->when(
                $allModes->count() > 1,
                fn($collection) =>
                $collection->reject(fn($mode) => $mode->mode_of_procurement_id == 1)
            )
            ->sortByDesc(function ($mode) {
                preg_match('/-(\d+)$/', $mode->uid, $matches);
                return isset($matches[1]) ? (int) $matches[1] : 0;
            })
            ->values();

        $this->form['modes'] = $modes->map(function ($mode) {
            $isNtf = $mode->mode_of_procurement_id == 4;

            $schedules = ($isNtf ? NtfBidSchedule::query() : BidSchedule::query())
                ->where('procID', $mode->procID)
                ->where('uid', 'LIKE', $mode->uid . '-%')
                ->get()
                ->sortByDesc(function ($schedule) {
                    preg_match('/-(\d+)$/', $schedule->uid, $matches);
                    return isset($matches[1]) ? (int) $matches[1] : 0;
                })
                ->map(function ($schedule) use ($isNtf) {
                    $base = [
                        'ib_number' => $schedule->ib_number,
                        'pre_proc_conference' => $schedule->pre_proc_conference,
                        'ads_post_ib' => $schedule->ads_post_ib,
                        'pre_bid_conf' => $schedule->pre_bid_conf,
                        'eligibility_check' => $schedule->eligibility_check,
                        'sub_open_bids' => $schedule->sub_open_bids,
                        'bidding_number' => $schedule->bidding_number,
                    ];

                    return $isNtf
                        ? array_merge($base, [
                            'ntf_no' => $schedule->ntf_no,
                            'ntf_bidding_date' => $schedule->ntf_bidding_date,
                            'ntf_bidding_result' => $schedule->ntf_bidding_result,
                            'rfq_no' => $schedule->rfq_no,
                            'canvass_date' => $schedule->canvass_date,
                            'date_returned_of_canvass' => $schedule->date_returned_of_canvass,
                            'abstract_of_canvass_date' => $schedule->abstract_of_canvass_date,
                        ])
                        : array_merge($base, [
                            'bidding_date' => $schedule->bidding_date,
                            'bidding_result' => $schedule->bidding_result,
                        ]);
                })->values()->toArray();

            return [
                'uid' => $mode->uid,
                'mode_of_procurement_id' => $mode->mode_of_procurement_id,
                'mode_order' => $mode->mode_order,
                'bid_schedules' => $schedules,
            ];
        })->toArray();
    }
    protected function update3()
    {
        $post = PostProcurement::where('procID', $this->procID)->latest()->first();

        if ($post) {
            $this->form['bidEvaluationDate'] = $post->bid_evaluation_date;
            $this->form['postQualDate'] = $post->post_qual_date;
            $this->form['resolutionNumber'] = $post->resolution_number;
            $this->form['recommendingForAward'] = $post->recommending_for_award;
            $this->form['noticeOfAward'] = $post->notice_of_award;
            $this->form['awardedAmount'] = $post->awarded_amount;
            $this->form['dateOfPostingOfAwardOnPhilGEPS'] = $post->date_of_posting_of_award_on_philgeps;
        }
    }
    public function updatedFormAbc($value)
    {
        $cleaned = preg_replace('/[^0-9.]/', '', $value);
        $numericValue = floatval($cleaned);
        $this->form['abc_50k'] = $numericValue >= 50000 ? 'above 50k' : '50k or less';
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
        logger('Updated category_venue to: ' . $this->form['category_venue']);
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
    public function toggleModeSelect()
    {
        $this->showModeSelect = true;
    }
    public function saveProcurement()
    {
        try {
            // Handle 'Others' override values
            if (($this->form['approved_ppmp'] ?? '') === 'Others') {
                $this->form['approved_ppmp'] = $this->otherPPMP;
            }
            if (($this->form['app_updated'] ?? '') === 'Others') {
                $this->form['app_updated'] = $this->otherAPP;
            }

            // Format the 'abc' field
            $this->form['abc'] = floatval(preg_replace('/[^0-9.]/', '', $this->form['abc']));

            // Required validations
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

            // Conditional validation for 'Others'
            if ($this->form['approved_ppmp'] === 'others') {
                $this->validate(['otherPPMP' => 'required|string|max:255']);
            }
            if ($this->form['app_updated'] === 'others') {
                $this->validate(['otherAPP' => 'required|string|max:255']);
            }

            // Nullify optional fields if empty
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
                'expense_class'
            ];

            foreach ($optionalFields as $field) {
                $this->form[$field] = empty($this->form[$field]) ? null : $this->form[$field];
            }

        } catch (ValidationException $e) {
            LivewireAlert::title('ERROR!')
                ->error()
                ->text('Required Fields Missing!')
                ->toast()
                ->position('top-end')
                ->show();
            return;
        }

        // If editing: update record
        if ($this->editingId) {
            Procurement::findOrFail($this->editingId)->update($this->form);

            // Restore 'Others' state for UI
            if (!in_array($this->form['approved_ppmp'], ['Yes', 'No'])) {
                $this->otherPPMP = $this->form['approved_ppmp'];
                $this->form['approved_ppmp'] = 'Others';
            }
            if (!in_array($this->form['app_updated'], ['Yes', 'No'])) {
                $this->otherAPP = $this->form['app_updated'];
                $this->form['app_updated'] = 'Others';
            }

            LivewireAlert::title('Updated!')
                ->success()
                ->toast()
                ->position('top-end')
                ->show();

        } else {
            // Assign procID
            $this->procID = 'BAC' . now()->format('YmdHis');

            // Create procurement
            $procurement = Procurement::create(array_merge($this->form, [
                'procID' => $this->procID,
                'early_procurement' => $this->form['early_procurement'] ?? null,
                'abc_50k' => $this->form['abc'] >= 50000 ? 'above 50k' : '50k or less',
            ]));

            // Track editing state
            $this->editingId = $procurement->id;
            $this->form = $procurement->toArray(); // Optional: rebind clean state

            // Create default MOP
            BidModeOfProcurement::create([
                'procID' => $this->procID,
                'uid' => 'MOP1-0',
                'mode_of_procurement_id' => 1,
                'mode_order' => 0,
            ]);

            $this->loadModeOfProcurement();
            $this->activeTab = 2;

            LivewireAlert::title('Saved!')
                ->success()
                ->toast()
                ->position('top-end')
                ->show();
        }
    }
    public function saveTab2()
    {
        try {
            \Log::info('Form Data:', $this->form);

            $this->validateTab2();
            $modesForProcessing = $this->prepareModes();

            foreach ($modesForProcessing as $modeIndex => $mode) {
                $this->processMode($mode, $modeIndex);
            }

            LivewireAlert::title('Saved Successfully!')
                ->success()->toast()->position('top-end')->show();

            // Check if any mode has mode_of_procurement_id == 5
            $this->hasMode5 = collect($modesForProcessing)
                ->pluck('mode_of_procurement_id')
                ->contains(5);

            if ($this->hasProcurementMode(5)) {
                $this->activeTab = 3;
            } else {
                $this->checkSuccessfulBidOrNtf();

                if ($this->hasSuccessfulBidOrNtf) {
                    $this->activeTab = 3;
                }
            }
        } catch (ValidationException $e) {
            LivewireAlert::title('Validation Failed!')
                ->error()->text($e->getMessage())->toast()->position('top-end')->show();
        } catch (\Exception $e) {
            \Log::error('Error Saving Data: ' . $e->getMessage());
            LivewireAlert::title('Error Saving Data!')
                ->error()->text($e->getMessage())->toast()->position('top-end')->show();
        }
    }
    public function savePost()
    {
        try {
            // Validate input
            $this->validate([
                'form.bidEvaluationDate' => 'nullable|date',
                'form.postQualDate' => 'nullable|date',
                'form.resolutionNumber' => 'nullable|string|max:255',
                'form.recommendingForAward' => 'nullable|date',
                'form.noticeOfAward' => 'nullable|date',
                'form.awardedAmount' => 'nullable|numeric|min:0',
                'form.dateOfPostingOfAwardOnPhilGEPS' => 'nullable|date',
            ]);

            // Normalize awardedAmount
            if (!empty($this->form['awardedAmount'])) {
                $this->form['awardedAmount'] = floatval(preg_replace('/[^0-9.]/', '', $this->form['awardedAmount']));
            }

            $data = [
                'bid_evaluation_date' => $this->form['bidEvaluationDate'] ?? null,
                'post_qual_date' => $this->form['postQualDate'] ?? null,
                'resolution_number' => $this->form['resolutionNumber'] ?? null,
                'recommending_for_award' => $this->form['recommendingForAward'] ?? null,
                'notice_of_award' => $this->form['noticeOfAward'] ?? null,
                'awarded_amount' => $this->form['awardedAmount'] ?? null,
                'date_of_posting_of_award_on_philgeps' => $this->form['dateOfPostingOfAwardOnPhilGEPS'] ?? null,
            ];

            if ($this->editingId) {
                $procurement = Procurement::findOrFail($this->editingId);

                PostProcurement::updateOrCreate(
                    ['procID' => $procurement->procID],
                    $data
                );

                LivewireAlert::title('Updated!')
                    ->success()->toast()->position('top-end')->show();

            } else {
                PostProcurement::create([
                    'procID' => $this->procID,
                    ...$data,
                ]);

                LivewireAlert::title('Saved!')
                    ->success()->toast()->position('top-end')->show();
            }

        } catch (\Exception $e) {
            \Log::error('Error saving PostProcurement: ' . $e->getMessage());

            LivewireAlert::title('Save Failed')
                ->error()->text('An error occurred while saving.')->toast()->position('top-end')->show();
        }
    }
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
                $this->savePost();
                break;
        }
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
    private function validateTab2()
    {
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
            'form.modes.*.bid_schedules.*.ntf_no' => 'nullable|string|max:255',
            'form.modes.*.bid_schedules.*.ntf_bidding_date' => 'nullable|date',
            'form.modes.*.bid_schedules.*.ntf_bidding_result' => 'nullable|string|max:255',
            'form.modes.*.bid_schedules.*.rfq_no' => 'nullable|string|max:255',
            'form.modes.*.bid_schedules.*.canvass_date' => 'nullable|date',
            'form.modes.*.bid_schedules.*.date_returned_of_canvass' => 'nullable|date',
            'form.modes.*.bid_schedules.*.abstract_of_canvass_date' => 'nullable|date',
        ]);
    }
    private function prepareModes()
    {
        $modes = $this->form['modes'];
        usort($modes, fn($a, $b) => ($a['mode_order'] ?? 0) <=> ($b['mode_order'] ?? 0));
        return $modes;
    }
    private function processMode(array $mode, int $modeIndex)
    {
        \Log::info("Processing Mode {$modeIndex}:", $mode);
        $modeId = $mode['mode_of_procurement_id'];
        $modeOrder = $mode['mode_order'] ?? ($modeIndex + 1);

        $this->preventDuplicateMode($modeId, $mode);
        $existingMode = $this->updateOrCreateBidMode($mode, $modeId, $modeOrder);
        $this->syncModeUidToForm($mode, $existingMode->uid, $modeOrder);

        if (!empty($mode['bid_schedules'])) {
            $this->processSchedules($mode['bid_schedules'], $existingMode->uid, $modeId);
        }
    }
    private function preventDuplicateMode($modeId, $mode)
    {
        if ($modeId == 1) {
            $exists = BidModeOfProcurement::where('procID', $this->procID)
                ->where('mode_of_procurement_id', 1)
                ->exists();

            if ($exists && (empty($mode['uid']) || str_starts_with($mode['uid'], 'TEMP-'))) {
                throw new \Exception('Mode of procurement ID 1 is already added.');
            }
        }
    }
    private function updateOrCreateBidMode($mode, $modeId, $modeOrder)
    {
        $existingMode = !empty($mode['uid']) && !str_starts_with($mode['uid'], 'TEMP-')
            ? BidModeOfProcurement::where('uid', $mode['uid'])->first()
            : null;

        // If existing record is mode_id == 1 and incoming is not 1, create new
        if ($existingMode && $existingMode->mode_of_procurement_id == 1 && $modeId != 1) {
            // Determine the next mode_order for this procID
            $newOrder = BidModeOfProcurement::where('procID', $this->procID)->max('mode_order') + 1;

            // Generate a clean uid (not temporary)
            $uid = "MOP{$modeId}-{$newOrder}";

            return BidModeOfProcurement::create([
                'procID' => $this->procID,
                'uid' => $uid,
                'mode_of_procurement_id' => $modeId,
                'mode_order' => $newOrder,
            ]);
        }

        // Standard update or create
        if ($existingMode) {
            $update = [
                'mode_of_procurement_id' => $modeId,
                'mode_order' => $modeOrder,
            ];

            $existingMode->update($update);
        } else {
            $uid = "MOP{$modeId}-{$modeOrder}";
            $existingMode = BidModeOfProcurement::create([
                'procID' => $this->procID,
                'uid' => $uid,
                'mode_of_procurement_id' => $modeId,
                'mode_order' => $modeOrder,
            ]);
        }

        return $existingMode;
    }
    private function syncModeUidToForm($mode, $uid, $modeOrder)
    {
        foreach ($this->form['modes'] as &$formMode) {
            if (
                (!empty($formMode['uid']) && $formMode['uid'] === $mode['uid']) ||
                (empty($formMode['uid']) && $formMode['mode_of_procurement_id'] === $mode['mode_of_procurement_id'])
            ) {
                $formMode['uid'] = $uid;
                $formMode['mode_order'] = $modeOrder;
                break;
            }
        }
    }
    private function processSchedules(array $schedules, string $uid, int $modeId)
    {
        $reorderedSchedules = array_reverse($schedules);

        foreach ($reorderedSchedules as $i => $schedule) {
            $biddingNumber = $i + 1;
            $scheduleUid = "{$uid}-{$biddingNumber}";

            \Log::info("Processing Schedule for UID: {$scheduleUid}", $schedule);

            $baseData = [
                'procID' => $this->procID,
                'uid' => $scheduleUid,
                'ib_number' => $schedule['ib_number'] ?? null,
                'pre_proc_conference' => $schedule['pre_proc_conference'] ?? null,
                'ads_post_ib' => $schedule['ads_post_ib'] ?? null,
                'pre_bid_conf' => $schedule['pre_bid_conf'] ?? null,
                'eligibility_check' => $schedule['eligibility_check'] ?? null,
                'sub_open_bids' => $schedule['sub_open_bids'] ?? null,
                'bidding_number' => $biddingNumber,
            ];

            if ($modeId == 4) {
                $ntfData = array_merge($baseData, [
                    'ntf_no' => $schedule['ntf_no'] ?? null,
                    'ntf_bidding_date' => $schedule['ntf_bidding_date'] ?? null,
                    'ntf_bidding_result' => $schedule['ntf_bidding_result'] ?? null,
                    'rfq_no' => $schedule['rfq_no'] ?? null,
                    'canvass_date' => $schedule['canvass_date'] ?? null,
                    'date_returned_of_canvass' => $schedule['date_returned_of_canvass'] ?? null,
                    'abstract_of_canvass_date' => $schedule['abstract_of_canvass_date'] ?? null,
                ]);

                NtfBidSchedule::updateOrCreate(
                    ['procID' => $this->procID, 'uid' => $scheduleUid],
                    $ntfData
                );

            } else {
                $bidData = array_merge($baseData, [
                    'bidding_date' => $schedule['bidding_date'] ?? null,
                    'bidding_result' => $schedule['bidding_result'] ?? null,
                ]);

                BidSchedule::updateOrCreate(
                    ['procID' => $this->procID, 'uid' => $scheduleUid],
                    $bidData
                );
            }
        }
    }
    public function getShowAddModeButtonProperty()
    {
        // If any mode's `mode_of_procurement_id` is not 1
        return !$this->viewOnly && collect($this->form['modes'])->contains(function ($mode) {
            return ($mode['mode_of_procurement_id'] ?? null) != 1;
        });
    }
    public function addMode()
    {
        $newMode = [
            'uid' => '',
            'mode_of_procurement_id' => '',
            'bid_schedules' => [],
        ];

        // Insert new mode at the top
        $this->form['modes'] = array_merge([$newMode], $this->form['modes'] ?? []);

        // Reassign mode_order from bottom (1) to top (N)
        $total = count($this->form['modes']);
        foreach ($this->form['modes'] as $index => &$mode) {
            $mode['mode_order'] = $total - $index; // Bottom = 1, top = N
        }
    }
    public function addBidSchedule($modeIndex)
    {
        $existingSchedules = $this->form['modes'][$modeIndex]['bid_schedules'] ?? [];

        $newBiddingNumber = count($existingSchedules) + 1;

        $newBidSchedule = [
            'ib_number' => '',
            'pre_proc_conference' => null,
            'ads_post_ib' => null,
            'pre_bid_conf' => null,
            'eligibility_check' => null,
            'sub_open_bids' => null,
            'bidding_date' => null,
            'bidding_result' => '',
            'bidding_number' => $newBiddingNumber,
            'ntfNumber' => '',
            'ntfBiddingDate' => null,
            'ntfBiddingResult' => '',
            'rfqNo' => '',
            'postQualDate' => null,
            'dateReturnedOfCanvass' => null,
            'abstractOfCanvassDate' => null,
        ];

        // Add to top (for UX), but with highest bidding_number
        $this->form['modes'][$modeIndex]['bid_schedules'] = array_merge(
            [$newBidSchedule],
            $existingSchedules
        );
    }
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
    public function checkSuccessfulBidOrNtf()
    {
        if (empty($this->procID)) {
            $this->hasSuccessfulBidOrNtf = false;
            return;
        }

        $this->hasSuccessfulBidOrNtf = BidSchedule::where('procID', $this->procID)
            ->where('bidding_result', 'SUCCESSFUL')
            ->exists() || NtfBidSchedule::where('procID', $this->procID)
                ->where('ntf_bidding_result', 'SUCCESSFUL')
                ->exists();
    }
    public function hasProcurementMode(int $modeId): bool
    {
        return BidModeOfProcurement::where('procID', $this->procID)
            ->where('mode_of_procurement_id', $modeId)
            ->exists();
    }
    protected function updateTabAccess()
    {
        $this->canAccessTab2 = !empty($this->procID) && !empty($this->form['modes'][0]['mode_of_procurement_id'] ?? null);

        $hasSuccessfulBid = BidSchedule::where('procID', $this->procID)
            ->where('bidding_result', 'SUCCESSFUL')
            ->exists();

        $hasSuccessfulNtf = NtfBidSchedule::where('procID', $this->procID)
            ->where('ntf_bidding_result', 'SUCCESSFUL')
            ->exists();

        $hasMode5 = BidModeOfProcurement::where('procID', $this->procID)
            ->where('mode_of_procurement_id', 5)
            ->exists();

        $this->canAccessTab3 = !empty($this->procID) && ($hasSuccessfulBid || $hasSuccessfulNtf || $hasMode5);
    }
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
            'abc_50k' => '50k or less',
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

        // General properties
        $this->activeTab = 1;
        $this->procID = '';
        $this->editingId = null;
        $this->modeBidUid = null;
        $this->isCreating = false;
        $this->showCreateModal = false;
        $this->viewOnly = false;

        // Radio/text pairs
        $this->approved_ppmp = '';
        $this->otherPPMP = '';
        $this->app_updated = '';
        $this->otherAPP = '';

        // Venue-related
        $this->venue_province_huc_id = null;
        $this->venue_specific_id = null;
        $this->category_venue = null;

        // Bid schedules
        $this->bid_schedules = [];

        // Tab and mode controls
        $this->showModeSelect = false;
        $this->hasSuccessfulBidOrNtf = false;
        $this->hasMode5 = false;
        $this->canAccessTab2 = false;
        $this->canAccessTab3 = false;
    }

}

