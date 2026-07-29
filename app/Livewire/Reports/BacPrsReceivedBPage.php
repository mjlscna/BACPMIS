<?php

namespace App\Livewire\Reports;

use App\Models\BacType;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Procurement;
use App\Exports\BacPrsReceivedBExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\MopLot;
use App\Models\BidSchedule;
use App\Models\PrSvp;
use App\Models\ModeOfProcurement;
use App\Models\Remarks;
use App\Models\ClusterCommittee;
use App\Models\ProcurementStage;
use App\Models\FundSource;
use App\Models\FundSourceGroup;
use App\Models\PmuPo;
use Livewire\Attributes\Title;

#[Title("PR's Received (B) | PMIS")]
class BacPrsReceivedBPage extends Component
{
    use WithPagination;

    // Filter panel visibility
    public bool $showFilters = false;

    // Pagination
    public $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
        'startDate' => ['except' => ''],
        'endDate' => ['except' => ''],
        'currentModeFilter' => ['except' => null],
        'clusterFilter' => ['except' => null],
        'procurementStageFilter' => ['except' => null],
        'fundSourceFilter' => ['except' => null],
        'fundSourceGroupFilter' => ['except' => null],
        'remarksFilter' => ['except' => null],
    ];
    protected $paginationTheme = 'tailwind';

    // Search
    public $search = '';

    // Filters
    public $startDate = '';
    public $endDate = '';
    public $currentModeFilter = null;
    public $clusterFilter = null;
    public $procurementStageFilter = null;
    public $fundSourceFilter = null;
    public $fundSourceGroupFilter = null;
    public $remarksFilter = null;

    public function mount()
    {
    }

    /**
     * Load filter options
     */

    /**
     * Reset pagination when search or filters change.
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function updatedStartDate()
    {
        $this->resetPage();
    }

    public function updatedEndDate()
    {
        $this->resetPage();
    }

    public function updatedCurrentModeFilter()
    {
        $this->resetPage();
    }

    public function updatedClusterFilter()
    {
        $this->resetPage();
    }

    public function updatedProcurementStageFilter()
    {
        $this->resetPage();
    }

    public function updatedFundSourceFilter()
    {
        $this->resetPage();
    }

    public function updatedFundSourceGroupFilter()
    {
        $this->resetPage();
    }

    public function updatedRemarksFilter()
    {
        $this->resetPage();
    }

    public function toggleFilters()
    {
        $this->showFilters = !$this->showFilters;
    }

    /**
     * Clear all filters
     */
    public function clearFilters()
    {
        $this->search = '';
        $this->startDate = '';
        $this->endDate = '';
        $this->currentModeFilter = null;
        $this->clusterFilter = null;
        $this->procurementStageFilter = null;
        $this->fundSourceFilter = null;
        $this->fundSourceGroupFilter = null;
        $this->remarksFilter = null;
        $this->resetPage();
    }

    /**
     * Export to Excel
     */
    public function exportToExcel()
    {
        $dateRange = '';
        if (!empty($this->startDate) && !empty($this->endDate)) {
            $dateRange = '_' . $this->startDate . '_to_' . $this->endDate;
        } elseif (!empty($this->startDate)) {
            $dateRange = '_from_' . $this->startDate;
        } elseif (!empty($this->endDate)) {
            $dateRange = '_to_' . $this->endDate;
        } else {
            $dateRange = '_' . now()->format('Y-m-d');
        }

        $fileName = 'BAC_PRs_Received_Category_B' . $dateRange . '.xlsx';

        return Excel::download(
            new BacPrsReceivedBExport(
                $this->search,
                $this->startDate,
                $this->endDate,
                $this->currentModeFilter,
                2,
                $this->clusterFilter,
                $this->procurementStageFilter,
                $this->fundSourceFilter,
                $this->fundSourceGroupFilter,
                $this->remarksFilter
            ),
            $fileName
        );
    }

    public function render()
    {
        $query = Procurement::query()
            ->with([
                'currentPrStage.procurementStage',
                'division',
                'clusterCommittee',
                'category.bacType',
                'fundSource.fundSourceGroup',
                'endUser',
                'mopLots.modeOfProcurement',
                'pr_items.mopItems.modeOfProcurement',
                'pr_items.prstage.stage',
                'pr_items.currentItemRemark.remark',
                'pr_items.postProcurement.supplier',
                'pr_items.postProcurement.pmu',
                'pr_items',
                'currentLotRemark.remark',
                'postProcurement.supplier',
                'postProcurement.pmu',
            ])
            ->whereHas('category', function ($q) {
                $q->where('bac_type_id', 2);
            })
            ->latest('date_receipt');

        // Apply search filter
        if (!empty($this->search)) {
            $searchTerm = '%' . $this->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('pr_number', 'like', $searchTerm)
                    ->orWhere('procurement_program_project', 'like', $searchTerm);
            });
        }

        // Apply period covered filter
        if (!empty($this->startDate) && !empty($this->endDate)) {
            $query->whereBetween('date_receipt', [$this->startDate, $this->endDate]);
        } elseif (!empty($this->startDate)) {
            $query->whereDate('date_receipt', '>=', $this->startDate);
        } elseif (!empty($this->endDate)) {
            $query->whereDate('date_receipt', '<=', $this->endDate);
        }

        // Current Mode filter
        if ($this->currentModeFilter) {
            $query->where(function ($q) {
                // Per-lot procurements
                $q->where('procurement_type', 'perLot')
                    ->whereHas('mopLots', function ($subQ) {
                        $subQ->where('mode_of_procurement_id', $this->currentModeFilter)
                            ->whereRaw('mode_order = (SELECT MAX(mode_order) FROM mop_lot WHERE procID = procurements.procID)');
                    });
                // Per-item procurements
                $q->orWhere('procurement_type', 'perItem')
                    ->whereHas('pr_items.mopItems', function ($subQ) {
                        $subQ->where('mode_of_procurement_id', $this->currentModeFilter)
                            ->whereRaw('mode_order = (SELECT MAX(mode_order) FROM mop_item WHERE prItemID = pr_items.prItemID)');
                    });
            });
        }

        // Cluster / Unit filter
        if ($this->clusterFilter) {
            $query->where('cluster_committees_id', $this->clusterFilter);
        }

        // Procurement Stage filter
        if ($this->procurementStageFilter) {
            $stageId = $this->procurementStageFilter;
            $query->where(function ($q) use ($stageId) {
                // perLot: filter by lot-level current stage
                $q->where(function ($sub) use ($stageId) {
                    $sub->where('procurement_type', 'perLot')
                        ->whereHas('currentPrStage', function ($sq) use ($stageId) {
                            $sq->where('pr_stage_id', $stageId);
                        });
                })
                    // perItem: filter if any item has that stage as its current stage
                    ->orWhere(function ($sub) use ($stageId) {
                        $sub->where('procurement_type', 'perItem')
                            ->whereHas('pr_items', function ($sq) use ($stageId) {
                                $sq->whereHas('prstage', function ($s) use ($stageId) {
                                    $s->where('pr_stage_id', $stageId);
                                });
                            });
                    });
            });
        }

        // Fund Source filter
        if ($this->fundSourceFilter) {
            $query->where('fund_source_id', $this->fundSourceFilter);
        }

        // Fund Source Group filter
        if ($this->fundSourceGroupFilter) {
            $query->whereHas('fundSource', function ($q) {
                $q->where('fund_source_group_id', $this->fundSourceGroupFilter);
            });
        }

        // Remarks filter (lot remarks only; converted PRs keep theirs as history)
        if ($this->remarksFilter) {
            $query->where('procurement_type', 'perLot')->whereHas('prLotRemarks', function ($q) {
                $q->where('remarks_id', $this->remarksFilter)
                    ->whereRaw('remark_history = (SELECT MAX(remark_history) FROM pr_lot_remark WHERE procID = procurements.procID)');
            });
        }

        $procurements = $query->paginate($this->perPage);

        // Calculate row counts for accurate pagination display
        $perPage = $this->perPage;
        $currentPage = $procurements->currentPage();
        $skip = ($currentPage - 1) * $perPage;

        $previousQuery = clone $query;
        $previousProcurements = $previousQuery->limit($skip)->get();
        $previousRows = $previousProcurements->sum(function ($p) {
            return $p->procurement_type === 'perLot' ? 1 : $p->pr_items->count();
        });

        $currentRows = collect($procurements->items())->sum(function ($p) {
            return $p->procurement_type === 'perLot' ? 1 : $p->pr_items->count();
        });

        $startRow = $previousRows + 1;
        $endRow = $previousRows + $currentRows;

        $totalQuery = clone $query;
        $totalProcurements = $totalQuery->get();
        $totalRows = $totalProcurements->sum(function ($p) {
            return $p->procurement_type === 'perLot' ? 1 : $p->pr_items->count();
        });

        // Batch-load BidSchedules for perItem procurements (to resolve IB No per item)
        $allLotProcIds = [];
        $allItemIds = [];
        foreach ($procurements->items() as $procurement) {
            if ($procurement->procurement_type === 'perItem') {
                foreach ($procurement->pr_items as $item) {
                    $allItemIds[] = $item->prItemID;
                }
            } else {
                $allLotProcIds[] = $procurement->procID;
            }
        }
        $itemBidScheduleMap = collect();
        if (!empty($allItemIds)) {
            $itemBidScheduleMap = BidSchedule::whereIn('ref_id', $allItemIds)
                ->get()
                ->keyBy(fn($b) => $b->ref_id . '_' . $b->mop_uid);
        }

        // Load pmu_po records directly by ref_id, mirroring how PmuEditPage accesses them.
        // perLot: ref_id = procID | perItem: ref_id = prItemID
        $pmuPoMap = !empty($allLotProcIds)
            ? PmuPo::whereIn('ref_id', $allLotProcIds)->get()->keyBy('ref_id')
            : collect();
        $itemPmuPoMap = !empty($allItemIds)
            ? PmuPo::whereIn('ref_id', $allItemIds)->get()->keyBy('ref_id')
            : collect();

        // Add current mode, status and IB No to each procurement
        foreach ($procurements as $procurement) {
            $modeStatus = $this->getCurrentModeAndStatus($procurement);
            $procurement->currentMode = $modeStatus['mode'];
            $procurement->currentStatus = $modeStatus['status'];
            $procurement->currentIbNo = $modeStatus['ibNo'];

            // For perItem: attach ibNo to each item
            if ($procurement->procurement_type === 'perItem') {
                foreach ($procurement->pr_items as $item) {
                    $latestMop = $item->mopItems
                        ->filter(fn($m) => $m->uid !== 'MOP-1-1')
                        ->sortByDesc('mode_order')
                        ->first();
                    $ibNo = null;
                    if ($latestMop && in_array($latestMop->mode_of_procurement_id, [2, 3, 4, 5, 6])) {
                        $compositeKey = $item->prItemID . '_' . $latestMop->uid;
                        $bidSchedule = $itemBidScheduleMap->get($compositeKey);
                        $ibNo = $bidSchedule?->ib_number;
                    }
                    $item->ibNo = $ibNo;
                }
            }
        }

        return view('livewire.reports.bac-prs-received-b-page', [
            'procurements' => $procurements,
            'pmuPoMap' => $pmuPoMap,
            'itemPmuPoMap' => $itemPmuPoMap,
            'modes' => $this->modes,
            'clusterOptions' => $this->clusterOptions,
            'procurementStages' => $this->procurementStages,
            'fundSources' => $this->fundSources,
            'fundSourceGroups' => $this->fundSourceGroups,
            'remarksOptions' => $this->remarksOptions,
            'startRow' => $startRow,
            'endRow' => $endRow,
            'totalRows' => $totalRows,
        ]);
    }

    public function getModesProperty()
    {
        return ModeOfProcurement::orderBy('id', 'asc')
            ->get()
            ->map(function ($mode) {
                return [
                    'id' => $mode->id,
                    'name' => $mode->modeofprocurements,
                ];
            });
    }

    public function getClusterOptionsProperty()
    {
        return ClusterCommittee::orderBy('clustercommittee', 'asc')
            ->get()
            ->map(function ($cluster) {
                return [
                    'id' => $cluster->id,
                    'name' => $cluster->clustercommittee,
                ];
            });
    }

    public function getProcurementStagesProperty()
    {
        return ProcurementStage::where('is_active', true)
            ->orderBy('procurementstage', 'asc')
            ->get()
            ->map(function ($stage) {
                return [
                    'id' => $stage->id,
                    'name' => $stage->procurementstage,
                ];
            });
    }

    public function getFundSourcesProperty()
    {
        return FundSource::orderBy('fundsources', 'asc')
            ->get()
            ->map(function ($fs) {
                return [
                    'id' => $fs->id,
                    'name' => $fs->fundsources,
                ];
            });
    }

    public function getFundSourceGroupsProperty()
    {
        return FundSourceGroup::where('is_active', true)
            ->orderBy('name', 'asc')
            ->get()
            ->map(function ($fsg) {
                return [
                    'id' => $fsg->id,
                    'name' => $fsg->name,
                ];
            });
    }

    public function getRemarksOptionsProperty()
    {
        return Remarks::where('is_active', true)
            ->orderBy('remarks', 'asc')
            ->get()
            ->map(function ($remark) {
                return [
                    'id' => $remark->id,
                    'name' => $remark->remarks,
                ];
            });
    }

    /**
     * Get the current mode of procurement and status for a procurement
     */
    private function getCurrentModeAndStatus($procurement)
    {
        if ($procurement->procurement_type === 'perLot') {
            $latestMop = $procurement->mopLots()
                ->orderBy('mode_order', 'desc')
                ->first();

            if (!$latestMop) {
                return ['mode' => null, 'status' => null];
            }

            $status = null;
            $modeId = $latestMop->mode_of_procurement_id;

            // Mode 1 - No schedule needed
            if ($modeId == 1) {
                return [
                    'mode' => $latestMop->modeOfProcurement,
                    'status' => 'No Schedule',
                    'ibNo' => null,
                ];
            }

            // Check bidding modes (2-6)
            $ibNo = null;
            if (in_array($modeId, [2, 3, 4, 5, 6])) {
                $bidSchedule = BidSchedule::where('mop_uid', $latestMop->uid)
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($bidSchedule) {
                    $ibNo = $bidSchedule->ib_number;
                    if ($bidSchedule->bidding_result) {
                        $status = 'Completed';
                    }
                }
            }

            // Check SVP modes (7-24)
            if (in_array($modeId, [7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24])) {
                $prSvp = PrSvp::where('mop_uid', $latestMop->uid)
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($prSvp && $prSvp->resolution_number) {
                    $status = 'Completed';
                }
            }

            return [
                'mode' => $latestMop->modeOfProcurement,
                'status' => $status,
                'ibNo' => $ibNo ?? null,
            ];
        } else {
            return ['mode' => null, 'status' => 'Multiple', 'ibNo' => null];
        }
    }
}
