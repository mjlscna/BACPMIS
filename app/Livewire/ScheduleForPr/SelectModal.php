<?php

namespace App\Livewire\ScheduleForPr;

use App\Models\Procurement;
use App\Models\PrItem;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;

class SelectModal extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    public bool $showModal = false;
    public string $search = '';
    public int $perPage = 10;
    public array $existingSelection = [];
    public string $procurementType = '';

    public array $existingLotIds = [];
    public array $existingItemIds = [];
    public array $selectedLotIds = [];
    public array $selectedItemIds = [];

    public int $perPageSelected = 5;
    public int $selectedLotsPage = 1;
    public int $selectedItemsPage = 1;

    protected array $queryString = ['search'];
    protected $listeners = ['open-mode-modal' => 'open'];

    public function open(array $existingLotIds = [], array $existingItemIds = [])
    {
        $this->resetState();

        // Restore previous selections
        $this->existingLotIds = $existingLotIds;
        $this->existingItemIds = $existingItemIds;
        $this->selectedLotIds = $existingLotIds;
        $this->selectedItemIds = $existingItemIds;

        $this->resetPage();
        $this->resetPage('selectedLotsPage');
        $this->resetPage('selectedItemsPage');
        $this->showModal = true;
    }

    public function close()
    {
        $this->showModal = false;
    }

    public function removeSelection(int $procurementId): void
    {
        $this->selectedLotIds = array_diff($this->selectedLotIds, [$procurementId]);
    }

    public function removeItemSelection(int $itemId): void
    {
        $this->selectedItemIds = array_diff($this->selectedItemIds, [$itemId]);
    }

    public function selectProcurements()
    {
        $selectedData = [];

        // Handle selected 'perLot' procurements
        if (!empty($this->selectedLotIds)) {
            $lots = Procurement::whereIn('id', $this->selectedLotIds)->get();
            foreach ($lots as $lot) {
                $data = $this->formatProcurementData($lot);
                $data['items'] = []; // 'perLot' has no specific items selected
                $selectedData[] = $data;
            }
        }

        // Handle selected 'perItem' items
        if (!empty($this->selectedItemIds)) {
            $items = PrItem::with('procurement')->whereIn('id', $this->selectedItemIds)->get();

            // Group items by their parent procurement
            $groupedItems = $items->groupBy('procurement_id');

            foreach ($groupedItems as $procurementId => $procItems) {
                $parentProc = $procItems->first()->procurement;
                if ($parentProc) {
                    $data = $this->formatProcurementData($parentProc);
                    $data['items'] = $procItems->map(fn($item) => [
                        'id' => $item->id,
                        'procID' => $item->procID,
                        'prItemID' => $item->prItemID,
                        'description' => $item->description,
                        'quantity' => $item->quantity,
                        'unit' => $item->unit,
                        'amount' => $item->amount,
                    ])->toArray();
                    $selectedData[] = $data;
                }
            }
        }

        $this->dispatch('procurementsSelected', selectedData: $selectedData);
        $this->close();
    }

    private function formatProcurementData(Procurement $proc): array
    {
        return [
            'id' => $proc->id,
            'procID' => $proc->procID,
            'pr_number' => $proc->pr_number,
            'procurement_program_project' => $proc->procurement_program_project,
            'division_abbreviation' => $proc->division?->abbreviation,
            'abc' => $proc->abc,
        ];
    }

    private function resetState(): void
    {
        $this->search = '';
        $this->selectedLotIds = [];
        $this->selectedItemIds = [];
        $this->resetPage();
    }

    private function paginateCollection($collection, $perPage, $pageName)
    {
        $page = $this->$pageName ?? 1;
        return new LengthAwarePaginator(
            $collection->forPage($page, $perPage),
            $collection->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'pageName' => $pageName]
        );
    }

    public function nextCustomPage(string $pageName)
    {
        if (property_exists($this, $pageName)) {
            $this->$pageName++;
        }
    }

    public function previousCustomPage(string $pageName)
    {
        if (property_exists($this, $pageName) && $this->$pageName > 1) {
            $this->$pageName--;
        }
    }

    public function render()
    {
        $results = null;

        if ($this->procurementType === 'perLot') {
            $query = Procurement::query()
                ->with('division')
                ->where('procurement_type', 'perLot')
                ->when(
                    $this->search,
                    fn($q) =>
                    $q->where('pr_number', 'like', "%{$this->search}%")
                        ->orWhere('procurement_program_project', 'like', "%{$this->search}%")
                );
            $results = $query->latest()->paginate($this->perPage);
        } elseif ($this->procurementType === 'perItem') {
            $query = PrItem::query()
                ->with('procurement.division')
                ->whereHas('procurement', fn($q) => $q->where('procurement_type', 'perItem'))
                ->when(
                    $this->search,
                    fn($q) =>
                    $q->where('description', 'like', "%{$this->search}%")
                        ->orWhereHas(
                            'procurement',
                            fn($subQ) =>
                            $subQ->where('pr_number', 'like', "%{$this->search}%")
                        )
                );
            $results = $query->latest()->paginate($this->perPage);
        }

        // Selected lots and items collections for the bottom section
        $selectedLotsCollection = !empty($this->selectedLotIds)
            ? Procurement::whereIn('id', $this->selectedLotIds)->get()
            : collect();

        $selectedItemsCollection = !empty($this->selectedItemIds)
            ? PrItem::with('procurement')->whereIn('id', $this->selectedItemIds)->get()
            : collect();

        $selectedLots = $this->paginateCollection($selectedLotsCollection, $this->perPageSelected, 'selectedLotsPage');
        $selectedItems = $this->paginateCollection($selectedItemsCollection, $this->perPageSelected, 'selectedItemsPage');
        $totalSelectedCount = $selectedLotsCollection->count() + $selectedItemsCollection->count();

        return view('livewire.schedule-for-pr.select-modal', [
            'results' => $results,
            'totalSelectedCount' => $totalSelectedCount,
            'selectedLots' => $selectedLots,
            'selectedItems' => $selectedItems,
        ]);
    }
}
