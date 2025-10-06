<?php

namespace App\Livewire\ModeOfProcurement;

use App\Models\Procurement;
use App\Models\PrItem;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;

class ModeProcSelectModal extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    public bool $showModal = false;
    public string $search = '';
    public int $perPage = 10;
    public array $existingSelection = [];
    public string $procurementType = '';
    public array $selectedProcurements = [];
    public ?int $expandedProcurementId = null;
    public array $selectedItemIds = [];
    public int $perPageSelected = 5;
    public int $selectedLotsPage = 1;
    public int $selectedItemsPage = 1;
    protected array $queryString = ['search'];
    protected $listeners = ['open-mode-modal' => 'open'];


    public function open()
    {
        $this->resetState();
        $this->selectedProcurements = $this->existingSelection;
        $this->resetPage();
        $this->resetPage('selectedPage');
        $this->showModal = true;
    }

    public function close()
    {
        $this->showModal = false;
    }
    public function toggleItems(int $procurementId): void
    {
        $this->expandedProcurementId = $this->expandedProcurementId === $procurementId ? null : $procurementId;
    }

    public function removeSelection(int $procurementId): void
    {
        $this->selectedProcurements = array_diff($this->selectedProcurements, [$procurementId]);
        if (count($this->selectedProcurements) % $this->perPageSelected === 0) {
            $this->resetPage('selectedPage');
        }
    }
    public function removeItemSelection(int $itemId): void
    {
        $this->selectedItemIds = array_diff($this->selectedItemIds, [$itemId]);
        if ((count($this->selectedProcurements) + count($this->selectedItemIds)) % $this->perPageSelected === 0) {
            $this->resetPage('selectedPage');
        }
    }
    public function selectProcurements()
    {
        $selectedData = [];

        if (!empty($this->selectedProcurements)) {
            $lots = Procurement::whereIn('id', $this->selectedProcurements)->get();
            foreach ($lots as $lot) {
                $data = $this->formatProcurementData($lot);
                $data['items'] = [];
                $selectedData[] = $data;
            }
        }

        if (!empty($this->selectedItemIds) && $this->expandedProcurementId) {
            $parentProc = Procurement::find($this->expandedProcurementId);
            $items = PrItem::whereIn('id', $this->selectedItemIds)->get();

            if ($parentProc && $items->isNotEmpty()) {
                $data = $this->formatProcurementData($parentProc);
                $data['items'] = $items->map(fn($item) => [
                    'id' => $item->id,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit' => $item->unit,
                    'amount' => $item->amount,
                ])->toArray();
                $selectedData[] = $data;
            }
        }

        if (empty($selectedData)) {
            return;
        }

        session()->flash('selected_procurements', $selectedData);
        $this->close();
        return redirect()->route('mode-of-procurement.create', ['type' => $this->procurementType]);

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
        $this->selectedProcurements = [];
        $this->expandedProcurementId = null;
        $this->selectedItemIds = [];
        $this->resetPage();
    }
    private function paginateCollection($collection, $perPage, $pageName)
    {
        $page = $this->$pageName ?? 1; // Read from Livewire state

        $paginator = new LengthAwarePaginator(
            $collection->forPage($page, $perPage),
            $collection->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'pageName' => $pageName,
            ]
        );

        return $paginator;
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
        $procurementsQuery = Procurement::query()
            ->with('pr_items', 'division')
            ->where('procurement_type', $this->procurementType)
            ->when(
                $this->search,
                fn($q) =>
                $q->where('pr_number', 'like', "%{$this->search}%")
                    ->orWhere('procurement_program_project', 'like', "%{$this->search}%")
            );

        // Main table pagination (already exists)
        $procurements = $procurementsQuery
            ->latest()
            ->paginate($this->perPage);


        // Selected lots and items collections
        $selectedLotsCollection = !empty($this->selectedProcurements)
            ? Procurement::whereIn('id', $this->selectedProcurements)->get()
            : collect();

        $selectedItemsCollection = !empty($this->selectedItemIds)
            ? PrItem::with('procurement')->whereIn('id', $this->selectedItemIds)->get()
            : collect();

        // Custom paginators for selected tables
        $selectedLots = $this->paginateCollection($selectedLotsCollection, $this->perPageSelected, 'selectedLotsPage');
        $selectedItems = $this->paginateCollection($selectedItemsCollection, $this->perPageSelected, 'selectedItemsPage');

        $totalSelectedCount = $selectedLotsCollection->count() + $selectedItemsCollection->count();

        return view('livewire.mode-of-procurement.mode-proc-select-modal', [
            'procurements' => $procurements,
            'totalSelectedCount' => $totalSelectedCount,
            'selectedLots' => $selectedLots,
            'selectedItems' => $selectedItems,
        ]);
    }
}
