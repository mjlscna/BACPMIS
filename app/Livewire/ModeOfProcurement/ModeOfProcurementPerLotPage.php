<?php

namespace App\Livewire\ModeOfProcurement;

use App\Models\BidSchedule;
use App\Models\ModeOfProcurement;
use App\Models\PostProcurement;
use App\Models\PrLotPrstage;
use App\Models\PrLotRemark;
use App\Models\PrSvp;
use App\Models\PmuPo;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Attributes\Title;
use Livewire\Component;
use App\Models\Procurement;
use App\Models\MopLot;
use Illuminate\Support\Facades\DB;

#[Title('Mode of Procurement | PMIS')]
class ModeOfProcurementPerLotPage extends Component
{
    // Constants for mode types
    const BIDDING_MODES = [2, 3, 4, 5, 6];
    const SVP_MODES = [7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24];
    const ABC_THRESHOLD = 200000;
    const MODE_PENDING = 1;

    // Stages set purely from the selected mode (no real data entered yet). A
    // placeholder stage may replace another placeholder/null stage, but it must
    // NEVER replace a data-derived stage (e.g. Canvass, Abstract of Canvass).
    const PLACEHOLDER_STAGES = [1, 3, 31, 32];

    // ============================================================================
    // HELPER METHODS: Eliminate magic numbers and improve code readability
    // ============================================================================

    /**
     * Check if mode is Competitive Bidding
     */
    public function isCompetitiveBidding(?int $modeId): bool
    {
        return $modeId && in_array($modeId, self::BIDDING_MODES);
    }

    /**
     * Check if mode is SVP/Alternative mode
     */
    public function isSvpMode(?int $modeId): bool
    {
        return $modeId && in_array($modeId, self::SVP_MODES);
    }

    /**
     * Check if mode is Pending
     */
    public function isPendingMode(?int $modeId): bool
    {
        return $modeId === self::MODE_PENDING;
    }

    /**
     * Check if ABC amount meets threshold
     */
    public function meetsAbcThreshold(?float $amount): bool
    {
        return $amount && $amount >= self::ABC_THRESHOLD;
    }

    /**
     * Check if mode requires PhilGEPS posting
     */
    public function requiresPhilgeps(?int $modeId, ?float $amount): bool
    {
        return $this->isSvpMode($modeId) && $this->meetsAbcThreshold($amount);
    }

    public Procurement $procurement;
    public array $form = [];

    public Collection $modeOfProcurements;
    public int $textareaRows = 1;
    public string $procID = '';
    public int $activeTab = 1;
    public bool $showHistory = false;
    public ?string $historyForKey = null;  // Track which procurement's history is shown

    // Post-Procurement Tab Fields
    public ?string $resolutionAwardNumber = null;
    public ?string $noticeOfAwardNumber = null;
    public ?string $noticeOfAward = null;
    public ?string $resolutionAwardDate = null;
    public $awardedAmount = null; // Accepts string from Alpine.js money mask, converted to float on save
    public ?string $philgepsNoticeOfAwardNo = null;
    public ?string $philgepsPostingOfAward = null;
    public ?int $supplier_id = null;
    public ?string $dateReceiptOfSupplierNoa = null;

    public Collection $suppliers;
    public $queryParams = [];
    public bool $showModal = false;
    public ?float $abc = null;
    public ?array $editingItem = null;
    public ?int $editingIndex = null;
    public array $scheduleValidationErrors = [];

    // Forward to PMU Modal Properties
    public bool $showForwardModal = false;
    public ?string $actualDateForwarded = null;

    public function mount(Procurement $procurement): void
    {
        $this->queryParams = request()->query();

        // ✅ Eager load all necessary relationships upfront
        $procurement->load([
            'mopLots' => function ($query) {
                $query->orderBy('mode_order');
            },
            'mopLots.modeOfProcurement'
        ]);
        $this->procurement = $procurement;
        $this->procID = $procurement->procID ?? '';

        $this->form = [
            'pr_number' => $procurement->pr_number ?? '',
            'procurement_program_project' => $procurement->procurement_program_project ?? '',
            'approved_ppmp' => (bool) ($procurement->approved_ppmp ?? false),
            'app_updated' => (bool) ($procurement->app_updated ?? false),
            'early_procurement' => (bool) ($procurement->early_procurement ?? false),
            'items' => [],
        ];

        $this->abc = $procurement->abc;

        $this->loadPostProcurementData($procurement);
        $this->modeOfProcurements = ModeOfProcurement::orderBy('id', 'asc')->get();
        $this->suppliers = Supplier::all();
        $this->loadPerLotData($procurement);
        $this->calculateTextareaRows($procurement->procurement_program_project ?? '');
    }

    // ============================================================================
    // FIX #5: CONSISTENT EMPTY CHECK PATTERN
    // ============================================================================
    /**
     * Standardized method to check if a value is considered "filled"
     * Handles: null, empty string, whitespace-only strings
     * Returns true if value has meaningful content
     */
    private function hasValue($value): bool
    {
        // null check first
        if (is_null($value)) {
            return false;
        }

        // Convert to string and trim
        $stringValue = trim((string) $value);

        // Check if empty after trimming
        return $stringValue !== '';
    }

    /**
     * Check if ANY of the provided fields have values
     */
    private function hasAnyValue(array $fields): bool
    {
        foreach ($fields as $value) {
            if ($this->hasValue($value)) {
                return true;
            }
        }
        return false;
    }

    private function loadPostProcurementData(Procurement $procurement): void
    {
        $post = PostProcurement::where('ref_id', $this->procID)->first();

        if ($post) {
            $this->resolutionAwardNumber = $post->resolution_award_number;
            $this->noticeOfAwardNumber = $post->notice_of_award_number;
            $this->noticeOfAward = $post->notice_of_award;
            $this->resolutionAwardDate = $post->resolution_award_date;
            $this->awardedAmount = $this->formatAmount($post->awarded_amount); // Format with commas for Alpine mask
            $this->philgepsNoticeOfAwardNo = $post->philgeps_notice_of_award_no;
            $this->philgepsPostingOfAward = $post->philgeps_posting_of_award;
            $this->supplier_id = $post->supplier_id;
            $this->dateReceiptOfSupplierNoa = $post->date_receipt_of_supplier_noa;
        }
    }

    private function calculateTextareaRows(string $text): void
    {
        $text = trim($text);
        $lineCount = substr_count($text, "\n") + 1;
        $approxExtraLines = ceil(strlen($text) / 150);
        $this->textareaRows = max($lineCount, $approxExtraLines, 1);
    }

