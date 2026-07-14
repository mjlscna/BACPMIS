<?php

namespace App\Livewire\PMU;

use App\Models\Procurement;
use App\Models\PostProcurement;
use App\Models\Pmu;
use App\Models\Supplier;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Illuminate\Support\Facades\DB;

#[Title('PMU | PMIS')]
class PmuIndexPage extends Component
{
    use WithPagination;

    private const TIMEZONE = 'Asia/Manila';
    private const DATE_FORMAT = 'Y-m-d\TH:i';
    private const PR_STAGE_ID = 7;
    private const ALERT_TYPES = ['success', 'error', 'warning', 'info'];
    private const WHITELISTED_PROPERTIES = ['expandedNoaNumber'];

    protected $paginationTheme = 'tailwind';

    // Pagination
    public int $pendingPerPage = 10;
    public int $receivedPerPage = 10;
    public int $expandedPage = 1;
    public int $expandedPerPage = 10;
    public int $modalPage = 1;
    public int $modalPerPage = 10;
    public int $pendingPage = 1;
    public int $receivedPage = 1;

    // Sort & Filters (received table)
    public string $sortBy = 'date_received';
    public string $sortDir = 'desc';
    public string $poStatusFilter = '';
    public string $poIssuanceFilter = '';

    // Search
    public string $search = '';

    // Collapsible functionality
    public ?string $expandedNoaNumber = null;

    // Receive Modal
    public bool $showReceiveModal = false;
    public ?string $receivingNoaNumber = null;
    public ?string $receiveDate = null;
    public ?string $receiveRemarks = null;

    // Bulk Receive
    public array $selectedNoaNumbers = [];
    public bool $showBulkReceiveModal = false;
    public ?string $bulkReceiveDate = null;
    public ?string $bulkReceiveRemarks = null;

    // View Modal
    public bool $showViewModal = false;
    public ?string $viewNoaNumber = null;
    public ?Pmu $viewPmuRecord = null;
    public ?PostProcurement $viewPostProcurement = null;
    public ?\Illuminate\Support\Collection $viewProcurements = null;
    public ?\Illuminate\Support\Collection $viewItemRows = null;
    public float $viewTotalAbc = 0;
    public ?Supplier $viewSupplier = null;

