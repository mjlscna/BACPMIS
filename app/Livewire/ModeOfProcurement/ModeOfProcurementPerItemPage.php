<?php

namespace App\Livewire\ModeOfProcurement;

use App\Models\BidSchedule;
use App\Models\ModeOfProcurement;
use App\Models\PostProcurement;
use App\Models\PrItemPrstage;
use App\Models\PrItemRemark;
use App\Models\Pmu;
use App\Models\PmuPo;
use App\Models\PrSvp;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Attributes\Title;
use Livewire\Component;
use App\Models\Procurement;
use App\Models\MopItem;
use Illuminate\Support\Facades\DB;

#[Title('Mode of Procurement | PMIS')]
class ModeOfProcurementPerItemPage extends Component
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

    // Helper methods to eliminate magic numbers
    public function isCompetitiveBidding(?int $modeId): bool
    {
        return $modeId && in_array($modeId, self::BIDDING_MODES);
    }

    public function isSvpMode(?int $modeId): bool
    {
        return $modeId && in_array($modeId, self::SVP_MODES);
    }

    public function isPendingMode(?int $modeId): bool
    {
        return $modeId === self::MODE_PENDING;
    }

    public function meetsAbcThreshold(?float $amount): bool
    {
        return $amount && $amount >= self::ABC_THRESHOLD;
    }

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
    public ?string $historyForPrItemId = null;

    // Pagination
    public int $perPage = 10;
    public int $currentPage = 1;

    // Post-Procurement Tab Fields
    public array $postItems = [];
    public ?string $resolutionAwardNumber = null;
    public ?string $noticeOfAward = null;
    public ?string $resolutionAwardDate = null;
    public $awardedAmount = null; // Accepts string from Alpine.js money mask, converted to float on save
    public ?string $awardNoticeNumber = null;
    public ?string $dateOfPostingOfAwardOnPhilGEPS = null;
    public ?int $supplier_id = null;
    public Collection $suppliers;

    // Edit History Modal Properties
    public bool $showModal = false;
    public ?array $editingItem = null;
    public ?int $editingIndex = null;
    public array $scheduleValidationErrors = [];
    public $queryParams = [];

    // Bulk Edit Properties
    public bool $showBulkEditModal = false;
    public bool $showAddForm = false;
    public array $selectedItems = [];
    public array $bulkEditData = [];
    public array $bulkEditErrors = [];

    // Post Procurement Bulk Edit Properties
    public bool $showPostBulkEditModal = false;
    public array $selectedPostItems = [];
    public array $postBulkEditData = [];
    public array $postBulkEditErrors = [];

    // Forward to PMU Modal
    public bool $showForwardModal = false;
    public ?string $actualDateForwarded = null;

    public function mount(Procurement $procurement): void
    {
        $this->queryParams = request()->query();

        // ✅ Eager load all necessary relationships upfront to prevent N+1 queries
        $procurement->load([
            'pr_items',
            'mopItems' => function ($query) {
                $query->orderBy('mode_order', 'desc');
            },
            'mopItems.modeOfProcurement',
            'mopItems.item'
        ]);
        $this->procurement = $procurement;
        $this->procID = $procurement->procID ?? '';

        // Initialize form with typed structure
        $this->form = [
            'pr_number' => $procurement->pr_number ?? '',
            'procurement_program_project' => $procurement->procurement_program_project ?? '',
            'approved_ppmp' => (bool) ($procurement->approved_ppmp ?? false),
            'app_updated' => (bool) ($procurement->app_updated ?? false),
            'early_procurement' => (bool) ($procurement->early_procurement ?? false),
            'items' => [],
        ];

        // Delegate to helper methods
        $this->modeOfProcurements = ModeOfProcurement::orderBy('id', 'asc')->get();
        $this->suppliers = Supplier::all();
        $this->loadPerItemData($procurement);
        $this->loadPostProcurementData($procurement);
        $this->calculateTextareaRows($procurement->procurement_program_project ?? '');
    }
    private function hasValue($value): bool
    {
        if (is_null($value)) {
            return false;
        }

        $stringValue = trim((string) $value);

        return $stringValue !== '';
    }
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
        // Initialize postItems array for each eligible item
        $this->postItems = [];

        foreach ($this->form['items'] as $index => $item) {
            $modeId = $item['mode_of_procurement_id'] ?? null;
            $prItemID = $item['prItemID'] ?? null;

            // Check if item is eligible for post-procurement
            $isEligible = false;

            if ($this->isCompetitiveBidding($modeId)) {
                $bidResult = $item['bidding_result'] ?? '';

                if ($bidResult === 'SUCCESSFUL') {
                    $isEligible = true;
                }
            }

            if ($this->isSvpMode($modeId)) {
                if (
                    !empty($item['resolution_number_mop']) &&
                    !empty($item['rfq_no']) &&
                    !empty($item['canvass_date']) &&
                    !empty($item['date_returned_of_canvass']) &&
                    !empty($item['abstract_of_canvass_date'])
                ) {
                    $isEligible = true;
                }
            }

            if ($isEligible && $prItemID) {
                // Load post-procurement data for this prItemID (stored as ref_id)
                $post = PostProcurement::where('ref_id', $prItemID)->first();

                $this->postItems[$prItemID] = [
                    'resolutionAwardNumber' => $post?->resolution_award_number ?? null,
                    'noticeOfAwardNumber' => $post?->notice_of_award_number ?? null,
                    'noticeOfAward' => $post?->notice_of_award ?? null,
                    'resolutionAwardDate' => $post?->resolution_award_date ?? null,
                    'awardedAmount' => $this->formatAmount($post?->awarded_amount), // Format with commas for Alpine mask
                    'philgepsNoticeOfAwardNo' => $post?->philgeps_notice_of_award_no ?? null,
                    'philgepsPostingOfAward' => $post?->philgeps_posting_of_award ?? null,
                    'supplier_id' => $post?->supplier_id ?? null,
                    'dateReceiptOfSupplierNoa' => $post?->date_receipt_of_supplier_noa ?? null,
                ];
            }
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
        foreach ($this->form['items'] as $item) {
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

                // Also require resolution_number_mop for competitive bidding modes
                $allBiddingFieldsFilled = $allBiddingFieldsFilled && $this->hasValue($item['resolution_number_mop']);

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

                // If PR ABC >= 200k, also require philgeps_posting_ref_no and ads_post_ib
                $procurementAbc = (float) ($this->procurement->abc ?? 0);
                if ($procurementAbc >= self::ABC_THRESHOLD) {
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
     * Load per-item data with all schedules
     *
     * OPTIMIZED: Uses already eager-loaded relationships to prevent N+1 queries
     */
    protected function loadPerItemData(Procurement $procurement): void
    {
        // ✅ Use already loaded mopItems from mount (no additional query)
        $mopItemsGrouped = $procurement->mopItems
            ->sortByDesc('mode_order')
            ->groupBy('prItemID');

        // Get prItemIDs for schedules
        $prItemIds = $procurement->pr_items->pluck('prItemID')->filter()->toArray();

        // Fetch Schedules by ref_id (prItemID)
        $bidSchedules = BidSchedule::whereIn('ref_id', $prItemIds)->get();
        $prSvps = PrSvp::whereIn('ref_id', $prItemIds)->get();

        // Build unified schedule map keyed by prItemID and mop_uid
        $scheduleMap = $this->buildScheduleMap($bidSchedules, $prSvps);

        $this->form['items'] = [];

        // Loop through PR Items - FIXED: Sort by item_no for correct display order
        $sortedPrItems = $procurement->pr_items
            ->sortBy(function ($prItem) {
                // Extract numeric part from item_no for proper sorting (e.g., "Item 1", "Item 10")
                preg_match('/\d+/', $prItem->item_no ?? '', $matches);
                return $matches[0] ?? 0;
            });

        foreach ($sortedPrItems as $prItem) {
            $prItemID = $prItem->prItemID;
            $relatedMops = $mopItemsGrouped->get($prItemID);

            if ($relatedMops && $relatedMops->count() > 0) {
                foreach ($relatedMops as $mopItem) {
                    $uid = $mopItem->uid;
                    // Check if prItemID exists in scheduleMap and get the schedule
                    $schedule = [];
                    if ($uid && $scheduleMap->has($prItemID)) {
                        $prItemSchedules = $scheduleMap->get($prItemID);
                        $schedule = $prItemSchedules->get($uid, []);
                    }
                    $this->form['items'][] = $this->mapItemToRow($prItem, $mopItem, $schedule);
                }
            } else {
                $this->form['items'][] = $this->mapItemToRow($prItem, null, []);
            }
        }
    }

    // Replace the buildScheduleMap function in ModeOfProcurementPerItemPage.php

    private function buildScheduleMap(
        Collection $bidSchedules,
        Collection $prSvps
    ): Collection {
        $map = collect();

        // Initialize map for all schedules
        foreach ($bidSchedules as $schedule) {
            $refId = $schedule->ref_id;
            if (!$map->has($refId)) {
                $map[$refId] = collect();
            }
            $map[$refId][$schedule->mop_uid] = [
                'mop_uid' => $schedule->mop_uid,
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
                'bidding_number' => $schedule->bidding_number,
                'bidding_result' => $schedule->bidding_result,
                'resolution_number_mop' => $schedule->resolution_number_mop,
            ];
        }

        // Merge PrSvp data
        foreach ($prSvps as $schedule) {
            $refId = $schedule->ref_id;
            if (!$map->has($refId)) {
                $map[$refId] = collect();
            }
            $mopUid = $schedule->mop_uid;
            $existing = $map[$refId]->get($mopUid, []);

            $map[$refId][$mopUid] = array_merge($existing, [
                'mop_uid' => $schedule->mop_uid,
                'philgeps_posting_ref_no' => $schedule->philgeps_posting_ref_no ?? ($existing['philgeps_posting_ref_no'] ?? null),
                'ads_post_ib' => $schedule->ads_post_ib ?? ($existing['ads_post_ib'] ?? null),
                'resolution_number_mop' => $schedule->resolution_number_mop,
                'rfq_no' => $schedule->rfq_no,
                'canvass_date' => $schedule->canvass_date,
                'date_returned_of_canvass' => $schedule->date_returned_of_canvass,
                'abstract_of_canvass_date' => $schedule->abstract_of_canvass_date,
            ]);
        }

        return $map;
    }
    private function mapItemToRow($prItem, $mopItem, array $schedule): array
    {
        return [
            'id' => $mopItem?->id,
            'prItemID' => $prItem->prItemID,
            'procID' => $prItem->procID,
            'item_no' => $prItem->item_no,
            'description' => $prItem->description,
            'amount' => number_format((float) $prItem->amount, 2, '.', ''),
            'mode_of_procurement_id' => $mopItem?->mode_of_procurement_id,
            'uid' => $mopItem?->uid ?? 'new_' . uniqid(),
            'mode_order' => $mopItem?->mode_order ?? 1,

            // Bidding schedule fields
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

    public function addItem($index): void
    {
        $referenceItem = $this->form['items'][$index] ?? null;

        if (!$referenceItem)
            return;

        $uniqueId = 'new_' . md5(microtime(true) . mt_rand());

        $newItem = [
            'id' => null,
            'prItemID' => $referenceItem['prItemID'],
            'procID' => $referenceItem['procID'],
            'item_no' => $referenceItem['item_no'],
            'description' => $referenceItem['description'],
            'amount' => $referenceItem['amount'],
            'uid' => $uniqueId,

            // Reset MOP fields
            'mode_of_procurement_id' => null,
            'mode_order' => ($referenceItem['mode_order'] ?? 0) + 1,

            // --- COMPETITIVE BIDDING FIELDS ---
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

            // --- RESOLUTION NUMBER (MOP) ---
            'resolution_number_mop' => null,

            // --- SVP FIELDS ---
            'rfq_no' => null,
            'canvass_date' => null,
            'date_returned_of_canvass' => null,
            'abstract_of_canvass_date' => null,
        ];

        array_splice($this->form['items'], $index, 0, [$newItem]);
        $this->showHistory = false;
    }
    public function toggleHistory(string $prItemID)
    {
        if ($this->historyForPrItemId === $prItemID && $this->showHistory) {
            // If clicking the same item, just toggle off
            $this->showHistory = false;
            $this->historyForPrItemId = null;
        } else {
            // Show history for this specific PR Item (all its modes)
            $this->showHistory = true;
            $this->historyForPrItemId = $prItemID;
        }
    }
    public function removeItem($uid): void
    {
        $this->form['items'] = array_filter($this->form['items'], function ($item) use ($uid) {
            return $item['uid'] !== $uid;
        });

        $this->form['items'] = array_values($this->form['items']);
        $this->reindexModeOrder();
    }

    private function reindexModeOrder(): void
    {
        // Group items by prItemID to reindex mode_order within each group
        $grouped = [];
        foreach ($this->form['items'] as $index => $item) {
            $prItemID = $item['prItemID'] ?? null;
            if ($prItemID) {
                if (!isset($grouped[$prItemID])) {
                    $grouped[$prItemID] = [];
                }
                $grouped[$prItemID][] = $index;
            }
        }

        // Reindex mode_order for each prItemID group
        foreach ($grouped as $prItemID => $indices) {
            $modeOrder = 1;
            foreach ($indices as $index) {
                $this->form['items'][$index]['mode_order'] = $modeOrder++;
            }
        }
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

        // Clear selections when changing tabs
        $this->selectedItems = [];
        $this->selectedPostItems = [];
        $this->dispatch('bulk-edit-closed');
        $this->dispatch('post-bulk-edit-closed');

        $this->activeTab = $step;
    }

    // --- History Editing Methods ---

    public function editHistoryItem($index): void
    {
        if (!isset($this->form['items'][$index])) {
            LivewireAlert::title('Error')
                ->error()
                ->text('History record not found.')
                ->toast()
                ->position('top-end')
                ->show();
            return;
        }

        // Check permissions before opening modal for successful bids
        $item = $this->form['items'][$index];
        $biddingResult = $item['bidding_result'] ?? null;
        $prItemID = $item['prItemID'] ?? null;
        $hasPostData = $prItemID && PostProcurement::where('ref_id', $prItemID)->exists();
        $canEditMop = auth()->user()->can('edit_mode::of::procurement');

        if ($biddingResult === 'SUCCESSFUL' && $hasPostData && !$canEditMop) {
            LivewireAlert::title('Permission Denied')
                ->warning()
                ->text('You do not have permission to edit successful bids with post-procurement data.')
                ->toast()
                ->position('top-end')
                ->show();
            return;
        }

        // Store the index and copy the item data for editing
        $this->editingIndex = $index;
        $this->editingItem = $this->form['items'][$index];
        $this->showModal = true;
    }

    public function updateHistoryItem(): void
    {
        if ($this->editingIndex === null || !isset($this->form['items'][$this->editingIndex])) {
            LivewireAlert::title('Error')
                ->error()
                ->text('Unable to update record - item not found.')
                ->toast()
                ->position('top-end')
                ->show();
            return;
        }

        // Update the item in the form array
        $this->form['items'][$this->editingIndex] = $this->editingItem;

        // Validate schedules before saving
        $this->scheduleValidationErrors = [];
        if (!$this->validateSchedules()) {
            $errorMessage = $this->formatValidationErrors($this->scheduleValidationErrors);
            LivewireAlert::title('Validation Failed')
                ->error()
                ->text($errorMessage)
                ->toast()
                ->position('top-end')
                ->show();

            // Restore original value on validation failure
            $this->mount($this->procurement);
            return;
        }

        // Save to database
        $this->saveTab1();

        // Close modal only after successful save
        $this->closeEditModal();
    }

    public function closeEditModal(): void
    {
        $this->showModal = false;
        $this->editingItem = null;
        $this->editingIndex = null;
    }
    private function validateSchedules(): bool
    {
        $isValid = true;
        $this->scheduleValidationErrors = [];

        // Items are sorted by mode_order DESC, so the first occurrence of each prItemID is current.
        // Skip history items (subsequent occurrences) to avoid false duplicate/stale validation errors.
        $validatedPrItemIds = [];

        foreach ($this->form['items'] as $index => $item) {
            $modeId = $item['mode_of_procurement_id'] ?? null;
            if (!$modeId)
                continue;

            $prItemID = $item['prItemID'];

            // Skip history items — only validate the current (first seen) entry per prItemID
            // EXCEPT when editing a specific history item via the modal ($this->editingIndex)
            $isBeingEdited = ($this->editingIndex !== null && $index === $this->editingIndex);
            if (in_array($prItemID, $validatedPrItemIds) && !$isBeingEdited) {
                continue;
            }
            if (!in_array($prItemID, $validatedPrItemIds)) {
                $validatedPrItemIds[] = $prItemID;
            }
            $itemNumber = $item['item_no'] ?? ($index + 1);
            $itemDesc = $item['description'] ?? 'Unknown Item';
            $shortDesc = strlen($itemDesc) > 50 ? substr($itemDesc, 0, 50) . '...' : $itemDesc;

            $matchCriteria = [
                'ref_id' => $prItemID,
                'mop_uid' => $item['uid']
            ];

            // Check if this item has any schedule data entered
            $biddingFields = [
                $item['ib_number'] ?? null,
                $item['philgeps_posting_ref_no'] ?? null,
                $item['ads_post_ib'] ?? null,
                $item['bidding_number'] ?? null,
                $item['pre_proc_conference'] ?? null,
                $item['list_invited_observers'] ?? null,
                $item['obsrvr_prebid_conf'] ?? null,
                $item['obsrvr_eligibility'] ?? null,
                $item['obsrvr_sub_open_of_bid'] ?? null,
                $item['obsrvr_bid'] ?? null,
                $item['obsrvr_post_qual'] ?? null,
                $item['pre_bid_conf'] ?? null,
                $item['eligibility_check'] ?? null,
                $item['sub_open_bids'] ?? null,
                $item['bid_evaluation_date'] ?? null,
                $item['post_qualification_date'] ?? null,
                $item['bidding_result'] ?? null,
            ];

            $svpFields = [
                $item['rfq_no'] ?? null,
                $item['canvass_date'] ?? null,
                $item['date_returned_of_canvass'] ?? null,
                $item['abstract_of_canvass_date'] ?? null,
            ];

            // For SVP modes (7-24) with ABC >= 200k, PhilGEPS and Ads/Post IB should also count as valid SVP data
            if ($this->isSvpMode($modeId)) {
                $prAbc = $this->procurement->abc ?? 0;
                if ($prAbc >= self::ABC_THRESHOLD) {
                    $svpFields[] = $item['philgeps_posting_ref_no'] ?? null;
                    $svpFields[] = $item['ads_post_ib'] ?? null;
                }
            }

            $hasAnyBiddingData = $this->hasAnyValue($biddingFields);
            $hasAnySvpData = $this->hasAnyValue($svpFields);
            $hasResolutionNumber = $this->hasValue($item['resolution_number_mop'] ?? null);

            // Check if there are existing database records for this item
            $existingBidSchedule = null;
            $existingSvp = null;

            if ($this->isCompetitiveBidding($modeId)) {
                $existingBidSchedule = BidSchedule::where($matchCriteria)->first();
            }

            if ($this->isSvpMode($modeId)) {
                $existingSvp = PrSvp::where($matchCriteria)->first();
            }

            // Skip validation if:
            // 1. No data has been entered for this item
            // 2. AND no existing database record exists
            $hasAnyData = $hasAnyBiddingData || $hasAnySvpData || $hasResolutionNumber;
            $hasExistingRecord = $existingBidSchedule || $existingSvp;

            if (!$hasAnyData && !$hasExistingRecord) {
                continue;
            }

            // COMPETITIVE BIDDING MODES (2, 3, 4, 5, 6)
            if ($this->isCompetitiveBidding($modeId)) {
                // Bidding number must match the expected sequence from the BidSchedule uid
                // e.g. uid MOP-2-1-1 → last segment is 1, so bidding number must be 1
                if ($this->hasValue($item['bidding_number'] ?? null)) {
                    $enteredBiddingNumber = (string) $item['bidding_number'];

                    if ($existingBidSchedule && $this->hasValue($existingBidSchedule->uid)) {
                        // Existing record: derive expected from saved uid's last segment
                        $uidParts = explode('-', $existingBidSchedule->uid);
                        $expectedBiddingNumber = (string) end($uidParts);
                        if ($enteredBiddingNumber !== $expectedBiddingNumber) {
                            $this->scheduleValidationErrors[] = sprintf(
                                "<strong>Item %s</strong> (%s): Bidding Number must be \"%s\" (based on record uid: %s).",
                                $itemNumber,
                                $shortDesc,
                                $expectedBiddingNumber,
                                $existingBidSchedule->uid
                            );
                            $isValid = false;
                        }
                    } elseif (!$existingBidSchedule) {
                        // New record: compute expected sequence (same logic as saveRelatedSchedules)
                        $relatedMopUids = MopItem::where('prItemID', $prItemID)
                            ->where('mode_of_procurement_id', $modeId)
                            ->pluck('uid');
                        $count = BidSchedule::where('ref_id', $prItemID)
                            ->whereIn('mop_uid', $relatedMopUids)
                            ->count();
                        $expectedBiddingNumber = (string) ($count + 1);
                        if ($enteredBiddingNumber !== $expectedBiddingNumber) {
                            $this->scheduleValidationErrors[] = sprintf(
                                "<strong>Item %s</strong> (%s): Bidding Number must be \"%s\" (next available sequence).",
                                $itemNumber,
                                $shortDesc,
                                $expectedBiddingNumber
                            );
                            $isValid = false;
                        }
                    }
                }

                // If there's an existing record but all data was removed, show error
                if ($existingBidSchedule && !$hasAnyBiddingData && !$hasResolutionNumber) {
                    $this->scheduleValidationErrors[] = sprintf(
                        "<strong>Item %s</strong> (%s): At least one bidding schedule field must be filled.",
                        $itemNumber,
                        $shortDesc
                    );
                    $isValid = false;
                    continue; // Skip further validation for this item
                }

                // Validate Bidding Result dependencies (only if bidding result is set)
                $biddingResult = $item['bidding_result'] ?? null;

                if ($this->hasValue($biddingResult)) {
                    $missingFields = [];
                    $hasPreProcConference = $this->hasValue($item['pre_proc_conference']);

                    if (!$hasPreProcConference) {
                        if (!$this->hasValue($item['bidding_number'])) {
                            $missingFields[] = '<strong>Bidding #</strong>';
                        }
                        if (!$this->hasValue($item['ib_number'])) {
                            $missingFields[] = '<strong>IB No.</strong>';
                        }
                        if (!$this->hasValue($item['sub_open_bids'])) {
                            $missingFields[] = '<strong>Submission & Opening of Bids</strong>';
                        }

                        if (!empty($missingFields)) {
                            $fieldsList = implode(', ', $missingFields);
                            $this->scheduleValidationErrors[] = sprintf(
                                "<strong>Item %s</strong> (%s): Cannot set Bidding Result to '%s' without %s or <strong>Pre-Proc Conference</strong>.",
                                $itemNumber,
                                $shortDesc,
                                $biddingResult,
                                $fieldsList
                            );
                            $isValid = false;
                        }
                    }

                    if ($biddingResult === 'SUCCESSFUL') {
                        $successMissingFields = [];

                        if (!$this->hasValue($item['bid_evaluation_date'])) {
                            $successMissingFields[] = '<strong>Bid Evaluation Date</strong>';
                        }
                        if (!$this->hasValue($item['post_qualification_date'])) {
                            $successMissingFields[] = '<strong>Post Qualification Date</strong>';
                        }

                        if (!empty($successMissingFields)) {
                            $fieldsList = implode(', ', $successMissingFields);
                            $this->scheduleValidationErrors[] = sprintf(
                                "<strong>Item %s</strong> (%s): %s required for SUCCESSFUL bidding result.",
                                $itemNumber,
                                $shortDesc,
                                $fieldsList
                            );
                            $isValid = false;
                        }
                    }
                }
            }

            // SVP/ALTERNATIVE MODES (7-24)
            if ($this->isSvpMode($modeId)) {
                // If there's an existing record but all data was removed, show error
                if ($existingSvp && !$hasAnySvpData && !$hasResolutionNumber) {
                    $this->scheduleValidationErrors[] = sprintf(
                        "<strong>Item %s</strong> (%s): At least one SVP field must be filled.",
                        $itemNumber,
                        $shortDesc
                    );
                    $isValid = false;
                    continue; // Skip further validation for this item
                }

                // No required field validation for SVP modes during save - all fields are optional
            }
        }

        return $isValid;
    }
    public function saveTab1()
    {
        // STEP 1: Validate form rules FIRST
        $rules = [
            'form.items.*.mode_of_procurement_id' => 'required|integer',
        ];

        $messages = [];
        $attributes = [];

        // Build custom messages for each item
        foreach ($this->form['items'] as $index => $item) {
            $itemNumber = $item['item_no'] ?? ($index + 1);

            $messages["form.items.{$index}.mode_of_procurement_id.required"] =
                "Item {$itemNumber}: Mode of Procurement is required.";
            $messages["form.items.{$index}.mode_of_procurement_id.integer"] =
                "Item {$itemNumber}: Invalid Mode of Procurement selected.";

            $attributes["form.items.{$index}.mode_of_procurement_id"] =
                "Item {$itemNumber} - Mode of Procurement";
        }

        try {
            $this->validate($rules, $messages, $attributes);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errorMessages = $e->validator->errors()->all();

            LivewireAlert::title('Validation Failed')
                ->error()
                ->text($this->formatValidationErrors($errorMessages))
                ->toast()
                ->position('top-end')
                ->show();

            // Exit early - don't proceed to schedule validation or DB transaction
            return;
        }

        // STEP 2: Validate schedules BEFORE starting transaction
        $this->scheduleValidationErrors = [];
        if (!$this->validateSchedules()) {
            LivewireAlert::title('Schedule Validation Failed')
                ->error()
                ->text($this->formatValidationErrors($this->scheduleValidationErrors))
                ->toast()
                ->position('top-end')
                ->show();

            // Exit early - validation failed, don't touch database
            return;
        }

        // STEP 3: ALL validation passed - NOW start the database transaction
        $isMopAdded = false;
        $isMopUpdated = false;
        $isScheduleAdded = false;
        $isScheduleUpdated = false;

        DB::transaction(function () use (&$isMopAdded, &$isMopUpdated, &$isScheduleAdded, &$isScheduleUpdated) {

            foreach ($this->form['items'] as $index => $item) {
                if (empty($item['mode_of_procurement_id']))
                    continue;

                $modeId = $item['mode_of_procurement_id'];
                $prItemID = $item['prItemID'] ?? null;

                // Check if the item is newly added in the UI
                $isUiNew = isset($item['uid']) && (str_starts_with($item['uid'], 'new_') || str_starts_with($item['uid'], 'temp_'));

                // Check if this is an existing saved record
                $isSavedRecord = isset($item['id']) && is_numeric($item['id']);

                if ($isUiNew) {
                    // FOR NEW ITEMS: Calculate mode_order based on existing DB records
                    $maxModeOrder = MopItem::where('procID', $this->procID)
                        ->where('prItemID', $prItemID)
                        ->max('mode_order') ?? 0;

                    $modeOrder = $maxModeOrder + 1;

                    // Generate UID
                    $generatedUid = "MOP-{$modeId}-{$modeOrder}";

                    // CREATE new record
                    $savedParentModel = MopItem::create([
                        'uid' => $generatedUid,
                        'mode_of_procurement_id' => $modeId,
                        'mode_order' => $modeOrder,
                        'procID' => $this->procID,
                        'prItemID' => $prItemID,
                    ]);

                    $isMopAdded = true;

                    // Save related schedules for new item
                    $this->saveRelatedSchedules(
                        $savedParentModel,
                        $item,
                        $isScheduleAdded,
                        $isScheduleUpdated
                    );

                } elseif ($isSavedRecord) {
                    // FOR EXISTING RECORDS: Check if mode_id changed
                    $existingRecord = MopItem::find($item['id']);

                    if (!$existingRecord) {
                        continue;
                    }

                    // Check if the mode_of_procurement_id has changed
                    $modeChanged = $existingRecord->mode_of_procurement_id != $modeId;

                    if ($modeChanged) {
                        // MODE CHANGED: Update the existing record instead of creating new one for bulk edits
                        $existingRecord->update([
                            'mode_of_procurement_id' => $modeId,
                            // Update UID to reflect new mode
                            'uid' => "MOP-{$modeId}-{$existingRecord->mode_order}",
                        ]);

                        $isMopUpdated = true;

                        // Save related schedules for updated record
                        $this->saveRelatedSchedules(
                            $existingRecord,
                            $item,
                            $isScheduleAdded,
                            $isScheduleUpdated
                        );

                    } else {
                        // MODE NOT CHANGED: Only update schedules on existing record
                        $this->saveRelatedSchedules(
                            $existingRecord,
                            $item,
                            $isScheduleAdded,
                            $isScheduleUpdated
                        );
                    }
                }
            }

        });

        if ($isMopAdded) {
            LivewireAlert::title('Mode Added Successfully!')->success()->text('The mode of procurement has been added.')->toast()->position('top-end')->show();
        } elseif ($isScheduleAdded) {
            LivewireAlert::title('Schedule Added!')->success()->text('A new bidding schedule has been created.')->toast()->position('top-end')->show();
        } elseif ($isMopUpdated || $isScheduleUpdated) {
            LivewireAlert::title('Updates Saved!')->success()->text('Changes have been saved successfully.')->toast()->position('top-end')->show();
        } else {
            LivewireAlert::title('No Changes')->info()->text('No changes were detected.')->toast()->position('top-end')->show();
        }

        $this->autoUpdateStageForModeFromPendingPerItem();
        $this->autoUpdateStageForSvpModePerItem();
        $this->autoUpdateStageForCompetitiveBiddingModePerItem();
        $this->mount($this->procurement);
    }

    private function formatValidationErrors(array $errors): string
    {
        if (empty($errors)) {
            return 'Unknown validation error occurred.';
        }

        if (count($errors) === 1) {
            return strip_tags($errors[0]);
        }

        // Create a simple numbered list
        $text = '';
        foreach ($errors as $index => $error) {
            $cleanError = strip_tags($error);
            $text .= "\n• " . $cleanError;
        }

        return $text;
    }

    protected function saveRelatedSchedules(
        $parentModel,
        array $itemData,
        bool &$isScheduleAdded,
        bool &$isScheduleUpdated
    ): void {
        $modeId = $itemData['mode_of_procurement_id'];
        $refId = $itemData['prItemID'] ?? $parentModel->prItemID;
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
                $itemData['ib_number'] ?? null,
                $itemData['philgeps_posting_ref_no'] ?? null,
                $itemData['ads_post_ib'] ?? null,
                $itemData['bidding_number'] ?? null,
                $itemData['pre_proc_conference'] ?? null,
                $itemData['list_invited_observers'] ?? null,
                $itemData['obsrvr_prebid_conf'] ?? null,
                $itemData['obsrvr_eligibility'] ?? null,
                $itemData['obsrvr_sub_open_of_bid'] ?? null,
                $itemData['obsrvr_bid'] ?? null,
                $itemData['obsrvr_post_qual'] ?? null,
                $itemData['pre_bid_conf'] ?? null,
                $itemData['eligibility_check'] ?? null,
                $itemData['sub_open_bids'] ?? null,
                $itemData['bid_evaluation_date'] ?? null,
                $itemData['post_qualification_date'] ?? null,
                $itemData['bidding_result'] ?? null,
            ];

            $hasBiddingData = $this->hasAnyValue($biddingFields);

            // Add resolution_number_mop check for modes 2-6
            $hasBiddingData = $hasBiddingData || $this->hasValue($itemData['resolution_number_mop']);

            $existingBidSchedule = BidSchedule::where($matchCriteria)->first();

            if ($hasBiddingData || $existingBidSchedule) {
                if (!$hasBiddingData && $existingBidSchedule) {
                    return;
                }

                if (!$existingBidSchedule) {
                    $relatedMopUids = MopItem::where('prItemID', $refId)
                        ->where('mode_of_procurement_id', $modeId)
                        ->pluck('uid');

                    $count = BidSchedule::where('ref_id', $refId)
                        ->whereIn('mop_uid', $relatedMopUids)
                        ->count();

                    $uid = $parentUid . '-' . ($count + 1);
                } else {
                    $uid = $existingBidSchedule->uid;
                }

                // UPDATED: Include all observer fields in the save
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
                    $relatedMopUids = MopItem::where('prItemID', $refId)
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
    }

    /**
     * Auto-update stage per PR item when the mode first changes away from the
     * default pending mode (mode_id = 1) created at PR creation.
     *
     * Fires per item ONLY when:
     *   - The item immediately before the current one (for that prItemID) had mode_id = 1
     *   - The current item has a non-pending mode
     *   - That item's stage is still at the initial pending stage (1)
     *
     * Stage mapping (placeholder stages):
     *   Mode 2     (Competitive Bidding) → Stage 3  (For Pre-Procurement)
     *   Mode 3-6   (Competitive Bidding) → Stage 31 (For Bidding Schedule)
     *   Mode 7-24  (SVP / Alternative)   → Stage 32 (For Other Mode of Procurement)
     */
    private function autoUpdateStageForModeFromPendingPerItem(): void
    {
        // Group all form items by prItemID, keep only the highest mode_order per group
        $latestByPrItemID = [];
        foreach ($this->form['items'] as $item) {
            $prItemID = $item['prItemID'] ?? null;
            if (!$prItemID) {
                continue;
            }
            $existing = $latestByPrItemID[$prItemID] ?? null;
            if (!$existing || ($item['mode_order'] ?? 0) > ($existing['mode_order'] ?? 0)) {
                $latestByPrItemID[$prItemID] = $item;
            }
        }

        foreach ($latestByPrItemID as $prItemID => $currentItem) {
            $latestModeId = (int) ($currentItem['mode_of_procurement_id'] ?? 0);

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

            $this->recordAutoStageForPrItem($prItemID, $targetStageId, true);
        }
    }

    /**
     * Centralized writer for every automatic stage transition (per PR item).
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
     * @param mixed $prItemID       PR item to update.
     * @param int   $targetStageId  Stage implied by the current mode/data.
     * @param bool  $isPlaceholder  True for mode-derived (placeholder) stages.
     * @param bool  $allowDowngrade False to block moving to a lower stage id —
     *                              used by partial recomputers (e.g. bidding).
     */
    private function recordAutoStageForPrItem($prItemID, int $targetStageId, bool $isPlaceholder, bool $allowDowngrade = true): void
    {
        $latestStage = PrItemPrstage::where('procID', $this->procID)
            ->where('prItemID', $prItemID)
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

        DB::transaction(function () use ($prItemID, $targetStageId, $currentStageId) {
            PrItemPrstage::create([
                'procID' => $this->procID,
                'prItemID' => $prItemID,
                'pr_stage_id' => $targetStageId,
                'stage_history' => $currentStageId ? (string) $currentStageId : null,
            ]);

            PrItemRemark::create([
                'procID' => $this->procID,
                'prItemID' => $prItemID,
                'remarks_id' => 3, // Ongoing
                'notes' => null,
                'remark_history' => now(),
            ]);
        });
    }

    /**
     * Auto-update procurement stage and remarks per PR item for SVP modes after a successful save.
     *
     * Each PR item is evaluated independently based on its own SVP schedule data
     * and its own post-procurement data (stored in $this->postItems[$prItemID]).
     *
     * For items with rebid history, only the current item (highest mode_order) is evaluated.
     * Uses PrItemPrstage and PrItemRemark (per-item equivalents of the per-lot models).
     *
     * Hierarchy (low → high):
     *   Stage 15 – Resolution to Recommend for Other Mode  (resolution_number_mop filled)
     *   Stage 29 – Canvass                                 (canvass_date filled)
     *   Stage 16 – Abstract of Canvass                     (abstract_of_canvass_date filled)
     *   Stage  8 – Resolution to Award (other mode)        (resolutionAwardNumber + resolutionAwardDate filled)
     *   Stage 17 – NOA for Approval of HoPE                (noticeOfAward + supplier_id filled)
     *   Stage  7 – Forwarded to PMU                        (handled separately via forwardToPmu)
     */
    private function autoUpdateStageForSvpModePerItem(): void
    {
        // Group form items by prItemID and keep only the current (highest mode_order) per item
        $currentItemsByPrItemID = [];
        foreach ($this->form['items'] as $item) {
            $prItemID = $item['prItemID'] ?? null;
            if (!$prItemID) {
                continue;
            }
            $existingOrder = $currentItemsByPrItemID[$prItemID]['mode_order'] ?? -1;
            if (($item['mode_order'] ?? 0) > $existingOrder) {
                $currentItemsByPrItemID[$prItemID] = $item;
            }
        }

        foreach ($currentItemsByPrItemID as $prItemID => $item) {
            $modeId = $item['mode_of_procurement_id'] ?? null;

            if (!$this->isSvpMode($modeId)) {
                continue;
            }

            // Walk up the hierarchy; last matching condition wins (highest stage)
            // for the data currently entered. Removing a field lets the stage fall
            // back to the next-highest filled field (flexible corrections).
            $targetStageId = null;

            // Stage 15 – Resolution to Recommend for Other Mode
            if ($this->hasValue($item['resolution_number_mop'] ?? null)) {
                $targetStageId = 15;
            }

            // Stage 29 – Canvass
            if ($this->hasValue($item['canvass_date'] ?? null)) {
                $targetStageId = 29;
            }

            // Stage 16 – Abstract of Canvass
            if ($this->hasValue($item['abstract_of_canvass_date'] ?? null)) {
                $targetStageId = 16;
            }

            // Get per-item post data (keyed by prItemID in $this->postItems)
            $postItem = $this->postItems[$prItemID] ?? null;

            // Stage 8 – Resolution to Award (other mode): both fields required
            if (
                $postItem
                && $this->hasValue($postItem['resolutionAwardNumber'] ?? null)
                && $this->hasValue($postItem['resolutionAwardDate'] ?? null)
            ) {
                $targetStageId = 8;
            }

            // Stage 17 – NOA for Approval of HoPE: both fields required
            if (
                $postItem
                && $this->hasValue($postItem['noticeOfAward'] ?? null)
                && !empty($postItem['supplier_id'])
            ) {
                $targetStageId = 17;
            }

            if ($targetStageId === null) {
                continue; // No applicable stage determined
            }

            // Data-derived write: fully flexible so corrections can move the stage
            // up or down to match the current data. recordAutoStageForPrItem()
            // still guards stage 7 and skips no-op repeats.
            $this->recordAutoStageForPrItem($prItemID, $targetStageId, false);
        }
    }


    private function autoUpdateStageForCompetitiveBiddingModePerItem(): void
    {
        // Group form items by prItemID, keep the one with the highest mode_order per item
        $currentItemsByPrItemID = [];
        foreach ($this->form['items'] as $item) {
            $prItemID = $item['prItemID'] ?? null;
            if (!$prItemID) {
                continue;
            }
            $existingOrder = $currentItemsByPrItemID[$prItemID]['mode_order'] ?? -1;
            if (($item['mode_order'] ?? 0) > $existingOrder) {
                $currentItemsByPrItemID[$prItemID] = $item;
            }
        }

        foreach ($currentItemsByPrItemID as $prItemID => $item) {
            if (!$this->isCompetitiveBidding((int) ($item['mode_of_procurement_id'] ?? 0))) {
                continue;
            }

            $targetStageId = null;

            // Stage 4 – For Pre-Bid Conference
            if ($this->hasValue($item['pre_bid_conf'] ?? null)) {
                $targetStageId = 4;
            }

            if ($targetStageId === null) {
                continue;
            }

            // This recomputer only knows the Pre-Bid stage, so it must not
            // downgrade an item that has already advanced past it.
            $this->recordAutoStageForPrItem($prItemID, $targetStageId, false, false);
        }
    }

    public function cancel()
    {
        return redirect()->route('mode-of-procurement.index', $this->queryParams);
    }
    public function getIsPostActiveProperty(): bool
    {
        // Check if any post-procurement records exist for the items
        $prItemIds = collect($this->form['items'])->pluck('prItemID')->filter()->unique();

        if ($prItemIds->isEmpty()) {
            return false;
        }

        return PostProcurement::whereIn('ref_id', $prItemIds)->exists();
    }
    public function savePost()
    {
        // Filter items that meet post-procurement criteria
        $postAvailableItems = array_filter($this->form['items'] ?? [], function ($item) {
            $modeId = $item['mode_of_procurement_id'] ?? null;

            if ($this->isCompetitiveBidding($modeId)) {
                $bidResult = $item['bidding_result'] ?? '';
                if ($bidResult === 'SUCCESSFUL') {
                    return true;
                }
            }

            if ($this->isSvpMode($modeId)) {
                if (
                    !empty($item['resolution_number_mop']) &&
                    !empty($item['rfq_no']) &&
                    !empty($item['canvass_date']) &&
                    !empty($item['date_returned_of_canvass']) &&
                    !empty($item['abstract_of_canvass_date'])
                ) {
                    return true;
                }
            }

            return false;
        });

        // Build validation rules with custom messages
        $rules = [];
        $messages = [];
        $attributes = [];

        foreach ($this->postItems as $prItemID => $postItem) {
            $item = collect($this->form['items'])->firstWhere('prItemID', $prItemID);

            if (!$item) {
                continue;
            }

            $itemNumber = $item['item_no'] ?? 'Unknown';
            $itemDesc = $item['description'] ?? '';
            $shortDesc = strlen($itemDesc) > 40 ? substr($itemDesc, 0, 40) . '...' : $itemDesc;

            // Check if this item has any data
            $postFields = [
                $postItem['resolutionAwardNumber'] ?? null,
                $postItem['resolutionAwardDate'] ?? null,
                $postItem['noticeOfAwardNumber'] ?? null,
                $postItem['noticeOfAward'] ?? null,
                $postItem['awardedAmount'] ?? null,
                $postItem['philgepsNoticeOfAwardNo'] ?? null,
                $postItem['philgepsPostingOfAward'] ?? null,
                $postItem['supplier_id'] ?? null,
            ];

            $hasData = $this->hasAnyValue($postFields);

            if ($hasData) {
                $rules["postItems.{$prItemID}.resolutionAwardNumber"] = 'required|string|max:255';
                $rules["postItems.{$prItemID}.resolutionAwardDate"] = 'nullable|date';
                $rules["postItems.{$prItemID}.noticeOfAwardNumber"] = 'nullable|string|max:255';
                $rules["postItems.{$prItemID}.noticeOfAward"] = 'nullable|date';
                $rules["postItems.{$prItemID}.awardedAmount"] = ['nullable', 'regex:/^[0-9,]+\\.?[0-9]{0,2}$/'];
                $rules["postItems.{$prItemID}.philgepsNoticeOfAwardNo"] = 'nullable|string|max:255';
                $rules["postItems.{$prItemID}.philgepsPostingOfAward"] = 'nullable|date';
                $rules["postItems.{$prItemID}.supplier_id"] = 'nullable|integer|exists:suppliers,id';



                // Custom messages
                $messages["postItems.{$prItemID}.resolutionAwardNumber.required"] =
                    "<strong>Item {$itemNumber}</strong> ({$shortDesc}): Resolution Award Number is required.";
                $messages["postItems.{$prItemID}.awardedAmount.regex"] =
                    "<strong>Item {$itemNumber}</strong> ({$shortDesc}): Awarded Amount must be a valid number with up to 2 decimal places.";
                $messages["postItems.{$prItemID}.supplier_id.exists"] =
                    "<strong>Item {$itemNumber}</strong> ({$shortDesc}): Selected supplier is invalid.";


                // Date validation messages
                foreach (['resolutionAwardDate', 'noticeOfAward', 'dateOfPostingOfAwardOnPhilGEPS'] as $dateField) {
                    $fieldLabel = ucwords(str_replace(['_', 'Date', 'Of'], [' ', '', 'of'], $dateField));
                    $messages["postItems.{$prItemID}.{$dateField}.date"] =
                        "<strong>Item {$itemNumber}</strong> ({$shortDesc}): {$fieldLabel} must be a valid date.";
                }

                // Attributes for better error display
                $attributes["postItems.{$prItemID}.resolutionAwardNumber"] = "Item {$itemNumber} - Resolution Award #";
                $attributes["postItems.{$prItemID}.awardedAmount"] = "Item {$itemNumber} - Awarded Amount";
                $attributes["postItems.{$prItemID}.supplier_id"] = "Item {$itemNumber} - Supplier";
            }
        }

        if (empty($rules)) {
            LivewireAlert::title('No Changes')
                ->info()
                ->text('No post-procurement data to save.')
                ->toast()->position('top-end')->show();
            return;
        }

        try {
            $this->validate($rules, $messages, $attributes);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errorMessages = $e->validator->errors()->all();

            LivewireAlert::title('Post-Procurement Validation Failed')
                ->error()
                ->text($this->formatValidationErrors($errorMessages))
                ->toast()
                ->position('top-end')
                ->show();

            throw $e;
        }

        // Validate ABC threshold requirements for post items
        if (!$this->validateAbcThresholdForPostItems()) {
            return; // Validation failed, abort save
        }

        $isAdded = false;
        $isUpdated = false;

        DB::transaction(function () use (&$isAdded, &$isUpdated) {
            // Loop through postItems (which contains the form data)
            foreach ($this->postItems as $prItemID => $postItem) {
                // Get the corresponding form item
                $item = collect($this->form['items'])->firstWhere('prItemID', $prItemID);

                // Skip if item doesn't exist or doesn't have a mode
                if (!$item || empty($item['mode_of_procurement_id'])) {
                    continue;
                }

                // Check if this item has any data
                $postFields = [
                    $postItem['resolutionAwardNumber'] ?? null,
                    $postItem['resolutionAwardDate'] ?? null,
                    $postItem['noticeOfAwardNumber'] ?? null,
                    $postItem['noticeOfAward'] ?? null,
                    $postItem['awardedAmount'] ?? null,
                    $postItem['awardNoticeNumber'] ?? null,
                    $postItem['dateOfPostingOfAwardOnPhilGEPS'] ?? null,
                    $postItem['supplier_id'] ?? null,
                    $postItem['dateReceiptOfSupplierNoa'] ?? null,
                ];

                $hasData = $this->hasAnyValue($postFields);

                if (!$hasData) {
                    continue;
                }

                $data = [
                    'ref_id' => $prItemID,
                    'resolution_award_number' => $postItem['resolutionAwardNumber'],
                    'resolution_award_date' => $this->nullableDate($postItem['resolutionAwardDate'] ?? null),
                    'notice_of_award_number' => $postItem['noticeOfAwardNumber'] ?? null,
                    'notice_of_award' => $this->nullableDate($postItem['noticeOfAward'] ?? null),
                    'awarded_amount' => $this->cleanAmount($postItem['awardedAmount'] ?? null),
                    'philgeps_notice_of_award_no' => $postItem['philgepsNoticeOfAwardNo'] ?? null,
                    'philgeps_posting_of_award' => $this->nullableDate($postItem['philgepsPostingOfAward'] ?? null),
                    'supplier_id' => $postItem['supplier_id'] ?? null,
                    'date_receipt_of_supplier_noa' => $this->nullableDate($postItem['dateReceiptOfSupplierNoa'] ?? null),
                ];

                // Use ref_id as prItemID (unique per item)
                $postModel = PostProcurement::updateOrCreate(
                    ['ref_id' => $prItemID],
                    $data
                );

                if ($postModel->wasRecentlyCreated) {
                    $isAdded = true;
                } elseif ($postModel->wasChanged()) {
                    $isUpdated = true;
                }
            }
        });

        if ($isAdded) {
            LivewireAlert::title('Post-Procurement Added!')
                ->success()
                ->text('The procurement award details have been saved.')
                ->toast()->position('top-end')->show();
        } elseif ($isUpdated) {
            LivewireAlert::title('Post-Procurement Updated!')
                ->success()
                ->text('The procurement award details have been updated.')
                ->toast()->position('top-end')->show();
        } else {
            LivewireAlert::title('No Changes')
                ->info()
                ->text('Post-procurement details remain unchanged.')
                ->toast()->position('top-end')->show();
        }

        // Reload data to refresh postItems
        $this->autoUpdateStageForSvpModePerItem();
        $this->autoUpdateStageForCompetitiveBiddingModePerItem();
        $this->mount($this->procurement);
    }
    public function getHistoryItemsProperty()
    {
        if (!$this->showHistory || !$this->historyForPrItemId) {
            return collect();
        }

        // Get all items for this prItemID except the first (current) one
        return collect($this->form['items'])
            ->filter(function ($item) {
                return ($item['prItemID'] ?? null) === $this->historyForPrItemId;
            })
            ->skip(1); // Skip the first item (current record)
    }
    public function hasPostDataForItem($itemIndex): bool
    {
        $item = $this->form['items'][$itemIndex] ?? null;
        if (!$item)
            return false;

        $prItemID = $item['prItemID'] ?? null;
        if (!$prItemID)
            return false;

        // Check if post data exists in the postItems array for this prItemID
        return isset($this->postItems[$prItemID]) &&
            !empty(array_filter($this->postItems[$prItemID] ?? []));
    }

    public function canAddNewModeForItem(array $item, ?int $modeId): bool
    {
        // SVP/Alternative modes cannot add new modes
        if ($this->isSvpMode($modeId)) {
            return false;
        }

        $bidResult = $item['bidding_result'] ?? '';

        $hasBiddingData = $this->hasValue($item['ib_number']) &&
            $this->hasValue($item['bidding_number']) &&
            $this->hasValue($item['sub_open_bids']);

        $hasPreProcConference = $this->hasValue($item['pre_proc_conference']);

        // Check if post data exists for THIS SPECIFIC item
        $prItemID = $item['prItemID'] ?? null;
        $hasPostDataForThisItem = $prItemID && isset($this->postItems[$prItemID]) &&
            !empty(array_filter($this->postItems[$prItemID] ?? []));

        return $this->isPendingMode($modeId) ||
            (($hasBiddingData || $hasPreProcConference) &&
                $bidResult === 'UNSUCCESSFUL' &&
                !$hasPostDataForThisItem);
    }

    public function save(): void
    {
        if ($this->activeTab == 2) {
            $this->savePost();
            return;
        } else {
            $this->saveTab1();
            $this->mount($this->procurement);
        }
    }

    private function nullableDate($value): ?string
    {
        return empty($value) ? null : $value;
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
     * Validate ABC threshold requirements when ABC or mode changes
     * Ensures PhilGEPS fields are filled when ABC >= ₱200,000
     * ONLY validates for SVP/Alternative modes (7-24) as competitive bidding always requires PhilGEPS
     *
     * @return bool True if validation passes
     */
    private function validateAbcThreshold(): bool
    {
        // Get all current (non-historical) items for validation
        $currentItems = [];
        $prItemsSeen = [];

        // Group items by prItemID and get only the first occurrence (current mode)
        foreach ($this->form['items'] as $item) {
            $prItemID = $item['prItemID'] ?? null;
            if ($prItemID && !isset($prItemsSeen[$prItemID])) {
                $currentItems[] = $item;
                $prItemsSeen[$prItemID] = true;
            }
        }

        $errors = [];

        // Check procurement ABC once (applies to all items)
        $procurementAbc = (float) ($this->procurement->abc ?? 0);
        $requiresPhilgeps = $procurementAbc >= self::ABC_THRESHOLD;

        foreach ($currentItems as $item) {
            $modeId = $item['mode_of_procurement_id'] ?? null;
            $itemNo = $item['item_no'] ?? 'Unknown';

            // Skip validation if no mode is selected
            if (empty($modeId)) {
                continue;
            }

            // Skip validation for empty new items (new_ UIDs with no data)
            if (str_starts_with($item['uid'] ?? '', 'new_')) {
                $hasAnyData = $this->hasValue($item['ib_number']) ||
                    $this->hasValue($item['philgeps_posting_ref_no']) ||
                    $this->hasValue($item['rfq_no']);
                if (!$hasAnyData) {
                    continue;
                }
            }

            // ONLY validate SVP/Alternative modes (7-24) for ABC threshold
            // Competitive bidding (2-6) always requires PhilGEPS regardless of ABC
            if ($this->isSvpMode($modeId)) {
                if ($requiresPhilgeps) {
                    $philgepsRef = $item['philgeps_posting_ref_no'] ?? null;
                    $adsPostIb = $item['ads_post_ib'] ?? null;

                    if (empty($philgepsRef) || empty($adsPostIb)) {
                        $modeName = $this->modeOfProcurements->firstWhere('id', $modeId)?->modeofprocurements ?? 'Mode ' . $modeId;
                        $errors[] = "Item #{$itemNo}: PhilGEPS Posting Ref # and Ads/Post IB are required for {$modeName} when procurement ABC is ₱" . number_format($procurementAbc, 2) . " (>= ₱200,000.00).";
                    }
                }
            }
        }

        if (!empty($errors)) {
            $errorMessage = implode(' ', $errors);
            LivewireAlert::title('Validation Failed')
                ->warning()
                ->text($errorMessage)
                ->toast()
                ->position('top-end')
                ->show();
            return false;
        }

        return true;
    }

    /**
     * Validate ABC threshold requirements for items eligible for post procurement
     * Only validates items with SUCCESSFUL bidding or complete SVP data
     *
     * @return bool True if validation passes
     */
    private function validateAbcThresholdForPostItems(): bool
    {
        // Get items eligible for post procurement
        $postAvailableItems = $this->getPostAvailableItemsProperty();

        if (empty($postAvailableItems)) {
            return true; // No items to validate
        }

        $errors = [];

        foreach ($postAvailableItems as $item) {
            $modeId = $item['mode_of_procurement_id'] ?? null;
            $amount = (float) str_replace(',', '', $item['amount'] ?? 0);
            $itemNo = $item['item_no'] ?? 'Unknown';

            // For competitive bidding modes (2-6): Check per-item ABC
            if ($this->isCompetitiveBidding($modeId)) {
                if ($amount >= self::ABC_THRESHOLD) {
                    $philgepsRef = $item['philgeps_posting_ref_no'] ?? null;
                    $adsPostIb = $item['ads_post_ib'] ?? null;

                    if (empty($philgepsRef) || empty($adsPostIb)) {
                        $modeName = $this->modeOfProcurements->firstWhere('id', $modeId)?->modeofprocurements ?? 'Mode ' . $modeId;
                        $errors[] = "Item #{$itemNo}: PhilGEPS Posting Ref # and Ads/Post IB are required for {$modeName} when item amount is ₱" . number_format($amount, 2) . " (>= ₱200,000.00).";
                    }
                }
            }

            // For SVP modes (7-24): Check whole procurement ABC
            if ($this->isSvpMode($modeId)) {
                $procurementAbc = (float) ($this->procurement->abc ?? 0);

                if ($procurementAbc >= self::ABC_THRESHOLD) {
                    $philgepsRef = $item['philgeps_posting_ref_no'] ?? null;
                    $adsPostIb = $item['ads_post_ib'] ?? null;

                    if (empty($philgepsRef) || empty($adsPostIb)) {
                        $modeName = $this->modeOfProcurements->firstWhere('id', $modeId)?->modeofprocurements ?? 'Mode ' . $modeId;
                        $errors[] = "Item #{$itemNo}: PhilGEPS Posting Ref # and Ads/Post IB are required for {$modeName} when procurement ABC is ₱" . number_format($procurementAbc, 2) . " (>= ₱200,000.00).";
                    }
                }
            }
        }

        if (!empty($errors)) {
            $errorMessage = implode(' ', $errors);
            LivewireAlert::title('Validation Failed')
                ->warning()
                ->text($errorMessage)
                ->toast()
                ->position('top-end')
                ->show();
            return false;
        }

        return true;
    }

    public function toggleItemSelection($index): void
    {
        if (in_array($index, $this->selectedItems)) {
            $this->selectedItems = array_values(array_diff($this->selectedItems, [$index]));
        } else {
            $this->selectedItems[] = $index;
        }
    }

    public function selectAll(): void
    {
        $this->selectedItems = [];
        foreach ($this->form['items'] as $index => $item) {
            // Only select items that are head records (not history)
            $currentPrID = $item['prItemID'] ?? null;
            $prevPrID = $this->form['items'][$index - 1]['prItemID'] ?? null;
            $isHead = $index === 0 || $currentPrID !== $prevPrID;

            if ($isHead) {
                $this->selectedItems[] = $index;
            }
        }
    }

    public function deselectAll(): void
    {
        $this->selectedItems = [];

        // Dispatch event to uncheck all checkboxes via JavaScript
        $this->dispatch('bulk-edit-closed');
    }

    /**
     * Computed property to determine if bulk edit inputs should be disabled
     * Fields are disabled when ALL selected items have:
     * 1. SUCCESSFUL bidding result
     * 2. Post-procurement data exists
     * 3. User doesn't have edit permission
     * Exception: When adding new mode (showAddForm = true), allow all inputs
     */
    public function getDisableBulkInputsProperty(): bool
    {
        // When adding new mode, allow all inputs
        if ($this->showAddForm) {
            return false;
        }

        $canEditMop = auth()->user()->can('edit_mode::of::procurement');

        if ($canEditMop) {
            return false; // User has permission, don't disable
        }

        // Only check the items that are currently selected for bulk edit
        if (empty($this->selectedItems)) {
            return false;
        }

        // Check if ALL selected items meet the disable criteria
        foreach ($this->selectedItems as $index) {
            if (!isset($this->form['items'][$index])) {
                continue;
            }

            $item = $this->form['items'][$index];
            $biddingResult = $item['bidding_result'] ?? '';
            $prItemID = $item['prItemID'] ?? null;

            if (!$prItemID) {
                continue;
            }

            $hasPostData = PostProcurement::where('ref_id', $prItemID)->exists();
            $isSuccessful = $biddingResult === 'SUCCESSFUL';

            // If ANY item doesn't meet criteria (not successful OR no post data), don't disable
            if (!($isSuccessful && $hasPostData)) {
                return false;
            }
        }

        // ALL items have successful bidding AND post data, so disable inputs
        return true;
    }

    /**
     * Computed property to determine if mode select should be disabled
     * Mode select is disabled if ANY selected item has schedule data
     * Exception: When adding new mode (showAddForm = true), allow mode selection
     */
    public function getDisableModeSelectProperty(): bool
    {
        // When adding new mode, allow mode selection
        if ($this->showAddForm) {
            return false;
        }

        if (empty($this->selectedItems)) {
            return false;
        }

        $currentItems = collect($this->form['items'])
            ->filter(function ($item, $index) {
                return in_array($index, $this->selectedItems);
            })
            ->toArray();

        foreach ($currentItems as $item) {
            if ($this->itemHasSchedule($item)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Computed property to determine if Add Mode button should show
     * Shows when items can accept new mode (not SVP, and either pending or unsuccessful bidding)
     */
    public function getShowAddModeButtonProperty(): bool
    {
        // Don't show button if form is already shown
        if ($this->showAddForm) {
            return false;
        }

        // Only check selected items
        if (empty($this->selectedItems)) {
            return false;
        }

        $currentItems = collect($this->form['items'])
            ->filter(function ($item, $index) {
                return in_array($index, $this->selectedItems);
            })
            ->values()
            ->toArray();

        if (empty($currentItems)) {
            return false;
        }

        // Check if ALL items meet the criteria to show Add button
        $allCanAdd = true;

        foreach ($currentItems as $item) {
            $modeId = $item['mode_of_procurement_id'] ?? null;

            // Can't add for SVP/Alternative modes
            if ($this->isSvpMode($modeId)) {
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
     * Check if an item has any schedule data
     *
     * @param array $item
     * @return bool
     */
    private function itemHasSchedule(array $item): bool
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
            'abstract_of_canvass_date',
        ];

        foreach ($scheduleFields as $field) {
            if ($this->hasValue($item[$field] ?? null)) {
                return true;
            }
        }

        return false;
    }

    public function openBulkEditModal(): void
    {
        if (empty($this->selectedItems)) {
            LivewireAlert::title('No Items Selected')
                ->warning()
                ->text('Please select at least one item to edit.')
                ->toast()
                ->position('top-end')
                ->show();
            return;
        }

        // Validate selected items
        $validation = $this->validateBulkEditSelection();

        if (!$validation['valid']) {
            $this->bulkEditErrors = $validation['errors'];
            $errorMessage = implode(' ', $validation['errors']);
            LivewireAlert::title('Bulk Edit Validation Failed')
                ->error()
                ->text($errorMessage)
                ->toast()
                ->position('top-end')
                ->show();
            return;
        }

        // Initialize bulk edit data based on the common mode
        $firstItem = $this->form['items'][$this->selectedItems[0]];
        $modeId = $validation['commonMode'] ?? $firstItem['mode_of_procurement_id'] ?? 1;
        $amountThreshold = $validation['amountThreshold'];

        $this->bulkEditData = [
            'mode_of_procurement_id' => $modeId,
            'amount_threshold' => $amountThreshold,
            'items_count' => count($this->selectedItems),
            'item_numbers' => $validation['itemNumbers'],
        ];

        // Initialize fields based on mode type with existing data
        $this->initializeBulkEditFields($modeId, $firstItem, $amountThreshold);

        $this->bulkEditErrors = [];
        $this->showBulkEditModal = true;
    }

    public function updatedBulkEditDataModeOfProcurementId()
    {
        // When mode changes, reinitialize fields based on new mode
        $modeId = $this->bulkEditData['mode_of_procurement_id'];

        // If we're adding a new mode, clear all schedule fields but preserve mode and metadata
        if ($this->showAddForm) {
            // Store the current values we want to preserve
            $preservedValues = [
                'mode_of_procurement_id' => $modeId,
                'amount_threshold' => $this->bulkEditData['amount_threshold'] ?? null,
                'items_count' => $this->bulkEditData['items_count'] ?? 0,
                'item_numbers' => $this->bulkEditData['item_numbers'] ?? [],
            ];

            $this->clearBulkEditScheduleFields();

            // Restore the preserved values
            $this->bulkEditData = array_merge($this->bulkEditData, $preservedValues);
            return;
        }

        $firstItem = $this->form['items'][$this->selectedItems[0]];
        $amountThreshold = $this->bulkEditData['amount_threshold'];

        $this->initializeBulkEditFields($modeId, $firstItem, $amountThreshold);
    }

    private function initializeBulkEditFields($modeId, $firstItem, $amountThreshold)
    {
        // Clear existing mode-specific fields
        $this->bulkEditData = array_intersect_key($this->bulkEditData, array_flip([
            'mode_of_procurement_id',
            'amount_threshold',
            'items_count',
            'item_numbers'
        ]));

        // Get all selected items for comparison
        $selectedItemsData = collect($this->selectedItems)->map(function ($index) {
            return $this->form['items'][$index] ?? [];
        })->filter();

        // Initialize fields based on mode type with common values across ALL selected items
        if ($this->isCompetitiveBidding($modeId)) {
            $biddingFields = [
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
            ];

            // For each field, check if all selected items have the same value
            foreach ($biddingFields as $field) {
                $values = $selectedItemsData->pluck($field)->toArray();
                $uniqueValues = array_unique($values);

                // If all values are identical, use that value; otherwise leave empty
                if (count($uniqueValues) === 1) {
                    $this->bulkEditData[$field] = reset($values) ?? '';
                } else {
                    $this->bulkEditData[$field] = '';
                }
            }
        } elseif ($this->isSvpMode($modeId)) {
            $svpFields = [
                'rfq_no',
                'canvass_date',
                'date_returned_of_canvass',
                'abstract_of_canvass_date',
                'resolution_number_mop',
            ];

            // Add PhilGEPS fields if threshold is >= 200k
            if ($amountThreshold === '>=200k') {
                $svpFields[] = 'philgeps_posting_ref_no';
                $svpFields[] = 'ads_post_ib';
            }

            // For each field, check if all selected items have the same value
            foreach ($svpFields as $field) {
                $values = $selectedItemsData->pluck($field)->toArray();
                $uniqueValues = array_unique($values);

                // If all values are identical, use that value; otherwise leave empty
                if (count($uniqueValues) === 1) {
                    $this->bulkEditData[$field] = reset($values) ?? '';
                } else {
                    $this->bulkEditData[$field] = '';
                }
            }
        }
    }

    private function validateBulkEditSelection(): array
    {
        $errors = [];
        $itemNumbers = [];
        $modes = [];
        $scheduleData = [];

        foreach ($this->selectedItems as $index) {
            $item = $this->form['items'][$index];
            $itemNumber = $item['item_no'];
            $modeId = $item['mode_of_procurement_id'] ?? null;

            $itemNumbers[] = $itemNumber;

            if ($modeId) {
                $modes[$modeId][] = $itemNumber;
            }

            // Collect schedule data for consistency check
            $schedule = [];
            if ($this->isCompetitiveBidding($modeId)) {
                // Bidding schedule fields
                $schedule = [
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
                ];
            } elseif ($this->isSvpMode($modeId)) {
                // SVP schedule fields
                $schedule = [
                    'rfq_no' => $item['rfq_no'] ?? null,
                    'canvass_date' => $item['canvass_date'] ?? null,
                    'date_returned_of_canvass' => $item['date_returned_of_canvass'] ?? null,
                    'abstract_of_canvass_date' => $item['abstract_of_canvass_date'] ?? null,
                    'resolution_number_mop' => $item['resolution_number_mop'] ?? null,
                ];
            }

            $scheduleData[$index] = $schedule;
        }

        // Check if all items have the same mode
        if (count($modes) > 1) {
            $modeDetails = [];
            foreach ($modes as $modeId => $items) {
                $modeName = $this->modeOfProcurements->firstWhere('id', $modeId)?->modeofprocurements ?? 'Unknown';
                $modeDetails[] = $modeName;
            }
            $errors[] = "Items have different modes: " . implode(', ', $modeDetails) . ". Bulk edit requires all selected items to have the same mode of procurement.";
        }

        if (empty($modes)) {
            $errors[] = "No valid mode selected. Please ensure all selected items have a mode of procurement.";
        }

        // Check if all items have identical schedule data (only when all have same mode)
        if (!empty($modes) && count($modes) === 1) {
            $scheduleHashes = [];
            $itemNumbersByHash = [];

            foreach ($this->selectedItems as $index) {
                $hash = md5(json_encode($scheduleData[$index]));
                $scheduleHashes[$index] = $hash;

                $itemNumber = $this->form['items'][$index]['item_no'];
                if (!isset($itemNumbersByHash[$hash])) {
                    $itemNumbersByHash[$hash] = [];
                }
                $itemNumbersByHash[$hash][] = $itemNumber;
            }

            $uniqueHashes = array_unique($scheduleHashes);

            if (count($uniqueHashes) > 1) {
                $errors[] = "Selected items have different schedule data. Bulk edit requires all selected items to have identical schedule data.";
            }
        }

        // Check amount threshold - ALWAYS use PR ABC for all modes (like Per-Lot)
        $commonMode = empty($modes) ? null : array_key_first($modes);
        $prAbc = $this->procurement->abc ?? 0;
        $amountThreshold = $prAbc >= self::ABC_THRESHOLD ? '>=200k' : '<200k';

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'itemNumbers' => $itemNumbers,
            'amountThreshold' => $amountThreshold,
            'commonMode' => $commonMode,
        ];
    }

    private function pluralize(int $count): string
    {
        return $count > 1 ? 's' : '';
    }

    public function applyBulkEdit(): void
    {
        if (empty($this->selectedItems)) {
            $this->closeBulkEditModal();
            return;
        }

        // Validate bulk edit data before applying
        if (!$this->validateBulkEditData()) {
            $errorMessage = implode(' ', $this->bulkEditErrors);
            LivewireAlert::title('Validation Failed')
                ->error()
                ->text($errorMessage)
                ->toast()
                ->position('top-end')
                ->show();
            return;
        }

        // Check permissions for modifying successful bids
        if (!$this->canModifySuccessfulBidsInBulk()) {
            return;
        }

        // Validate against clearing existing schedule data (MISSING from per-item!)
        if (!$this->validateBulkEditScheduleDeletion()) {
            return;
        }

        // Check if mode has changed or if any schedule field has data
        $modeId = $this->bulkEditData['mode_of_procurement_id'];
        $hasScheduleData = false;
        $hasModeChange = false;

        // Check if mode is changing for any selected item
        foreach ($this->selectedItems as $index) {
            $originalMode = $this->form['items'][$index]['mode_of_procurement_id'] ?? null;
            if ($originalMode != $modeId) {
                $hasModeChange = true;
                break;
            }
        }

        // Check if any schedule fields have data
        if ($this->isCompetitiveBidding($modeId)) {
            $biddingFields = [
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
                'resolution_number_mop'
            ];

            foreach ($biddingFields as $field) {
                if (!empty($this->bulkEditData[$field])) {
                    $hasScheduleData = true;
                    break;
                }
            }
        } elseif ($this->isSvpMode($modeId)) {
            $svpFields = [
                'rfq_no',
                'canvass_date',
                'date_returned_of_canvass',
                'abstract_of_canvass_date',
                'resolution_number_mop'
            ];

            if ($this->bulkEditData['amount_threshold'] === '>=200k') {
                $svpFields[] = 'philgeps_posting_ref_no';
                $svpFields[] = 'ads_post_ib';
            }

            foreach ($svpFields as $field) {
                if (!empty($this->bulkEditData[$field])) {
                    $hasScheduleData = true;
                    break;
                }
            }
        }

        // Allow save if mode changed OR schedule fields have data
        if (!$hasModeChange && !$hasScheduleData) {
            LivewireAlert::title('No Changes')
                ->warning()
                ->text('Please change the mode or fill in at least one field to update.')
                ->toast()
                ->position('top-end')
                ->show();
            return;
        }

        // Apply changes to selected items
        foreach ($this->selectedItems as $index) {
            // Update mode if changed
            $originalMode = $this->form['items'][$index]['mode_of_procurement_id'];
            if ($originalMode != $modeId) {
                $this->form['items'][$index]['mode_of_procurement_id'] = $modeId;
                // Clear fields from old mode when mode changes
                $this->clearFieldsForModeChange($index, $originalMode, $modeId);
            }

            if ($this->isCompetitiveBidding($modeId)) {
                // Update bidding fields
                $this->updateBiddingFields($index);
            } elseif ($this->isSvpMode($modeId)) {
                // Update SVP fields
                $this->updateSvpFields($index);
            }
        }

        // SAVE TO DATABASE
        $this->saveTab1();

        LivewireAlert::title('Bulk Edit Applied')
            ->success()
            ->text(count($this->selectedItems) . ' items updated successfully.')
            ->toast()
            ->position('top-end')
            ->show();

        // Reload data to refresh the form with latest values
        // Modal stays open so user can verify changes or continue editing
        $this->mount($this->procurement);

        // Re-initialize bulk edit data with fresh values
        if (!empty($this->selectedItems)) {
            $validation = $this->validateBulkEditSelection();
            if ($validation['valid']) {
                $firstItem = $this->form['items'][$this->selectedItems[0]];
                $modeId = $validation['commonMode'] ?? $firstItem['mode_of_procurement_id'] ?? 1;
                $amountThreshold = $validation['amountThreshold'];

                $this->bulkEditData = [
                    'mode_of_procurement_id' => $modeId,
                    'amount_threshold' => $amountThreshold,
                    'items_count' => count($this->selectedItems),
                    'item_numbers' => $validation['itemNumbers'],
                ];

                $this->initializeBulkEditFields($modeId, $firstItem, $amountThreshold);
            }
        }
    }

    private function clearFieldsForModeChange($index, $oldMode, $newMode)
    {
        // Clear bidding fields if switching from bidding to SVP
        if ($this->isCompetitiveBidding($oldMode) && $this->isSvpMode($newMode)) {
            $biddingFields = [
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
                'resolution_number_mop'
            ];
            foreach ($biddingFields as $field) {
                $this->form['items'][$index][$field] = '';
            }
        }

        // Clear SVP fields if switching from SVP to bidding
        if ($this->isSvpMode($oldMode) && $this->isCompetitiveBidding($newMode)) {
            $svpFields = [
                'rfq_no',
                'canvass_date',
                'date_returned_of_canvass',
                'abstract_of_canvass_date',
                'resolution_number_mop',
                'philgeps_posting_ref_no',
                'ads_post_ib'
            ];
            foreach ($svpFields as $field) {
                $this->form['items'][$index][$field] = '';
            }
        }
    }

    private function validateBulkEditData(): bool
    {
        $this->bulkEditErrors = [];
        $modeId = $this->bulkEditData['mode_of_procurement_id'] ?? null;

        if (!$modeId) {
            $this->bulkEditErrors[] = 'Mode of Procurement is required.';
            return false;
        }

        // COMPETITIVE BIDDING MODES
        if ($this->isCompetitiveBidding($modeId)) {
            // Validate bidding number per item against expected sequence
            if ($this->hasValue($this->bulkEditData['bidding_number'] ?? '')) {
                $enteredBiddingNumber = (string) $this->bulkEditData['bidding_number'];

                foreach ($this->selectedItems as $index) {
                    $item = $this->form['items'][$index] ?? null;
                    if (!$item)
                        continue;

                    $prItemID = $item['prItemID'];
                    $itemNo = $item['item_no'] ?? "Item #{$index}";
                    $mopUid = $item['uid'] ?? null;
                    if (!$mopUid)
                        continue;

                    $matchCriteria = ['ref_id' => $prItemID, 'mop_uid' => $mopUid];
                    $existingBidSchedule = BidSchedule::where($matchCriteria)->first();

                    if ($existingBidSchedule && $this->hasValue($existingBidSchedule->uid)) {
                        $uidParts = explode('-', $existingBidSchedule->uid);
                        $expectedBiddingNumber = (string) end($uidParts);
                        if ($enteredBiddingNumber !== $expectedBiddingNumber) {
                            $this->bulkEditErrors[] = "Item {$itemNo}: Bidding Number must be \"{$expectedBiddingNumber}\" (based on record uid: {$existingBidSchedule->uid}).";
                        }
                    } else {
                        $relatedMopUids = MopItem::where('prItemID', $prItemID)
                            ->where('mode_of_procurement_id', $modeId)
                            ->pluck('uid');
                        $count = BidSchedule::where('ref_id', $prItemID)
                            ->whereIn('mop_uid', $relatedMopUids)
                            ->count();
                        $expectedBiddingNumber = (string) ($count + 1);
                        if ($enteredBiddingNumber !== $expectedBiddingNumber) {
                            $this->bulkEditErrors[] = "Item {$itemNo}: Bidding Number must be \"{$expectedBiddingNumber}\" (next available sequence).";
                        }
                    }
                }

                if (!empty($this->bulkEditErrors)) {
                    return false;
                }
            }

            // Validate Bidding Result dependencies
            $biddingResult = $this->bulkEditData['bidding_result'] ?? null;

            if ($this->hasValue($biddingResult)) {
                $missingFields = [];
                $hasPreProcConference = $this->hasValue($this->bulkEditData['pre_proc_conference'] ?? '');

                if (!$hasPreProcConference) {
                    if (!$this->hasValue($this->bulkEditData['bidding_number'] ?? '')) {
                        $missingFields[] = 'Bidding #';
                    }
                    if (!$this->hasValue($this->bulkEditData['ib_number'] ?? '')) {
                        $missingFields[] = 'IB No.';
                    }
                    if (!$this->hasValue($this->bulkEditData['sub_open_bids'] ?? '')) {
                        $missingFields[] = 'Submission & Opening of Bids';
                    }

                    if (!empty($missingFields)) {
                        $fieldsList = implode(', ', $missingFields);
                        $this->bulkEditErrors[] = "Competitive Bidding: Cannot set Bidding Result without {$fieldsList} or Pre-Proc Conference.";
                    }
                }

                if ($biddingResult === 'SUCCESSFUL') {
                    $successMissingFields = [];

                    if (!$this->hasValue($this->bulkEditData['bid_evaluation_date'] ?? '')) {
                        $successMissingFields[] = 'Bid Evaluation Date';
                    }
                    if (!$this->hasValue($this->bulkEditData['post_qualification_date'] ?? '')) {
                        $successMissingFields[] = 'Post Qualification Date';
                    }

                    if (!empty($successMissingFields)) {
                        $fieldsList = implode(', ', $successMissingFields);
                        $this->bulkEditErrors[] = "Competitive Bidding: {$fieldsList} required for SUCCESSFUL bidding result.";
                    }
                }

                // Validate Resolution Number for Bidding Result
                if (!$this->hasValue($this->bulkEditData['resolution_number_mop'] ?? '')) {
                    $this->bulkEditErrors[] = 'Competitive Bidding: Resolution Number is required when Bidding Result is set.';
                }
            }
        }

        return empty($this->bulkEditErrors);
    }

    private function canModifySuccessfulBidsInBulk(): bool
    {
        // Check if user has permission to modify items with post-procurement data
        if (!auth()->user()->can('edit_mode::of::procurement')) {
            foreach ($this->selectedItems as $index) {
                $item = $this->form['items'][$index];
                $biddingResult = $item['bidding_result'] ?? '';

                if ($biddingResult === 'SUCCESSFUL') {
                    $prItemID = $item['prItemID'];

                    $hasPostData = \App\Models\PostProcurement::where('ref_id', $prItemID)->exists();

                    if ($hasPostData) {
                        $itemNo = $item['item_no'] ?? ($index + 1);
                        LivewireAlert::title('Permission Required')
                            ->error()
                            ->text("Cannot modify Item #{$itemNo} - it has a SUCCESSFUL bidding result with post-procurement data. This requires 'Edit Mode of Procurement' permission.")
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

    private function validateBulkEditScheduleDeletion(): bool
    {
        // Check if we're trying to clear existing schedules without providing new data
        $modeId = $this->bulkEditData['mode_of_procurement_id'];

        foreach ($this->selectedItems as $index) {
            $item = $this->form['items'][$index];
            $currentModeId = $item['mode_of_procurement_id'] ?? null;

            // Only check if mode is staying the same (not adding new mode)
            if ($currentModeId == $modeId) {
                // For competitive bidding
                if ($this->isCompetitiveBidding($modeId)) {
                    $hasExistingBiddingData = $this->hasValue($item['bidding_number']) ||
                        $this->hasValue($item['ib_number']) ||
                        $this->hasValue($item['sub_open_bids']);

                    $clearingBiddingData = !$this->hasValue($this->bulkEditData['bidding_number'] ?? '') &&
                        !$this->hasValue($this->bulkEditData['ib_number'] ?? '') &&
                        !$this->hasValue($this->bulkEditData['sub_open_bids'] ?? '') &&
                        !$this->hasValue($this->bulkEditData['pre_proc_conference'] ?? '');

                    if ($hasExistingBiddingData && $clearingBiddingData) {
                        $itemNo = $item['item_no'] ?? ($index + 1);
                        LivewireAlert::title('Cannot Clear Existing Data')
                            ->error()
                            ->text("Cannot clear existing bidding data for Item #{$itemNo} without providing replacement data or Pre-Proc Conference.")
                            ->toast()
                            ->position('top-end')
                            ->show();
                        return false;
                    }
                }

                // For SVP/Alternative modes
                if ($this->isSvpMode($modeId)) {
                    $hasExistingSvpData = $this->hasValue($item['rfq_no']) ||
                        $this->hasValue($item['canvass_date']) ||
                        $this->hasValue($item['abstract_of_canvass_date']);

                    $clearingSvpData = !$this->hasValue($this->bulkEditData['rfq_no'] ?? '') &&
                        !$this->hasValue($this->bulkEditData['canvass_date'] ?? '') &&
                        !$this->hasValue($this->bulkEditData['abstract_of_canvass_date'] ?? '');

                    if ($hasExistingSvpData && $clearingSvpData) {
                        $itemNo = $item['item_no'] ?? ($index + 1);
                        LivewireAlert::title('Cannot Clear Existing Data')
                            ->error()
                            ->text("Cannot clear existing SVP data for Item #{$itemNo} without providing replacement data.")
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

    private function updateBiddingFields($index): void
    {
        $fields = [
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
            'resolution_number_mop'
        ];

        foreach ($fields as $field) {
            if (!empty($this->bulkEditData[$field])) {
                $this->form['items'][$index][$field] = $this->bulkEditData[$field];
            }
        }
    }

    private function updateSvpFields($index): void
    {
        $fields = [
            'rfq_no',
            'canvass_date',
            'date_returned_of_canvass',
            'abstract_of_canvass_date',
            'resolution_number_mop'
        ];

        if ($this->bulkEditData['amount_threshold'] === '>=200k') {
            $fields[] = 'philgeps_posting_ref_no';
            $fields[] = 'ads_post_ib';
        }

        foreach ($fields as $field) {
            if (!empty($this->bulkEditData[$field])) {
                $this->form['items'][$index][$field] = $this->bulkEditData[$field];
            }
        }
    }
    public function bulkAddMode(): void
    {
        // Validate that selected items can accept a new mode
        if (empty($this->selectedItems)) {
            LivewireAlert::title('Error')
                ->error()
                ->text('No items selected.')
                ->toast()
                ->position('top-end')
                ->show();
            return;
        }

        // Check if ALL selected items can add a new mode
        foreach ($this->selectedItems as $index) {
            if (!isset($this->form['items'][$index])) {
                continue;
            }

            $item = $this->form['items'][$index];
            $modeId = $item['mode_of_procurement_id'] ?? null;

            // Cannot add for SVP modes
            if ($this->isSvpMode($modeId)) {
                LivewireAlert::title('Cannot Add Mode')
                    ->warning()
                    ->text('Cannot add new mode for SVP/Alternative modes.')
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

            // Can add if: mode_id = 1 (Shopping/Pending) OR (has bidding data/pre-proc AND result is UNSUCCESSFUL)
            $canAdd = $modeId == 1 ||
                (($hasBiddingData || $hasPreProcConference) && $bidResult === 'UNSUCCESSFUL');

            if (!$canAdd) {
                LivewireAlert::title('Cannot Add Mode')
                    ->warning()
                    ->text('Items must have UNSUCCESSFUL bidding result or be in Shopping mode to add new modes.')
                    ->toast()
                    ->position('top-end')
                    ->show();
                return;
            }
        }

        // All validations passed - enable the add form
        // Preserve metadata before clearing
        $preservedValues = [
            'amount_threshold' => $this->bulkEditData['amount_threshold'] ?? null,
            'items_count' => $this->bulkEditData['items_count'] ?? 0,
            'item_numbers' => $this->bulkEditData['item_numbers'] ?? [],
        ];

        $this->bulkEditData['mode_of_procurement_id'] = null;
        $this->clearBulkEditScheduleFields();

        // Restore preserved metadata
        $this->bulkEditData = array_merge($this->bulkEditData, $preservedValues);
        $this->showAddForm = true;
    }

    public function closeBulkEditModal(): void
    {
        $this->showBulkEditModal = false;
        $this->showAddForm = false;
        $this->clearBulkEditScheduleFields();
        $this->bulkEditErrors = [];
        // Keep items selected when modal closes
    }

    /**
     * Clear all bulk edit schedule fields to prevent data leakage between sessions
     */
    private function clearBulkEditScheduleFields(): void
    {
        $this->bulkEditData = [
            // Reset mode-related fields
            'mode_of_procurement_id' => null,
            'amount_threshold' => null,
            'items_count' => 0,
            'item_numbers' => [],

            // Clear competitive bidding fields
            'bidding_number' => '',
            'ib_number' => '',
            'philgeps_posting_ref_no' => '',
            'ads_post_ib' => '',
            'pre_proc_conference' => '',
            'list_invited_observers' => '',
            'obsrvr_prebid_conf' => '',
            'obsrvr_eligibility' => '',
            'obsrvr_sub_open_of_bid' => '',
            'obsrvr_bid' => '',
            'obsrvr_post_qual' => '',
            'pre_bid_conf' => '',
            'eligibility_check' => '',
            'sub_open_bids' => '',
            'bid_evaluation_date' => '',
            'post_qualification_date' => '',
            'bidding_result' => '',

            // Clear SVP fields
            'rfq_no' => '',
            'canvass_date' => '',
            'date_returned_of_canvass' => '',
            'abstract_of_canvass_date' => '',

            // Clear common field
            'resolution_number_mop' => '',
        ];
    }

    private function validatePostBulkEditSelection(): array
    {
        $errors = [];
        $itemNumbers = [];
        $postData = [];

        foreach ($this->selectedPostItems as $prItemID) {
            // Find the item to get item_no
            $itemNumber = null;
            foreach ($this->form['items'] as $item) {
                if (($item['prItemID'] ?? null) === $prItemID) {
                    $itemNumber = $item['item_no'] ?? $prItemID;
                    break;
                }
            }

            $itemNumbers[] = $itemNumber;

            // Collect post procurement data for consistency check
            $postItem = $this->postItems[$prItemID] ?? [];
            $postData[$prItemID] = [
                'resolutionAwardNumber' => $postItem['resolutionAwardNumber'] ?? null,
                'noticeOfAwardNumber' => $postItem['noticeOfAwardNumber'] ?? null,
                'noticeOfAward' => $postItem['noticeOfAward'] ?? null,
                'resolutionAwardDate' => $postItem['resolutionAwardDate'] ?? null,
                'awardedAmount' => $postItem['awardedAmount'] ?? null,
                'philgepsNoticeOfAwardNo' => $postItem['philgepsNoticeOfAwardNo'] ?? null,
                'philgepsPostingOfAward' => $postItem['philgepsPostingOfAward'] ?? null,
                'supplier_id' => $postItem['supplier_id'] ?? null,
            ];
        }

        // Check if all items have identical post procurement data
        $postDataHashes = [];
        $itemNumbersByHash = [];

        foreach ($this->selectedPostItems as $prItemID) {
            $hash = md5(json_encode($postData[$prItemID]));
            $postDataHashes[$prItemID] = $hash;

            $itemNumber = null;
            foreach ($this->form['items'] as $item) {
                if (($item['prItemID'] ?? null) === $prItemID) {
                    $itemNumber = $item['item_no'] ?? $prItemID;
                    break;
                }
            }

            if (!isset($itemNumbersByHash[$hash])) {
                $itemNumbersByHash[$hash] = [];
            }
            $itemNumbersByHash[$hash][] = $itemNumber;
        }

        $uniqueHashes = array_unique($postDataHashes);

        if (count($uniqueHashes) > 1) {
            $errors[] = "Selected items have different post procurement field values. Bulk edit requires all selected items to have identical field values.";
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'itemNumbers' => $itemNumbers,
        ];
    }

    // Post Procurement Bulk Edit Methods
    public function openPostBulkEditModal(): void
    {
        if (empty($this->selectedPostItems)) {
            LivewireAlert::title('No Items Selected')
                ->warning()
                ->text('Please select at least one item to edit.')
                ->toast()
                ->position('top-end')
                ->show();
            return;
        }

        // Validate selected items (like Tab 1)
        $validation = $this->validatePostBulkEditSelection();

        if (!$validation['valid']) {
            $this->postBulkEditErrors = $validation['errors'];
            $errorMessage = implode(' ', $validation['errors']);
            LivewireAlert::title('Bulk Edit Validation Failed')
                ->error()
                ->text($errorMessage)
                ->toast()
                ->position('top-end')
                ->show();
            return;
        }

        // Get item numbers for display
        $itemNumbers = $validation['itemNumbers'];

        // Get existing data from first selected item (like Tab 1 does)
        $firstPrItemID = $this->selectedPostItems[0] ?? null;
        $firstPostItem = $firstPrItemID && isset($this->postItems[$firstPrItemID])
            ? $this->postItems[$firstPrItemID]
            : [];

        // Initialize post bulk edit data with single set of fields (like Tab 1 bulk edit)
        // Populate with existing data from first item (using camelCase field names from postItems)
        $this->postBulkEditData = [
            'items_count' => count($this->selectedPostItems),
            'item_numbers' => $itemNumbers,
            'selected_items' => [], // Will hold item info for display
            'resolutionAwardNumber' => $firstPostItem['resolutionAwardNumber'] ?? '',
            'noticeOfAwardNumber' => $firstPostItem['noticeOfAwardNumber'] ?? '',
            'noticeOfAward' => $firstPostItem['noticeOfAward'] ?? '',
            'resolutionAwardDate' => $firstPostItem['resolutionAwardDate'] ?? '',
            'awardedAmount' => $firstPostItem['awardedAmount'] ?? '',
            'philgepsNoticeOfAwardNo' => $firstPostItem['philgepsNoticeOfAwardNo'] ?? '',
            'philgepsPostingOfAward' => $firstPostItem['philgepsPostingOfAward'] ?? '',
            'supplier_id' => $firstPostItem['supplier_id'] ?? '',
            'dateReceiptOfSupplierNoa' => $firstPostItem['dateReceiptOfSupplierNoa'] ?? '',
        ];

        // Populate selected_items for display in the table
        foreach ($this->selectedPostItems as $prItemID) {
            foreach ($this->form['items'] as $item) {
                if (($item['prItemID'] ?? null) === $prItemID) {
                    $this->postBulkEditData['selected_items'][] = [
                        'prItemID' => $prItemID,
                        'item_no' => $item['item_no'] ?? '',
                        'description' => $item['description'] ?? '',
                    ];
                    break;
                }
            }
        }

        $this->postBulkEditErrors = [];
        $this->showPostBulkEditModal = true;
    }

    public function applyPostBulkEdit(): void
    {
        if (empty($this->selectedPostItems)) {
            $this->closePostBulkEditModal();
            return;
        }

        // Validate post bulk edit data (like Tab 1 bulk edit)
        $this->postBulkEditErrors = [];
        $hasData = false;

        $fields = [
            'resolutionAwardNumber',
            'noticeOfAwardNumber',
            'noticeOfAward',
            'resolutionAwardDate',
            'awardedAmount',
            'philgepsNoticeOfAwardNo',
            'philgepsPostingOfAward',
            'supplier_id',
            'dateReceiptOfSupplierNoa',
        ];

        foreach ($fields as $field) {
            if (!empty($this->postBulkEditData[$field])) {
                $hasData = true;
                break;
            }
        }

        if (!$hasData) {
            LivewireAlert::title('No Changes')
                ->warning()
                ->text('Please fill in at least one field to update.')
                ->toast()
                ->position('top-end')
                ->show();
            return;
        }

        // Apply changes to selected post items (like Tab 1 bulk edit)
        DB::transaction(function () {
            foreach ($this->selectedPostItems as $prItemID) {
                $postData = [
                    'ref_id' => $prItemID,
                    'resolution_award_number' => $this->postBulkEditData['resolutionAwardNumber'] ?: null,
                    'notice_of_award_number' => $this->postBulkEditData['noticeOfAwardNumber'] ?: null,
                    'notice_of_award' => $this->postBulkEditData['noticeOfAward'] ?: null,
                    'resolution_award_date' => $this->nullableDate($this->postBulkEditData['resolutionAwardDate']),
                    'awarded_amount' => $this->cleanAmount($this->postBulkEditData['awardedAmount']) ?: null,
                    'philgeps_notice_of_award_no' => $this->postBulkEditData['philgepsNoticeOfAwardNo'] ?: null,
                    'philgeps_posting_of_award' => $this->nullableDate($this->postBulkEditData['philgepsPostingOfAward']),
                    'supplier_id' => $this->postBulkEditData['supplier_id'] ?: null,
                    'date_receipt_of_supplier_noa' => $this->nullableDate($this->postBulkEditData['dateReceiptOfSupplierNoa']),
                ];

                PostProcurement::updateOrCreate(
                    ['ref_id' => $prItemID],
                    $postData
                );
            }
        });

        // Reload post procurement data
        $this->loadPostProcurementData($this->procurement);

        // Refresh the modal data with updated values from database
        $this->refreshPostBulkEditData();

        LivewireAlert::title('Post Procurement Bulk Edit Applied')
            ->success()
            ->text(count($this->selectedPostItems) . ' items updated successfully.')
            ->toast()
            ->position('top-end')
            ->show();

        // Modal remains open to allow user to verify changes or make additional edits
        // User can manually close modal when done
    }

    public function closePostBulkEditModal(): void
    {
        $this->showPostBulkEditModal = false;
        $this->postBulkEditData = [];
        $this->postBulkEditErrors = [];
        // Keep items selected when modal closes
    }

    /**
     * Refresh post bulk edit modal data after save
     * Reloads fresh values from database to show updated data
     */
    private function refreshPostBulkEditData(): void
    {
        if (empty($this->selectedPostItems)) {
            return;
        }

        // Get first item to check if all have same values
        $firstPrItemID = $this->selectedPostItems[0] ?? null;
        if (!$firstPrItemID || !isset($this->postItems[$firstPrItemID])) {
            return;
        }

        $firstPostItem = $this->postItems[$firstPrItemID];
        $allIdentical = true;

        // Check if all selected items have identical post data
        foreach ($this->selectedPostItems as $prItemID) {
            $postItem = $this->postItems[$prItemID] ?? [];

            if (
                ($postItem['resolutionAwardNumber'] ?? null) !== ($firstPostItem['resolutionAwardNumber'] ?? null) ||
                ($postItem['resolutionAwardDate'] ?? null) !== ($firstPostItem['resolutionAwardDate'] ?? null) ||
                ($postItem['noticeOfAwardNumber'] ?? null) !== ($firstPostItem['noticeOfAwardNumber'] ?? null) ||
                ($postItem['noticeOfAward'] ?? null) !== ($firstPostItem['noticeOfAward'] ?? null) ||
                ($postItem['awardedAmount'] ?? null) !== ($firstPostItem['awardedAmount'] ?? null) ||
                ($postItem['philgepsNoticeOfAwardNo'] ?? null) !== ($firstPostItem['philgepsNoticeOfAwardNo'] ?? null) ||
                ($postItem['philgepsPostingOfAward'] ?? null) !== ($firstPostItem['philgepsPostingOfAward'] ?? null) ||
                ($postItem['supplier_id'] ?? null) !== ($firstPostItem['supplier_id'] ?? null) ||
                ($postItem['dateReceiptOfSupplierNoa'] ?? null) !== ($firstPostItem['dateReceiptOfSupplierNoa'] ?? null)
            ) {
                $allIdentical = false;
                break;
            }
        }

        // Update the form with fresh values from database (if all identical)
        if ($allIdentical && isset($this->postBulkEditData)) {
            $this->postBulkEditData['resolutionAwardNumber'] = $firstPostItem['resolutionAwardNumber'] ?? '';
            $this->postBulkEditData['resolutionAwardDate'] = $firstPostItem['resolutionAwardDate'] ?? '';
            $this->postBulkEditData['noticeOfAwardNumber'] = $firstPostItem['noticeOfAwardNumber'] ?? '';
            $this->postBulkEditData['noticeOfAward'] = $firstPostItem['noticeOfAward'] ?? '';
            $this->postBulkEditData['awardedAmount'] = $firstPostItem['awardedAmount'] ?? ''; // Raw value for Alpine mask
            $this->postBulkEditData['philgepsNoticeOfAwardNo'] = $firstPostItem['philgepsNoticeOfAwardNo'] ?? '';
            $this->postBulkEditData['philgepsPostingOfAward'] = $firstPostItem['philgepsPostingOfAward'] ?? '';
            $this->postBulkEditData['supplier_id'] = $firstPostItem['supplier_id'] ?? '';
            $this->postBulkEditData['dateReceiptOfSupplierNoa'] = $firstPostItem['dateReceiptOfSupplierNoa'] ?? '';
        }
    }

    public function toggleAllPostItems(): void
    {
        $availablePrItemIDs = [];
        foreach ($this->form['items'] ?? [] as $item) {
            $modeId = $item['mode_of_procurement_id'] ?? null;
            $prItemID = $item['prItemID'] ?? null;

            if (!$prItemID)
                continue;

            // Check for SUCCESSFUL bidding in competitive modes
            if ($this->isCompetitiveBidding($modeId)) {
                $bidResult = $item['bidding_result'] ?? '';
                if ($bidResult === 'SUCCESSFUL') {
                    $availablePrItemIDs[] = $prItemID;
                    continue;
                }
            }

            // Check for complete SVP data in alternative modes
            if ($this->isSvpMode($modeId)) {
                if (
                    !empty($item['resolution_number_mop']) &&
                    !empty($item['rfq_no']) &&
                    !empty($item['canvass_date']) &&
                    !empty($item['date_returned_of_canvass']) &&
                    !empty($item['abstract_of_canvass_date'])
                ) {
                    $availablePrItemIDs[] = $prItemID;
                }
            }
        }

        if (count($this->selectedPostItems) === count($availablePrItemIDs)) {
            // All are selected, deselect all
            $this->selectedPostItems = [];
        } else {
            // Select all available items
            $this->selectedPostItems = $availablePrItemIDs;
        }
    }

    public function togglePostItemSelection($prItemID): void
    {
        if (in_array($prItemID, $this->selectedPostItems)) {
            $this->selectedPostItems = array_diff($this->selectedPostItems, [$prItemID]);
        } else {
            $this->selectedPostItems[] = $prItemID;
        }
    }

    public function deselectAllPostItems(): void
    {
        $this->selectedPostItems = [];

        // Dispatch event to uncheck all post checkboxes via JavaScript
        $this->dispatch('post-bulk-edit-closed');
    }

    public function getPostAvailableItemsProperty(): array
    {
        $postAvailableItems = [];
        $seenPrItemIds = []; // Track which prItemIDs we've already processed

        foreach ($this->form['items'] ?? [] as $index => $item) {
            $prItemID = $item['prItemID'] ?? null;

            // Skip if we've already processed this prItemID (only check current mode, not history)
            if (!$prItemID || in_array($prItemID, $seenPrItemIds)) {
                continue;
            }

            $seenPrItemIds[] = $prItemID;
            $modeId = $item['mode_of_procurement_id'] ?? null;

            // Check for SUCCESSFUL bidding in competitive modes
            if ($this->isCompetitiveBidding($modeId)) {
                $bidResult = $item['bidding_result'] ?? '';

                if ($bidResult === 'SUCCESSFUL') {
                    $postAvailableItems[$index] = $item;
                    continue;
                }
            }

            // Check for complete SVP data in alternative modes
            if ($this->isSvpMode($modeId)) {
                if (
                    $this->hasValue($item['resolution_number_mop']) &&
                    $this->hasValue($item['rfq_no']) &&
                    $this->hasValue($item['canvass_date']) &&
                    $this->hasValue($item['date_returned_of_canvass']) &&
                    $this->hasValue($item['abstract_of_canvass_date'])
                ) {
                    $postAvailableItems[$index] = $item;
                }
            }
        }

        return $postAvailableItems;
    }

    public function nextPage(): void
    {
        $this->currentPage++;
    }

    public function previousPage(): void
    {
        if ($this->currentPage > 1) {
            $this->currentPage--;
        }
    }

    public function gotoPage($page): void
    {
        $this->currentPage = $page;
    }

    public function updatedPerPage(): void
    {
        // Reset to page 1 when items per page changes
        $this->currentPage = 1;
    }

    public function getPaginatedItemsProperty(): array
    {
        // Get unique prItemIDs for pagination
        $uniquePrItemIDs = collect($this->form['items'])
            ->pluck('prItemID')
            ->unique()
            ->values()
            ->all();

        $totalUniqueItems = count($uniquePrItemIDs);
        $totalPages = max(1, ceil($totalUniqueItems / $this->perPage));

        // Ensure current page is within bounds
        if ($this->currentPage > $totalPages) {
            $this->currentPage = $totalPages;
        }

        // Get paginated prItemIDs
        $offset = ($this->currentPage - 1) * $this->perPage;
        $paginatedPrItemIDs = array_slice($uniquePrItemIDs, $offset, $this->perPage);

        // Filter items to only include those in current page - PRESERVE ORIGINAL KEYS
        $paginatedItems = [];
        foreach ($this->form['items'] as $originalIndex => $item) {
            if (in_array($item['prItemID'], $paginatedPrItemIDs)) {
                $paginatedItems[$originalIndex] = $item;
            }
        }

        return $paginatedItems;
    }

    public function getPaginationDataProperty(): array
    {
        $uniquePrItemIDs = collect($this->form['items'])
            ->pluck('prItemID')
            ->unique()
            ->values()
            ->all();

        $total = count($uniquePrItemIDs);
        $totalPages = max(1, ceil($total / $this->perPage));
        $from = $total > 0 ? (($this->currentPage - 1) * $this->perPage) + 1 : 0;
        $to = min($this->currentPage * $this->perPage, $total);

        return [
            'currentPage' => $this->currentPage,
            'perPage' => $this->perPage,
            'total' => $total,
            'totalPages' => $totalPages,
            'from' => $from,
            'to' => $to,
            'hasMorePages' => $this->currentPage < $totalPages,
            'hasPreviousPages' => $this->currentPage > 1,
        ];
    }

    public function render()
    {
        return view('livewire.mode-of-procurement.mode-of-procurement-per-item-page', [
            'modeOfProcurements' => $this->modeOfProcurements,
            'suppliers' => $this->suppliers,
        ]);
    }

    // ============================================================================
    // FORWARD TO PMU
    // ============================================================================

    /**
     * Check if at least one selected post item has all 6 required fields filled
     */
    public function getCanForwardToPmuProperty(): bool
    {
        if (empty($this->selectedPostItems)) {
            return false;
        }

        foreach ($this->selectedPostItems as $prItemID) {
            $post = PostProcurement::where('ref_id', $prItemID)->first();

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
     * Count how many selected post items qualify for forwarding
     */
    public function getEligibleForwardCountProperty(): int
    {
        $count = 0;

        foreach ($this->selectedPostItems as $prItemID) {
            $post = PostProcurement::where('ref_id', $prItemID)->first();

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
     * Get forwarded vs pending summary for selected post items
     * Stage 7 is recorded at prItemID level (one per item)
     */
    public function getForwardedToPmuSummaryProperty(): array
    {
        $forwarded = 0;
        $pending = 0;

        foreach ($this->selectedPostItems as $prItemID) {
            $exists = PrItemPrstage::where('prItemID', $prItemID)
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
     * Resolve unique procIDs from the selected prItemIDs
     */
    private function getSelectedProcIds(): array
    {
        $procIds = [];
        foreach ($this->selectedPostItems as $prItemID) {
            $item = collect($this->form['items'])->firstWhere('prItemID', $prItemID);
            if ($item && !empty($item['procID'])) {
                $procIds[$item['procID']] = $item['procID'];
            }
        }
        return array_values($procIds);
    }

    /**
     * Open the Forward to PMU modal
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

        // Collect ineligible items
        $ineligibleItems = [];
        foreach ($this->selectedPostItems as $prItemID) {
            $post = PostProcurement::where('ref_id', $prItemID)->first();

            $isEligible = $post &&
                $this->hasValue($post->resolution_award_number) &&
                $this->hasValue($post->resolution_award_date) &&
                $this->hasValue($post->notice_of_award_number) &&
                $this->hasValue($post->notice_of_award) &&
                $this->hasValue($post->awarded_amount) &&
                $this->hasValue($post->supplier_id);

            if (!$isEligible) {
                $item = collect($this->form['items'])->firstWhere('prItemID', $prItemID);
                $ineligibleItems[] = 'Item #' . ($item['item_no'] ?? $prItemID);
            }
        }

        if (!empty($ineligibleItems)) {
            $list = implode(', ', $ineligibleItems);
            LivewireAlert::title('Cannot Forward to PMU')
                ->error()
                ->text("The following item(s) are missing required post-procurement fields (Resolution Award Number/Date, Notice of Award Number/Date, Awarded Amount, or Supplier): {$list}. Please complete all fields before forwarding.")
                ->toast()
                ->position('top-end')
                ->timer(8000)
                ->show();
            return;
        }

        // Check already-forwarded status (at prItemID level)
        $alreadyForwardedCount = 0;
        foreach ($this->selectedPostItems as $prItemID) {
            if (PrItemPrstage::where('prItemID', $prItemID)->where('pr_stage_id', 7)->whereNotNull('actual_date_forwarded')->exists()) {
                $alreadyForwardedCount++;
            }
        }

        if ($alreadyForwardedCount > 0 && $alreadyForwardedCount === count($this->selectedPostItems)) {
            LivewireAlert::title('Already Forwarded')
                ->warning()
                ->text('All selected items are already forwarded to PMU. Proceeding will update the forwarded date.')
                ->toast()
                ->position('top-end')
                ->timer(5000)
                ->show();
        }

        // Pre-fill date from existing PrItemPrstage records if consistent
        $dates = [];
        foreach ($this->selectedPostItems as $prItemID) {
            $stage = PrItemPrstage::where('prItemID', $prItemID)
                ->where('pr_stage_id', 7)
                ->orderBy('id', 'desc')
                ->first();
            if ($stage && $stage->actual_date_forwarded) {
                $dates[] = $stage->actual_date_forwarded instanceof \Carbon\Carbon
                    ? $stage->actual_date_forwarded->format('Y-m-d H:i:s')
                    : $stage->actual_date_forwarded;
            }
        }

        $uniqueDates = array_unique($dates);
        $this->actualDateForwarded = count($uniqueDates) === 1
            ? Carbon::parse(reset($uniqueDates))->setTimezone('Asia/Manila')->format('Y-m-d\TH:i')
            : now('Asia/Manila')->format('Y-m-d\TH:i');

        $this->showForwardModal = true;
    }

    /**
     * Open the Forward to PMU modal pre-selected for a single item
     * Used by the edit pencil button on already-forwarded rows
     */
    public function openForwardModalForItem(string $prItemID): void
    {
        $this->selectedPostItems = [$prItemID];
        $this->openForwardModal();
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
     * Forward selected items to PMU (Stage 7)
     * PrItemPrstage stage 7 is written per prItemID
     * Pmu record is upserted per item via notice_of_award_number
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
                // Stage 7 per prItemID
                foreach ($this->selectedPostItems as $prItemID) {
                    $prItem = collect($this->form['items'])->firstWhere('prItemID', $prItemID);
                    if (!$prItem || empty($prItem['procID'])) {
                        continue;
                    }

                    $latestItemStage = PrItemPrstage::where('procID', $prItem['procID'])
                        ->where('prItemID', $prItemID)
                        ->orderBy('created_at', 'desc')
                        ->orderBy('id', 'desc')
                        ->first();

                    if ($latestItemStage && $latestItemStage->pr_stage_id == 7) {
                        // Already at stage 7 — update the existing record to refresh history
                        $previousItemStage = PrItemPrstage::where('procID', $prItem['procID'])
                            ->where('prItemID', $prItemID)
                            ->where('id', '<', $latestItemStage->id)
                            ->orderBy('created_at', 'desc')
                            ->orderBy('id', 'desc')
                            ->first();

                        $latestItemStage->update([
                            'stage_history' => $previousItemStage ? (string) $previousItemStage->pr_stage_id : null,
                            'actual_date_forwarded' => $utcDateForwarded,
                        ]);
                        $updated++;
                    } else {
                        PrItemPrstage::create([
                            'procID' => $prItem['procID'],
                            'prItemID' => $prItemID,
                            'pr_stage_id' => 7,
                            'stage_history' => $latestItemStage ? (string) $latestItemStage->pr_stage_id : null,
                            'actual_date_forwarded' => $utcDateForwarded,
                        ]);

                        PrItemRemark::create([
                            'procID' => $prItem['procID'],
                            'prItemID' => $prItemID,
                            'remarks_id' => 1, // Awarded
                            'notes' => null,
                            'remark_history' => now(),
                        ]);

                        $forwarded++;
                    }
                }

                // Upsert Pmu record per eligible selected item
                foreach ($this->selectedPostItems as $prItemID) {
                    $post = PostProcurement::where('ref_id', $prItemID)->first();

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

                    if ($this->hasValue($post->notice_of_award_number)) {
                        $pmu = Pmu::updateOrCreate(
                            ['notice_of_award_number' => $post->notice_of_award_number],
                            ['date_forwarded' => $utcDateForwarded]
                        );

                        $poDate = $this->calculatePoDate($post->date_receipt_of_supplier_noa);
                        if ($poDate) {
                            PmuPo::updateOrCreate(
                                ['pmu_id' => $pmu->id, 'ref_id' => $prItemID],
                                ['po_date' => $poDate]
                            );
                        }
                    }
                }
            });

            $this->closeForwardModal();

            $message = '';
            if ($forwarded > 0 && $updated > 0) {
                $message = "{$forwarded} procurement(s) forwarded and {$updated} updated.";
            } elseif ($forwarded > 0) {
                $message = "{$forwarded} procurement(s) successfully forwarded to PMU.";
            } elseif ($updated > 0) {
                $message = "Forwarded date updated for {$updated} procurement(s).";
            }

            if ($skipped > 0) {
                $message .= " {$skipped} item(s) skipped (incomplete post-procurement data).";
            }

            if ($forwarded + $updated > 0) {
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
            $this->dispatch('post-bulk-edit-closed');

        } catch (\Exception $e) {
            \Log::error('Forward to PMU (per item) failed', [
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
}