    public function getIsPostAvailableProperty(): bool
    {
        foreach ($this->form['items'] ?? [] as $item) {
            $modeId = $item['mode_of_procurement_id'] ?? null;

            // COMPETITIVE BIDDING MODES
            if ($this->isCompetitiveBidding($modeId)) {
                // Check all required bidding fields are filled
                $allBiddingFieldsFilled =
                    $this->hasValue($item['bidding_number']) &&
                    $this->hasValue($item['ib_number']) &&
                    $this->hasValue($item['philgeps_posting_ref_no']) &&
                    $this->hasValue($item['ads_post_ib']) &&
                    $this->hasValue($item['list_invited_observers']) &&
                    $this->hasValue($item['obsrvr_eligibility']) &&
                    $this->hasValue($item['obsrvr_sub_open_of_bid']) &&
                    $this->hasValue($item['obsrvr_bid']) &&
                    $this->hasValue($item['obsrvr_post_qual']) &&
                    $this->hasValue($item['eligibility_check']) &&
                    $this->hasValue($item['sub_open_bids']) &&
                    $this->hasValue($item['bid_evaluation_date']) &&
                    $this->hasValue($item['post_qualification_date']) &&
                    $this->hasValue($item['bidding_result']) &&
                    ($item['bidding_result'] === 'SUCCESSFUL');

                // For bidding modes, also require resolution_number_mop
                if ($this->isCompetitiveBidding($modeId)) {
                    $allBiddingFieldsFilled = $allBiddingFieldsFilled && $this->hasValue($item['resolution_number_mop']);
                }

                if ($allBiddingFieldsFilled) {
                    return true;
                }
            }

            // SVP/ALTERNATIVE MODES
            if ($this->isSvpMode($modeId)) {
                // Base required SVP fields
                $allSvpFieldsFilled =
                    $this->hasValue($item['resolution_number_mop']) &&
                    $this->hasValue($item['rfq_no']) &&
                    $this->hasValue($item['canvass_date']) &&
                    $this->hasValue($item['date_returned_of_canvass']) &&
                    $this->hasValue($item['abstract_of_canvass_date']);

                // If ABC is 200k or above, also require philgeps_posting_ref_no and ads_post_ib
                if ($this->abc >= self::ABC_THRESHOLD) {
                    $allSvpFieldsFilled = $allSvpFieldsFilled &&
                        $this->hasValue($item['philgeps_posting_ref_no']) &&
                        $this->hasValue($item['ads_post_ib']);
                }

                if ($allSvpFieldsFilled) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Load per-lot data with all schedules
     *
     * OPTIMIZED: Eager loads relationships to prevent N+1 queries
     */
    protected function loadPerLotData(Procurement $procurement): void
    {
        // ✅ Eager load mopLots with modeOfProcurement to prevent N+1 query
        $mopLots = $procurement->mopLots;

        // If mopLots weren't loaded in mount, load them now with relationship
        if (!$mopLots->isNotEmpty() || !$mopLots->first()?->relationLoaded('modeOfProcurement')) {
            $procurement->load([
                'mopLots' => function ($query) {
                    $query->orderBy('mode_order');
                },
                'mopLots.modeOfProcurement'
            ]);
            $mopLots = $procurement->mopLots;
        }

        $uids = $mopLots->pluck('uid')->filter()->toArray();
        $procID = $procurement->procID;

        if (empty($uids)) {
            $this->form['items'] = [];
            return;
        }

        $bidSchedules = BidSchedule::whereIn('mop_uid', $uids)
            ->where('ref_id', $procID)
            ->get()
            ->keyBy('mop_uid');

        $prSvps = PrSvp::whereIn('mop_uid', $uids)
            ->where('ref_id', $procID)
            ->get()
            ->keyBy('mop_uid');

        $scheduleMap = $this->buildScheduleMap($bidSchedules, $prSvps);

        $this->form['items'] = $mopLots->map(function ($mopLot, $index) use ($scheduleMap) {
            $uid = $mopLot->uid;
            $schedule = $uid ? $scheduleMap->get($uid, []) : [];
            return $this->buildItemArray($mopLot, $index, $schedule);
        })->toArray();
    }

    private function buildItemArray($mopLot, int $index, array $schedule): array
    {
        return [
            'id' => $mopLot->id ?? null,
            'uid' => $mopLot->uid ?? 'temp_' . uniqid(),
            'mode_of_procurement_id' => $mopLot->mode_of_procurement_id,
            'mode_order' => $mopLot->mode_order ?? ($index + 1),
            'bidding_number' => $schedule['bidding_number'] ?? null,
            'ib_number' => $schedule['ib_number'] ?? null,
            'philgeps_posting_ref_no' => $schedule['philgeps_posting_ref_no'] ?? null,
            'ads_post_ib' => $schedule['ads_post_ib'] ?? null,
            'pre_proc_conference' => $schedule['pre_proc_conference'] ?? null,
            'list_invited_observers' => $schedule['list_invited_observers'] ?? null,
            'obsrvr_prebid_conf' => $schedule['obsrvr_prebid_conf'] ?? null,
            'obsrvr_eligibility' => $schedule['obsrvr_eligibility'] ?? null,
            'obsrvr_sub_open_of_bid' => $schedule['obsrvr_sub_open_of_bid'] ?? null,
            'obsrvr_bid' => $schedule['obsrvr_bid'] ?? null,
            'obsrvr_post_qual' => $schedule['obsrvr_post_qual'] ?? null,
            'pre_bid_conf' => $schedule['pre_bid_conf'] ?? null,
            'eligibility_check' => $schedule['eligibility_check'] ?? null,
            'sub_open_bids' => $schedule['sub_open_bids'] ?? null,
            'bid_evaluation_date' => $schedule['bid_evaluation_date'] ?? null,
            'post_qualification_date' => $schedule['post_qualification_date'] ?? null,
            'bidding_result' => $schedule['bidding_result'] ?? null,
            'resolution_number_mop' => $schedule['resolution_number_mop'] ?? null,
            'rfq_no' => $schedule['rfq_no'] ?? null,
            'canvass_date' => $schedule['canvass_date'] ?? null,
            'date_returned_of_canvass' => $schedule['date_returned_of_canvass'] ?? null,
            'abstract_of_canvass_date' => $schedule['abstract_of_canvass_date'] ?? null,
        ];
    }

    private function buildScheduleMap(Collection $bidSchedules, Collection $prSvps): Collection
    {
        $map = collect();

        foreach ($bidSchedules as $uid => $schedule) {
            $map[$uid] = [
                'bidding_number' => $schedule->bidding_number,
                'ib_number' => $schedule->ib_number,
                'philgeps_posting_ref_no' => $schedule->philgeps_posting_ref_no,
                'ads_post_ib' => $schedule->ads_post_ib,
                'pre_proc_conference' => $schedule->pre_proc_conference,
                'list_invited_observers' => $schedule->list_invited_observers,
                'obsrvr_prebid_conf' => $schedule->obsrvr_prebid_conf,
                'obsrvr_eligibility' => $schedule->obsrvr_eligibility,
                'obsrvr_sub_open_of_bid' => $schedule->obsrvr_sub_open_of_bid,
                'obsrvr_bid' => $schedule->obsrvr_bid,
                'obsrvr_post_qual' => $schedule->obsrvr_post_qual,
                'pre_bid_conf' => $schedule->pre_bid_conf,
                'eligibility_check' => $schedule->eligibility_check,
                'sub_open_bids' => $schedule->sub_open_bids,
                'bid_evaluation_date' => $schedule->bid_evaluation_date,
                'post_qualification_date' => $schedule->post_qualification_date,
                'bidding_result' => $schedule->bidding_result,
                'resolution_number_mop' => $schedule->resolution_number_mop,
            ];
        }

        foreach ($prSvps as $uid => $schedule) {
            $existing = $map->get($uid, []);
            $map[$uid] = array_merge($existing, [
                'philgeps_posting_ref_no' => $schedule->philgeps_posting_ref_no ?? $existing['philgeps_posting_ref_no'] ?? null,
                'ads_post_ib' => $schedule->ads_post_ib ?? $existing['ads_post_ib'] ?? null,
                'resolution_number_mop' => $schedule->resolution_number_mop,
                'rfq_no' => $schedule->rfq_no,
                'canvass_date' => $schedule->canvass_date,
                'date_returned_of_canvass' => $schedule->date_returned_of_canvass,
                'abstract_of_canvass_date' => $schedule->abstract_of_canvass_date,
            ]);
        }

        return $map;
    }

    public function addItem(): void
    {
        // Get the highest mode_order from existing items
        $maxModeOrder = 0;
        foreach ($this->form['items'] as $item) {
            if (isset($item['mode_order']) && $item['mode_order'] > $maxModeOrder) {
                $maxModeOrder = $item['mode_order'];
            }
        }

        // New item gets next mode_order
        $nextModeOrder = $maxModeOrder + 1;

        $newItem = [
            'id' => null,
            'uid' => 'temp_' . uniqid(),
            'mode_of_procurement_id' => null,
            'mode_order' => $nextModeOrder,
            'bidding_number' => null,
            'ib_number' => null,
            'philgeps_posting_ref_no' => null,
            'ads_post_ib' => null,
            'pre_proc_conference' => null,
            'list_invited_observers' => null,
            'obsrvr_prebid_conf' => null,
            'obsrvr_eligibility' => null,
            'obsrvr_sub_open_of_bid' => null,
            'obsrvr_bid' => null,
            'obsrvr_post_qual' => null,
            'pre_bid_conf' => null,
            'eligibility_check' => null,
            'sub_open_bids' => null,
            'bid_evaluation_date' => null,
            'post_qualification_date' => null,
            'bidding_result' => null,
            'resolution_number_mop' => null,
            'rfq_no' => null,
            'canvass_date' => null,
            'date_returned_of_canvass' => null,
            'abstract_of_canvass_date' => null,
        ];

        $this->form['items'][] = $newItem;
        $this->showHistory = false;
    }

    private function reindexModeOrder(): void
    {
        foreach ($this->form['items'] as $index => &$item) {
            $item['mode_order'] = $index + 1;
        }
    }

    /**
     * Toggle history display for a specific procurement
     * Unified implementation across all modules
     */
    public function toggleHistory(?string $key = null)
    {
        if ($key && $this->historyForKey === $key && $this->showHistory) {
            // Clicking same item - close history
            $this->showHistory = false;
            $this->historyForKey = null;
        } elseif ($key) {
            // Open history for this specific item
            $this->showHistory = true;
            $this->historyForKey = $key;
        } else {
            // Legacy support: toggle all history
            $this->showHistory = !$this->showHistory;
            $this->historyForKey = null;
        }
    }

    public function removeItem(string $uid): void
    {
        $this->form['items'] = array_filter($this->form['items'], function ($item) use ($uid) {
            return $item['uid'] !== $uid;
        });

        $this->form['items'] = array_values($this->form['items']);
        $this->reindexModeOrder();
    }

    public function setStep(int $step): void
    {
        if ($step == 2 && !$this->isPostAvailable) {
            LivewireAlert::title('Cannot Access Post Tab')
                ->warning()
                ->text('Please complete the Mode of Procurement details first. A SUCCESSFUL bidding result or complete SVP data is required.')
                ->toast()
                ->position('top-end')
                ->show();
            return;
        }

        // Validate ABC threshold when activating Post tab
        if ($step == 2) {
            if (!$this->validateAbcThreshold()) {
                return; // Validation failed, stay on current tab
            }
        }

        $this->activeTab = $step;
    }

    // ============================================================================
    // FIX #6: TRANSACTION ROLLBACK - VALIDATE BEFORE TRANSACTION
    // ============================================================================
    public function saveTab1()
    {
        // STEP 1: Validate form rules FIRST
        $rules = [
            'form.items.*.mode_of_procurement_id' => 'required|integer',
        ];

        $attributes = [];

        try {
            $this->validate($rules, [], $attributes);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errorMessages = $e->validator->errors()->all();
            $errorString = implode(' ', $errorMessages);

            LivewireAlert::title('Validation Failed')
                ->error()
                ->text('Please check the following errors: ' . $errorString)
                ->toast()->position('top-end')->show();

            // Exit early - don't proceed to schedule validation or DB transaction
            return;
        }

        // STEP 2: Validate schedules BEFORE starting transaction
        $this->scheduleValidationErrors = [];
        if (!$this->validateSchedules()) {
            $errorMessage = implode(' ', $this->scheduleValidationErrors);
            LivewireAlert::title('Validation Failed')
                ->error()
                ->text($errorMessage)
                ->toast()->position('top-end')->show();

            // Exit early - validation failed, don't touch database
            return;
        }

        // STEP 3: Check if user can modify successful bids
        if (!$this->canModifySuccessfulBids()) {
            LivewireAlert::title('Permission Denied!')
                ->error()
                ->text('You do not have permission to edit procurement with post-procurement data.')
                ->toast()
                ->position('top-end')
                ->show();
            return;
        }

        // STEP 4: Validate against clearing existing schedule data
        if (!$this->validateScheduleDeletion()) {
            return; // Error already shown in method
        }

        // STEP 5: ALL validation passed - NOW start the database transaction
        $isMopAdded = false;
        $isMopUpdated = false;
        $isScheduleAdded = false;
        $isScheduleUpdated = false;

        try {
            DB::transaction(function () use (&$isMopAdded, &$isMopUpdated, &$isScheduleAdded, &$isScheduleUpdated) {
                foreach ($this->form['items'] as $index => $item) {
                    if (empty($item['mode_of_procurement_id']))
                        continue;

                    $modeId = $item['mode_of_procurement_id'];
                    $modeOrder = $index + 1;
                    $isUiNew = isset($item['uid']) && (str_starts_with($item['uid'], 'new_') || str_starts_with($item['uid'], 'temp_'));

                    if ($isUiNew) {
                        // Create new record - don't use updateOrCreate to avoid overwriting
                        $generatedUid = "MOP-{$modeId}-{$modeOrder}";

                        $savedParentModel = MopLot::create([
                            'procID' => $this->procID,
                            'uid' => $generatedUid,
                            'mode_of_procurement_id' => $modeId,
                            'mode_order' => $modeOrder
                        ]);

                        $isMopAdded = true;
                    } else {
                        // Update existing record
                        $generatedUid = $item['uid'];

                        $savedParentModel = MopLot::updateOrCreate(
                            [
                                'procID' => $this->procID,
                                'uid' => $item['uid']
                            ],
                            [
                                'mode_of_procurement_id' => $modeId,
                                'mode_order' => $modeOrder
                            ]
                        );

                        if ($savedParentModel->wasChanged()) {
                            $isMopUpdated = true;
                        }
                    }

                    if ($savedParentModel) {
                        $this->saveRelatedSchedules(
                            $savedParentModel,
                            $item,
                            $isScheduleAdded,
                            $isScheduleUpdated
                        );
                    }
                }
            });
        } catch (\Exception $e) {
            \Log::error('ModeOfProcurement save failed', [
                'procID' => $this->procID,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            LivewireAlert::title('Save Failed')
                ->error()
                ->text('An error occurred while saving. Please try again or contact support.')
                ->toast()
                ->position('top-end')
                ->show();

            return;
        }

        // Show appropriate success message
        if ($isMopAdded) {
            LivewireAlert::title('Mode Added Successfully!')->success()->text('The mode of procurement has been added.')->toast()->position('top-end')->show();
        } elseif ($isScheduleAdded) {
            LivewireAlert::title('Schedule Added!')->success()->text('A new bidding schedule has been created.')->toast()->position('top-end')->show();
        } elseif ($isMopUpdated || $isScheduleUpdated) {
            LivewireAlert::title('Updates Saved!')->success()->text('Changes have been saved successfully.')->toast()->position('top-end')->show();
        } else {
            LivewireAlert::title('No Changes')->info()->text('No changes were detected.')->toast()->position('top-end')->show();
        }

        $this->autoUpdateStageForModeFromPending();
        $this->autoUpdateStageForSvpMode();
        $this->autoUpdateStageForCompetitiveBiddingMode();
        $this->mount($this->procurement);
    }

    private function validateSchedules(): bool
    {
        $isValid = true;

        $allItems = $this->form['items'];
        $totalItems = count($allItems);

        foreach ($allItems as $index => $item) {
            // History items (all except the last/current) are already saved — skip validation
            // EXCEPT when editing a specific history item via the modal ($this->editingIndex)
            $isHistoryItem = ($index !== $totalItems - 1);
            $isBeingEdited = ($this->editingIndex !== null && $index === $this->editingIndex);
            if ($isHistoryItem && !$isBeingEdited) {
                continue;
            }

            $modeId = $item['mode_of_procurement_id'] ?? null;
            if (!$modeId)
                continue;

            $itemNumber = $index + 1;
            $modeName = $this->modeOfProcurements->firstWhere('id', $modeId)?->modeofprocurements ?? "Item {$itemNumber}";

            $matchCriteria = [
                'ref_id' => $this->procID,
                'mop_uid' => $item['uid']
            ];

            // FIX #5: Using hasAnyValue() for consistent checking
            $biddingFields = [
                $item['bidding_number'] ?? null,
                $item['ib_number'] ?? null,
                $item['philgeps_posting_ref_no'] ?? null,
                $item['ads_post_ib'] ?? null,
                $item['pre_proc_conference'] ?? null,
                $item['pre_bid_conf'] ?? null,
                $item['eligibility_check'] ?? null,
                $item['sub_open_bids'] ?? null,
                $item['bid_evaluation_date'] ?? null,
                $item['post_qualification_date'] ?? null,
                $item['bidding_date'] ?? null,
                $item['bidding_result'] ?? null,
            ];

            $hasAnyData = $this->hasAnyValue($biddingFields);

            // Skip validation for empty new items
            if (!$hasAnyData && str_starts_with($item['uid'], 'temp_')) {
                continue;
            }

            // COMPETITIVE BIDDING MODES
            if ($this->isCompetitiveBidding($modeId)) {
                $existingBidSchedule = BidSchedule::where($matchCriteria)->first();

                // Bidding number must match the expected sequence based on the BidSchedule uid
                // e.g. uid MOP-2-2-1 → last segment is 1, so bidding number must be 1
                // For new records, expected = total existing BidSchedules for this mode + 1
                if ($this->hasValue($item['bidding_number'] ?? null)) {
                    $enteredBiddingNumber = (string) $item['bidding_number'];

                    if ($existingBidSchedule && $this->hasValue($existingBidSchedule->uid)) {
                        // Existing record: derive expected number from saved uid's last segment
                        $uidParts = explode('-', $existingBidSchedule->uid);
                        $expectedBiddingNumber = (string) end($uidParts);
                        if ($enteredBiddingNumber !== $expectedBiddingNumber) {
                            $this->scheduleValidationErrors[] = "{$modeName}: Bidding Number must be \"{$expectedBiddingNumber}\" (based on record uid: {$existingBidSchedule->uid}).";
                            $isValid = false;
                        }
                    } elseif (!$existingBidSchedule) {
                        // New record: compute expected sequence (same logic as saveRelatedSchedules)
                        $relatedMopUids = MopLot::where('procID', $this->procID)
                            ->where('mode_of_procurement_id', $modeId)
                            ->pluck('uid');
                        $count = BidSchedule::where('ref_id', $this->procID)
                            ->whereIn('mop_uid', $relatedMopUids)
                            ->count();
                        $expectedBiddingNumber = (string) ($count + 1);
                        if ($enteredBiddingNumber !== $expectedBiddingNumber) {
                            $this->scheduleValidationErrors[] = "{$modeName}: Bidding Number must be \"{$expectedBiddingNumber}\" (next available sequence).";
                            $isValid = false;
                        }
                    }
                }

                $hasBiddingData = $this->hasAnyValue($biddingFields);

                if ($existingBidSchedule && !$hasBiddingData) {
                    $this->scheduleValidationErrors[] = "{$modeName}: At least one bidding schedule field must be filled.";
                    $isValid = false;
                }

                // Validate Bidding Result dependencies
                $biddingResult = $item['bidding_result'] ?? null;

                // FIX #5: Using hasValue() for consistent checking
                if ($this->hasValue($biddingResult)) {
                    $missingFields = [];
                    $hasPreProcConference = $this->hasValue($item['pre_proc_conference']);

                    if (!$hasPreProcConference) {
                        if (!$this->hasValue($item['bidding_number'])) {
                            $missingFields[] = 'Bidding #';
                        }
                        if (!$this->hasValue($item['ib_number'])) {
                            $missingFields[] = 'IB No.';
                        }
                        if (!$this->hasValue($item['sub_open_bids'])) {
                            $missingFields[] = 'Submission & Opening of Bids';
                        }

                        if (!empty($missingFields)) {
                            $fieldsList = implode(', ', $missingFields);
                            $this->scheduleValidationErrors[] = "{$modeName}: Cannot set Bidding Result without {$fieldsList} or Pre-Proc Conference.";
                            $isValid = false;
                        }
                    }

                    if ($biddingResult === 'SUCCESSFUL') {
                        $successMissingFields = [];

                        if (!$this->hasValue($item['bid_evaluation_date'])) {
                            $successMissingFields[] = 'Bid Evaluation Date';
                        }
                        if (!$this->hasValue($item['post_qualification_date'])) {
                            $successMissingFields[] = 'Post Qualification Date';
                        }

                        if (!empty($successMissingFields)) {
                            $fieldsList = implode(', ', $successMissingFields);
                            $this->scheduleValidationErrors[] = "{$modeName}: {$fieldsList} required for SUCCESSFUL bidding result.";
                            $isValid = false;
                        }
                    }
                }
            }

            // SVP/ALTERNATIVE MODES
            if ($this->isSvpMode($modeId)) {
                $existingSvp = PrSvp::where($matchCriteria)->first();

                // FIX #5: Using hasAnyValue() for consistent checking
                $svpFields = [
                    $item['rfq_no'] ?? null,
                    $item['canvass_date'] ?? null,
                    $item['date_returned_of_canvass'] ?? null,
                    $item['abstract_of_canvass_date'] ?? null,
                ];

                // For SVP modes (7-24) with ABC >= 200k, PhilGEPS and Ads/Post IB should also count as valid SVP data
                $prAbc = $this->abc ?? 0;
                if ($prAbc >= 200000) {
                    $svpFields[] = $item['philgeps_posting_ref_no'] ?? null;
                    $svpFields[] = $item['ads_post_ib'] ?? null;
                }

                $hasSvpData = $this->hasAnyValue($svpFields);

                if ($existingSvp && !$hasSvpData) {
                    $this->scheduleValidationErrors[] = "{$modeName}: At least one SVP field must be filled.";
                    $isValid = false;
                }

                // No required field validation for SVP modes during save - all fields are optional
            }
        }

        return $isValid;
    }

    /**
     * Validate ABC threshold requirements when ABC or mode changes
     * Ensures PhilGEPS fields are filled when ABC >= ₱200,000
     *
     * @return bool True if validation passes
     */
    private function validateAbcThreshold(): bool
    {
        $abc = $this->abc ?? 0;
        $prNumber = $this->form['pr_number'] ?? 'this PR';

        // Only validate the CURRENT item (first item in array), not history
        $items = $this->form['items'] ?? [];
        if (empty($items)) {
            return true;
        }

        // Get the current/latest item (first in array after reverse)
        $currentItem = reset($items);
        $modeId = $currentItem['mode_of_procurement_id'] ?? null;

        // Skip validation if no mode is selected
        if (empty($modeId)) {
            return true;
        }

        // Skip validation for empty new items (temp UIDs with no data)
        if (str_starts_with($currentItem['uid'] ?? '', 'temp_')) {
            // Check if this temp item has any schedule data
            $hasAnyScheduleData = $this->itemHasSchedule($currentItem);
            if (!$hasAnyScheduleData) {
                return true; // Skip validation for completely empty new items
            }
        }

        // Only validate for modes that require PhilGEPS
        if ($this->isCompetitiveBidding($modeId) || $this->isSvpMode($modeId)) {
            // ABC >= ₱200,000 requires PhilGEPS posting
            if ($abc >= self::ABC_THRESHOLD) {
                $philgepsRef = trim($currentItem['philgeps_posting_ref_no'] ?? '');
                $adsPostIb = trim($currentItem['ads_post_ib'] ?? '');

                if (empty($philgepsRef)) {
                    LivewireAlert::title('Validation Error!')
                        ->error()
                        ->text("PR {$prNumber}: PhilGEPS Posting Ref # is required when ABC is ₱200,000.00 or more.")
                        ->toast()
                        ->position('top-end')
                        ->show();
                    return false;
                }

                if (empty($adsPostIb)) {
                    LivewireAlert::title('Validation Error!')
                        ->error()
                        ->text("PR {$prNumber}: Ads/Post IB date is required when ABC is ₱200,000.00 or more.")
                        ->toast()
                        ->position('top-end')
                        ->show();
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Validate that editing is not clearing existing schedule data without replacement
     * Prevents accidental data loss when user edits existing schedules
     *
     * @return bool True if validation passes
     */
    private function validateScheduleDeletion(): bool
    {
        foreach ($this->form['items'] as $item) {
            $modeId = $item['mode_of_procurement_id'] ?? null;
            $prNumber = $this->form['pr_number'] ?? 'PR';

            // Skip validation for new items (no existing data to protect)
            if (str_starts_with($item['uid'] ?? '', 'temp_')) {
                continue;
            }

            // For competitive bidding modes
            if ($this->isCompetitiveBidding($modeId)) {
                // Check if item has existing bidding data
                $hasExistingBiddingData = $this->hasValue($item['bidding_number']) ||
                    $this->hasValue($item['ib_number']) ||
                    $this->hasValue($item['sub_open_bids']);

                // Check if user is trying to clear the data (no replacement values)
                $clearingBiddingData = !$this->hasValue($item['bidding_number']) &&
                    !$this->hasValue($item['ib_number']) &&
                    !$this->hasValue($item['sub_open_bids']) &&
                    !$this->hasValue($item['pre_proc_conference']);

                if ($hasExistingBiddingData && $clearingBiddingData) {
                    LivewireAlert::title('Cannot Clear Existing Data')
                        ->error()
                        ->text("Cannot clear existing bidding data for {$prNumber} without providing replacement data or Pre-Proc Conference.")
                        ->toast()
                        ->position('top-end')
                        ->show();
                    return false;
                }
            }

            // For SVP/Alternative modes
            if ($this->isSvpMode($modeId)) {
                // Check if item has existing SVP data
                $hasExistingSvpData = $this->hasValue($item['rfq_no']) ||
                    $this->hasValue($item['canvass_date']) ||
                    $this->hasValue($item['abstract_of_canvass_date']);

                // Check if user is trying to clear the data (no replacement values)
                $clearingSvpData = !$this->hasValue($item['rfq_no']) &&
                    !$this->hasValue($item['canvass_date']) &&
                    !$this->hasValue($item['abstract_of_canvass_date']);

                if ($hasExistingSvpData && $clearingSvpData) {
                    LivewireAlert::title('Cannot Clear Existing Data')
                        ->error()
                        ->text("Cannot clear existing SVP data for {$prNumber} without providing replacement data.")
                        ->toast()
                        ->position('top-end')
                        ->show();
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Check if user has permission to modify successful bids
     * Users without edit permission cannot modify items with post-procurement data
     *
     * @return bool True if user can modify or items don't have post data
     */
    private function canModifySuccessfulBids(): bool
    {
        // Check if user has permission to modify items with post-procurement data
        if (!auth()->user()->can('edit_mode::of::procurement')) {
            foreach ($this->form['items'] as $item) {
                $biddingResult = $item['bidding_result'] ?? null;

                // If any item is SUCCESSFUL, check for post-procurement data
                if ($biddingResult === 'SUCCESSFUL') {
                    $hasPostData = PostProcurement::where('ref_id', $this->procID)->exists();

                    if ($hasPostData) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    protected function saveRelatedSchedules(
        $parentModel,
        array $itemData,
        bool &$isScheduleAdded,
        bool &$isScheduleUpdated
    ): void {
        $modeId = $itemData['mode_of_procurement_id'];
        $refId = $this->procID;
        $parentUid = $parentModel->uid;

        $matchCriteria = [
            'ref_id' => $refId,
            'mop_uid' => $parentUid
        ];

        $checkStatus = function ($model) use (&$isScheduleAdded, &$isScheduleUpdated) {
            if ($model->wasRecentlyCreated) {
                $isScheduleAdded = true;
            } elseif ($model->wasChanged()) {
                $isScheduleUpdated = true;
            }
        };

        if ($this->isCompetitiveBidding($modeId)) {
            $biddingFields = [
                $itemData['bidding_number'] ?? null,
                $itemData['ib_number'] ?? null,
                $itemData['philgeps_posting_ref_no'] ?? null,
                $itemData['ads_post_ib'] ?? null,
                $itemData['pre_proc_conference'] ?? null,
                $itemData['pre_bid_conf'] ?? null,
                $itemData['eligibility_check'] ?? null,
                $itemData['sub_open_bids'] ?? null,
                $itemData['bid_evaluation_date'] ?? null,
                $itemData['post_qualification_date'] ?? null,
                $itemData['bidding_date'] ?? null,
                $itemData['bidding_result'] ?? null,
            ];

            $hasBiddingData = $this->hasAnyValue($biddingFields);
            $existingBidSchedule = BidSchedule::where($matchCriteria)->first();

            if ($hasBiddingData || $existingBidSchedule) {
                if (!$hasBiddingData && $existingBidSchedule) {
                    return;
                }

                if (!$existingBidSchedule) {
                    $relatedMopUids = MopLot::where('procID', $refId)
                        ->where('mode_of_procurement_id', $modeId)
                        ->pluck('uid');

                    $count = BidSchedule::where('ref_id', $refId)
                        ->whereIn('mop_uid', $relatedMopUids)
                        ->count();

                    $uid = $parentUid . '-' . ($count + 1);
                } else {
                    // If existing record has no uid, generate one
                    if (empty($existingBidSchedule->uid)) {
                        $relatedMopUids = MopLot::where('procID', $refId)
                            ->where('mode_of_procurement_id', $modeId)
                            ->pluck('uid');

                        $count = BidSchedule::where('ref_id', $refId)
                            ->whereIn('mop_uid', $relatedMopUids)
                            ->count();

                        $uid = $parentUid . '-' . ($count + 1);
                    } else {
                        $uid = $existingBidSchedule->uid;
                    }
                }

                $model = BidSchedule::updateOrCreate(
                    $matchCriteria,
                    [
                        'uid' => $uid,
                        'ref_id' => $refId,
                        'mop_uid' => $parentUid,
                        'bidding_number' => $itemData['bidding_number'] ?? null,
                        'ib_number' => $itemData['ib_number'] ?? null,
                        'philgeps_posting_ref_no' => $itemData['philgeps_posting_ref_no'] ?? null,
                        'ads_post_ib' => $this->nullableDate($itemData['ads_post_ib'] ?? null),
                        'pre_proc_conference' => $this->nullableDate($itemData['pre_proc_conference'] ?? null),
                        'list_invited_observers' => $itemData['list_invited_observers'] ?? null,
                        'obsrvr_prebid_conf' => $this->nullableDate($itemData['obsrvr_prebid_conf'] ?? null),
                        'obsrvr_eligibility' => $this->nullableDate($itemData['obsrvr_eligibility'] ?? null),
                        'obsrvr_sub_open_of_bid' => $this->nullableDate($itemData['obsrvr_sub_open_of_bid'] ?? null),
                        'obsrvr_bid' => $this->nullableDate($itemData['obsrvr_bid'] ?? null),
                        'obsrvr_post_qual' => $this->nullableDate($itemData['obsrvr_post_qual'] ?? null),
                        'pre_bid_conf' => $this->nullableDate($itemData['pre_bid_conf'] ?? null),
                        'eligibility_check' => $this->nullableDate($itemData['eligibility_check'] ?? null),
                        'sub_open_bids' => $this->nullableDate($itemData['sub_open_bids'] ?? null),
                        'bid_evaluation_date' => $this->nullableDate($itemData['bid_evaluation_date'] ?? null),
                        'post_qualification_date' => $this->nullableDate($itemData['post_qualification_date'] ?? null),
                        'bidding_result' => $itemData['bidding_result'] ?? null,
                        'resolution_number_mop' => $itemData['resolution_number_mop'] ?? null,
                    ]
                );
                $checkStatus($model);
            }
        }

        if ($this->isSvpMode($modeId)) {
            // Save SVP fields to PrSvp for SVP modes
            $svpFields = [
                $itemData['philgeps_posting_ref_no'] ?? null,
                $itemData['ads_post_ib'] ?? null,
                $itemData['resolution_number_mop'] ?? null,
                $itemData['rfq_no'] ?? null,
                $itemData['canvass_date'] ?? null,
                $itemData['date_returned_of_canvass'] ?? null,
                $itemData['abstract_of_canvass_date'] ?? null,
            ];

            $hasSvpData = $this->hasAnyValue($svpFields);
            $existingSvp = PrSvp::where($matchCriteria)->first();

            if ($hasSvpData || $existingSvp) {
                if (!$hasSvpData && $existingSvp) {
                    return;
                }

                if (!$existingSvp) {
                    $relatedMopUids = MopLot::where('procID', $refId)
                        ->where('mode_of_procurement_id', $modeId)
                        ->pluck('uid');

                    $count = PrSvp::where('ref_id', $refId)
                        ->whereIn('mop_uid', $relatedMopUids)
                        ->count();

                    $uid = $parentUid . '-' . ($count + 1);
                } else {
                    // If existing record has no uid, generate one
                    if (empty($existingSvp->uid)) {
                        $relatedMopUids = MopLot::where('procID', $refId)
                            ->where('mode_of_procurement_id', $modeId)
                            ->pluck('uid');

                        $count = PrSvp::where('ref_id', $refId)
                            ->whereIn('mop_uid', $relatedMopUids)
                            ->count();

                        $uid = $parentUid . '-' . ($count + 1);
                    } else {
                        $uid = $existingSvp->uid;
                    }
                }

                $model = PrSvp::updateOrCreate(
                    $matchCriteria,
                    [
                        'uid' => $uid,
                        'ref_id' => $refId,
                        'mop_uid' => $parentUid,
                        'philgeps_posting_ref_no' => $itemData['philgeps_posting_ref_no'] ?? null,
                        'ads_post_ib' => $this->nullableDate($itemData['ads_post_ib'] ?? null),
                        'resolution_number_mop' => $itemData['resolution_number_mop'] ?? null,
                        'rfq_no' => $itemData['rfq_no'] ?? null,
                        'canvass_date' => $this->nullableDate($itemData['canvass_date'] ?? null),
                        'date_returned_of_canvass' => $this->nullableDate($itemData['date_returned_of_canvass'] ?? null),
                        'abstract_of_canvass_date' => $this->nullableDate($itemData['abstract_of_canvass_date'] ?? null),
                    ]
                );
                $checkStatus($model);
            }
        }
    }

    // ============================================================================
    // FIX #9: IMPROVED POST-PROCUREMENT SAVE LOGIC WITH CLEAR VALIDATION
    // ============================================================================
    public function savePost()
    {
        // First check if Post tab should be accessible
        if (!$this->isPostAvailable) {
            LivewireAlert::title('Invalid Action')
                ->error()
                ->text('Cannot save post-procurement data. Prerequisites not met.')
                ->toast()
                ->position('top-end')
                ->show();
            return;
        }

        $postFields = [
            $this->resolutionAwardNumber,
            $this->resolutionAwardDate,
            $this->noticeOfAwardNumber,
            $this->noticeOfAward,
            $this->awardedAmount,
            $this->philgepsNoticeOfAwardNo,
            $this->philgepsPostingOfAward,
            $this->supplier_id,
            $this->dateReceiptOfSupplierNoa,
        ];


        $hasAnyPostData = $this->hasAnyValue($postFields);

        // Define base validation rules (all nullable by default)
        $rules = [
            'resolutionAwardNumber' => 'nullable|string|max:255',
            'resolutionAwardDate' => 'nullable|date',
            'noticeOfAwardNumber' => 'nullable|string|max:255',
            'noticeOfAward' => 'nullable|date',
            'awardedAmount' => ['nullable', 'regex:/^[0-9,]+\.?[0-9]{0,2}$/'], // Accepts: 1234.56 or 1,234.56
            'philgepsNoticeOfAwardNo' => 'nullable|string|max:255',
            'philgepsPostingOfAward' => 'nullable|date',
            'supplier_id' => 'nullable|integer|exists:suppliers,id',
            'dateReceiptOfSupplierNoa' => 'nullable|date',
        ];

        // FIX #9: If ANY field has data, make Resolution Award Number and Date REQUIRED
        if ($hasAnyPostData) {
            $rules['resolutionAwardNumber'] = 'required|string|max:255';
            $rules['resolutionAwardDate'] = 'required|date';
        }

        $attributes = [
            'resolutionAwardNumber' => 'Resolution Award Number',
            'resolutionAwardDate' => 'Resolution Award Date',
            'noticeOfAwardNumber' => 'Notice of Award Number',
            'noticeOfAward' => 'Notice of Award Date',
            'awardedAmount' => 'Awarded Amount',
            'philgepsNoticeOfAwardNo' => 'PhilGEPS Notice of Award Number',
            'philgepsPostingOfAward' => 'PhilGEPS Posting of Award',
            'supplier_id' => 'Supplier',
            'dateReceiptOfSupplierNoa' => 'Date Receipt of Supplier (NOA)',
        ];

        // FIX #9: Custom error messages for better UX
        $messages = [
            'resolutionAwardNumber.required' => 'Resolution Award Number is required when entering post-procurement data.',
            'resolutionAwardDate.required' => 'Resolution Award Date is required when entering post-procurement data.',
            'awardedAmount.regex' => 'Awarded Amount must be a valid number with up to 2 decimal places.',
        ];

        try {
            $this->validate($rules, $messages, $attributes);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errorMessages = $e->validator->errors()->all();
            $errorString = implode(' ', $errorMessages);

            LivewireAlert::title('Validation Failed')
                ->error()
                ->text($errorString)
                ->toast()->position('top-end')->show();

            return; // Exit early on validation failure
        }

        $isAdded = false;
        $isUpdated = false;

        try {
            DB::transaction(function () use (&$isAdded, &$isUpdated) {
                $data = [
                    'ref_id' => $this->procID,
                    'resolution_award_number' => $this->resolutionAwardNumber,
                    'resolution_award_date' => $this->resolutionAwardDate,
                    'notice_of_award_number' => $this->noticeOfAwardNumber,
                    'notice_of_award' => $this->noticeOfAward,
                    'awarded_amount' => $this->cleanAmount($this->awardedAmount),
                    'philgeps_notice_of_award_no' => $this->philgepsNoticeOfAwardNo,
                    'philgeps_posting_of_award' => $this->nullableDate($this->philgepsPostingOfAward),
                    'supplier_id' => $this->supplier_id,
                    'date_receipt_of_supplier_noa' => $this->nullableDate($this->dateReceiptOfSupplierNoa),
                ];

                $postModel = PostProcurement::updateOrCreate(
                    ['ref_id' => $this->procID],
                    $data
                );

                if ($postModel->wasRecentlyCreated) {
                    $isAdded = true;
                } elseif ($postModel->wasChanged()) {
                    $isUpdated = true;
                }
            });
        } catch (\Exception $e) {
            \Log::error('Post-Procurement save failed', [
                'procID' => $this->procID,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            LivewireAlert::title('Save Failed')
                ->error()
                ->text('Failed to save post-procurement data. Please try again.')
                ->toast()
                ->position('top-end')
                ->show();

            return;
        }

        if ($isAdded) {
            LivewireAlert::title('Post-Procurement Added!')
                ->success()
                ->text('The procurement award details have been saved.')
                ->toast()
                ->position('top-end')
                ->show();
        } elseif ($isUpdated) {
            LivewireAlert::title('Post-Procurement Updated!')
                ->success()
                ->text('The procurement award details have been updated.')
                ->toast()
                ->position('top-end')
                ->show();
        } else {
            LivewireAlert::title('No Changes')
                ->info()
                ->text('Post-procurement details remain unchanged.')
                ->toast()
                ->position('top-end')
                ->show();
        }

        // Reload data to reflect changes from database
        $this->autoUpdateStageForSvpMode();
        $this->autoUpdateStageForCompetitiveBiddingMode();
        $this->mount($this->procurement);
    }

    public function save()
    {
        if ($this->activeTab == 2) {
            if (!$this->isPostAvailable) {
                LivewireAlert::title('Cannot Save')
                    ->error()
                    ->text('Complete Mode of Procurement details in Tab 1 first.')
                    ->toast()
                    ->position('top-end')
                    ->show();
                $this->activeTab = 1;
                return;
            }
            $this->savePost();
        } else {
            $this->saveTab1();
        }

        // Note: mount() is called within saveTab1() and savePost() after successful save
        // Don't reload here to preserve user input when validation fails
    }

    public function editHistoryItem($index): void
    {
        $totalItems = count($this->form['items']);
        $originalIndex = $totalItems - 1 - $index;

        if (!isset($this->form['items'][$originalIndex])) {
            LivewireAlert::title('Error')
                ->error()
                ->text('History record not found.')
                ->toast()
                ->position('top-end')
                ->show();
            return;
        }

        $item = $this->form['items'][$originalIndex];
        $biddingResult = $item['bidding_result'] ?? null;
        $hasPostData = PostProcurement::where('ref_id', $this->procID)->exists();
        $canEditMop = auth()->user()->can('edit_mode::of::procurement');

        if ($biddingResult === 'SUCCESSFUL' && $hasPostData && !$canEditMop) {
            LivewireAlert::title('Permission Denied')
                ->error()
                ->text('Cannot edit SUCCESSFUL bidding records when post-procurement data exists. This requires special permission.')
                ->toast()
                ->position('top-end')
                ->show();
            return;
        }

        $this->editingIndex = $originalIndex;
        $this->editingItem = $this->form['items'][$originalIndex];
        $this->showModal = true;
    }

    public function getIsPostActiveProperty(): bool
    {
        $post = PostProcurement::where('ref_id', $this->procID)->first();
        return $post !== null;
    }

    /**
     * Check if all required Post Procurement fields are filled
     * Enables "Forward to PMU" button when all 6 core fields are complete
     *
     * Required fields (excludes PhilGEPS - handled in Tab 1):
     * - Resolution Award Number
     * - Resolution Award Date
     * - Notice of Award Number
     * - Notice of Award Date
     * - Awarded Amount
     * - Supplier
     * - Date Receipt of Supplier (NOA)
     */
    public function getCanForwardToPmuProperty(): bool
    {
        // Check if data exists in database (not just form)
        $post = PostProcurement::where('ref_id', $this->procID)->first();

        if (!$post) {
            return false;
        }

        // Check all 7 required fields are filled in database
        return $this->hasValue($post->resolution_award_number) &&
            $this->hasValue($post->resolution_award_date) &&
            $this->hasValue($post->notice_of_award_number) &&
            $this->hasValue($post->notice_of_award) &&
            $this->hasValue($post->awarded_amount) &&
            $this->hasValue($post->supplier_id) &&
            $this->hasValue($post->date_receipt_of_supplier_noa);
    }

    /**
     * Check if this procurement has already been forwarded to PMU (stage 7 exists with actual_date_forwarded)
     */
    public function getIsForwardedToPmuProperty(): bool
    {
        return PrLotPrstage::where('procID', $this->procID)
            ->where('pr_stage_id', 7)
            ->whereNotNull('actual_date_forwarded')
            ->exists();
    }

    /**
     * Get the forwarded date from the PMU stage record
     */
    public function getForwardedToPmuDateProperty(): ?string
    {
        $stage = PrLotPrstage::where('procID', $this->procID)
            ->where('pr_stage_id', 7)
            ->orderBy('created_at', 'desc')
            ->first();

        return $stage?->actual_date_forwarded;
    }

    public function updateHistoryItem(): void
    {
        if ($this->editingIndex === null || !isset($this->form['items'][$this->editingIndex])) {
            LivewireAlert::title('Error')
                ->error()
                ->text('Unable to update record.')
                ->toast()
                ->position('top-end')
                ->show();
            return;
        }

        $this->form['items'][$this->editingIndex] = $this->editingItem;

        $this->scheduleValidationErrors = [];
        if (!$this->validateSchedules()) {
            $errorMessage = implode(' ', $this->scheduleValidationErrors);
            LivewireAlert::title('Validation Failed')
                ->error()
                ->text($errorMessage)
                ->toast()
                ->position('top-end')
                ->show();

            // Don't reload data - keep modal open with user's input so they can fix errors
            return;
        }

        $this->saveTab1();
        $this->closeModal();
    }
    public function itemHasSchedule(array $item): bool
    {
        $scheduleFields = [
            // Bidding fields
            $item['bidding_number'] ?? null,
            $item['ib_number'] ?? null,
            $item['philgeps_posting_ref_no'] ?? null,
            $item['pre_proc_conference'] ?? null,
            $item['ads_post_ib'] ?? null,
            $item['pre_bid_conf'] ?? null,
            $item['eligibility_check'] ?? null,
            $item['sub_open_bids'] ?? null,
            $item['bid_evaluation_date'] ?? null,
            $item['post_qualification_date'] ?? null,
            $item['bidding_date'] ?? null,
            $item['bidding_result'] ?? null,
            // Other Modes
            $item['resolution_number_mop'] ?? null,
            $item['rfq_no'] ?? null,
            $item['canvass_date'] ?? null,
            $item['date_returned_of_canvass'] ?? null,
            $item['abstract_of_canvass_date'] ?? null,
        ];

        return $this->hasAnyValue($scheduleFields);
    }
    public function canAddRebidForItem(array $item, ?int $modeId): bool
    {
        if ($this->isSvpMode($modeId)) {
            return false;
        }

        $bidResult = $item['bidding_result'] ?? '';

        $hasBiddingData = $this->hasValue($item['ib_number']) &&
            $this->hasValue($item['bidding_number']) &&
            $this->hasValue($item['sub_open_bids']);

        $hasPreProcConference = $this->hasValue($item['pre_proc_conference']);

        return $this->isPendingMode($modeId) ||
            (($hasBiddingData || $hasPreProcConference) &&
                $bidResult === 'UNSUCCESSFUL' &&
                !$this->isPostAvailable);
    }
    public function closeModal(): void
    {
        $this->showModal = false;
        $this->editingItem = null;
        $this->editingIndex = null;
    }

    /**
     * Open the Forward to PMU modal
     * Pre-fills with existing date if already forwarded
     */
    public function openForwardModal(): void
    {
        // Check if already forwarded and pre-fill date
        $existingStage = PrLotPrstage::where('procID', $this->procID)
            ->where('pr_stage_id', 7)
            ->first();

        if ($existingStage && $existingStage->actual_date_forwarded) {
            $this->actualDateForwarded = Carbon::parse($existingStage->actual_date_forwarded)->setTimezone('Asia/Manila')->format('Y-m-d\TH:i');
        } else {
            $this->actualDateForwarded = now('Asia/Manila')->format('Y-m-d\TH:i'); // Default to now (PHT)
        }

        $this->showForwardModal = true;
    }

    /**
     * Close the Forward to PMU modal
     */
    public function closeForwardModal(): void
    {
        $this->showForwardModal = false;
        $this->actualDateForwarded = null;
    }

    /**
     * Forward procurement to PMU (Stage 7)
     * Creates new stage row or updates existing stage 7 row with actual_date_forwarded
     * Stage history tracks the previous stage ID
     */
    public function forwardToPmu(): void
    {
        // Validate actual date
        $this->validate([
            'actualDateForwarded' => 'required|date'
        ], [
            'actualDateForwarded.required' => 'Please enter the actual date and time forwarded.',
            'actualDateForwarded.date' => 'Please enter a valid date and time.'
        ]);

        // Convert user-entered Asia/Manila datetime to UTC for storage
        $utcDateForwarded = Carbon::createFromFormat('Y-m-d\TH:i', $this->actualDateForwarded, 'Asia/Manila')
            ->utc()
            ->format('Y-m-d H:i:s');

        try {
            DB::transaction(function () use ($utcDateForwarded) {
                // Get all stage records for this procurement, ordered by latest first
                $latestStage = PrLotPrstage::where('procID', $this->procID)
                    ->orderBy('created_at', 'desc')
                    ->orderBy('id', 'desc')
                    ->first();

                if ($latestStage) {
                    $latestStageId = $latestStage->pr_stage_id;

                    if ($latestStageId == 7) {
                        // Latest stage IS 7: UPDATE existing row
                        // Get the stage before this one (second latest)
                        $previousStage = PrLotPrstage::where('procID', $this->procID)
                            ->where('id', '<', $latestStage->id)
                            ->orderBy('created_at', 'desc')
                            ->orderBy('id', 'desc')
                            ->first();

                        $stageHistory = $previousStage ? (string) $previousStage->pr_stage_id : null;

                        $latestStage->update([
                            'stage_history' => $stageHistory,
                            'actual_date_forwarded' => $utcDateForwarded,
                        ]);

                        // Update PMU record with new date_forwarded
                        $post = PostProcurement::where('ref_id', $this->procID)->first();
                        if ($post && $this->hasValue($post->notice_of_award_number)) {
                            $pmu = \App\Models\Pmu::updateOrCreate(
                                ['notice_of_award_number' => $post->notice_of_award_number],
                                ['date_forwarded' => $utcDateForwarded]
                            );

                            $poDate = $this->calculatePoDate($post->date_receipt_of_supplier_noa);
                            if ($poDate) {
                                PmuPo::updateOrCreate(
                                    ['pmu_id' => $pmu->id, 'ref_id' => $this->procID],
                                    ['po_date_deadline' => $poDate]
                                );
                            }
                        }

                        LivewireAlert::title('Date Updated!')
                            ->success()
                            ->text('Actual date forwarded has been updated for this PMU record.')
                            ->toast()
                            ->position('top-end')
                            ->show();
                    } else {
                        // Latest stage is NOT 7: CREATE new row with stage 7
                        PrLotPrstage::create([
                            'procID' => $this->procID,
                            'pr_stage_id' => 7, // PMU Stage
                            'stage_history' => (string) $latestStageId, // Previous stage
                            'actual_date_forwarded' => $utcDateForwarded,
                        ]);

                        PrLotRemark::create([
                            'procID' => $this->procID,
                            'remarks_id' => 1, // Awarded
                            'notes' => null,
                            'remark_history' => now(),
                        ]);

                        // Insert/update PMU record
                        $post = PostProcurement::where('ref_id', $this->procID)->first();
                        if ($post && $this->hasValue($post->notice_of_award_number)) {
                            $pmu = \App\Models\Pmu::updateOrCreate(
                                ['notice_of_award_number' => $post->notice_of_award_number],
                                ['date_forwarded' => $utcDateForwarded]
                            );

                            $poDate = $this->calculatePoDate($post->date_receipt_of_supplier_noa);
                            if ($poDate) {
                                PmuPo::updateOrCreate(
                                    ['pmu_id' => $pmu->id, 'ref_id' => $this->procID],
                                    ['po_date_deadline' => $poDate]
                                );
                            }
                        }

                        LivewireAlert::title('Forwarded to PMU!')
                            ->success()
                            ->text('Procurement has been successfully forwarded to PMU.')
                            ->toast()
                            ->position('top-end')
                            ->show();
                    }
                } else {
                    // No existing stages: Create first stage as PMU
                    PrLotPrstage::create([
                        'procID' => $this->procID,
                        'pr_stage_id' => 7, // PMU Stage
                        'stage_history' => null, // No previous stage
                        'actual_date_forwarded' => $utcDateForwarded,
                    ]);

                    PrLotRemark::create([
                        'procID' => $this->procID,
                        'remarks_id' => 1, // Awarded
                        'notes' => null,
                        'remark_history' => now(),
                    ]);

                    // Insert/update PMU record
                    $post = PostProcurement::where('ref_id', $this->procID)->first();
                    if ($post && $this->hasValue($post->notice_of_award_number)) {
                        $pmu = \App\Models\Pmu::updateOrCreate(
                            ['notice_of_award_number' => $post->notice_of_award_number],
                            ['date_forwarded' => $utcDateForwarded]
                        );

                        $poDate = $this->calculatePoDate($post->date_receipt_of_supplier_noa);
                        if ($poDate) {
                            PmuPo::updateOrCreate(
                                ['pmu_id' => $pmu->id, 'ref_id' => $this->procID],
                                ['po_date_deadline' => $poDate]
                            );
                        }
                    }

                    LivewireAlert::title('Forwarded to PMU!')
                        ->success()
                        ->text('Procurement has been successfully forwarded to PMU.')
                        ->toast()
                        ->position('top-end')
                        ->show();
                }
            });

            $this->closeForwardModal();
            $this->mount($this->procurement); // Reload data
        } catch (\Exception $e) {
            \Log::error('Forward to PMU failed', [
                'procID' => $this->procID,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            LivewireAlert::title('Forward Failed')
                ->error()
                ->text('Failed to forward to PMU. Please try again.')
                ->toast()
                ->position('top-end')
                ->show();
        }
    }

    private function nullableDate($value)
    {
        return $this->hasValue($value) ? $value : null;
    }

    /**
     * Calculate PO date: 10th calendar day from Date Receipt of Supplier (NOA).
     * If the result falls on a Saturday, move back 1 day to Friday.
     * If the result falls on a Sunday, move back 2 days to Friday.
     */
    private function calculatePoDate(?string $dateReceiptOfSupplierNoa): ?string
    {
        if (!$this->hasValue($dateReceiptOfSupplierNoa)) {
            return null;
        }

        $date = Carbon::parse($dateReceiptOfSupplierNoa)->addDays(10);

        if ($date->dayOfWeek === Carbon::SUNDAY) {
            $date->subDays(2); // move back to Friday
        } elseif ($date->dayOfWeek === Carbon::SATURDAY) {
            $date->subDays(1); // move back to Friday
        }

        return $date->format('Y-m-d');
    }

    /**
     * Clean and convert formatted amount string to float
     * Removes commas from Alpine.js money mask format
     */
    private function cleanAmount($value): ?float
    {
        if (!$this->hasValue($value)) {
            return null;
        }

        // Remove commas and convert to float
        $cleaned = str_replace(',', '', (string) $value);
        return (float) $cleaned;
    }

    /**
     * Format amount with comma separators and 2 decimal places
     * Used when loading from database for display
     */
    private function formatAmount($value): ?string
    {
        if (!$this->hasValue($value)) {
            return null;
        }

        return number_format((float) $value, 2, '.', ',');
    }

    /**
     * Centralized writer for every automatic stage transition.
     *
     * Guarantees, for all auto-stage updates:
     *   - Stage 7 (Forwarded to PMU) is never overridden (explicit human action).
     *   - No duplicate record when the target already equals the current stage.
     *   - A placeholder/mode stage (For Pre-Procurement, For Bidding Schedule,
     *     For Other Mode of Procurement) can NEVER replace a data-derived stage.
     *     Data-derived stages stay fully flexible, so a user correcting fields
     *     (clearing the wrong one, filling another) moves the stage up or down to
     *     match what is actually entered.
     *
     * @param int  $targetStageId  Stage implied by the current mode/data.
     * @param bool $isPlaceholder  True for mode-derived (placeholder) stages.
     * @param bool $allowDowngrade False to block moving to a lower stage id — used
     *                             by partial recomputers (e.g. bidding) that only
     *                             know one stage in their hierarchy.
     */
    private function recordAutoStage(int $targetStageId, bool $isPlaceholder, bool $allowDowngrade = true): void
    {
        $latestStage = PrLotPrstage::where('procID', $this->procID)
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        // Never override an explicit Forward to PMU action
        if ($latestStage && $latestStage->pr_stage_id == 7) {
            return;
        }

        $currentStageId = $latestStage ? (int) $latestStage->pr_stage_id : null;

        // Already at the target – nothing to do
        if ($targetStageId === $currentStageId) {
            return;
        }

        // Placeholder/mode stages must never clobber a data-derived stage
        if (
            $isPlaceholder
            && $currentStageId !== null
            && !\in_array($currentStageId, self::PLACEHOLDER_STAGES, true)
        ) {
            return;
        }

        // Guard for recomputers that only know part of the hierarchy
        if (
            !$allowDowngrade
            && $currentStageId !== null
            && $currentStageId > $targetStageId
        ) {
            return;
        }

        DB::transaction(function () use ($targetStageId, $currentStageId) {
            PrLotPrstage::create([
                'procID' => $this->procID,
                'pr_stage_id' => $targetStageId,
                'stage_history' => $currentStageId ? (string) $currentStageId : null,
            ]);

            PrLotRemark::create([
                'procID' => $this->procID,
                'remarks_id' => 3, // Ongoing
                'notes' => null,
                'remark_history' => now(),
            ]);
        });
    }

    /**
     * Set the placeholder stage implied purely by the currently selected mode.
     *
     * This only establishes the starting stage for a mode; recordAutoStage()
     * guarantees it can never overwrite a stage already derived from real data.
     *
     * Mode mapping (placeholder stages):
     *   Mode 2            (Competitive Bidding) → Stage 3  (For Pre-Procurement)
     *   Mode 3-6          (Competitive Bidding) → Stage 31 (For Bidding Schedule)
     *   Mode 7-24         (SVP / Alternative)   → Stage 32 (For Other Mode of Procurement)
     *
     * All SVP/alternative modes — including 7 (Direct Contracting) and
     * 19 (Lease of Real Property and Venue) — are data-driven: they start at the
     * generic stage 32 and advance only once real data is entered.
     */
    private function autoUpdateStageForModeFromPending(): void
    {
        // Get the current item — highest mode_order
        $sortedItems = $this->form['items'];
        usort($sortedItems, fn($a, $b) => ($b['mode_order'] ?? 0) <=> ($a['mode_order'] ?? 0));

        if (empty($sortedItems)) {
            return;
        }

        $latestModeId = (int) ($sortedItems[0]['mode_of_procurement_id'] ?? 0);

        // Determine the placeholder stage implied purely by the selected mode
        if ($latestModeId === 2) {
            $targetStageId = 3;            // For Pre-Procurement
        } elseif ($this->isCompetitiveBidding($latestModeId)) {
            $targetStageId = 31;           // For Bidding Schedule
        } elseif ($this->isSvpMode($latestModeId)) {
            $targetStageId = 32;           // For Other Mode of Procurement
        } else {
            return; // Mode is still pending (1) or unrecognised
        }

        $this->recordAutoStage($targetStageId, true);
    }

    /**
     * Auto-update procurement stage and remarks for SVP modes after a successful save.
     *
     * Evaluates all relevant fields and applies the highest applicable stage in the
     * hierarchy below. Stage 7 (Forwarded to PMU) is never overridden here.
     *
     * Hierarchy (low → high):
     *   Stage 15 – Resolution to Recommend for Other Mode  (resolution_number_mop filled)
     *   Stage 29 – Canvass                                 (canvass_date filled)
     *   Stage 16 – Abstract of Canvass                     (abstract_of_canvass_date filled)
     *   Stage  8 – Resolution to Award (other mode)        (resolutionAwardNumber + resolutionAwardDate filled)
     *   Stage 17 – NOA for Approval of HoPE                (noticeOfAward + supplier_id filled)
     *   Stage  7 – Forwarded to PMU                        (handled separately via forwardToPmu)
     */
    private function autoUpdateStageForSvpMode(): void
    {
        // Find the current (latest) SVP item in form data (last one with highest mode_order)
        $currentItem = null;
        foreach ($this->form['items'] as $item) {
            $modeId = $item['mode_of_procurement_id'] ?? null;
            if ($this->isSvpMode($modeId)) {
                $currentItem = $item;
            }
        }

        if (!$currentItem) {
            return; // No SVP mode item – nothing to auto-stage
        }

        // Walk up the hierarchy; each matching condition overwrites the previous,
        // so $targetStageId ends up as the highest applicable stage for the data
        // currently entered. Removing a field lets the stage fall back to the
        // next-highest filled field (flexible corrections).
        $targetStageId = null;

        // Stage 15 – Resolution to Recommend for Other Mode
        if ($this->hasValue($currentItem['resolution_number_mop'] ?? null)) {
            $targetStageId = 15;
        }

        // Stage 29 – Canvass
        if ($this->hasValue($currentItem['canvass_date'] ?? null)) {
            $targetStageId = 29;
        }

        // Stage 16 – Abstract of Canvass
        if ($this->hasValue($currentItem['abstract_of_canvass_date'] ?? null)) {
            $targetStageId = 16;
        }

        // Stage 8 – Resolution to Award (other mode): both fields required
        if ($this->hasValue($this->resolutionAwardNumber) && $this->hasValue($this->resolutionAwardDate)) {
            $targetStageId = 8;
        }

        // Stage 17 – NOA for Approval of HoPE: both fields required
        if ($this->hasValue($this->noticeOfAward) && !empty($this->supplier_id)) {
            $targetStageId = 17;
        }

        if ($targetStageId === null) {
            return; // No applicable stage determined
        }

        // Data-derived write: fully flexible so corrections can move the stage up
        // or down to match the current data. recordAutoStage() still guards
        // stage 7 and skips no-op repeats.
        $this->recordAutoStage($targetStageId, false);
    }


    private function autoUpdateStageForCompetitiveBiddingMode(): void
    {
        // Find the latest Competitive Bidding (modes 2-6) item
        $currentItem = null;
        foreach ($this->form['items'] as $item) {
            if ($this->isCompetitiveBidding((int) ($item['mode_of_procurement_id'] ?? 0))) {
                $currentItem = $item;
            }
        }

        if (!$currentItem) {
            return;
        }

        $targetStageId = null;

        // Stage 4 – For Pre-Bid Conference
        if ($this->hasValue($currentItem['pre_bid_conf'] ?? null)) {
            $targetStageId = 4;
        }

        if ($targetStageId === null) {
            return;
        }

        // This recomputer only knows the Pre-Bid stage, so it must not downgrade a
        // PR that has already advanced past it (allowDowngrade = false).
        $this->recordAutoStage($targetStageId, false, false);
    }

    public function cancel()
    {
        return redirect()->route('mode-of-procurement.index', $this->queryParams);
    }

    public function render()
    {
        return view('livewire.mode-of-procurement.mode-of-procurement-per-lot-page', [
            'modeOfProcurements' => $this->modeOfProcurements,
            'suppliers' => $this->suppliers,
            'isPostActive' => $this->isPostActive,
        ]);
    }
}
