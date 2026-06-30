<?php

namespace App\Livewire;

use App\Models\Procurement;
use App\Models\Division;
use App\Models\FundSource;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

#[Title('WVCHD PMIS')]
#[Lazy]
class HomePage extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    public $selectedDivision = 'all';
    public $selectedYear;
    public $selectedQuarter = 'all';
    public $search = '';
    public $selectedProcurement = null;
    public $expandedProcurementId = null;
    public int $perPage = 10;
    public $selectedDivisionId = null;
    public $selectedDivisionName = '';

    public $form = [
        'items' => [],
    ];

    protected $queryString = [
        'search' => ['except' => ''],
        'selectedDivision' => ['except' => 'all'],
        'selectedYear' => ['except' => 'all'],
        'selectedQuarter' => ['except' => 'all'],
        'perPage' => ['except' => 10],
    ];

    public function mount()
    {
        // Default to the current year when none is provided via the query string.
        $this->selectedYear ??= (string) now()->year;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingSelectedDivision()
    {
        $this->resetPage();
    }

    public function updatingSelectedYear($value)
    {
        // Quarter only applies within a specific year; clear it for "All Years".
        if ($value === 'all') {
            $this->selectedQuarter = 'all';
        }

        $this->resetPage();
    }

    public function updatingSelectedQuarter()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function toggle($field, $id)
    {
        $this->$field = $this->$field === $id ? null : $id;

        if ($this->$field) {
            $procurement = Procurement::with('pr_items')->find($id);
            $this->form['items'] = $procurement?->pr_items?->toArray() ?? [];
        } else {
            $this->form['items'] = [];
        }
    }

    /**
     * Start/end dates (within the selected year) for the chosen quarter, or null
     * when no specific year+quarter is selected.
     */
    private function getQuarterDates(): ?array
    {
        if (
            !$this->selectedYear || $this->selectedYear === 'all'
            || !$this->selectedQuarter || $this->selectedQuarter === 'all'
        ) {
            return null;
        }

        $quarterRange = [
            1 => ['01-01', '03-31'],
            2 => ['04-01', '06-30'],
            3 => ['07-01', '09-30'],
            4 => ['10-01', '12-31'],
        ];

        $range = $quarterRange[(int) $this->selectedQuarter] ?? null;
        if (!$range) {
            return null;
        }

        return [$this->selectedYear . '-' . $range[0], $this->selectedYear . '-' . $range[1]];
    }

    /**
     * Base query with the dashboard's active filters (division + PR-number year
     * prefix + optional quarter) applied. Every stat property builds off this so
     * a single change to the filters propagates everywhere.
     */
    private function baseQuery()
    {
        $query = Procurement::query();

        if ($this->selectedDivision !== 'all') {
            $query->where('procurements.divisions_id', $this->selectedDivision);
        }

        if ($this->selectedYear && $this->selectedYear !== 'all') {
            $query->where('procurements.pr_number', 'like', $this->selectedYear . '-%');
        }

        if ($quarterDates = $this->getQuarterDates()) {
            $query->whereBetween('procurements.date_receipt', $quarterDates);
        }

        return $query;
    }

    /**
     * Distinct years present in the pr_number prefix (e.g. "2026-00001" -> 2026),
     * newest first, for the year filter.
     */
    public function getAvailableYearsProperty()
    {
        return Procurement::query()
            ->whereNotNull('pr_number')
            ->where('pr_number', 'like', '%-%')
            ->selectRaw('SUBSTRING_INDEX(pr_number, "-", 1) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->map(fn($year) => (string) $year);
    }

    public function getSummaryStatsProperty()
    {
        $baseQuery = $this->baseQuery();

        $total = (clone $baseQuery)->count();

        // Get completed procurements

        $totalAbc = (clone $baseQuery)->sum('abc') ?? 0;

        // Get BAC categories breakdown with ABC totals
        $bacCategories = (clone $baseQuery)
            ->join('bac_types', 'procurements.bac_type_id', '=', 'bac_types.id')
            ->select(
                'bac_types.abbreviation',
                'bac_types.name',
                DB::raw('count(*) as count'),
                DB::raw('SUM(procurements.abc) as total_abc')
            )
            ->groupBy('bac_types.id', 'bac_types.abbreviation', 'bac_types.name')
            ->orderByDesc('count')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->abbreviation,
                    'fullName' => $item->name,
                    'count' => $item->count,
                    'totalAbc' => number_format($item->total_abc ?? 0, 2),
                ];
            });

        // Get Division ABC breakdown
        $divisionAbc = (clone $baseQuery)
            ->join('divisions', 'procurements.divisions_id', '=', 'divisions.id')
            ->select(
                'divisions.abbreviation',
                'divisions.divisions',
                DB::raw('SUM(procurements.abc) as total_abc')
            )
            ->groupBy('divisions.id', 'divisions.abbreviation', 'divisions.divisions')
            ->orderByDesc('total_abc')
            ->get()
            ->map(function ($item) {
                return [
                    'abbreviation' => $item->abbreviation,
                    'fullName' => $item->divisions,
                    'totalAbc' => number_format($item->total_abc ?? 0, 2),
                ];
            });

        return [
            'total' => $total,
            'totalAbc' => number_format($totalAbc, 2),
            'bacCategories' => $bacCategories,
            'divisionAbc' => $divisionAbc,
        ];
    }

    public function getProcurementByBacTypeProperty()
    {
        $baseQuery = $this->baseQuery();

        return (clone $baseQuery)
            ->join('bac_types', 'procurements.bac_type_id', '=', 'bac_types.id')
            ->select('bac_types.name', DB::raw('count(*) as count'))
            ->groupBy('bac_types.id', 'bac_types.abbreviation')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->name,
                    'value' => $item->count,
                ];
            });
    }


    public function getDivisionCountsProperty()
    {
        $baseQuery = $this->baseQuery();

        return (clone $baseQuery)
            ->join('divisions', 'procurements.divisions_id', '=', 'divisions.id')
            ->select('divisions.id', 'divisions.abbreviation', 'divisions.divisions', DB::raw('count(*) as count'))
            ->groupBy('divisions.id', 'divisions.abbreviation', 'divisions.divisions')
            ->orderBy('divisions.id', 'asc') // Changed from orderByDesc('count')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'division' => $item->abbreviation,
                    'name' => $item->divisions,
                    'count' => $item->count,
                ];
            });
    }



    public function getRecentProcurementsProperty()
    {
        $baseQuery = $this->baseQuery()
            ->with(['division', 'prLotPrstages.procurementStage']);

        return $baseQuery
            ->orderByDesc('created_at')
            ->take(10)
            ->get();
    }


    public function selectDivision($divisionId)
    {
        // Toggle: if clicking the same division, close it
        if ($this->selectedDivisionId === $divisionId) {
            $this->selectedDivisionId = null;
            $this->selectedDivisionName = '';
            return;
        }

        // Otherwise, open the selected division
        $this->selectedDivisionId = $divisionId;
        $division = Division::find($divisionId);
        $this->selectedDivisionName = $division->divisions ?? '';
    }

    public function getClusterCommitteeCountsProperty()
    {
        if (!$this->selectedDivisionId) {
            return collect();
        }

        $baseQuery = Procurement::query()
            ->where('procurements.divisions_id', $this->selectedDivisionId);

        if ($this->selectedYear && $this->selectedYear !== 'all') {
            $baseQuery->where('procurements.pr_number', 'like', $this->selectedYear . '-%');
        }

        if ($quarterDates = $this->getQuarterDates()) {
            $baseQuery->whereBetween('procurements.date_receipt', $quarterDates);
        }

        return $baseQuery
            ->join('cluster_committees', 'procurements.cluster_committees_id', '=', 'cluster_committees.id')
            ->select(
                'cluster_committees.clustercommittee as name',
                DB::raw('count(*) as count'),
                DB::raw('SUM(procurements.abc) as total_abc')
            )
            ->groupBy('cluster_committees.id', 'cluster_committees.clustercommittee')
            ->orderByDesc('count')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->name,
                    'count' => $item->count,
                    'totalAbc' => number_format($item->total_abc ?? 0, 2),
                ];
            });
    }

    public function getCategoryCountsProperty()
    {
        $baseQuery = $this->baseQuery();

        $result = (clone $baseQuery)
            ->join('categories', 'procurements.category_id', '=', 'categories.id')
            ->select('categories.category as name', DB::raw('count(*) as count'))
            ->groupBy('categories.id', 'categories.category')
            ->orderByDesc('count')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->name,
                    'count' => $item->count,
                ];
            });

        // Debug: Log the data
        \Log::info('Category Counts:', [
            'count' => $result->count(),
            'data' => $result->toArray()
        ]);

        return $result;
    }

    // Also add this method to check the raw SQL queries
    public function debugQueries()
    {
        // Enable query log
        DB::enableQueryLog();

        // Call the properties
        $categories = $this->categoryCounts;
        $clusters = $this->clusterCommitteeCounts;

        // Get the queries
        $queries = DB::getQueryLog();

        // Log them
        \Log::info('SQL Queries:', $queries);
    }

    public function getCategoryTypeCountsProperty()
    {
        $baseQuery = $this->baseQuery()
            ->whereNotNull('procurements.category_type_id');

        return (clone $baseQuery)
            ->join('category_types', 'procurements.category_type_id', '=', 'category_types.id')
            ->select('category_types.category_type as name', DB::raw('count(*) as count'))
            ->groupBy('category_types.id', 'category_types.category_type')
            ->orderByDesc('count')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->name,
                    'count' => $item->count,
                ];
            });
    }

    public function getVenueSpecificCountsProperty()
    {
        $baseQuery = $this->baseQuery()
            ->whereNotNull('procurements.venue_specific_id');

        return (clone $baseQuery)
            ->join('venue_specifics', 'procurements.venue_specific_id', '=', 'venue_specifics.id')
            ->select('venue_specifics.name', DB::raw('count(*) as count'))
            ->groupBy('venue_specifics.id', 'venue_specifics.name')
            ->orderByDesc('count')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->name,
                    'count' => $item->count,
                ];
            });
    }

    public function getVenueProvinceHucCountsProperty()
    {
        $baseQuery = $this->baseQuery()
            ->whereNotNull('procurements.venue_province_huc_id');

        return (clone $baseQuery)
            ->join('province_hucs', 'procurements.venue_province_huc_id', '=', 'province_hucs.id')
            ->select('province_hucs.province_huc as name', DB::raw('count(*) as count'))
            ->groupBy('province_hucs.id', 'province_hucs.province_huc')
            ->orderByDesc('count')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->name,
                    'count' => $item->count,
                ];
            });
    }

    public function getProcurementStagePerLotCountsProperty()
    {
        $baseQuery = $this->baseQuery();

        return (clone $baseQuery)
            ->join('pr_lot_prstage', 'procurements.procID', '=', 'pr_lot_prstage.procID')
            ->join('procurement_stages', 'pr_lot_prstage.pr_stage_id', '=', 'procurement_stages.id')
            ->select(
                'procurement_stages.procurementstage as name',
                DB::raw('COUNT(DISTINCT procurements.procID) as count')
            )
            ->groupBy('procurement_stages.id', 'procurement_stages.procurementstage')
            ->orderByDesc('count')
            ->get();
    }

    public function getProcurementStagePerItemCountsProperty()
    {
        $baseQuery = $this->baseQuery();

        return (clone $baseQuery)
            ->join('pr_item_prstage', 'procurements.procID', '=', 'pr_item_prstage.procID')
            ->join('procurement_stages', 'pr_item_prstage.pr_stage_id', '=', 'procurement_stages.id')
            ->select(
                'procurement_stages.procurementstage as name',
                DB::raw('COUNT(DISTINCT pr_item_prstage.prItemID) as count')
            )
            ->groupBy('procurement_stages.id', 'procurement_stages.procurementstage')
            ->orderByDesc('count')
            ->get();
    }

    public function getRemarksPerLotCountsProperty()
    {
        $baseQuery = $this->baseQuery();

        return (clone $baseQuery)
            ->join('pr_lot_remark', 'procurements.procID', '=', 'pr_lot_remark.procID')
            ->join('remarks', 'pr_lot_remark.remarks_id', '=', 'remarks.id')
            ->select(
                'remarks.remarks as name',
                DB::raw('COUNT(DISTINCT procurements.procID) as count')
            )
            ->groupBy('remarks.id', 'remarks.remarks')
            ->orderByDesc('count')
            ->get();
    }

    public function getRemarksPerItemCountsProperty()
    {
        $baseQuery = $this->baseQuery();

        return (clone $baseQuery)
            ->join('pr_item_remark', 'procurements.procID', '=', 'pr_item_remark.procID')
            ->join('remarks', 'pr_item_remark.remarks_id', '=', 'remarks.id')
            ->select(
                'remarks.remarks as name',
                DB::raw('COUNT(DISTINCT pr_item_remark.prItemID) as count')
            )
            ->groupBy('remarks.id', 'remarks.remarks')
            ->orderByDesc('count')
            ->get();
    }
    public function getFundSourceCountsProperty()
    {
        $baseQuery = $this->baseQuery()
            ->whereNotNull('procurements.fund_source_id');

        return (clone $baseQuery)
            ->join('fund_sources', 'procurements.fund_source_id', '=', 'fund_sources.id')
            ->select('fund_sources.fundsources as name', DB::raw('count(*) as count'))
            ->groupBy('fund_sources.id', 'fund_sources.fundsources')
            ->orderByDesc('count')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->name,
                    'count' => $item->count,
                ];
            });
    }

    public function placeholder()
    {
        return view('livewire.home-page-skeleton');
    }

    public function render()
    {
        return view('livewire.home-page', [
            'summaryStats' => $this->summaryStats,
            'procurementByType' => $this->procurementByBacType,
            'divisionCounts' => $this->divisionCounts,
            'clusterCommitteeCounts' => $this->clusterCommitteeCounts,
            'categoryCounts' => $this->categoryCounts,
            'categoryTypeCounts' => $this->categoryTypeCounts,
            'venueSpecificCounts' => $this->venueSpecificCounts,
            'venueProvinceHucCounts' => $this->venueProvinceHucCounts,
            'procurementStagePerLotCounts' => $this->procurementStagePerLotCounts,
            'procurementStagePerItemCounts' => $this->procurementStagePerItemCounts,
            'remarksPerLotCounts' => $this->remarksPerLotCounts,
            'remarksPerItemCounts' => $this->remarksPerItemCounts,
            'fundSourceCounts' => $this->fundSourceCounts,
            'divisions' => Division::where('is_active', true)->get(),
            'availableYears' => $this->availableYears,
        ]);
    }

}