    private ?array $renderQueryCache = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'pendingPerPage' => ['except' => 10],
        'receivedPerPage' => ['except' => 10],
        'sortBy' => ['except' => 'date_received'],
        'sortDir' => ['except' => 'desc'],
        'poStatusFilter' => ['except' => ''],
        'poIssuanceFilter' => ['except' => ''],
    ];

    public function mount(): void
    {
        if (session('alert')) {
            $alert = session('alert');
            $alertType = $alert['type'] ?? 'info';

            // Whitelist alert types to prevent method injection
            if (!in_array($alertType, self::ALERT_TYPES)) {
                $alertType = 'info';
            }

            LivewireAlert::title($alert['title'] ?? 'Alert')
                ->{$alertType}()
                    ->text($alert['message'] ?? '')
                    ->toast()
                    ->position('top-end')
                    ->show();

            session()->forget('alert');
        }
    }

    /**
     * Toggle expanded/collapsed state for NOA items
     */
    public function toggle(string $property, string $value): void
    {
        // Whitelist allowed properties to prevent arbitrary property modification
        if (!in_array($property, self::WHITELISTED_PROPERTIES)) {
            return;
        }

        $value = (string) $value;

        if ($this->$property === $value) {
            $this->$property = null;
        } else {
            $this->$property = $value;
            $this->expandedPage = 1;
        }
    }

    public function setExpandedPage(int $page): void
    {
        $this->expandedPage = $page;
    }

    public function updatingExpandedPerPage(): void
    {
        $this->expandedPage = 1;
    }

    public function setModalPage(int $page): void
    {
        $this->modalPage = $page;
    }

    public function updatingModalPerPage(): void
    {
        $this->modalPage = 1;
    }

    public function updatingSearch(): void
    {
        $this->receivedPage = 1;
        $this->pendingPage = 1;
    }

    public function updatingPoStatusFilter(): void
    {
        $this->receivedPage = 1;
    }

    public function updatingPoIssuanceFilter(): void
    {
        $this->receivedPage = 1;
    }

    public function updatingSortBy(): void
    {
        $this->receivedPage = 1;
    }

    public function updatingSortDir(): void
    {
        $this->receivedPage = 1;
    }

    public function clearReceivedFilters(): void
    {
        $this->sortBy = 'date_received';
        $this->sortDir = 'desc';
        $this->poStatusFilter = '';
        $this->poIssuanceFilter = '';
        $this->search = '';
        $this->receivedPage = 1;
    }

    public function setPendingPage(int $page): void
    {
        $this->pendingPage = $page;
    }

    public function updatingPendingPerPage(): void
    {
        $this->pendingPage = 1;
    }

    public function setReceivedPage(int $page): void
    {
        $this->receivedPage = $page;
    }

    public function updatingReceivedPerPage(): void
    {
        $this->receivedPage = 1;
    }

    // Keep for backward compat – resets both tables
    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        // Return cached union results if already fetched in this cycle
        // to prevent re-executing expensive queries on every Livewire update
        if ($this->renderQueryCache !== null) {
            $unionResults = $this->renderQueryCache;
        } else {
            $unionResults = $this->buildUnionQuery();
            $this->renderQueryCache = $unionResults;
        }

        $pendingItems = $unionResults['pending'];
        $receivedItems = $unionResults['received'];
        $expandedPaginator = $unionResults['expanded'];
        $warningCounts = $unionResults['warnings'];
        $poIssuanceCounts = $unionResults['poIssuance'];

        return view('livewire.pmu.pmu-index-page', [
            'pendingItems' => $pendingItems,
            'receivedItems' => $receivedItems,
            'expandedPaginator' => $expandedPaginator,
            'modalPaginator' => $this->buildModalPaginator(),
            'warningCounts' => $warningCounts,
            'poIssuanceCounts' => $poIssuanceCounts,
        ]);
    }

    private function buildUnionQuery(): array
    {
        // Build a union of:
        //   (A) per-lot: post_procurements.ref_id = procurements.procID  AND  pr_lot_prstage has stage 7
        //   (B) per-item: post_procurements.ref_id = pr_items.prItemID   AND  pr_item_prstage has stage 7

        $lotQuery = \DB::table('procurements')
            ->join('post_procurements', 'procurements.procID', '=', 'post_procurements.ref_id')
            ->join('pmus', 'post_procurements.notice_of_award_number', '=', 'pmus.notice_of_award_number')
            ->leftJoin('divisions', 'procurements.divisions_id', '=', 'divisions.id')
            ->whereExists(function ($q) {
                $q->select(\DB::raw(1))
                    ->from('pr_lot_prstage')
                    ->whereColumn('pr_lot_prstage.procID', 'procurements.procID')
                    ->where('pr_lot_prstage.pr_stage_id', self::PR_STAGE_ID);
            })
            ->whereNotNull('post_procurements.notice_of_award_number')
            ->whereNull('pmus.deleted_at');

        $itemQuery = \DB::table('procurements')
            ->join('pr_items', 'pr_items.procID', '=', 'procurements.procID')
            ->join('post_procurements', 'post_procurements.ref_id', '=', 'pr_items.prItemID')
            ->join('pmus', 'post_procurements.notice_of_award_number', '=', 'pmus.notice_of_award_number')
            ->leftJoin('divisions', 'procurements.divisions_id', '=', 'divisions.id')
            ->whereExists(function ($q) {
                $q->select(\DB::raw(1))
                    ->from('pr_item_prstage')
                    ->whereColumn('pr_item_prstage.prItemID', 'pr_items.prItemID')
                    ->where('pr_item_prstage.pr_stage_id', self::PR_STAGE_ID);
            })
            ->whereNotNull('post_procurements.notice_of_award_number')
            ->whereNull('pmus.deleted_at');

        // Apply search to both
        if (!empty($this->search)) {
            $lotQuery->where(function ($q) {
                $q->where('procurements.pr_number', 'like', '%' . $this->search . '%')
                    ->orWhere('post_procurements.notice_of_award_number', 'like', '%' . $this->search . '%')
                    ->orWhere('procurements.procurement_program_project', 'like', '%' . $this->search . '%')
                    ->orWhereExists(function ($sub) {
                        $sub->select(\DB::raw(1))
                            ->from('pmu_po')
                            ->whereColumn('pmu_po.ref_id', 'procurements.procID')
                            ->whereColumn('pmu_po.pmu_id', 'pmus.id')
                            ->whereNull('pmu_po.deleted_at')
                            ->where('pmu_po.po_contract_number', 'like', '%' . $this->search . '%');
                    });
            });
            $itemQuery->where(function ($q) {
                $q->where('procurements.pr_number', 'like', '%' . $this->search . '%')
                    ->orWhere('post_procurements.notice_of_award_number', 'like', '%' . $this->search . '%')
                    ->orWhere('pr_items.description', 'like', '%' . $this->search . '%')
                    ->orWhereExists(function ($sub) {
                        $sub->select(\DB::raw(1))
                            ->from('pmu_po')
                            ->whereColumn('pmu_po.ref_id', 'pr_items.prItemID')
                            ->whereColumn('pmu_po.pmu_id', 'pmus.id')
                            ->whereNull('pmu_po.deleted_at')
                            ->where('pmu_po.po_contract_number', 'like', '%' . $this->search . '%');
                    });
            });
        }

        $unionQuery = $lotQuery
            ->select(
                'post_procurements.notice_of_award_number',
                'pmus.date_forwarded',
                'post_procurements.notice_of_award',
                'pmus.date_received',
                'pmus.received_remarks'
            )
            ->unionAll(
                $itemQuery->select(
                    'post_procurements.notice_of_award_number',
                    'pmus.date_forwarded',
                    'post_procurements.notice_of_award',
                    'pmus.date_received',
                    'pmus.received_remarks'
                )
            );

        $outerBase = \DB::table(\DB::raw('(' . $unionQuery->toSql() . ') as combined'))
            ->mergeBindings($unionQuery)
            ->select(
                'notice_of_award_number',
                \DB::raw('MAX(date_forwarded) as date_forwarded'),
                \DB::raw('MAX(notice_of_award) as notice_of_award'),
                \DB::raw('MAX(date_received) as date_received'),
                \DB::raw('MAX(received_remarks) as received_remarks'),
                \DB::raw('COUNT(*) as procurement_count')
            )
            ->groupBy(
                'notice_of_award_number'
            )
            ->orderBy('notice_of_award_number', 'desc');

        $pendingItems = (clone $outerBase)
            ->whereNull('date_received')
            ->paginate($this->pendingPerPage, ['*'], 'pending_page', $this->pendingPage);

        $receivedQuery = (clone $outerBase)->whereNotNull('date_received');

        // ── PO Status filter ──────────────────────────────────────────────────
        // Re-use the same aggregation that drives the display badges to guarantee
        // the filter matches exactly what the user sees on screen.
        if ($this->poStatusFilter !== '') {
            $iCounts = $this->buildPoIssuanceCounts(); // keyed by notice_of_award_number

            // All received NOA numbers (needed for "not started" / "pending_entry")
            $allReceivedNoas = \DB::table('pmus')
                ->whereNotNull('date_received')
                ->whereNull('deleted_at')
                ->pluck('notice_of_award_number');

            $poNoas = match ($this->poStatusFilter) {
                // Not Started: poTotal === 0 → NOA has no pmu_po records at all
                'not_started' => $allReceivedNoas->filter(
                    fn($noa) => !$iCounts->has($noa)
                ),

                // Pending Entry: poTotal > 0 but all derived counts are zero
                'pending_entry' => $iCounts->filter(function ($ic) {
                        return (int) $ic->total_count > 0
                        && (int) $ic->ready_to_forward_count === 0
                        && (int) $ic->po_prep_count === 0
                        && (int) $ic->usec_count === 0
                        && (int) $ic->return_to_bac_count === 0
                        && (int) $ic->end_user_count === 0
                        && (int) $ic->forwarded_to_supply_count === 0;
                    })->keys(),

                // forwarded_to_supply > 0
                'forwarded_to_supply' => $iCounts
                    ->filter(fn($ic) => (int) $ic->forwarded_to_supply_count > 0)
                    ->keys(),

                // return_to_bac > 0
                'return_to_bac' => $iCounts
                    ->filter(fn($ic) => (int) $ic->return_to_bac_count > 0)
                    ->keys(),

                // end_user > 0
                'for_end_user_compliance' => $iCounts
                    ->filter(fn($ic) => (int) $ic->end_user_count > 0)
                    ->keys(),

                // USEC badge: usec_count > 0 (counts are now mutually exclusive with ready_to_forward)
                'usec' => $iCounts->filter(function ($ic) {
                        return (int) $ic->usec_count > 0;
                    })->keys(),

                // PO Prep badge: po_prep_count > 0 (counts are now mutually exclusive with usec and ready_to_forward)
                'po_prep' => $iCounts->filter(function ($ic) {
                        return (int) $ic->po_prep_count > 0;
                    })->keys(),

                default => null,
            };

            if ($poNoas !== null) {
                // Use whereRaw + addBinding('union') instead of whereIn to avoid
                // a binding-order bug: mergeBindings() places the union subquery's
                // item-side bindings in the 'union' bucket, but whereIn() inserts
                // its bindings into the 'where' bucket (before 'union' in getBindings()).
                // This causes MySQL to receive [7, noa, 7] instead of [7, 7, noa].
                $noaArr = $poNoas->values()->toArray();
                if (count($noaArr) > 0) {
                    $placeholders = implode(',', array_fill(0, count($noaArr), '?'));
                    $receivedQuery->whereRaw("notice_of_award_number IN ({$placeholders})");
                    $receivedQuery->addBinding($noaArr, 'union');
                } else {
                    $receivedQuery->whereRaw('1=0');
                }
            }
        }

        // ── PO Issuance filter ────────────────────────────────────────────────
        // Re-use the same aggregation that drives the display badges to guarantee
        // the filter matches exactly what the user sees on screen.
        if ($this->poIssuanceFilter !== '') {
            $warnCounts = $this->buildWarningCounts(); // keyed by notice_of_award_number

            $warnNoas = match ($this->poIssuanceFilter) {
                'exceeded' => $warnCounts
                    ->filter(fn($wc) => (int) $wc->exceeded_count > 0)
                    ->keys(),

                'overdue' => $warnCounts
                    ->filter(fn($wc) => (int) $wc->overdue_count > 0)
                    ->keys(),

                'due_soon' => $warnCounts
                    ->filter(fn($wc) => (int) $wc->soon_count > 0)
                    ->keys(),

                // On Track = badge shows when ALL counts are zero.
                // Must also include NOAs with no pmu_po deadline records (wc is null → all-zero).
                // Fetch all received NOA numbers then exclude any that have exceeded/overdue/due-soon.
                'on_track' => \DB::table('pmus')
                    ->whereNotNull('date_received')
                    ->whereNull('deleted_at')
                    ->pluck('notice_of_award_number')
                    ->filter(function ($noa) use ($warnCounts) {
                            $wc = $warnCounts->get($noa);
                            return !$wc
                            || ((int) $wc->exceeded_count === 0
                                && (int) $wc->overdue_count === 0
                                && (int) $wc->soon_count === 0);
                        }),

                default => null,
            };

            if ($warnNoas !== null) {
                // Same binding-order fix as above.
                $noaArr = $warnNoas->values()->toArray();
                if (count($noaArr) > 0) {
                    $placeholders = implode(',', array_fill(0, count($noaArr), '?'));
                    $receivedQuery->whereRaw("notice_of_award_number IN ({$placeholders})");
                    $receivedQuery->addBinding($noaArr, 'union');
                } else {
                    $receivedQuery->whereRaw('1=0');
                }
            }
        }

        // ── Dynamic sort ──────────────────────────────────────────────────────
        $allowedSorts = ['date_received', 'notice_of_award_number'];
        $sortCol = in_array($this->sortBy, $allowedSorts, true) ? $this->sortBy : 'date_received';
        $sortDir = $this->sortDir === 'asc' ? 'asc' : 'desc';

        $receivedItems = $receivedQuery
            ->reorder($sortCol, $sortDir)
            ->paginate($this->receivedPerPage, ['*'], 'received_page', $this->receivedPage);

        return [
            'pending' => $pendingItems,
            'received' => $receivedItems,
            'expanded' => $this->buildExpandedPaginator(),
            'warnings' => $this->buildWarningCounts(),
            'poIssuance' => $this->buildPoIssuanceCounts(),
        ];
    }

    private function buildExpandedPaginator(): ?\Illuminate\Pagination\LengthAwarePaginator
    {

        if (!$this->expandedNoaNumber) {
            return null;
        }
        $expandedPmu = Pmu::where('notice_of_award_number', $this->expandedNoaNumber)->first();
        $expandedPmuId = $expandedPmu?->id;

        // Per-lot procurements
        $lots = \DB::table('procurements')
            ->join('post_procurements', 'procurements.procID', '=', 'post_procurements.ref_id')
            ->join('pmus', 'post_procurements.notice_of_award_number', '=', 'pmus.notice_of_award_number')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'post_procurements.supplier_id')
            ->leftJoin('pmu_po', function ($join) use ($expandedPmuId) {
                $join->on('pmu_po.ref_id', '=', 'procurements.procID')
                    ->where('pmu_po.pmu_id', '=', $expandedPmuId)
                    ->whereNull('pmu_po.deleted_at');
            })
            ->where('post_procurements.notice_of_award_number', $this->expandedNoaNumber)
            ->whereNull('pmus.deleted_at')
            ->whereExists(function ($q) {
                $q->select(\DB::raw(1))
                    ->from('pr_lot_prstage')
                    ->whereColumn('pr_lot_prstage.procID', 'procurements.procID')
                    ->where('pr_lot_prstage.pr_stage_id', self::PR_STAGE_ID);
            })
            ->select(
                'procurements.procID',
                'procurements.pr_number',
                \DB::raw('procurements.procurement_program_project as description'),
                'procurements.abc',
                \DB::raw("'lot' as row_type"),
                'post_procurements.resolution_award_number',
                'post_procurements.resolution_award_date',
                'post_procurements.awarded_amount',
                'post_procurements.date_receipt_of_supplier_noa',
                'suppliers.name as supplier_name',
                'pmu_po.po_date_deadline',
                'pmu_po.po_date',
                'pmu_po.po_contract_number',
                'pmu_po.po_contract_number_link',
                'pmu_po.ntp_link',
                'pmu_po.contract_amount as pmu_contract_amount',
                'pmu_po.contract_signing_date as pmu_contract_signing_date',
                'pmu_po.notice_to_proceed_date as pmu_notice_to_proceed_date',
                'pmu_po.date_po_receipt_by_supplier as pmu_date_po_receipt_by_supplier',
                'pmu_po.date_coa_stamped_received as pmu_date_coa_stamped_received',
                'pmu_po.remarks as pmu_remarks',
                'pmu_po.manual_status as pmu_manual_status',
                'pmu_po.id as pmu_po_id'
            )
            ->get();

        // Per-item rows
        $items = \DB::table('pr_items')
            ->join('procurements', 'procurements.procID', '=', 'pr_items.procID')
            ->join('post_procurements', 'post_procurements.ref_id', '=', 'pr_items.prItemID')
            ->join('pmus', 'post_procurements.notice_of_award_number', '=', 'pmus.notice_of_award_number')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'post_procurements.supplier_id')
            ->leftJoin('pmu_po', function ($join) use ($expandedPmuId) {
                $join->on('pmu_po.ref_id', '=', 'pr_items.prItemID')
                    ->where('pmu_po.pmu_id', '=', $expandedPmuId)
                    ->whereNull('pmu_po.deleted_at');
            })
            ->whereExists(function ($q) {
                $q->select(\DB::raw(1))
                    ->from('pr_item_prstage')
                    ->whereColumn('pr_item_prstage.prItemID', 'pr_items.prItemID')
                    ->where('pr_item_prstage.pr_stage_id', self::PR_STAGE_ID);
            })
            ->where('post_procurements.notice_of_award_number', $this->expandedNoaNumber)
            ->whereNull('pmus.deleted_at')
            ->select(
                'procurements.procID',
                'procurements.pr_number',
                'pr_items.description',
                \DB::raw('pr_items.amount as abc'),
                \DB::raw("'item' as row_type"),
                'post_procurements.resolution_award_number',
                'post_procurements.resolution_award_date',
                'post_procurements.awarded_amount',
                'post_procurements.date_receipt_of_supplier_noa',
                'suppliers.name as supplier_name',
                'pmu_po.po_date_deadline',
                'pmu_po.po_date',
                'pmu_po.po_contract_number',
                'pmu_po.po_contract_number_link',
                'pmu_po.ntp_link',
                'pmu_po.contract_amount as pmu_contract_amount',
                'pmu_po.contract_signing_date as pmu_contract_signing_date',
                'pmu_po.notice_to_proceed_date as pmu_notice_to_proceed_date',
                'pmu_po.date_po_receipt_by_supplier as pmu_date_po_receipt_by_supplier',
                'pmu_po.date_coa_stamped_received as pmu_date_coa_stamped_received',
                'pmu_po.remarks as pmu_remarks',
                'pmu_po.manual_status as pmu_manual_status',
                'pmu_po.id as pmu_po_id'
            )
            ->orderBy('procurements.pr_number')
            ->orderBy('pr_items.item_no')
            ->get();

        $combined = $lots->merge($items);
        $total = $combined->count();
        $perPage = max(1, (int) $this->expandedPerPage);
        $page = max(1, (int) $this->expandedPage);
        $sliced = $combined->forPage($page, $perPage)->values();

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $sliced,
            $total,
            $perPage,
            $page,
            ['pageName' => 'expanded_page']
        );
    }
    private function buildWarningCounts(): \Illuminate\Support\Collection
    {
        // Build warning counts (exceeded / overdue / due-soon) per NOA number
        $today = \Carbon\Carbon::today()->toDateString();
        $soonDate = \Carbon\Carbon::today()->addDays(3)->toDateString();

        return \DB::table('pmus')
            ->join('pmu_po', 'pmu_po.pmu_id', '=', 'pmus.id')
            ->whereNotNull('pmu_po.po_date_deadline')
            ->whereNull('pmus.deleted_at')
            ->select(
                'pmus.notice_of_award_number',
                \DB::raw("SUM(CASE WHEN pmu_po.po_date IS NOT NULL AND pmu_po.po_date > pmu_po.po_date_deadline THEN 1 ELSE 0 END) as exceeded_count"),
                \DB::raw("SUM(CASE WHEN pmu_po.po_date IS NULL AND pmu_po.po_date_deadline < '{$today}' THEN 1 ELSE 0 END) as overdue_count"),
                \DB::raw("SUM(CASE WHEN pmu_po.po_date IS NULL AND pmu_po.po_date_deadline >= '{$today}' AND pmu_po.po_date_deadline <= '{$soonDate}' THEN 1 ELSE 0 END) as soon_count")
            )
            ->groupBy('pmus.notice_of_award_number')
            ->get()
            ->keyBy('notice_of_award_number');
    }

    private function buildPoIssuanceCounts(): \Illuminate\Support\Collection
    {
        // Per NOA: count total pmu_po rows, PO Preparation (po_date + po_contract_number filled),
        // and For Approval of HOPE (contract_amount filled).
        return \DB::table('pmus')
            ->join('pmu_po', 'pmu_po.pmu_id', '=', 'pmus.id')
            ->whereNull('pmus.deleted_at')
            ->whereNull('pmu_po.deleted_at')
            ->select(
                'pmus.notice_of_award_number',
                \DB::raw('COUNT(*) as total_count'),
                \DB::raw('SUM(CASE WHEN
                    pmu_po.manual_status IS NULL AND
                    pmu_po.po_date IS NOT NULL AND
                    pmu_po.po_contract_number IS NOT NULL AND
                    pmu_po.contract_amount IS NOT NULL AND
                    pmu_po.contract_signing_date IS NOT NULL AND
                    pmu_po.notice_to_proceed_date IS NOT NULL AND
                    pmu_po.po_contract_number_link IS NOT NULL AND
                    pmu_po.date_po_receipt_by_supplier IS NOT NULL AND
                    pmu_po.date_coa_stamped_received IS NOT NULL
                THEN 1 ELSE 0 END) as ready_to_forward_count'),
                // po_prep: has PO Date + PO/Contract No., but NOT contract_amount yet (which would push it to usec/ready_to_forward)
                \DB::raw('SUM(CASE WHEN pmu_po.manual_status IS NULL AND pmu_po.po_date IS NOT NULL AND pmu_po.po_contract_number IS NOT NULL AND pmu_po.contract_amount IS NULL THEN 1 ELSE 0 END) as po_prep_count'),
                // usec: has contract_amount, but NOT yet fully ready_to_forward (at least one required field is still missing)
                \DB::raw('SUM(CASE WHEN pmu_po.manual_status IS NULL AND pmu_po.contract_amount IS NOT NULL AND NOT (
                    pmu_po.po_date IS NOT NULL AND
                    pmu_po.po_contract_number IS NOT NULL AND
                    pmu_po.contract_signing_date IS NOT NULL AND
                    pmu_po.notice_to_proceed_date IS NOT NULL AND
                    pmu_po.po_contract_number_link IS NOT NULL AND
                    pmu_po.date_po_receipt_by_supplier IS NOT NULL AND
                    pmu_po.date_coa_stamped_received IS NOT NULL
                ) THEN 1 ELSE 0 END) as usec_count'),
                \DB::raw("SUM(CASE WHEN pmu_po.manual_status = 'return_to_bac' AND NOT (
                    pmu_po.po_date IS NOT NULL AND
                    pmu_po.po_contract_number IS NOT NULL AND
                    pmu_po.contract_amount IS NOT NULL AND
                    pmu_po.contract_signing_date IS NOT NULL AND
                    pmu_po.notice_to_proceed_date IS NOT NULL AND
                    pmu_po.po_contract_number_link IS NOT NULL AND
                    pmu_po.date_po_receipt_by_supplier IS NOT NULL AND
                    pmu_po.date_coa_stamped_received IS NOT NULL
                ) THEN 1 ELSE 0 END) as return_to_bac_count"),
                \DB::raw("SUM(CASE WHEN pmu_po.manual_status = 'for_end_user_compliance' AND NOT (
                    pmu_po.po_date IS NOT NULL AND
                    pmu_po.po_contract_number IS NOT NULL AND
                    pmu_po.contract_amount IS NOT NULL AND
                    pmu_po.contract_signing_date IS NOT NULL AND
                    pmu_po.notice_to_proceed_date IS NOT NULL AND
                    pmu_po.po_contract_number_link IS NOT NULL AND
                    pmu_po.date_po_receipt_by_supplier IS NOT NULL AND
                    pmu_po.date_coa_stamped_received IS NOT NULL
                ) THEN 1 ELSE 0 END) as end_user_count"),
                \DB::raw("SUM(CASE WHEN pmu_po.manual_status = 'forwarded_to_supply' THEN 1 ELSE 0 END) as forwarded_to_supply_count")
            )
            ->groupBy('pmus.notice_of_award_number')
            ->get()
            ->keyBy('notice_of_award_number');
    }

    private function buildModalPaginator(): ?\Illuminate\Pagination\LengthAwarePaginator
    {
        if (!$this->showViewModal) {
            return null;
        }

        $lots = collect($this->viewProcurements ?? [])->map(fn($p) => (object) [
            'procID' => $p->procID,
            'pr_number' => $p->pr_number,
            'description' => $p->procurement_program_project,
            'abc' => $p->abc,
            'resolution_award_number' => $p->resolution_award_number ?? null,
            'resolution_award_date' => $p->resolution_award_date ?? null,
            'awarded_amount' => $p->awarded_amount ?? null,
            'date_receipt_of_supplier_noa' => $p->date_receipt_of_supplier_noa ?? null,
            'supplier_name' => $p->supplier_name ?? null,
            'po_date_deadline' => $p->po_date_deadline ?? null,
            'po_date' => $p->po_date ?? null,
            'po_contract_number' => $p->po_contract_number ?? null,
            'po_contract_number_link' => $p->po_contract_number_link ?? null,
            'ntp_link' => $p->ntp_link ?? null,
            'contract_amount' => $p->pmu_contract_amount ?? null,
            'contract_signing_date' => $p->pmu_contract_signing_date ?? null,
            'notice_to_proceed_date' => $p->pmu_notice_to_proceed_date ?? null,
            'remarks' => $p->pmu_remarks ?? null,
        ]);

        $items = collect($this->viewItemRows ?? [])->map(fn($r) => (object) [
            'procID' => $r->procID,
            'pr_number' => $r->pr_number,
            'description' => $r->description,
            'abc' => $r->amount,
            'resolution_award_number' => $r->resolution_award_number ?? null,
            'resolution_award_date' => $r->resolution_award_date ?? null,
            'awarded_amount' => $r->awarded_amount ?? null,
            'date_receipt_of_supplier_noa' => $r->date_receipt_of_supplier_noa ?? null,
            'supplier_name' => $r->supplier_name ?? null,
            'po_date_deadline' => $r->po_date_deadline ?? null,
            'po_date' => $r->po_date ?? null,
            'po_contract_number' => $r->po_contract_number ?? null,
            'po_contract_number_link' => $r->po_contract_number_link ?? null,
            'ntp_link' => $r->ntp_link ?? null,
            'contract_amount' => $r->pmu_contract_amount ?? null,
            'contract_signing_date' => $r->pmu_contract_signing_date ?? null,
            'notice_to_proceed_date' => $r->pmu_notice_to_proceed_date ?? null,
            'remarks' => $r->pmu_remarks ?? null,
        ]);

        $combined = $lots->merge($items);
        $total = $combined->count();
        $perPage = max(1, (int) $this->modalPerPage);
        $page = max(1, (int) $this->modalPage);

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $combined->forPage($page, $perPage)->values(),
            $total,
            $perPage,
            $page,
            ['pageName' => 'modal_page']
        );
    }

    public function openViewModal(string $noaNumber): void
    {
        $this->viewNoaNumber = $noaNumber;

        $this->viewPmuRecord = Pmu::where('notice_of_award_number', $noaNumber)
            ->whereNull('deleted_at')
            ->first();

        $this->viewPostProcurement = PostProcurement::where('notice_of_award_number', $noaNumber)->first();

        $this->viewProcurements = Procurement::query()
            ->join('post_procurements', 'procurements.procID', '=', 'post_procurements.ref_id')
            ->join('pmus', 'post_procurements.notice_of_award_number', '=', 'pmus.notice_of_award_number')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'post_procurements.supplier_id')
            ->leftJoin('pmu_po', function ($join) {
                $join->on('pmu_po.ref_id', '=', 'procurements.procID')
                    ->on('pmu_po.pmu_id', '=', 'pmus.id')
                    ->whereNull('pmu_po.deleted_at');
            })
            ->where('post_procurements.notice_of_award_number', $noaNumber)
            ->whereNull('pmus.deleted_at')
            ->whereHas('prLotPrstages', fn($q) => $q->where('pr_stage_id', 7))
            ->select(
                'procurements.procID',
                'procurements.pr_number',
                'procurements.procurement_program_project',
                'procurements.abc',
                'post_procurements.resolution_award_number',
                'post_procurements.resolution_award_date',
                'post_procurements.awarded_amount',
                'post_procurements.date_receipt_of_supplier_noa',
                'suppliers.name as supplier_name',
                'pmu_po.po_date_deadline',
                'pmu_po.po_date',
                'pmu_po.po_contract_number',
                'pmu_po.po_contract_number_link',
                'pmu_po.ntp_link',
                'pmu_po.contract_amount as pmu_contract_amount',
                'pmu_po.contract_signing_date as pmu_contract_signing_date',
                'pmu_po.notice_to_proceed_date as pmu_notice_to_proceed_date',
                'pmu_po.remarks as pmu_remarks'
            )
            ->get();

        $this->viewItemRows = DB::table('pr_items')
            ->join('procurements', 'procurements.procID', '=', 'pr_items.procID')
            ->join('post_procurements', 'post_procurements.ref_id', '=', 'pr_items.prItemID')
            ->join('pmus', 'post_procurements.notice_of_award_number', '=', 'pmus.notice_of_award_number')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'post_procurements.supplier_id')
            ->leftJoin('pmu_po', function ($join) {
                $join->on('pmu_po.ref_id', '=', 'pr_items.prItemID')
                    ->on('pmu_po.pmu_id', '=', 'pmus.id')
                    ->whereNull('pmu_po.deleted_at');
            })
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('pr_item_prstage')
                    ->whereColumn('pr_item_prstage.prItemID', 'pr_items.prItemID')
                    ->where('pr_item_prstage.pr_stage_id', 7);
            })
            ->where('post_procurements.notice_of_award_number', $noaNumber)
            ->whereNull('pmus.deleted_at')
            ->select(
                'procurements.procID',
                'procurements.pr_number',
                'pr_items.prItemID',
                'pr_items.item_no',
                'pr_items.description',
                'pr_items.amount',
                'post_procurements.resolution_award_number',
                'post_procurements.resolution_award_date',
                'post_procurements.awarded_amount',
                'post_procurements.date_receipt_of_supplier_noa',
                'suppliers.name as supplier_name',
                'pmu_po.po_date_deadline',
                'pmu_po.po_date',
                'pmu_po.po_contract_number',
                'pmu_po.po_contract_number_link',
                'pmu_po.ntp_link',
                'pmu_po.contract_amount as pmu_contract_amount',
                'pmu_po.contract_signing_date as pmu_contract_signing_date',
                'pmu_po.notice_to_proceed_date as pmu_notice_to_proceed_date',
                'pmu_po.remarks as pmu_remarks'
            )
            ->orderBy('procurements.pr_number')
            ->orderBy('pr_items.item_no')
            ->get();

        $this->viewTotalAbc = (float) $this->viewProcurements->sum('abc');

        if ($this->viewPostProcurement?->supplier_id) {
            $this->viewSupplier = Supplier::find($this->viewPostProcurement->supplier_id);
        } else {
            $this->viewSupplier = null;
        }

        $this->modalPage = 1;
        $this->showViewModal = true;
    }

    public function closeViewModal(): void
    {
        $this->showViewModal = false;
        $this->viewNoaNumber = null;
        $this->viewPmuRecord = null;
        $this->viewPostProcurement = null;
        $this->viewProcurements = null;
        $this->viewItemRows = null;
        $this->viewTotalAbc = 0;
        $this->viewSupplier = null;
        $this->modalPage = 1;
        $this->renderQueryCache = null; // Clear cache when closing modal to ensure fresh data on re-open
    }

    public function openReceiveModal(string $noaNumber): void
    {
        $pmu = Pmu::where('notice_of_award_number', $noaNumber)->whereNull('deleted_at')->first();

        $this->receivingNoaNumber = $noaNumber;
        $this->receiveDate = $pmu?->date_received
            ? $pmu->date_received->setTimezone(self::TIMEZONE)->format(self::DATE_FORMAT)
            : now(self::TIMEZONE)->format(self::DATE_FORMAT);
        $this->receiveRemarks = $pmu?->received_remarks ?? '';
        $this->showReceiveModal = true;
    }

    public function confirmReceive(): void
    {
        $this->validate([
            'receiveDate' => 'required|date',
            'receiveRemarks' => 'nullable|string|max:1000',
        ], [
            'receiveDate.required' => 'Received date is required.',
            'receiveDate.date' => 'Please enter a valid date.',
        ]);

        try {
            $pmu = Pmu::where('notice_of_award_number', $this->receivingNoaNumber)
                ->whereNull('deleted_at')
                ->first();

            if (!$pmu) {
                LivewireAlert::title('Error')->error()->text('PMU record not found.')->toast()->position('top-end')->show();
                return;
            }

            $pmu->update([
                'date_received' => \Carbon\Carbon::createFromFormat(self::DATE_FORMAT, $this->receiveDate, self::TIMEZONE)->utc(),
                'received_remarks' => $this->receiveRemarks ?: null,
            ]);

            $this->closeReceiveModal();

            LivewireAlert::title('Marked as Received!')
                ->success()
                ->text('NOA ' . $this->receivingNoaNumber . ' has been marked as received.')
                ->toast()
                ->position('top-end')
                ->show();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('PmuIndexPage: Failed to mark PMU as received', [
                'noa_number' => $this->receivingNoaNumber,
                'error' => $e->getMessage(),
            ]);
            LivewireAlert::title('Error')
                ->error()
                ->text('Failed to mark as received. Please try again or contact support.')
                ->toast()
                ->position('top-end')
                ->show();
        }
    }

    public function closeReceiveModal(): void
    {
        $this->showReceiveModal = false;
        $this->receivingNoaNumber = null;
        $this->receiveDate = null;
        $this->receiveRemarks = null;
        $this->resetValidation(['receiveDate', 'receiveRemarks']);
        $this->renderQueryCache = null; // Invalidate cache after modal close
    }

    // ─── Bulk Receive ────────────────────────────────────────────────────────

    public function toggleNoaSelection(string $noaNumber): void
    {
        if (in_array($noaNumber, $this->selectedNoaNumbers)) {
            $this->selectedNoaNumbers = array_values(
                array_filter($this->selectedNoaNumbers, fn($n) => $n !== $noaNumber)
            );
        } else {
            $this->selectedNoaNumbers[] = $noaNumber;
        }
    }

    public function clearSelection(): void
    {
        $this->selectedNoaNumbers = [];
    }

    public function setManualStatus(int $pmuPoId, ?string $status): void
    {
        $allowed = [null, 'return_to_bac', 'for_end_user_compliance', 'forwarded_to_supply'];
        if (!in_array($status, $allowed, true)) {
            return;
        }

        $pmuPo = \App\Models\PmuPo::find($pmuPoId);
        if (!$pmuPo) {
            return;
        }

        $pmuPo->update(['manual_status' => $status]);
        $this->renderQueryCache = null;
    }

    private function invalidateRenderCache(): void
    {
        $this->renderQueryCache = null;
    }

    public function openBulkReceiveModal(): void
    {
        if (empty($this->selectedNoaNumbers)) {
            return;
        }
        $this->bulkReceiveDate = now(self::TIMEZONE)->format(self::DATE_FORMAT);
        $this->bulkReceiveRemarks = null;
        $this->showBulkReceiveModal = true;
    }

    public function confirmBulkReceive(): void
    {
        $this->validate([
            'bulkReceiveDate' => 'required|date',
            'bulkReceiveRemarks' => 'nullable|string|max:1000',
        ], [
            'bulkReceiveDate.required' => 'Received date is required.',
            'bulkReceiveDate.date' => 'Please enter a valid date.',
        ]);

        try {
            $receiveDateTime = \Carbon\Carbon::createFromFormat(self::DATE_FORMAT, $this->bulkReceiveDate, self::TIMEZONE)->utc();

            // Update each PMU individually to trigger audit events
            foreach ($this->selectedNoaNumbers as $noaNumber) {
                $pmu = Pmu::where('notice_of_award_number', $noaNumber)
                    ->whereNull('deleted_at')
                    ->first();

                if ($pmu) {
                    $pmu->update([
                        'date_received' => $receiveDateTime,
                        'received_remarks' => $this->bulkReceiveRemarks ?: null,
                    ]);
                }
            }

            $count = count($this->selectedNoaNumbers);
            $this->closeBulkReceiveModal();
            $this->selectedNoaNumbers = [];
            $this->renderQueryCache = null; // Invalidate cache after bulk update

            LivewireAlert::title('Bulk Receive Successful!')
                ->success()
                ->text("{$count} NOA(s) have been marked as received.")
                ->toast()
                ->position('top-end')
                ->show();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('PmuIndexPage: Failed to bulk receive PMUs', [
                'noa_numbers' => $this->selectedNoaNumbers,
                'error' => $e->getMessage(),
            ]);
            LivewireAlert::title('Error')
                ->error()
                ->text('Failed to mark records as received. Please try again or contact support.')
                ->toast()
                ->position('top-end')
                ->show();
        }
    }

    public function closeBulkReceiveModal(): void
    {
        $this->showBulkReceiveModal = false;
        $this->bulkReceiveDate = null;
        $this->bulkReceiveRemarks = null;
        $this->resetValidation(['bulkReceiveDate', 'bulkReceiveRemarks']);
        $this->renderQueryCache = null; // Invalidate cache after modal close
    }
}
