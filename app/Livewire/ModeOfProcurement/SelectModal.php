<?php

namespace App\Livewire\ModeOfProcurement;

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
    public int $selectedPRPage = 1;

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
        $this->resetPage('selectedPRPage');
        $this->showModal = true;
    }

    public function close()
    {
        $this->showModal = false;
    }

    public function removeSelectedPR(int $id): void
    {
        // Check if it's in selectedLotIds and remove it
        if (in_array($id, $this->selectedLotIds)) {
            $this->selectedLotIds = array_diff($this->selectedLotIds, [$id]);
        }
        // Check if it's in selectedItemIds and remove it
        elseif (in_array($id, $this->selectedItemIds)) {
            $this->selectedItemIds = array_diff($this->selectedItemIds, [$id]);
        }
    }

    public function toggleSelection($type, $id)
    {
        $id = (int) $id;

        if ($type === 'lot') {
            if (in_array($id, $this->selectedLotIds)) {
                // If it's already in the array, remove it
                $this->selectedLotIds = array_diff($this->selectedLotIds, [$id]);
            } else {
                // If it's not in the array, add it
                $this->selectedLotIds[] = $id;
            }
        } elseif ($type === 'item') {
            if (in_array($id, $this->selectedItemIds)) {
                // If it's already in the array, remove it
                $this->selectedItemIds = array_diff($this->selectedItemIds, [$id]);
            } else {
                // If it's not in the array, add it
                $this->selectedItemIds[] = $id;
            }
        }
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
            // 🔹 Get all Procurement.procID values already used in MOP
            $usedProcIds = \App\Models\Mop::where('procurable_type', Procurement::class)
                ->pluck('procurable_id')
                ->toArray();

            $query = Procurement::query()
                ->with('division')
                ->where('procurement_type', 'perLot')
                ->whereNotIn('procID', $usedProcIds) // 🔥 exclude already used procurements
                ->when(
                    $this->search,
                    fn($q) =>
                    $q->where('pr_number', 'like', "%{$this->search}%")
                        ->orWhere('procurement_program_project', 'like', "%{$this->search}%")
                );

            $results = $query->latest()->paginate($this->perPage);
        } elseif ($this->procurementType === 'perItem') {
            // 🔹 Get all PrItem.prItemID values already used in MOP
            $usedPrItemIds = \App\Models\Mop::where('procurable_type', PrItem::class)
                ->pluck('procurable_id')
                ->toArray();

            $query = PrItem::query()
                ->with('procurement.division')
                ->whereHas('procurement', fn($q) => $q->where('procurement_type', 'perItem'))
                ->whereNotIn('prItemID', $usedPrItemIds) // 🔥 exclude already used items
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


        // --- START OF CHANGES ---

        // 1. Get the raw collections
        $selectedLotsCollection = !empty($this->selectedLotIds)
            ? Procurement::whereIn('id', $this->selectedLotIds)->get()
            : collect();

        $selectedItemsCollection = !empty($this->selectedItemIds)
            ? PrItem::with('procurement')->whereIn('id', $this->selectedItemIds)->get()
            : collect();

        // 2. Format them into consistent arrays (matching what the Blade file expects)
        $formattedLots = $selectedLotsCollection->map(function ($lot) {
            return [
                'id' => $lot->id,
                'pr_number' => $lot->pr_number,
                'procurement_program_project' => $lot->procurement_program_project, // for description
                'abc' => $lot->abc, // for amount
            ];
        });

        $formattedItems = $selectedItemsCollection->map(function ($item) {
            return [
                'id' => $item->id,
                'pr_number' => $item->procurement->pr_number,
                'description' => $item->description, // for description
                'amount' => $item->amount, // for amount
            ];
        });

        // 3. Merge them into one collection
        $allSelected = $formattedLots->merge($formattedItems);

        // 4. Paginate the merged collection using the correct page name
        $selectedPR = $this->paginateCollection($allSelected, $this->perPageSelected, 'selectedPRPage');

        // 5. Get the total count
        $totalSelectedCount = $allSelected->count();

        // --- END OF CHANGES ---

        return view('livewire.mode-of-procurement.select-modal', [
            'results' => $results,
            'totalSelectedCount' => $totalSelectedCount,
            'selectedPR' => $selectedPR, // Pass the new unified variable
            // 'selectedLots' and 'selectedItems' are no longer needed here
        ]);
    }
}
