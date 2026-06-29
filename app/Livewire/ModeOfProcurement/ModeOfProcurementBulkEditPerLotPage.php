<?php

namespace App\Livewire\ModeOfProcurement;

use App\Models\BidSchedule;
use App\Models\ModeOfProcurement;
use App\Models\PostProcurement;
use App\Models\PrLotPrstage;
use App\Models\PrLotRemark;
use App\Models\PmuPo;
use App\Models\PrSvp;
use Illuminate\Support\Collection;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Attributes\Title;
use Livewire\Component;
use App\Models\Procurement;
use App\Models\MopLot;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

#[Title('Mode of Procurement | PMIS')]
class ModeOfProcurementBulkEditPerLotPage extends Component
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

    public array $items = [];
    public Collection $modeOfProcurements;
    public array $procurementIds = [];
    public bool $showHistory = false;
    public ?string $historyForKey = null;
    public array $bulkEdit = [];
    public array $scheduleValidationErrors = [];
    public array $postBulkEditErrors = [];
    public int $activeTab = 1;
    public bool $showAddForm = false;
    public array $queryParams = [];

    // Selection functionality
    public array $selectedItems = [];
    public bool $selectAll = false;
    public bool $showBulkEditModal = false;

    // Post-Procurement Selection & Bulk Edit
    public array $selectedPostItems = [];
    public bool $selectAllPost = false;
    public bool $showPostBulkEditModal = false;
    public array $postBulkEditData = [];

    // Forward to PMU Modal
    public bool $showForwardModal = false;
    public ?string $actualDateForwarded = null;

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

    /**
     * Initialize component with procurement IDs from query params
     * Loads necessary data and validates that items were selected
     */
    public function mount(): void
    {
        $this->queryParams = request()->query();
        $this->procurementIds = request()->query('items', []);

        if (empty($this->procurementIds)) {
            LivewireAlert::title('No Items Selected')
                ->warning()
                ->text('Please select procurement items to edit.')
                ->show();
            $this->redirect(route('mode-of-procurement.index'));
            return;
        }

        $this->modeOfProcurements = ModeOfProcurement::orderBy('id', 'asc')->get();
        $this->suppliers = \App\Models\Supplier::all();
        $this->loadProcurementData();
        $this->populateBulkEditData();
        $this->loadPostProcurementData();
    }

    /**
     * Handle select all checkbox toggle
     * Selects/deselects all current procurement items
     */
    public function updatedSelectAll($value): void
    {
        if ($value) {
            $this->selectedItems = collect($this->getCurrentItems())
                ->pluck('procID')
                ->toArray();
        } else {
            $this->selectedItems = [];
        }
        $this->dispatch('$refresh');
    }

    /**
     * Handle individual item selection changes
     * Updates select all checkbox state based on selected items
     */
    public function updatedSelectedItems(): void
    {
        $currentProcIds = collect($this->getCurrentItems())->pluck('procID')->toArray();
        $selectedUnique = array_unique($this->selectedItems);
        $this->selectAll = !empty($currentProcIds) &&
            count($selectedUnique) === count($currentProcIds) &&
            empty(array_diff($selectedUnique, $currentProcIds));
        $this->dispatch('$refresh');
    }

    /**
     * Clear all item selections
     */
    public function clearSelections(): void
    {
        $this->selectedItems = [];
        $this->selectAll = false;
    }

    public function openBulkEditModal()
    {
        if (empty($this->selectedItems)) {
            LivewireAlert::title('No Items Selected')
                ->warning()
                ->text('Please select at least one procurement to edit.')
                ->toast()
                ->position('top-end')
                ->show();
            return;
        }

        // Validate selected items before opening modal
        $validation = $this->validateBulkEditSelection();

        if (!$validation['valid']) {
            $errorMessage = implode(' ', $validation['errors']);
            LivewireAlert::title('Bulk Edit Validation Failed')
                ->error()
                ->text($errorMessage)
                ->toast()
                ->position('top-end')
                ->show();
            return;
        }

        // Pre-validate ABC threshold consistency (ONLY for SVP modes 7-24)
        $commonMode = $validation['commonMode'] ?? null;
        if ($commonMode && in_array($commonMode, self::SVP_MODES)) {
            if (!$this->validateBulkEditAbcThreshold()) {
                return; // Error already shown in method
            }
        }

        $this->populateBulkEditData();
        $this->showBulkEditModal = true;
    }

    /**
     * Close bulk edit modal and clear schedule fields
     */
    public function closeBulkEditModal(): void
    {
        $this->showBulkEditModal = false;
        $this->clearBulkEditScheduleFields();
    }

    /**
     * Validate bulk edit selection before opening modal
     * Ensures all selected items have same mode and identical field values
     *
     * @return array ['valid' => bool, 'errors' => array, 'commonMode' => int|null, 'prNumbers' => array, 'amountThreshold' => string|null]
     */
    private function validateBulkEditSelection(): array
    {
        $errors = [];
        $modes = [];
        $scheduleData = [];
        $prNumbers = [];
        $amounts = [];

        // Get CURRENT items only (highest mode_order) that match selected IDs
        $currentItems = $this->getCurrentItems();
        $selectedItemsData = collect($currentItems)
            ->filter(function ($item) {
                return in_array($item['procID'], $this->selectedItems);
            })
            ->values();

        if ($selectedItemsData->isEmpty()) {
            return [
                'valid' => false,
                'errors' => ['No valid items found in selection.']
            ];
        }

        foreach ($selectedItemsData as $item) {
            $procId = $item['procID'];
            $prNumbers[] = $item['pr_number'];
            $modeId = $item['mode_of_procurement_id'] ?? null;

            // Track ABC amounts
            $amounts[$item['pr_number']] = $item['abc'] ?? 0;

            // Track modes
            if ($modeId) {
                if (!isset($modes[$modeId])) {
                    $modes[$modeId] = [];
                }
                $modes[$modeId][] = $item['pr_number'];
            }

            // Extract schedule data for comparison
            $scheduleData[$procId] = [
                'mode_of_procurement_id' => $modeId,
                'bidding_number' => $item['bidding_number'] ?? null,
                'ib_number' => $item['ib_number'] ?? null,
                'philgeps_posting_ref_no' => $item['philgeps_posting_ref_no'] ?? null,
                'ads_post_ib' => $item['ads_post_ib'] ?? null,
                'pre_proc_conference' => $item['pre_proc_conference'] ?? null,
                'list_invited_observers' => $item['list_invited_observers'] ?? null,
                'obsrvr_prebid_conf' => $item['obsrvr_prebid_conf'] ?? null,
                'obsrvr_eligibility' => $item['obsrvr_eligibility'] ?? null,
                'obsrvr_sub_open_of_bid' => $item['obsrvr_sub_open_of_bid'] ?? null,
                'obsrvr_bid' => $item['obsrvr_bid'] ?? null,
                'obsrvr_post_qual' => $item['obsrvr_post_qual'] ?? null,
                'pre_bid_conf' => $item['pre_bid_conf'] ?? null,
                'eligibility_check' => $item['eligibility_check'] ?? null,
                'sub_open_bids' => $item['sub_open_bids'] ?? null,
                'bid_evaluation_date' => $item['bid_evaluation_date'] ?? null,
                'post_qualification_date' => $item['post_qualification_date'] ?? null,
                'bidding_result' => $item['bidding_result'] ?? null,
                'resolution_number_mop' => $item['resolution_number_mop'] ?? null,
                'rfq_no' => $item['rfq_no'] ?? null,
                'canvass_date' => $item['canvass_date'] ?? null,
                'date_returned_of_canvass' => $item['date_returned_of_canvass'] ?? null,
                'abstract_of_canvass_date' => $item['abstract_of_canvass_date'] ?? null,
            ];
        }

        // Check if all items have the same mode (ALWAYS required)
        if (count($modes) > 1) {
            $modeDetails = [];
            foreach ($modes as $modeId => $prs) {
                $modeName = $this->modeOfProcurements->firstWhere('id', $modeId)?->modeofprocurements ?? 'Unknown';
                $modeDetails[] = "{$modeName}: " . implode(', ', $prs);
            }
            $errors[] = "Items have different modes: " . implode('; ', $modeDetails) . ". Bulk edit requires all selected PRs to have the same mode of procurement.";
        }

        if (empty($modes)) {
            $errors[] = "No valid mode selected for PRs: " . implode(', ', $prNumbers);
        }

        // Check if items have schedule data - separate items with data vs without data
        $prsWithData = [];
        $prsWithoutData = [];

        foreach ($scheduleData as $procId => $schedule) {
            $scheduleFieldsOnly = $schedule;
            unset($scheduleFieldsOnly['mode_of_procurement_id']); // Exclude mode from check

            if ($this->hasAnyValue(array_values($scheduleFieldsOnly))) {
                $prsWithData[] = $procId;
            } else {
                $prsWithoutData[] = $procId;
            }
        }

        // STRICT CHECK: If same mode but mixed (some have data, some don't), block bulk edit
        if (!empty($modes) && count($modes) === 1) {
            if (!empty($prsWithData) && !empty($prsWithoutData)) {
                $errors[] = "Data mismatch: Some items have schedule data while others do not. All selected items must either all have data or all be empty for bulk editing.";
            }
        }

        // Check if all items have identical schedule data (only when all have same mode AND all have data)
        $hasAnyScheduleData = !empty($prsWithData);

        if (!empty($modes) && count($modes) === 1 && $hasAnyScheduleData && empty($prsWithoutData)) {
            $scheduleHashes = [];
            $prNumbersByHash = [];

            foreach ($scheduleData as $procId => $schedule) {
                $hash = md5(json_encode($schedule));
                $scheduleHashes[$procId] = $hash;

                $prNumber = $selectedItemsData->firstWhere('procID', $procId)['pr_number'];
                if (!isset($prNumbersByHash[$hash])) {
                    $prNumbersByHash[$hash] = [];
                }
                $prNumbersByHash[$hash][] = $prNumber;
            }

            $uniqueHashes = array_unique($scheduleHashes);

            if (count($uniqueHashes) > 1) {
                // Find the minority group (items with different data)
                $hashCounts = array_count_values($scheduleHashes);
                arsort($hashCounts);
                $majorityHash = array_key_first($hashCounts);

                $differentPRs = [];
                foreach ($scheduleHashes as $procId => $hash) {
                    if ($hash !== $majorityHash) {
                        $prNumber = $selectedItemsData->firstWhere('procID', $procId)['pr_number'];
                        $differentPRs[] = $prNumber;
                    }
                }

                $prList = implode(', ', $differentPRs);
                $errors[] = "Field mismatch: PR" . (count($differentPRs) > 1 ? 's' : '') . " {$prList} " .
                    (count($differentPRs) > 1 ? 'have' : 'has') . " different field values from the others. Bulk edit requires all selected PRs to have identical field values.";
            }
        }

        // Check amount threshold consistency (ONLY for SVP modes 7-24)
        $below200k = [];
        $above200k = [];
        $amountThreshold = null;
        $commonMode = count($modes) === 1 ? array_key_first($modes) : null;

        // Only validate ABC threshold for SVP modes (7-24)
        if ($commonMode && in_array($commonMode, self::SVP_MODES)) {
            foreach ($amounts as $prNum => $amount) {
                if ($amount < self::ABC_THRESHOLD) {
                    $below200k[] = $prNum;
                } else {
                    $above200k[] = $prNum;
                }
            }

            if (!empty($below200k) && !empty($above200k)) {
                $errors[] = "Mixed amount thresholds: Below ₱200,000: " . implode(', ', $below200k) .
                    "; ₱200,000 and above: " . implode(', ', $above200k) . ". Bulk edit requires all PRs to have the same amount threshold for SVP modes.";
            } elseif (!empty($below200k)) {
                $amountThreshold = 'Below ₱200,000.00';
            } else {
                $amountThreshold = '₱200,000.00 and Above';
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'commonMode' => count($modes) === 1 ? array_key_first($modes) : null,
            'prNumbers' => $prNumbers,
            'amountThreshold' => $amountThreshold
        ];
    }

    /**
     * Standardized method to check if a value is considered "filled"
     * Handles: null, empty string, whitespace-only strings
     *
     * @param mixed $value The value to check
     * @return bool True if value has meaningful content
     *
     * @example hasValue(null) returns false
     * @example hasValue('') returns false
     * @example hasValue('  ') returns false
     * @example hasValue('0') returns true
     * @example hasValue(0) returns true
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
     * Check if any field in the array has a meaningful value
     *
     * @param array $fields Array of field names or values to check
     * @return bool True if at least one field has a value
     */
    private function hasAnyValue(array $fields): bool
    {
        foreach ($fields as $field) {
            if ($this->hasValue($field)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Load existing post-procurement data
     * If all PRs have identical post data, pre-fill the form
     * Otherwise, leave fields empty for manual entry
     *
     * This prevents accidental overwriting of different values across PRs
     */
    private function loadPostProcurementData(): void
    {
        $currentItems = $this->getCurrentItems();

        if (empty($currentItems)) {
            return;
        }

        // Load all post records in a single query instead of N+1 per item
        $refIds = array_column($currentItems, 'procID');
        $postRecords = \App\Models\PostProcurement::whereIn('ref_id', $refIds)->get()->keyBy('ref_id');

        if ($postRecords->isEmpty()) {
            return;
        }

        $firstPost = $postRecords->first();

        // Check if ALL PRs have identical post data
        $allIdentical = true;

        foreach ($currentItems as $item) {
            $refId = $item['procID'];
            $itemPost = $postRecords->get($refId);

            // If any PR is missing post data or has different data, don't pre-fill
            if (
                !$itemPost ||
                $itemPost->resolution_award_number !== $firstPost->resolution_award_number ||
                $itemPost->resolution_award_date !== $firstPost->resolution_award_date ||
                $itemPost->notice_of_award_number !== $firstPost->notice_of_award_number ||
                $itemPost->notice_of_award !== $firstPost->notice_of_award ||
                $itemPost->awarded_amount !== $firstPost->awarded_amount ||
                $itemPost->philgeps_notice_of_award_no !== $firstPost->philgeps_notice_of_award_no ||
                $itemPost->philgeps_posting_of_award !== $firstPost->philgeps_posting_of_award ||
                $itemPost->supplier_id !== $firstPost->supplier_id ||
                $itemPost->date_receipt_of_supplier_noa !== $firstPost->date_receipt_of_supplier_noa
            ) {
                $allIdentical = false;
                break;
            }
        }

        // Only pre-fill if all PRs have identical post data
        if ($allIdentical) {
            $this->resolutionAwardNumber = $firstPost->resolution_award_number;
            $this->resolutionAwardDate = $firstPost->resolution_award_date;
            $this->noticeOfAwardNumber = $firstPost->notice_of_award_number;
            $this->noticeOfAward = $firstPost->notice_of_award;
            $this->awardedAmount = $this->formatAmount($firstPost->awarded_amount);
            $this->philgepsNoticeOfAwardNo = $firstPost->philgeps_notice_of_award_no;
            $this->philgepsPostingOfAward = $firstPost->philgeps_posting_of_award;
            $this->supplier_id = $firstPost->supplier_id;
            $this->dateReceiptOfSupplierNoa = $firstPost->date_receipt_of_supplier_noa;
        }
    }

    /**
     * Check if item has any schedule data
     * Used to determine if mode can be changed or only schedules updated
     *
     * @param array $item The procurement item to check
     * @return bool True if item has any schedule fields filled
     */
    public function itemHasSchedule(array $item): bool
    {
        $scheduleFields = [
            'bidding_number',
            'ib_number',
            'philgeps_posting_ref_no',
            'ads_post_ib',
            'pre_proc_conference',
            'pre_bid_conf',
            'eligibility_check',
            'sub_open_bids',
            'bid_evaluation_date',
            'post_qualification_date',
            'bidding_result',
            'resolution_number_mop',
            'rfq_no',
            'canvass_date',
            'date_returned_of_canvass',
            'abstract_of_canvass_date'
        ];

        foreach ($scheduleFields as $field) {
            if ($this->hasValue($item[$field] ?? '')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validate bulk schedule data based on procurement mode
     * Different validation rules for bidding vs SVP modes
     * Includes per-PR error messages for better user feedback
     *
     * @return bool True if validation passes
     */
    private function validateBulkSchedules(): bool
    {
        $this->scheduleValidationErrors = [];
        $modeId = $this->bulkEdit['mode_of_procurement_id'] ?? null;

        // Check permission first — adds errors to $scheduleValidationErrors if blocked
        if (!$this->canModifySuccessfulBids()) {
            return false;
        }

        if (!$modeId) {
            $this->scheduleValidationErrors[] = 'Mode of Procurement is required.';
            return false;
        }

        // Get selected items for validation
        $currentItems = collect($this->getCurrentItems())
            ->whereIn('procID', $this->selectedItems)
            ->values()
            ->toArray();

        // COMPETITIVE BIDDING MODES
        if (in_array($modeId, self::BIDDING_MODES)) {
            // Validate bidding number per PR against expected sequence
            if ($this->hasValue($this->bulkEdit['bidding_number'] ?? '')) {
                $enteredBiddingNumber = (string) $this->bulkEdit['bidding_number'];

                foreach ($currentItems as $item) {
                    $prNumber = $item['pr_number'] ?? $item['procID'];
                    $mopUid = $item['mop_uid'] ?? null;

                    if (!$mopUid) {
                        continue;
                    }

                    $matchCriteria = [
                        'ref_id' => $item['procID'],
                        'mop_uid' => $mopUid,
                    ];

                    $existingBidSchedule = BidSchedule::where($matchCriteria)->first();

                    // When adding a new mode (rebid), always compute the next sequence number
                    // instead of matching the existing record's uid segment.
                    if (!$this->showAddForm && $existingBidSchedule && $this->hasValue($existingBidSchedule->uid)) {
                        // Existing record being updated: expected = last segment of saved uid
                        $uidParts = explode('-', $existingBidSchedule->uid);
                        $expectedBiddingNumber = (string) end($uidParts);
                        if ($enteredBiddingNumber !== $expectedBiddingNumber) {
                            $this->scheduleValidationErrors[] = "PR {$prNumber}: Bidding Number must be \"{$expectedBiddingNumber}\" (based on record uid: {$existingBidSchedule->uid}).";
                        }
                    } else {
                        // New record (or rebid): expected = count of existing BidSchedules for this mode + 1
                        $relatedMopUids = MopLot::where('procID', $item['procID'])
                            ->where('mode_of_procurement_id', $modeId)
                            ->pluck('uid');
                        $count = BidSchedule::where('ref_id', $item['procID'])
                            ->whereIn('mop_uid', $relatedMopUids)
                            ->count();
                        $expectedBiddingNumber = (string) ($count + 1);
                        if ($enteredBiddingNumber !== $expectedBiddingNumber) {
                            $this->scheduleValidationErrors[] = "PR {$prNumber}: Bidding Number must be \"{$expectedBiddingNumber}\" (next available sequence).";
                        }
                    }
                }

                if (!empty($this->scheduleValidationErrors)) {
                    return false;
                }
            }

            // Validate Bidding Result dependencies
            $biddingResult = $this->bulkEdit['bidding_result'] ?? null;

            if ($this->hasValue($biddingResult)) {
                $missingFields = [];
                $hasPreProcConference = $this->hasValue($this->bulkEdit['pre_proc_conference'] ?? '');

                if (!$hasPreProcConference) {
                    if (!$this->hasValue($this->bulkEdit['bidding_number'] ?? '')) {
                        $missingFields[] = 'Bidding #';
                    }
                    if (!$this->hasValue($this->bulkEdit['ib_number'] ?? '')) {
                        $missingFields[] = 'IB No.';
                    }
                    if (!$this->hasValue($this->bulkEdit['sub_open_bids'] ?? '')) {
                        $missingFields[] = 'Submission & Opening of Bids';
                    }

                    if (!empty($missingFields)) {
                        $fieldsList = implode(', ', $missingFields);
                        $this->scheduleValidationErrors[] = "Cannot set Bidding Result without {$fieldsList} or Pre-Proc Conference.";
                    }
                }

                if ($biddingResult === 'SUCCESSFUL') {
                    $successMissingFields = [];

                    if (!$this->hasValue($this->bulkEdit['bid_evaluation_date'] ?? '')) {
                        $successMissingFields[] = 'Bid Evaluation Date';
                    }
                    if (!$this->hasValue($this->bulkEdit['post_qualification_date'] ?? '')) {
                        $successMissingFields[] = 'Post Qualification Date';
                    }

                    if (!empty($successMissingFields)) {
                        $fieldsList = implode(', ', $successMissingFields);
                        $this->scheduleValidationErrors[] = "{$fieldsList} required for SUCCESSFUL bidding result.";
                    }
                }

                // Validate Resolution Number for Bidding Result
                if (!$this->hasValue($this->bulkEdit['resolution_number_mop'] ?? '')) {
                    $this->scheduleValidationErrors[] = "Resolution Number is required when Bidding Result is set.";
                }
            }
        }

        // SVP/ALTERNATIVE MODES
        if (in_array($modeId, self::SVP_MODES)) {
            // Validate PhilGEPS requirements based on individual PR ABC
            $requiresPhilgeps = false;

            foreach ($currentItems as $item) {
                $procurement = Procurement::find($item['procID']);
                $abc = $procurement ? $procurement->abc : 0;

                if ($abc >= self::ABC_THRESHOLD) {
                    $requiresPhilgeps = true;
                    break; // We only need to know if any item requires it
                }
            }

            // If any PR requires PhilGEPS (ABC >= 200K), validate PhilGEPS fields
            // Only validate if at least one field has data (to allow mode-only changes)
            if ($requiresPhilgeps) {
                $hasPhilgeps = $this->hasValue($this->bulkEdit['philgeps_posting_ref_no'] ?? '');
                $hasAdsPost = $this->hasValue($this->bulkEdit['ads_post_ib'] ?? '');

                // If either field has data, both must be filled
                if ($hasPhilgeps || $hasAdsPost) {
                    $missingPhilgepsFields = [];

                    if (!$hasPhilgeps) {
                        $missingPhilgepsFields[] = 'PhilGEPS Posting Ref No';
                    }
                    if (!$hasAdsPost) {
                        $missingPhilgepsFields[] = 'Advertisement/Posting of IB/REI';
                    }

                    if (!empty($missingPhilgepsFields)) {
                        $fieldsList = implode(', ', $missingPhilgepsFields);
                        $this->scheduleValidationErrors[] = "{$fieldsList} required (ABC ≥ ₱200,000).";
                    }
                }
            }
        }

        return empty($this->scheduleValidationErrors);
    }

    /**
     * Validate ABC threshold consistency before opening bulk edit modal
     * Prevents bulk editing PRs with different ABC thresholds
     *
     * @return bool True if validation passes
     */
    private function validateBulkEditAbcThreshold(): bool
    {
        $below200k = [];
        $above200k = [];

        // Get selected procurements
        $selectedProcurements = Procurement::whereIn('procID', $this->selectedItems)->get();

        foreach ($selectedProcurements as $procurement) {
            $abc = $procurement->abc ?? 0;
            $prNumber = $procurement->pr_number;

            if ($abc < self::ABC_THRESHOLD) {
                $below200k[] = $prNumber;
            } else {
                $above200k[] = $prNumber;
            }
        }

        // If we have both below and above 200k, it's inconsistent
        if (!empty($below200k) && !empty($above200k)) {
            $belowList = implode(', ', array_slice($below200k, 0, 5));
            if (count($below200k) > 5) {
                $belowList .= '...';
            }
            $aboveList = implode(', ', array_slice($above200k, 0, 5));
            if (count($above200k) > 5) {
                $aboveList .= '...';
            }

            LivewireAlert::title('Inconsistent ABC Thresholds')
                ->error()
                ->text("Cannot bulk edit SVP mode PRs with different ABC thresholds. Below ₱200,000: {$belowList}; ₱200,000 and above: {$aboveList}. For SVP modes, bulk edit requires all PRs to have the same amount threshold.")
                ->toast()
                ->position('top-end')
                ->show();
            return false;
        }

        return true;
    }

    /**
     * Check if user has permission to modify successful bids in bulk
     * Users without edit permission cannot modify PRs with post-procurement data
     *
     * @return bool True if user can modify or PRs don't have post data
     */
    private function canModifySuccessfulBidsInBulk(): bool
    {
        // Check if user has permission to modify items with post-procurement data
        if (!auth()->user()->can('edit_mode::of::procurement')) {
            // Get selected procurements
            $currentItems = collect($this->getCurrentItems())
                ->whereIn('procID', $this->selectedItems)
                ->values();

            foreach ($currentItems as $item) {
                $biddingResult = $item['bidding_result'] ?? '';

                if ($biddingResult === 'SUCCESSFUL') {
                    $procID = $item['procID'];
                    $hasPostData = \App\Models\PostProcurement::where('ref_id', $procID)->exists();

                    if ($hasPostData) {
                        $prNumber = $item['pr_number'] ?? 'Unknown';
                        LivewireAlert::title('Permission Required')
                            ->error()
                            ->text("Cannot modify PR {$prNumber} - it has a SUCCESSFUL bidding result with post-procurement data. This requires 'Edit Mode of Procurement' permission.")
                            ->toast()
                            ->position('top-end')
                            ->show();
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * Validate that bulk edit is not clearing existing schedule data without replacement
     * Prevents accidental data loss
     *
     * @return bool True if validation passes
     */
    private function validateBulkEditScheduleDeletion(): bool
    {
        $modeId = $this->bulkEdit['mode_of_procurement_id'] ?? null;

        // Get current items for selected PRs
        $currentItems = collect($this->getCurrentItems())
            ->whereIn('procID', $this->selectedItems)
            ->values();

        foreach ($currentItems as $item) {
            $currentModeId = $item['mode_of_procurement_id'] ?? null;
            $prNumber = $item['pr_number'] ?? 'Unknown';

            // Only check if mode is staying the same (not adding new mode)
            if ($currentModeId == $modeId) {
                // For competitive bidding
                if (in_array($modeId, self::BIDDING_MODES)) {
                    $hasExistingBiddingData = $this->hasValue($item['bidding_number']) ||
                        $this->hasValue($item['ib_number']) ||
                        $this->hasValue($item['sub_open_bids']);

                    $clearingBiddingData = !$this->hasValue($this->bulkEdit['bidding_number'] ?? '') &&
                        !$this->hasValue($this->bulkEdit['ib_number'] ?? '') &&
                        !$this->hasValue($this->bulkEdit['sub_open_bids'] ?? '') &&
                        !$this->hasValue($this->bulkEdit['pre_proc_conference'] ?? '');

                    if ($hasExistingBiddingData && $clearingBiddingData) {
                        LivewireAlert::title('Cannot Clear Existing Data')
                            ->error()
                            ->text("Cannot clear existing bidding data for PR {$prNumber} without providing replacement data or Pre-Proc Conference.")
                            ->toast()
                            ->position('top-end')
                            ->show();
                        return false;
                    }
                }

                // For SVP/Alternative modes
                if (in_array($modeId, self::SVP_MODES)) {
                    $hasExistingSvpData = $this->hasValue($item['rfq_no']) ||
                        $this->hasValue($item['canvass_date']) ||
                        $this->hasValue($item['abstract_of_canvass_date']);

                    $clearingSvpData = !$this->hasValue($this->bulkEdit['rfq_no'] ?? '') &&
                        !$this->hasValue($this->bulkEdit['canvass_date'] ?? '') &&
                        !$this->hasValue($this->bulkEdit['abstract_of_canvass_date'] ?? '');

                    if ($hasExistingSvpData && $clearingSvpData) {
                        LivewireAlert::title('Cannot Clear Existing Data')
                            ->error()
                            ->text("Cannot clear existing SVP data for PR {$prNumber} without providing replacement data.")
                            ->toast()
                            ->position('top-end')
                            ->show();
                        return false;
                    }
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
            $currentItems = collect($this->getCurrentItems())
                ->whereIn('procID', $this->selectedItems)
                ->values()
                ->toArray();

            foreach ($currentItems as $item) {
                $biddingResult = $item['bidding_result'] ?? '';

                if ($biddingResult === 'SUCCESSFUL') {
                    $refId = $item['procID'];

                    $hasPostData = \App\Models\PostProcurement::where('ref_id', $refId)->exists();

                    if ($hasPostData) {
                        $this->scheduleValidationErrors[] = "Cannot modify item - it has a SUCCESSFUL bidding result with post-procurement data. This requires 'Edit Mode of Procurement' permission.";
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * Get current (latest) items based on highest mode_order
     * Filters out historical rebid records
     *
     * @return array Array of current procurement items
     */
    private function getCurrentItems(): array
    {
        // Group items by procurement and get the one with highest mode_order (current mode)
        $groupedByProc = [];

        foreach ($this->items as $item) {
            $key = 'lot_' . $item['procID'];

            if (!isset($groupedByProc[$key]) || $item['mode_order'] > $groupedByProc[$key]['mode_order']) {
                $groupedByProc[$key] = $item;
            }
        }

        return array_values($groupedByProc);
    }

    private function populateBulkEditData(): void
    {
        // Clear existing data first to prevent leftover data from previous transactions
        $this->bulkEdit = [];

        // Get current items (highest mode_order) from selected PRs only
        if (empty($this->items) || empty($this->selectedItems)) {
            return;
        }

        $currentItems = collect($this->getCurrentItems())
            ->whereIn('procID', $this->selectedItems)
            ->values();

        if ($currentItems->isEmpty()) {
            return;
        }

        // Fields to check for identical values
        $fields = [
            'mode_of_procurement_id',
            'bidding_number',
            'ib_number',
            'philgeps_posting_ref_no',
            'ads_post_ib',
            'pre_proc_conference',
            'list_invited_observers',
            'obsrvr_prebid_conf',
            'obsrvr_eligibility',
            'obsrvr_sub_open_of_bid',
            'obsrvr_bid',
            'obsrvr_post_qual',
            'pre_bid_conf',
            'eligibility_check',
            'sub_open_bids',
            'bid_evaluation_date',
            'post_qualification_date',
            'bidding_result',
            'resolution_number_mop',
            'resolution_number',
            'rfq_no',
            'canvass_date',
            'date_returned_of_canvass',
            'abstract_of_canvass_date'
        ];

        // For each field, check if all items have the same value
        foreach ($fields as $field) {
            $values = $currentItems->map(fn($item) => $item[$field] ?? '')->toArray();
            $uniqueValues = array_unique($values);

            // If all values are identical, use that value; otherwise leave empty
            if (count($uniqueValues) === 1) {
                $this->bulkEdit[$field] = reset($values);
            } else {
                $this->bulkEdit[$field] = '';
            }
        }
    }

    /**
     * Load procurement data with all mode history (including rebids)
     * Fetches schedules from both BidSchedule and PrSvp tables
     *
     * OPTIMIZED: Uses eager loading to prevent N+1 query problem
     * Instead of 100+ queries for 50 PRs, this executes only 3-4 queries total
     */
    private function loadProcurementData(): void
    {
        // ✅ Eager load ALL relationships upfront to prevent N+1 queries
        $procurements = Procurement::with([
            'pr_items',
            'mopLots' => function ($query) {
                $query->orderBy('mode_order', 'desc');
            },
            'mopLots.modeOfProcurement' // Eager load nested relationship
        ])
            ->whereIn('procID', $this->procurementIds)
            ->where('procurement_type', 'perLot')
            ->orderBy('pr_number')
            ->get();

        // Get all procIDs for schedules
        $procIds = $procurements->pluck('procID')->toArray();

        // Fetch all schedules at once
        $bidSchedules = BidSchedule::whereIn('ref_id', $procIds)->get();
        $prSvps = PrSvp::whereIn('ref_id', $procIds)->get();

        // Build schedule maps
        $scheduleMap = $this->buildScheduleMap($bidSchedules, $prSvps);

        $this->items = [];

        foreach ($procurements as $procurement) {
            // ✅ Use already loaded relationships - NO additional database queries
            foreach ($procurement->mopLots as $mopLot) {
                $this->items[] = $this->buildPerLotRowFromMop($procurement, $mopLot, $scheduleMap);
            }
        }
    }

    /**
     * Build a map of schedules indexed by ref_id and mop_uid
     * Merges data from both BidSchedule and PrSvp tables
     *
     * @param Collection $bidSchedules Bidding schedules
     * @param Collection $prSvps SVP schedules
     * @return Collection Map of schedules
     */
    private function buildScheduleMap(Collection $bidSchedules, Collection $prSvps): Collection
    {
        $map = collect();

        foreach ($bidSchedules as $schedule) {
            $refId = $schedule->ref_id;
            if (!$map->has($refId)) {
                $map[$refId] = collect();
            }
            $map[$refId][$schedule->mop_uid] = [
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

        foreach ($prSvps as $schedule) {
            $refId = $schedule->ref_id;
            if (!$map->has($refId)) {
                $map[$refId] = collect();
            }
            $mopUid = $schedule->mop_uid;
            $existing = $map[$refId]->get($mopUid, []);

            $map[$refId][$mopUid] = array_merge($existing, [
                'resolution_number_mop' => $schedule->resolution_number_mop,
                'philgeps_posting_ref_no' => $schedule->philgeps_posting_ref_no,
                'ads_post_ib' => $schedule->ads_post_ib,
                'rfq_no' => $schedule->rfq_no,
                'canvass_date' => $schedule->canvass_date,
                'date_returned_of_canvass' => $schedule->date_returned_of_canvass,
                'abstract_of_canvass_date' => $schedule->abstract_of_canvass_date,
            ]);
        }

        return $map;
    }

    /**
     * Build a table row for a specific MOP lot with schedule data
     *
     * @param Procurement $procurement The procurement record
     * @param mixed $mopLot The MOP lot record
     * @param Collection $scheduleMap Map of schedule data
     * @return array Row data for display
     */
    private function buildPerLotRowFromMop(Procurement $procurement, $mopLot, Collection $scheduleMap): array
    {
        $schedule = [];
        if ($mopLot && $scheduleMap->has($procurement->procID)) {
            $schedule = $scheduleMap[$procurement->procID][$mopLot->uid] ?? [];
        }

        return [
            'procID' => $procurement->procID,
            'prItemID' => null,
            'pr_number' => $procurement->pr_number,
            'procurement_program_project' => $procurement->procurement_program_project,
            'abc' => $procurement->abc,
            'procurement_type' => 'perLot',
            'mop_id' => $mopLot?->id,
            'mop_uid' => $mopLot?->uid,
            'mode_of_procurement_id' => $mopLot?->mode_of_procurement_id,
            'mode_order' => $mopLot?->mode_order ?? 0,

            // Schedule fields
            'bidding_number' => $schedule['bidding_number'] ?? '',
            'ib_number' => $schedule['ib_number'] ?? '',
            'philgeps_posting_ref_no' => $schedule['philgeps_posting_ref_no'] ?? '',
            'ads_post_ib' => $schedule['ads_post_ib'] ?? '',
            'pre_proc_conference' => $schedule['pre_proc_conference'] ?? '',
            'list_invited_observers' => $schedule['list_invited_observers'] ?? '',
            'obsrvr_prebid_conf' => $schedule['obsrvr_prebid_conf'] ?? '',
            'obsrvr_eligibility' => $schedule['obsrvr_eligibility'] ?? '',
            'obsrvr_sub_open_of_bid' => $schedule['obsrvr_sub_open_of_bid'] ?? '',
            'obsrvr_bid' => $schedule['obsrvr_bid'] ?? '',
            'obsrvr_post_qual' => $schedule['obsrvr_post_qual'] ?? '',
            'pre_bid_conf' => $schedule['pre_bid_conf'] ?? '',
            'eligibility_check' => $schedule['eligibility_check'] ?? '',
            'sub_open_bids' => $schedule['sub_open_bids'] ?? '',
            'bid_evaluation_date' => $schedule['bid_evaluation_date'] ?? '',
            'post_qualification_date' => $schedule['post_qualification_date'] ?? '',
            'bidding_result' => $schedule['bidding_result'] ?? '',
            'resolution_number_mop' => $schedule['resolution_number_mop'] ?? '',
            'resolution_number' => $schedule['resolution_number'] ?? '',
            'rfq_no' => $schedule['rfq_no'] ?? '',
            'canvass_date' => $schedule['canvass_date'] ?? '',
            'date_returned_of_canvass' => $schedule['date_returned_of_canvass'] ?? '',
            'abstract_of_canvass_date' => $schedule['abstract_of_canvass_date'] ?? '',
        ];
    }



    // buildPerLotRow() removed — loadProcurementData() uses buildPerLotRowFromMop() exclusively



    /**
     * Validate that all selected items have same ABC threshold category
     * Prevents mixing items below and above threshold in same bulk edit
     *
     * @return bool True if all items in same threshold category
     */
    private function validateAbcThreshold(): bool
    {
        // Get selected procurements
        $procurements = Procurement::whereIn('procID', $this->selectedItems)->get();

        $below200k = [];
        $above200k = [];

        foreach ($procurements as $procurement) {
            $abcAmount = $procurement->abc ?? 0;

            if ($abcAmount < self::ABC_THRESHOLD) {
                $below200k[] = $procurement->pr_number;
            } else {
                $above200k[] = $procurement->pr_number;
            }
        }

        // If mismatch found
        if (!empty($below200k) && !empty($above200k)) {
            LivewireAlert::title('ABC Threshold Mismatch')
                ->warning()
                ->text('PRs have mixed ABC threshold categories. Below \u20b1200K: ' . implode(', ', $below200k) . '; \u20b1200K and above: ' . implode(', ', $above200k) . '. For SVP and Other modes, all PRs must be in the same threshold category.')
                ->toast()
                ->position('top-end')
                ->show();
            return false;
        }

        return true;
    }

    public function save()
    {
        if (empty($this->selectedItems)) {
            LivewireAlert::title('No Items Selected')
                ->warning()
                ->text('Please select at least one procurement to edit.')
                ->toast()
                ->position('top-end')
                ->show();
            return;
        }

        // Validate ABC threshold first (ONLY for SVP modes 7-24)
        $modeId = $this->bulkEdit['mode_of_procurement_id'] ?? null;
        if ($modeId && in_array($modeId, self::SVP_MODES)) {
            if (!$this->validateAbcThreshold()) {
                return;
            }
        }

        // Check for permission to modify successful bids
        if (!$this->canModifySuccessfulBidsInBulk()) {
            return; // Error already shown in method
        }

        // Check if we're trying to clear existing schedule data
        if (!$this->validateBulkEditScheduleDeletion()) {
            return; // Error already shown in method
        }

        // Validate schedule data before starting transaction
        if (!$this->validateBulkSchedules()) {
            $errorMessage = implode(' ', $this->scheduleValidationErrors);
            LivewireAlert::title('Validation Failed')
                ->error()
                ->text($errorMessage)
                ->toast()->position('top-end')->show();
            return;
        }

        $isMopAdded = false;
        $isMopUpdated = false;
        $isScheduleAdded = false;
        $isScheduleUpdated = false;

        DB::transaction(function () use (&$isMopAdded, &$isMopUpdated, &$isScheduleAdded, &$isScheduleUpdated) {
            // Get selected procurements
            $procurements = Procurement::whereIn('procID', $this->selectedItems)->get();

            foreach ($procurements as $procurement) {
                $currentItems = collect($this->items)
                    ->where('procID', $procurement->procID)
                    ->sortByDesc('mode_order')
                    ->values();

                if ($currentItems->isEmpty()) {
                    continue;
                }

                $currentItem = $currentItems->first();
                $modeId = $this->bulkEdit['mode_of_procurement_id'] ?? null;

                // Check if mode has changed and there's no schedule data - UPDATE mode instead of adding
                $modeHasChanged = $currentItem['mode_of_procurement_id'] != $modeId && $modeId && $modeId != self::MODE_PENDING;
                $canUpdateMode = $modeHasChanged && !$this->itemHasSchedule($currentItem);

                // Check if we're adding a NEW mode (for rebidding):
                // 1. showAddForm is true (Add button was clicked), OR
                // 2. Current mode is PENDING and we're changing to another mode
                $isAddingNewMode = $this->showAddForm ||
                    ($currentItem['mode_of_procurement_id'] == self::MODE_PENDING && $modeId && $modeId != self::MODE_PENDING);

                if ($canUpdateMode) {
                    // Update existing mode_of_procurement_id (no schedule data yet)
                    MopLot::where('id', $currentItem['mop_id'])->update([
                        'mode_of_procurement_id' => $modeId,
                    ]);
                    $isMopUpdated = true;

                    // Also save the entered schedule fields for the updated mode
                    $mopLot = MopLot::find($currentItem['mop_id']);
                    if ($mopLot) {
                        $this->saveRelatedSchedules(
                            $mopLot,
                            array_merge($this->bulkEdit, ['procID' => $procurement->procID]),
                            $isScheduleAdded,
                            $isScheduleUpdated
                        );
                    }
                } elseif ($isAddingNewMode && $modeId && $modeId != self::MODE_PENDING) {
                    $modeOrder = ($currentItem['mode_order'] ?? 0) + 1;
                    $generatedUid = "MOP-{$modeId}-{$modeOrder}";

                    $savedMop = MopLot::create([
                        'procID' => $procurement->procID,
                        'uid' => $generatedUid,
                        'mode_of_procurement_id' => $modeId,
                        'mode_order' => $modeOrder,
                    ]);
                    $isMopAdded = true;

                    // Save schedules for the new mode
                    $this->saveRelatedSchedules(
                        $savedMop,
                        array_merge($this->bulkEdit, ['procID' => $procurement->procID]),
                        $isScheduleAdded,
                        $isScheduleUpdated
                    );
                } else {
                    // Update existing mode schedules - get the MopLot model
                    $mopLot = MopLot::find($currentItem['mop_id']);
                    if ($mopLot) {
                        $this->saveRelatedSchedules(
                            $mopLot,
                            array_merge($this->bulkEdit, ['procID' => $procurement->procID]),
                            $isScheduleAdded,
                            $isScheduleUpdated
                        );
                    }
                    $isMopUpdated = true;
                }
            }
        });

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

        // Reload data to reflect changes
        $this->autoUpdateStageForModeFromPendingForProcIDs($this->selectedItems);
        $this->autoUpdateStageForSvpModeForProcIDs($this->selectedItems);
        $this->autoUpdateStageForCbModeForProcIDs($this->selectedItems);
        $this->loadProcurementData();
        $this->populateBulkEditData();
        $this->loadPostProcurementData();
        $this->showAddForm = false;

        // Modal remains open to allow user to continue editing or verify changes
        // User can manually close modal or make additional edits
    }

    /**
     * Save schedule data to appropriate table (BidSchedule or PrSvp)
     * Uses complex UID generation and status tracking
     *
     * @param MopLot $parentModel The parent MopLot model
     * @param array $itemData The schedule data from bulkEdit
     * @param bool $isScheduleAdded Reference to track if schedule was created
     * @param bool $isScheduleUpdated Reference to track if schedule was updated
     */
    protected function saveRelatedSchedules(
        $parentModel,
        array $itemData,
        bool &$isScheduleAdded,
        bool &$isScheduleUpdated
    ): void {
        $modeId = $itemData['mode_of_procurement_id'];
        $refId = $itemData['procID'];
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

        if (in_array($modeId, self::BIDDING_MODES)) {
            $existingBidSchedule = BidSchedule::where($matchCriteria)->first();

            if (!$existingBidSchedule) {
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

        if (in_array($modeId, self::SVP_MODES)) {
            // Save SVP fields to PrSvp for modes 7-24
            $existingSvp = PrSvp::where($matchCriteria)->first();

            if (!$existingSvp) {
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



    /**
     * Convert value to null if empty, otherwise return value
     * Used for date fields that should be nullable in database
     *
     * @param mixed $value The value to check
     * @return mixed|null The value or null
     */
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
     * Toggle history display for a specific procurement item
     * Shows/hides rebid history (previous modes)
     *
     * @param string $key The item key to toggle history for
     */
    public function toggleHistory(string $key): void
    {
        if ($this->historyForKey === $key && $this->showHistory) {
            $this->showHistory = false;
            $this->historyForKey = null;
        } else {
            $this->showHistory = true;
            $this->historyForKey = $key;
        }
    }

    /**
     * Get historical mode records for display
     * Returns all previous modes excluding the current one
     *
     * @return Collection Historical items ordered by mode_order descending
     */
    public function getHistoryItemsProperty()
    {
        if (!$this->showHistory || !$this->historyForKey) {
            return collect();
        }

        // Parse the key to get procID
        $parts = explode('_', $this->historyForKey);
        $type = $parts[0]; // 'lot'
        $id = $parts[1];

        // Get all items for this PR except the first (current) one
        return collect($this->items)
            ->filter(function ($item) use ($id) {
                return $item['procurement_type'] === 'perLot' && $item['procID'] == $id;
            })
            ->skip(1) // Skip the current/first mode
            ->values();
    }

    private function formatDate($date): string
    {
        if (empty($date)) {
            return '-';
        }

        try {
            return Carbon::parse($date)->format('m/d/Y');
        } catch (\Exception $e) {
            return $date;
        }
    }

    public function setStep(int $step): void
    {
        if ($step == 2 && !$this->isPostAvailable) {
            LivewireAlert::title('Cannot Access Post Tab')
                ->warning()
                ->text('Post-procurement tab is not yet available. Please complete the bulk edit first.')
                ->toast()
                ->position('top-end')
                ->show();
            return;
        }

        // Clear selections when changing tabs
        $this->selectedItems = [];
        $this->selectedPostItems = [];
        $this->selectAll = false;
        $this->selectAllPost = false;

        $this->activeTab = $step;
    }

    /**
     * Check if a single PR item meets post-procurement eligibility based on its CURRENT mode
     *
     * @param array $item The procurement item to check
     * @return bool True if item is eligible for post-procurement
     */
    public function isItemEligibleForPost(array $item): bool
    {
        $modeId = $item['mode_of_procurement_id'] ?? null;

        // Items with no mode or pending mode are not eligible
        if (!$modeId || $modeId === self::MODE_PENDING) {
            return false;
        }

        // COMPETITIVE BIDDING MODES
        if (in_array($modeId, self::BIDDING_MODES)) {
            return $this->hasValue($item['bidding_number']) &&
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
                ($item['bidding_result'] === 'SUCCESSFUL') &&
                $this->hasValue($item['resolution_number_mop']);
        }

        // SVP/ALTERNATIVE MODES
        if (in_array($modeId, self::SVP_MODES)) {
            // ABC is already available from the loaded item array — no extra query needed
            $abc = $item['abc'] ?? 0;

            // Base required SVP fields
            $allSvpFieldsFilled =
                $this->hasValue($item['resolution_number_mop']) &&
                $this->hasValue($item['rfq_no']) &&
                $this->hasValue($item['canvass_date']) &&
                $this->hasValue($item['date_returned_of_canvass']) &&
                $this->hasValue($item['abstract_of_canvass_date']);

            // If ABC is at threshold or above, also require philgeps_posting_ref_no and ads_post_ib
            if ($abc >= self::ABC_THRESHOLD) {
                $allSvpFieldsFilled = $allSvpFieldsFilled &&
                    $this->hasValue($item['philgeps_posting_ref_no']) &&
                    $this->hasValue($item['ads_post_ib']);
            }

            return $allSvpFieldsFilled;
        }

        // Mode not recognized as eligible
        return false;
    }

    /**
     * Determine if Post-Procurement tab should be available
     * Tab activates when AT LEAST ONE PR meets the criteria based on its CURRENT mode
     *
     * @return bool True if at least one item is ready for post-procurement
     */
    public function getIsPostAvailableProperty(): bool
    {
        $currentItems = $this->getCurrentItems();

        if (empty($currentItems)) {
            return false;
        }

        // Check if ANY item meets post-procurement criteria
        foreach ($currentItems as $item) {
            if ($this->isItemEligibleForPost($item)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if mode selection dropdown should be disabled
     * Disabled when items have existing schedule data (to prevent data loss)
     * OR when Add Mode workflow is required (failed bidding needs rebid)
     *
     * @return bool True if mode select should be disabled
     */
    public function getDisableModeSelectProperty(): bool
    {
        // If we're in "add mode", always enable the dropdown
        if ($this->showAddForm) {
            return false;
        }

        $currentItems = collect($this->getCurrentItems())
            ->whereIn('procID', $this->selectedItems)
            ->values()
            ->toArray();

        if (empty($currentItems)) {
            return false;
        }

        // Check if ANY item has actual schedule data (not just mode)
        $hasAnyScheduleData = false;
        foreach ($currentItems as $item) {
            if ($this->itemHasSchedule($item)) {
                $hasAnyScheduleData = true;
                break;
            }
        }

        // If items have schedule data, disable mode select
        // This prevents accidental overwriting of existing schedule data
        if ($hasAnyScheduleData) {
            return true;
        }

        // If no schedule data exists, allow mode changes freely
        return false;
    }

    /**
     * Determine if "Add Mode" button should be displayed
     * Shows when items can accept a rebid/fallback mode
     *
     * @return bool True if Add Mode button should show
     */
    public function getShowAddModeButtonProperty(): bool
    {
        // Don't show button if form is already shown
        if ($this->showAddForm) {
            return false;
        }

        // Only check the items that are currently selected for bulk edit
        if (empty($this->selectedItems)) {
            return false;
        }

        $currentItems = collect($this->getCurrentItems())
            ->whereIn('procID', $this->selectedItems)
            ->values()
            ->toArray();

        if (empty($currentItems)) {
            return false;
        }

        // Check if ALL items meet the criteria to show Add button
        $allCanAdd = true;

        foreach ($currentItems as $item) {
            $modeId = $item['mode_of_procurement_id'] ?? null;

            // Can't add for SVP modes
            if (in_array($modeId, self::SVP_MODES)) {
                $allCanAdd = false;
                break;
            }

            $bidResult = $item['bidding_result'] ?? '';

            $hasBiddingData = $this->hasValue($item['ib_number']) &&
                $this->hasValue($item['bidding_number']) &&
                $this->hasValue($item['sub_open_bids']);

            $hasPreProcConference = $this->hasValue($item['pre_proc_conference']);

            // Item can add if: mode_id = 1 OR (has bidding data/pre-proc AND result is UNSUCCESSFUL)
            $itemCanAdd = $modeId == 1 ||
                (($hasBiddingData || $hasPreProcConference) &&
                    $bidResult === 'UNSUCCESSFUL');

            if (!$itemCanAdd) {
                $allCanAdd = false;
                break;
            }
        }

        return $allCanAdd;
    }

    /**
     * Determine if bidding-specific fields should be shown
     *
     * @return bool True if current mode is a bidding mode
     */
    public function getShowBiddingFieldsProperty(): bool
    {
        $modeId = $this->bulkEdit['mode_of_procurement_id'] ?? null;
        return in_array($modeId, self::BIDDING_MODES);
    }

    /**
     * Determine if SVP-specific fields should be shown
     *
     * @return bool True if current mode is an SVP/alternative mode
     */
    public function getShowSvpFieldsProperty(): bool
    {
        $modeId = $this->bulkEdit['mode_of_procurement_id'] ?? null;
        return in_array($modeId, self::SVP_MODES);
    }

    /**
     * Determine if form inputs should be disabled
     * Disabled when user lacks permission and items have successful bids with post data
     *
     * @return bool True if inputs should be disabled
     */
    public function getDisableInputsProperty(): bool
    {
        // Disable inputs if ALL selected PRs have:
        // 1. SUCCESSFUL bidding result
        // 2. Post-procurement data exists
        // 3. User doesn't have edit permission

        $canEditMop = auth()->user()->can('edit_mode::of::procurement');

        if ($canEditMop) {
            return false; // User has permission, don't disable
        }

        // Only check the items that are currently selected for bulk edit
        if (empty($this->selectedItems)) {
            return false;
        }

        $currentItems = collect($this->getCurrentItems())
            ->whereIn('procID', $this->selectedItems)
            ->values()
            ->toArray();

        if (empty($currentItems)) {
            return false;
        }

        // Check if ALL items meet the disable criteria
        foreach ($currentItems as $item) {
            $biddingResult = $item['bidding_result'] ?? '';
            $refId = $item['procurement_type'] === 'perLot' ? $item['procID'] : $item['prItemID'];

            $hasPostData = \App\Models\PostProcurement::where('ref_id', $refId)->exists();
            $isSuccessful = $biddingResult === 'SUCCESSFUL';

            // If ANY item doesn't meet criteria, don't disable
            if (!($isSuccessful && $hasPostData)) {
                return false;
            }
        }

        // All items are successful with post data and user lacks permission
        return true;
    }

    /**
     * Get the ABC threshold category for selected items
     * Used for display and validation purposes
     *
     * @return string Threshold category text
     */
    public function getAbcThresholdCategoryProperty(): string
    {
        // Check SELECTED items for bulk edit, not all initial procurements
        $itemsToCheck = !empty($this->selectedItems) ? $this->selectedItems : $this->procurementIds;

        $procurements = Procurement::whereIn('procID', $itemsToCheck)->get();

        if ($procurements->isEmpty()) {
            return 'N/A';
        }

        // Check if all selected PRs have the same threshold category
        $belowThreshold = 0;
        $aboveThreshold = 0;

        foreach ($procurements as $procurement) {
            $abc = $procurement->abc ?? 0;
            if ($abc < self::ABC_THRESHOLD) {
                $belowThreshold++;
            } else {
                $aboveThreshold++;
            }
        }

        // Return category based on what the majority are
        // (validation prevents mixing, so they should all be the same)
        if ($aboveThreshold > 0) {
            return '₱200,000.00 and Above';
        } else {
            return 'Below ₱200,000.00';
        }
    }

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
            'awardedAmount' => ['nullable', 'regex:/^[0-9,]+\\.?[0-9]{0,2}$/'],
            'philgepsNoticeOfAwardNo' => 'nullable|string|max:255',
            'philgepsPostingOfAward' => 'nullable|date',
            'supplier_id' => 'nullable|integer|exists:suppliers,id',
            'dateReceiptOfSupplierNoa' => 'nullable|date',
        ];

        // If ANY field has data, make Resolution Award Number and Date REQUIRED
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

        // Custom error messages for better UX
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

        // For bulk edit, apply post-procurement to ALL current items
        $currentItems = $this->getCurrentItems();
        $isAdded = false;
        $isUpdated = false;
        $updatedCount = 0;
        $addedCount = 0;

        DB::transaction(function () use ($currentItems, &$isAdded, &$isUpdated, &$addedCount, &$updatedCount) {
            foreach ($currentItems as $item) {
                // Only save post data for PRs that meet post-procurement eligibility
                if (!$this->isItemEligibleForPost($item)) {
                    continue;
                }

                $refId = $item['procurement_type'] === 'perLot' ? $item['procID'] : $item['prItemID'];

                $data = [
                    'ref_id' => $refId,
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

                $postModel = \App\Models\PostProcurement::updateOrCreate(
                    ['ref_id' => $refId],
                    $data
                );

                if ($postModel->wasRecentlyCreated) {
                    $isAdded = true;
                    $addedCount++;
                } elseif ($postModel->wasChanged()) {
                    $isUpdated = true;
                    $updatedCount++;
                }
            }
        });

        if ($isAdded) {
            LivewireAlert::title('Post-Procurement Added!')
                ->success()
                ->text("The procurement award details have been saved for {$addedCount} PR(s).")
                ->toast()
                ->position('top-end')
                ->show();
        } elseif ($isUpdated) {
            LivewireAlert::title('Post-Procurement Updated!')
                ->success()
                ->text("The procurement award details have been updated for {$updatedCount} PR(s).")
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

        // Reload data to reflect changes
        $this->autoUpdateStageForSvpModeForProcIDs(
            array_column($this->getCurrentItems(), 'procID')
        );
        $this->loadProcurementData();
        $this->populateBulkEditData();
        $this->loadPostProcurementData();
    }

    /**
     * Auto-update procurement stage when the mode first changes away from the default
     * pending mode (mode_id = 1) for each of the supplied procIDs.
     *
     * Reads from the database (post-save state) so it works correctly in bulk context.
     *
     * Fires per procurement ONLY when:
     *   - The MopLot with mode_order = 1 still has mode_id = 1 (pending)
     *   - The MopLot with the highest mode_order has a non-pending mode
     *   - The procurement's current stage is still at the initial pending stage (1)
     *
     * Stage mapping:
     *   Mode  2-6  (Competitive Bidding)  → Stage 31
     *   Mode 7-24  (SVP / Alternative)    → Stage 32
     */
    /**
     * Centralized writer for every automatic stage transition (per procurement).
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
     * @param string $procID         Procurement to update.
     * @param int    $targetStageId  Stage implied by the current mode/data.
     * @param bool   $isPlaceholder  True for mode-derived (placeholder) stages.
     * @param bool   $allowDowngrade False to block moving to a lower stage id —
     *                               used by partial recomputers (e.g. bidding).
     */
    private function recordAutoStageForProcID(string $procID, int $targetStageId, bool $isPlaceholder, bool $allowDowngrade = true): void
    {
        $latestStage = PrLotPrstage::where('procID', $procID)
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

        DB::transaction(function () use ($procID, $targetStageId, $currentStageId) {
            PrLotPrstage::create([
                'procID' => $procID,
                'pr_stage_id' => $targetStageId,
                'stage_history' => $currentStageId ? (string) $currentStageId : null,
            ]);

            PrLotRemark::create([
                'procID' => $procID,
                'remarks_id' => 3, // Ongoing
                'notes' => null,
                'remark_history' => now(),
            ]);
        });
    }

    private function autoUpdateStageForModeFromPendingForProcIDs(array $procIDs): void
    {
        if (empty($procIDs)) {
            return;
        }

        foreach ($procIDs as $procID) {
            $latestLot = MopLot::where('procID', $procID)
                ->orderBy('mode_order', 'desc')
                ->first();

            if (!$latestLot) {
                continue;
            }

            $latestModeId = (int) ($latestLot->mode_of_procurement_id ?? 0);

            // Determine the placeholder stage implied purely by the selected mode.
            // All SVP/alternative modes — including 7 (Direct Contracting) and
            // 19 (Lease of Real Property and Venue) — are data-driven: they start
            // at the generic stage 32 and advance only once real data is entered.
            if ($latestModeId === 2) {
                $targetStageId = 3;            // For Pre-Procurement
            } elseif ($this->isCompetitiveBidding($latestModeId)) {
                $targetStageId = 31;           // For Bidding Schedule
            } elseif ($this->isSvpMode($latestModeId)) {
                $targetStageId = 32;           // For Other Mode of Procurement
            } else {
                continue; // Mode is still pending (1) or unrecognised
            }

            $this->recordAutoStageForProcID($procID, $targetStageId, true);
        }
    }

    /**
     * Auto-update procurement stage and remarks for each SVP-mode procurement
     * after a successful save (Tab 1 or Tab 2).
     *
     * Reads the freshest data directly from the database so it works correctly
     * regardless of whether $this->items has been reloaded yet.
     *
     * Hierarchy (low → high):
     *   Stage 15 – Resolution to Recommend for Other Mode  (resolution_number_mop filled)
     *   Stage 29 – Canvass                                 (canvass_date filled)
     *   Stage 16 – Abstract of Canvass                     (abstract_of_canvass_date filled)
     *   Stage  8 – Resolution to Award (other mode)        (resolutionAwardNumber + resolutionAwardDate filled)
     *   Stage 17 – NOA for Approval of HoPE                (noticeOfAward + supplier_id filled)
     *   Stage  7 – Forwarded to PMU                        (handled separately via forwardToPmu)
     */
    private function autoUpdateStageForSvpModeForProcIDs(array $procIDs): void
    {
        if (empty($procIDs)) {
            return;
        }

        foreach ($procIDs as $procID) {
            // Get the latest MopLot for this procurement (highest mode_order = current)
            $latestMopLot = MopLot::where('procID', $procID)
                ->orderBy('mode_order', 'desc')
                ->first();

            if (!$latestMopLot) {
                continue;
            }

            $modeId = (int) $latestMopLot->mode_of_procurement_id;

            if (!$this->isSvpMode($modeId)) {
                continue;
            }

            // Get the freshest SVP schedule data from the database
            $prSvp = PrSvp::where('ref_id', $procID)
                ->where('mop_uid', $latestMopLot->uid)
                ->first();

            // Get post-procurement data
            $post = PostProcurement::where('ref_id', $procID)->first();

            // Walk up the hierarchy; last matching condition wins (highest stage)
            $targetStageId = null;

            // Stage 15 – Resolution to Recommend for Other Mode
            if ($prSvp && $this->hasValue($prSvp->resolution_number_mop)) {
                $targetStageId = 15;
            }

            // Stage 29 – Canvass
            if ($prSvp && $this->hasValue($prSvp->canvass_date)) {
                $targetStageId = 29;
            }

            // Stage 16 – Abstract of Canvass
            if ($prSvp && $this->hasValue($prSvp->abstract_of_canvass_date)) {
                $targetStageId = 16;
            }

            // Stage 8 – Resolution to Award (other mode): both fields required
            if ($post && $this->hasValue($post->resolution_award_number) && $this->hasValue($post->resolution_award_date)) {
                $targetStageId = 8;
            }

            // Stage 17 – NOA for Approval of HoPE: both fields required
            if ($post && $this->hasValue($post->notice_of_award) && !empty($post->supplier_id)) {
                $targetStageId = 17;
            }

            if ($targetStageId === null) {
                continue; // No applicable stage determined
            }

            // Data-derived write: fully flexible so corrections can move the stage
            // up or down to match the current data. recordAutoStageForProcID()
            // still guards stage 7 and skips no-op repeats.
            $this->recordAutoStageForProcID($procID, $targetStageId, false);
        }
    }


    private function autoUpdateStageForCbModeForProcIDs(array $procIDs): void
    {
        if (empty($procIDs)) {
            return;
        }

        foreach ($procIDs as $procID) {
            // Only apply to procurements whose current mode is Competitive Bidding (modes 2-6)
            $latestMopLot = MopLot::where('procID', $procID)
                ->orderBy('mode_order', 'desc')
                ->first();

            if (!$latestMopLot || !$this->isCompetitiveBidding((int) $latestMopLot->mode_of_procurement_id)) {
                continue;
            }

            $bidSchedule = BidSchedule::where('ref_id', $procID)
                ->where('mop_uid', $latestMopLot->uid)
                ->first();

            $targetStageId = null;

            // Stage 4 – For Pre-Bid Conference
            if ($bidSchedule && $this->hasValue($bidSchedule->pre_bid_conf)) {
                $targetStageId = 4;
            }

            if ($targetStageId === null) {
                continue;
            }

            // This recomputer only knows the Pre-Bid stage, so it must not
            // downgrade a PR that has already advanced past it.
            $this->recordAutoStageForProcID($procID, $targetStageId, false, false);
        }
    }

    /**
     * Cancel bulk edit and return to index page
     * Preserves query parameters for filter state
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancel()
    {
        // Preserve query parameters (filters, pagination) when returning to index
        $queryParams = $this->queryParams;
        unset($queryParams['items']); // Remove items param

        return redirect()->route('mode-of-procurement.index', $queryParams);
    }

    /**
     * Add a new mode/rebid for all selected PRs
     * Creates new MOP records with proper mode_order indexing
     */
    public function addItem(): void
    {
        // Validate that all selected PRs can accept a new mode
        if (empty($this->selectedItems)) {
            LivewireAlert::title('Error')
                ->error()
                ->text('No procurement items selected.')
                ->toast()
                ->position('top-end')
                ->show();
            return;
        }

        $currentItems = collect($this->getCurrentItems())
            ->whereIn('procID', $this->selectedItems)
            ->values()
            ->toArray();

        if (empty($currentItems)) {
            LivewireAlert::title('Error')
                ->error()
                ->text('No procurement items found.')
                ->toast()
                ->position('top-end')
                ->show();
            return;
        }

        // Check if ALL selected PRs can add rebid
        foreach ($currentItems as $item) {
            $modeId = $item['mode_of_procurement_id'] ?? null;

            // Cannot rebid SVP modes
            if (in_array($modeId, self::SVP_MODES)) {
                LivewireAlert::title('Cannot Add Rebid')
                    ->warning()
                    ->text('Cannot add rebid for SVP/Alternative modes. PR: ' . $item['pr_number'])
                    ->toast()
                    ->position('top-end')
                    ->show();
                return;
            }

            $bidResult = $item['bidding_result'] ?? '';
            $hasBiddingData = $this->hasValue($item['ib_number']) &&
                $this->hasValue($item['bidding_number']) &&
                $this->hasValue($item['sub_open_bids']);
            $hasPreProcConference = $this->hasValue($item['pre_proc_conference']);

            // Can add if: mode_id = 1 OR (has bidding data/pre-proc AND result is UNSUCCESSFUL)
            $canAdd = $modeId == 1 ||
                (($hasBiddingData || $hasPreProcConference) && $bidResult === 'UNSUCCESSFUL');

            if (!$canAdd) {
                LivewireAlert::title('Cannot Add Rebid')
                    ->warning()
                    ->text('PR ' . $item['pr_number'] . ' does not meet requirements for adding a new mode.')
                    ->toast()
                    ->position('top-end')
                    ->show();
                return;
            }
        }

        // All validations passed - enable the add form
        $this->bulkEdit['mode_of_procurement_id'] = null;
        $this->clearBulkEditScheduleFields();
        $this->showAddForm = true;
    }

    /**
     * Clear all schedule fields in bulk edit form
     * Used when adding new mode or resetting form
     */
    private function clearBulkEditScheduleFields(): void
    {
        // Clear bidding fields
        $this->bulkEdit['bidding_number'] = '';
        $this->bulkEdit['ib_number'] = '';
        $this->bulkEdit['philgeps_posting_ref_no'] = '';
        $this->bulkEdit['ads_post_ib'] = '';
        $this->bulkEdit['pre_proc_conference'] = '';
        $this->bulkEdit['list_invited_observers'] = '';
        $this->bulkEdit['obsrvr_prebid_conf'] = '';
        $this->bulkEdit['obsrvr_eligibility'] = '';
        $this->bulkEdit['obsrvr_sub_open_of_bid'] = '';
        $this->bulkEdit['obsrvr_bid'] = '';
        $this->bulkEdit['obsrvr_post_qual'] = '';
        $this->bulkEdit['pre_bid_conf'] = '';
        $this->bulkEdit['eligibility_check'] = '';
        $this->bulkEdit['sub_open_bids'] = '';
        $this->bulkEdit['bid_evaluation_date'] = '';
        $this->bulkEdit['post_qualification_date'] = '';
        $this->bulkEdit['bidding_result'] = '';
        $this->bulkEdit['resolution_number_mop'] = '';

        // Clear SVP fields
        $this->bulkEdit['resolution_number'] = '';
        $this->bulkEdit['rfq_no'] = '';
        $this->bulkEdit['canvass_date'] = '';
        $this->bulkEdit['date_returned_of_canvass'] = '';
        $this->bulkEdit['abstract_of_canvass_date'] = '';
    }

    /**
     * Handle select all checkbox toggle for post-procurement items
     * Only selects items eligible for post-procurement based on their CURRENT mode
     */
    public function updatedSelectAllPost($value): void
    {
        if ($value) {
            $eligibleItems = [];
            $currentItems = $this->getCurrentItems();

            // Batch-load all forwarded statuses in a single query
            $procIds = array_column($currentItems, 'procID');
            $forwardedIds = \App\Models\PrLotPrstage::whereIn('procID', $procIds)
                ->where('pr_stage_id', 7)
                ->whereNotNull('actual_date_forwarded')
                ->pluck('procID')
                ->toArray();

            foreach ($currentItems as $item) {
                // Check if this item meets post-procurement eligibility
                if (!$this->isItemEligibleForPost($item)) {
                    continue;
                }

                // Exclude already-forwarded PRs (they have no checkbox)
                if (!in_array($item['procID'], $forwardedIds)) {
                    $eligibleItems[] = $item['procID'];
                }
            }

            $this->selectedPostItems = $eligibleItems;
        } else {
            $this->selectedPostItems = [];
        }
        $this->dispatch('$refresh');
    }

    /**
     * Handle individual post item selection changes
     * Updates select all checkbox state for post items based on CURRENT mode eligibility
     */
    public function updatedSelectedPostItems(): void
    {
        $eligibleItems = [];
        $currentItems = $this->getCurrentItems();

        // Batch-load all forwarded statuses in a single query
        $procIds = array_column($currentItems, 'procID');
        $forwardedIds = \App\Models\PrLotPrstage::whereIn('procID', $procIds)
            ->where('pr_stage_id', 7)
            ->whereNotNull('actual_date_forwarded')
            ->pluck('procID')
            ->toArray();

        foreach ($currentItems as $item) {
            // Check if this item meets post-procurement eligibility based on current mode
            if (!$this->isItemEligibleForPost($item)) {
                continue;
            }

            // Exclude already-forwarded PRs (they have no checkbox)
            if (!in_array($item['procID'], $forwardedIds)) {
                $eligibleItems[] = $item['procID'];
            }
        }

        $selectedUnique = array_unique($this->selectedPostItems);
        $this->selectAllPost = !empty($eligibleItems) &&
            count($selectedUnique) === count($eligibleItems) &&
            empty(array_diff($selectedUnique, $eligibleItems));
        $this->dispatch('$refresh');
    }

    /**
     * Clear all post-procurement item selections
     */
    public function clearPostSelections(): void
    {
        $this->selectedPostItems = [];
        $this->selectAllPost = false;
    }

    /**
     * Validate post-procurement bulk edit selection
     * Ensures all selected items have identical post-procurement values`
     *
     * @param \Illuminate\Support\Collection $postProcurements Post-procurement records
     * @param array $refIds Reference IDs of selected items
     * @param array $selectedItems Selected item data with PR numbers
     * @return array ['valid' => bool, 'message' => string]
     */
    private function validatePostBulkEditSelection($postProcurements, array $refIds, array $selectedItems): array
    {
        // If no items have post data yet, allow bulk edit (will create new records)
        if ($postProcurements->isEmpty()) {
            return ['valid' => true, 'message' => ''];
        }

        // If some items have post data and some don't, show which ones don't
        if ($postProcurements->count() < count($refIds)) {
            return [
                'valid' => false,
                'message' => "Some items do not have post-procurement data yet. All selected items must have post-procurement data for bulk editing. Please add data to these items first or deselect them."
            ];
        }

        // All items have post data - check if values are identical
        $firstPost = $postProcurements->first();
        $differentFields = [];
        $prsByField = [];

        foreach ($postProcurements as $post) {
            // Track which PRs have different values for each field
            $prNumber = collect($selectedItems)->firstWhere('ref_id', $post->ref_id)['pr_number'] ?? $post->ref_id;

            if ($post->resolution_award_number !== $firstPost->resolution_award_number) {
                $differentFields['Resolution Award Number'] = true;
                $prsByField['Resolution Award Number'][] = "{$prNumber} ({$post->resolution_award_number})";
            }
            if ($post->resolution_award_date !== $firstPost->resolution_award_date) {
                $differentFields['Resolution Award Date'] = true;
                $prsByField['Resolution Award Date'][] = "{$prNumber} ({$post->resolution_award_date})";
            }
            if ($post->notice_of_award_number !== $firstPost->notice_of_award_number) {
                $differentFields['Notice of Award Number'] = true;
                $prsByField['Notice of Award Number'][] = "{$prNumber} ({$post->notice_of_award_number})";
            }
            if ($post->notice_of_award !== $firstPost->notice_of_award) {
                $differentFields['Notice of Award'] = true;
                $prsByField['Notice of Award'][] = "{$prNumber} ({$post->notice_of_award})";
            }
            if ($post->awarded_amount !== $firstPost->awarded_amount) {
                $differentFields['Awarded Amount'] = true;
                $prsByField['Awarded Amount'][] = "{$prNumber} (₱" . number_format($post->awarded_amount, 2) . ")";
            }
            if ($post->philgeps_notice_of_award_no !== $firstPost->philgeps_notice_of_award_no) {
                $differentFields['PhilGEPS Notice of Award No'] = true;
                $prsByField['PhilGEPS Notice of Award No'][] = "{$prNumber} ({$post->philgeps_notice_of_award_no})";
            }
            if ($post->philgeps_posting_of_award !== $firstPost->philgeps_posting_of_award) {
                $differentFields['PhilGEPS Posting of Award'] = true;
                $prsByField['PhilGEPS Posting of Award'][] = "{$prNumber} ({$post->philgeps_posting_of_award})";
            }
            if ($post->supplier_id !== $firstPost->supplier_id) {
                $differentFields['Supplier'] = true;
                $supplierName = \App\Models\Supplier::find($post->supplier_id)?->business_name ?? 'Unknown';
                $prsByField['Supplier'][] = "{$prNumber} ({$supplierName})";
            }
            if ($post->date_receipt_of_supplier_noa !== $firstPost->date_receipt_of_supplier_noa) {
                $differentFields['Date Receipt of Supplier (NOA)'] = true;
                $prsByField['Date Receipt of Supplier (NOA)'][] = "{$prNumber} ({$post->date_receipt_of_supplier_noa})";
            }
        }

        // If any fields are different, show validation error
        if (!empty($differentFields)) {
            $fieldsList = implode(', ', array_keys($differentFields));
            $details = [];

            foreach ($prsByField as $field => $prs) {
                $prsList = implode('; ', array_slice($prs, 0, 3)); // Show first 3
                if (count($prs) > 3) {
                    $prsList .= '...';
                }
                $details[] = "{$field}: {$prsList}";
            }

            $detailsText = implode(' | ', array_slice($details, 0, 2)); // Show first 2 fields

            return [
                'valid' => false,
                'message' => "Selected items have different values for: {$fieldsList}. Bulk edit requires all selected PRs to have identical post-procurement data. Details: {$detailsText}"
            ];
        }

        return ['valid' => true, 'message' => ''];
    }

    /**
     * Open post-procurement bulk edit modal
     * Prepares data for bulk editing post-procurement fields
     * Pre-fills fields if all selected items have identical values
     * Validates that fields are identical before opening
     */
    public function openPostBulkEditModal(): void
    {
        if (empty($this->selectedPostItems)) {
            LivewireAlert::title('No Items Selected')
                ->warning()
                ->text('Please select at least one procurement to bulk edit.')
                ->toast()
                ->position('top-end')
                ->show();
            return;
        }

        // Prepare selected items data for display
        $selectedItemsData = [];
        foreach ($this->items as $item) {
            $refId = $item['procurement_type'] === 'perLot'
                ? $item['procID']
                : $item['prItemID'];

            if (in_array($refId, $this->selectedPostItems)) {
                $selectedItemsData[] = [
                    'ref_id' => $refId,
                    'pr_number' => $item['pr_number'],
                    'procurement_program_project' => $item['procurement_program_project'],
                ];
            }
        }

        // Get unique ref_ids from selected items
        $uniqueSelectedItems = array_values(array_unique($selectedItemsData, SORT_REGULAR));
        $refIds = array_column($uniqueSelectedItems, 'ref_id');

        // Fetch existing post-procurement data for selected items
        $postProcurements = \App\Models\PostProcurement::whereIn('ref_id', $refIds)->get();

        // Validate that all items have identical post-procurement data
        $validation = $this->validatePostBulkEditSelection($postProcurements, $refIds, $uniqueSelectedItems);

        if (!$validation['valid']) {
            LivewireAlert::title('Post-Procurement Validation Failed')
                ->warning()
                ->text($validation['message'])
                ->toast()
                ->position('top-end')
                ->show();
            return;
        }

        // Initialize default values
        $commonValues = [
            'resolutionAwardNumber' => '',
            'resolutionAwardDate' => '',
            'noticeOfAwardNumber' => '',
            'noticeOfAward' => '',
            'awardedAmount' => null,
            'philgepsNoticeOfAwardNo' => '',
            'philgepsPostingOfAward' => '',
            'supplier_id' => null,
            'dateReceiptOfSupplierNoa' => '',
        ];

        // If all selected items have post data, check if values are identical
        if ($postProcurements->count() === count($refIds)) {
            // Get first record as reference
            $firstPost = $postProcurements->first();
            $allIdentical = true;

            // Check if all records have identical values
            foreach ($postProcurements as $post) {
                if (
                    $post->resolution_award_number !== $firstPost->resolution_award_number ||
                    $post->resolution_award_date !== $firstPost->resolution_award_date ||
                    $post->notice_of_award_number !== $firstPost->notice_of_award_number ||
                    $post->notice_of_award !== $firstPost->notice_of_award ||
                    $post->awarded_amount !== $firstPost->awarded_amount ||
                    $post->philgeps_notice_of_award_no !== $firstPost->philgeps_notice_of_award_no ||
                    $post->philgeps_posting_of_award !== $firstPost->philgeps_posting_of_award ||
                    $post->supplier_id !== $firstPost->supplier_id ||
                    $post->date_receipt_of_supplier_noa !== $firstPost->date_receipt_of_supplier_noa
                ) {
                    $allIdentical = false;
                    break;
                }
            }

            // If all identical, pre-fill the form
            if ($allIdentical) {
                $commonValues = [
                    'resolutionAwardNumber' => $firstPost->resolution_award_number ?? '',
                    'resolutionAwardDate' => $firstPost->resolution_award_date ?? '',
                    'noticeOfAwardNumber' => $firstPost->notice_of_award_number ?? '',
                    'noticeOfAward' => $firstPost->notice_of_award ?? '',
                    'awardedAmount' => $this->formatAmount($firstPost->awarded_amount), // Format with commas for Alpine mask
                    'philgepsNoticeOfAwardNo' => $firstPost->philgeps_notice_of_award_no ?? '',
                    'philgepsPostingOfAward' => $firstPost->philgeps_posting_of_award ?? '',
                    'supplier_id' => $firstPost->supplier_id,
                    'dateReceiptOfSupplierNoa' => $firstPost->date_receipt_of_supplier_noa ?? '',
                ];
            }
        }

        $this->postBulkEditData = array_merge(
            ['selected_items' => $uniqueSelectedItems],
            $commonValues
        );

        $this->showPostBulkEditModal = true;
    }

    /**
     * Close post-procurement bulk edit modal and clear data
     */
    public function closePostBulkEditModal(): void
    {
        $this->showPostBulkEditModal = false;
        $this->postBulkEditData = [];
    }

    /**
     * Refresh post bulk edit modal data after save
     * Reloads fresh values from database to show updated data
     */
    private function refreshPostBulkEditData(): void
    {
        if (empty($this->postBulkEditData['selected_items'])) {
            return;
        }

        $refIds = array_column($this->postBulkEditData['selected_items'], 'ref_id');
        $postProcurements = \App\Models\PostProcurement::whereIn('ref_id', $refIds)->get();

        if ($postProcurements->isEmpty()) {
            return;
        }

        // Get first record as reference
        $firstPost = $postProcurements->first();
        $allIdentical = true;

        // Check if all records still have identical values after update
        foreach ($postProcurements as $post) {
            if (
                $post->resolution_award_number !== $firstPost->resolution_award_number ||
                $post->resolution_award_date !== $firstPost->resolution_award_date ||
                $post->notice_of_award_number !== $firstPost->notice_of_award_number ||
                $post->notice_of_award !== $firstPost->notice_of_award ||
                $post->awarded_amount !== $firstPost->awarded_amount ||
                $post->philgeps_notice_of_award_no !== $firstPost->philgeps_notice_of_award_no ||
                $post->philgeps_posting_of_award !== $firstPost->philgeps_posting_of_award ||
                $post->supplier_id !== $firstPost->supplier_id ||
                $post->date_receipt_of_supplier_noa !== $firstPost->date_receipt_of_supplier_noa
            ) {
                $allIdentical = false;
                break;
            }
        }

        // Update the form with fresh values from database (if all identical)
        if ($allIdentical) {
            $this->postBulkEditData['resolutionAwardNumber'] = $firstPost->resolution_award_number ?? '';
            $this->postBulkEditData['resolutionAwardDate'] = $firstPost->resolution_award_date ?? '';
            $this->postBulkEditData['noticeOfAwardNumber'] = $firstPost->notice_of_award_number ?? '';
            $this->postBulkEditData['noticeOfAward'] = $firstPost->notice_of_award ?? '';
            $this->postBulkEditData['awardedAmount'] = $this->formatAmount($firstPost->awarded_amount); // Format with commas for Alpine mask
            $this->postBulkEditData['philgepsNoticeOfAwardNo'] = $firstPost->philgeps_notice_of_award_no ?? '';
            $this->postBulkEditData['philgepsPostingOfAward'] = $firstPost->philgeps_posting_of_award ?? '';
            $this->postBulkEditData['supplier_id'] = $firstPost->supplier_id;
            $this->postBulkEditData['dateReceiptOfSupplierNoa'] = $firstPost->date_receipt_of_supplier_noa ?? '';
        }
    }

    /**
     * Save post-procurement bulk edit changes
     * Applies selected field updates to all selected items
     * Only updates fields that have values (partial updates)
     *
     * @return void
     */
    public function savePostBulkEdit(): void
    {
        if (empty($this->postBulkEditData['selected_items'])) {
            LivewireAlert::title('No Items Selected')
                ->warning()
                ->text('Please select items to update.')
                ->toast()
                ->position('top-end')
                ->show();
            return;
        }

        // Validate that at least one field has data
        $hasAnyData = false;
        $fieldsToCheck = [
            'resolutionAwardNumber',
            'resolutionAwardDate',
            'noticeOfAwardNumber',
            'noticeOfAward',
            'awardedAmount',
            'philgepsNoticeOfAwardNo',
            'philgepsPostingOfAward',
            'supplier_id',
            'dateReceiptOfSupplierNoa'
        ];

        foreach ($fieldsToCheck as $field) {
            if ($this->hasValue($this->postBulkEditData[$field] ?? null)) {
                $hasAnyData = true;
                break;
            }
        }

        if (!$hasAnyData) {
            LivewireAlert::title('No Data to Save')
                ->warning()
                ->text('Please enter at least one field to update.')
                ->toast()
                ->position('top-end')
                ->show();
            return;
        }

        $updatedCount = 0;

        try {
            DB::transaction(function () use (&$updatedCount) {
                foreach ($this->postBulkEditData['selected_items'] as $item) {
                    $refId = $item['ref_id'];

                    // Find or create post-procurement record
                    $postProc = \App\Models\PostProcurement::firstOrNew(['ref_id' => $refId]);

                    $hasChanges = false;

                    // Update only non-empty fields using hasValue for consistency
                    if ($this->hasValue($this->postBulkEditData['resolutionAwardNumber'] ?? null)) {
                        $postProc->resolution_award_number = $this->postBulkEditData['resolutionAwardNumber'];
                        $hasChanges = true;
                    }
                    if ($this->hasValue($this->postBulkEditData['resolutionAwardDate'] ?? null)) {
                        $postProc->resolution_award_date = $this->postBulkEditData['resolutionAwardDate'];
                        $hasChanges = true;
                    }
                    if ($this->hasValue($this->postBulkEditData['noticeOfAwardNumber'] ?? null)) {
                        $postProc->notice_of_award_number = $this->postBulkEditData['noticeOfAwardNumber'];
                        $hasChanges = true;
                    }
                    if ($this->hasValue($this->postBulkEditData['noticeOfAward'] ?? null)) {
                        $postProc->notice_of_award = $this->postBulkEditData['noticeOfAward'];
                        $hasChanges = true;
                    }
                    if ($this->hasValue($this->postBulkEditData['awardedAmount'] ?? null)) {
                        $postProc->awarded_amount = $this->cleanAmount($this->postBulkEditData['awardedAmount']);
                        $hasChanges = true;
                    }
                    if ($this->hasValue($this->postBulkEditData['philgepsNoticeOfAwardNo'] ?? null)) {
                        $postProc->philgeps_notice_of_award_no = $this->postBulkEditData['philgepsNoticeOfAwardNo'];
                        $hasChanges = true;
                    }
                    if ($this->hasValue($this->postBulkEditData['philgepsPostingOfAward'] ?? null)) {
                        $postProc->philgeps_posting_of_award = $this->postBulkEditData['philgepsPostingOfAward'];
                        $hasChanges = true;
                    }
                    if ($this->hasValue($this->postBulkEditData['supplier_id'] ?? null)) {
                        $postProc->supplier_id = $this->postBulkEditData['supplier_id'];
                        $hasChanges = true;
                    }
                    if ($this->hasValue($this->postBulkEditData['dateReceiptOfSupplierNoa'] ?? null)) {
                        $postProc->date_receipt_of_supplier_noa = $this->postBulkEditData['dateReceiptOfSupplierNoa'];
                        $hasChanges = true;
                    }

                    if ($hasChanges) {
                        $postProc->save();
                        $updatedCount++;
                    }
                }
            });

            LivewireAlert::title('Success!')
                ->success()
                ->text("{$updatedCount} post-procurement record(s) updated successfully.")
                ->toast()
                ->position('top-end')
                ->show();

            // Reload data to reflect changes
            $this->loadProcurementData();
            $this->loadPostProcurementData();

            // Refresh the modal data with updated values from database
            $this->refreshPostBulkEditData();

        } catch (\Exception $e) {
            LivewireAlert::title('Error!')
                ->error()
                ->text('Failed to update post-procurement data: ' . $e->getMessage())
                ->toast()
                ->position('top-end')
                ->show();
        }
    }

    // ============================================================================
    // FORWARD TO PMU
    // ============================================================================

    /**
     * Check if at least one selected post item has all 6 required fields filled
     * Returns true if ANY selected post item qualifies for forwarding
     *
     * Required fields: Resolution Award Number/Date, NOA Number/Date, Awarded Amount, Supplier
     */
    public function getCanForwardToPmuProperty(): bool
    {
        if (empty($this->selectedPostItems)) {
            return false;
        }

        foreach ($this->selectedPostItems as $refId) {
            $post = PostProcurement::where('ref_id', $refId)->first();

            if (
                $post &&
                $this->hasValue($post->resolution_award_number) &&
                $this->hasValue($post->resolution_award_date) &&
                $this->hasValue($post->notice_of_award_number) &&
                $this->hasValue($post->notice_of_award) &&
                $this->hasValue($post->awarded_amount) &&
                $this->hasValue($post->supplier_id) &&
                $this->hasValue($post->date_receipt_of_supplier_noa)
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Count how many selected post items actually qualify for forwarding
     * (have all 6 required post fields filled)
     */
    public function getEligibleForwardCountProperty(): int
    {
        $count = 0;

        foreach ($this->selectedPostItems as $refId) {
            $post = PostProcurement::where('ref_id', $refId)->first();

            if (
                $post &&
                $this->hasValue($post->resolution_award_number) &&
                $this->hasValue($post->resolution_award_date) &&
                $this->hasValue($post->notice_of_award_number) &&
                $this->hasValue($post->notice_of_award) &&
                $this->hasValue($post->awarded_amount) &&
                $this->hasValue($post->supplier_id) &&
                $this->hasValue($post->date_receipt_of_supplier_noa)
            ) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Get a summary of forwarded vs pending status for selected post items
     * Returns ['forwarded' => int, 'pending' => int]
     */
    public function getForwardedToPmuSummaryProperty(): array
    {
        $forwarded = 0;
        $pending = 0;

        foreach ($this->selectedPostItems as $refId) {
            $exists = PrLotPrstage::where('procID', $refId)
                ->where('pr_stage_id', 7)
                ->whereNotNull('actual_date_forwarded')
                ->exists();

            if ($exists) {
                $forwarded++;
            } else {
                $pending++;
            }
        }

        return ['forwarded' => $forwarded, 'pending' => $pending];
    }

    /**
     * Open the Forward to PMU modal
     * Validates at least one selected post item qualifies
     * Pre-fills date only if all already-forwarded items share the same date
     */
    public function openForwardModal(): void
    {
        if (empty($this->selectedPostItems)) {
            LivewireAlert::title('No Items Selected')
                ->warning()
                ->text('Please select at least one item to forward to PMU.')
                ->toast()
                ->position('top-end')
                ->show();
            return;
        }

        // Collect ineligible PR numbers
        $ineligiblePrs = [];
        foreach ($this->selectedPostItems as $refId) {
            $post = PostProcurement::where('ref_id', $refId)->first();

            $isEligible = $post &&
                $this->hasValue($post->resolution_award_number) &&
                $this->hasValue($post->resolution_award_date) &&
                $this->hasValue($post->notice_of_award_number) &&
                $this->hasValue($post->notice_of_award) &&
                $this->hasValue($post->awarded_amount) &&
                $this->hasValue($post->supplier_id);

            if (!$isEligible) {
                $prItem = collect($this->items)->firstWhere('procID', $refId);
                $ineligiblePrs[] = $prItem['pr_number'] ?? $refId;
            }
        }

        if (!empty($ineligiblePrs)) {
            $prList = implode(', ', $ineligiblePrs);
            LivewireAlert::title('Cannot Forward to PMU')
                ->error()
                ->text("The following PR(s) are missing required post-procurement fields (Resolution Award Number/Date, Notice of Award Number/Date, Awarded Amount, or Supplier): {$prList}. Please complete all fields before forwarding.")
                ->toast()
                ->position('top-end')
                ->timer(8000)
                ->show();
            return;
        }

        // Warn (but still open) if some selected items are already forwarded
        $alreadyForwardedPrs = [];
        foreach ($this->selectedPostItems as $refId) {
            $exists = PrLotPrstage::where('procID', $refId)
                ->where('pr_stage_id', 7)
                ->whereNotNull('actual_date_forwarded')
                ->exists();
            if ($exists) {
                $prItem = collect($this->items)->firstWhere('procID', $refId);
                $alreadyForwardedPrs[] = $prItem['pr_number'] ?? $refId;
            }
        }

        if (!empty($alreadyForwardedPrs)) {
            $prList = implode(', ', $alreadyForwardedPrs);

            // If ALL selected items are already forwarded, block and do not open modal
            if (count($alreadyForwardedPrs) === count($this->selectedPostItems)) {
                LivewireAlert::title('Already Forwarded')
                    ->error()
                    ->text("The following PR(s) are already forwarded to PMU: {$prList}. No action needed.")
                    ->toast()
                    ->position('top-end')
                    ->timer(6000)
                    ->show();
                return;
            }

            // Some (but not all) are already forwarded — warn but still open
            LivewireAlert::title('Already Forwarded')
                ->warning()
                ->text("The following PR(s) are already forwarded to PMU: {$prList}. Proceeding will update their forwarded date.")
                ->toast()
                ->position('top-end')
                ->timer(6000)
                ->show();
        }

        // Pre-fill date if all already-forwarded items share the same date
        $dates = [];
        foreach ($this->selectedPostItems as $refId) {
            $stage = PrLotPrstage::where('procID', $refId)
                ->where('pr_stage_id', 7)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($stage && $stage->actual_date_forwarded) {
                $dates[] = $stage->actual_date_forwarded;
            }
        }

        $uniqueDates = array_unique($dates);
        if (count($uniqueDates) === 1) {
            $this->actualDateForwarded = Carbon::parse(reset($uniqueDates))->setTimezone('Asia/Manila')->format('Y-m-d\TH:i');
        } else {
            $this->actualDateForwarded = now('Asia/Manila')->format('Y-m-d\TH:i');
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
     * Forward selected post items to PMU (Stage 7) in bulk
     * Skips items that don't have all 6 required post fields
     * Creates or updates PrLotPrstage stage 7 record per PR
     * Also creates/updates PMU record via notice_of_award_number
     */
    public function forwardToPmu(): void
    {
        $this->validate([
            'actualDateForwarded' => 'required|date'
        ], [
            'actualDateForwarded.required' => 'Please enter the actual date and time forwarded.',
            'actualDateForwarded.date' => 'Please enter a valid date and time.'
        ]);

        $forwarded = 0;
        $updated = 0;
        $skipped = 0;

        // Convert user-entered Asia/Manila datetime to UTC for storage
        $utcDateForwarded = Carbon::createFromFormat('Y-m-d\TH:i', $this->actualDateForwarded, 'Asia/Manila')
            ->utc()
            ->format('Y-m-d H:i:s');

        try {
            DB::transaction(function () use (&$forwarded, &$updated, &$skipped, $utcDateForwarded) {
                foreach ($this->selectedPostItems as $refId) {
                    $post = PostProcurement::where('ref_id', $refId)->first();

                    // Skip if post data missing or required fields incomplete
                    if (
                        !$post ||
                        !$this->hasValue($post->resolution_award_number) ||
                        !$this->hasValue($post->resolution_award_date) ||
                        !$this->hasValue($post->notice_of_award_number) ||
                        !$this->hasValue($post->notice_of_award) ||
                        !$this->hasValue($post->awarded_amount) ||
                        !$this->hasValue($post->supplier_id)
                    ) {
                        $skipped++;
                        continue;
                    }

                    // Get latest stage for this PR
                    $latestStage = PrLotPrstage::where('procID', $refId)
                        ->orderBy('created_at', 'desc')
                        ->orderBy('id', 'desc')
                        ->first();

                    if ($latestStage && $latestStage->pr_stage_id == 7) {
                        // Already at stage 7 — update date only
                        $previousStage = PrLotPrstage::where('procID', $refId)
                            ->where('id', '<', $latestStage->id)
                            ->orderBy('created_at', 'desc')
                            ->orderBy('id', 'desc')
                            ->first();

                        $latestStage->update([
                            'stage_history' => $previousStage ? (string) $previousStage->pr_stage_id : null,
                            'actual_date_forwarded' => $utcDateForwarded,
                        ]);

                        $updated++;
                    } else {
                        // Create new stage 7 row
                        PrLotPrstage::create([
                            'procID' => $refId,
                            'pr_stage_id' => 7,
                            'stage_history' => $latestStage ? (string) $latestStage->pr_stage_id : null,
                            'actual_date_forwarded' => $utcDateForwarded,
                        ]);

                        PrLotRemark::create([
                            'procID' => $refId,
                            'remarks_id' => 1, // Awarded
                            'notes' => null,
                            'remark_history' => now(),
                        ]);

                        $forwarded++;
                    }

                    // Create/update PMU record
                    if ($this->hasValue($post->notice_of_award_number)) {
                        $pmu = \App\Models\Pmu::updateOrCreate(
                            ['notice_of_award_number' => $post->notice_of_award_number],
                            ['date_forwarded' => $utcDateForwarded]
                        );

                        $poDate = $this->calculatePoDate($post->date_receipt_of_supplier_noa);
                        if ($poDate) {
                            PmuPo::updateOrCreate(
                                ['pmu_id' => $pmu->id, 'ref_id' => $refId],
                                ['po_date_deadline' => $poDate]
                            );
                        }
                    }
                }
            });

            $this->closeForwardModal();

            $total = $forwarded + $updated;
            $message = '';
            if ($forwarded > 0 && $updated > 0) {
                $message = "{$forwarded} PR(s) forwarded and {$updated} PR(s) date updated.";
            } elseif ($forwarded > 0) {
                $message = "{$forwarded} PR(s) successfully forwarded to PMU.";
            } elseif ($updated > 0) {
                $message = "Date updated for {$updated} PR(s).";
            }

            if ($skipped > 0) {
                $message .= " {$skipped} PR(s) skipped (incomplete post-procurement data).";
            }

            if ($total > 0) {
                LivewireAlert::title('Forwarded to PMU!')
                    ->success()
                    ->text($message)
                    ->toast()
                    ->position('top-end')
                    ->show();
            } else {
                LivewireAlert::title('No Items Forwarded')
                    ->warning()
                    ->text($message ?: 'All selected items were skipped due to incomplete post-procurement data.')
                    ->toast()
                    ->position('top-end')
                    ->show();
            }

            $this->selectedPostItems = [];
            $this->selectAllPost = false;

            $this->loadProcurementData();
            $this->populateBulkEditData();
            $this->loadPostProcurementData();

        } catch (\Exception $e) {
            \Log::error('Bulk Forward to PMU failed', [
                'procIDs' => $this->selectedPostItems,
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

    /**
     * Render the component view with all necessary data
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.mode-of-procurement.mode-of-procurement-bulk-edit-per-lot-page', [
            'modeOfProcurements' => $this->modeOfProcurements,
            'suppliers' => $this->suppliers,
            'isPostAvailable' => $this->isPostAvailable,
            'disableInputs' => $this->disableInputs,
            'disableModeSelect' => $this->disableModeSelect,
            'showBiddingFields' => $this->showBiddingFields,
            'showSvpFields' => $this->showSvpFields,
            'abcThresholdCategory' => $this->abcThresholdCategory,
            'showAddModeButton' => $this->showAddModeButton,
            'showAddForm' => $this->showAddForm,
            'canForwardToPmu' => $this->canForwardToPmu,
            'forwardedToPmuSummary' => $this->forwardedToPmuSummary,
            'eligibleForwardCount' => $this->eligibleForwardCount,
        ]);
    }
}
