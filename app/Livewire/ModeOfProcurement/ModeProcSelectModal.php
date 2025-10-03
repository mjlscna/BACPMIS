<?php

namespace App\Livewire\ModeOfProcurement;

use App\Models\Procurement;
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
    public array $selectedProcurements = [];
    public array $existingSelection = [];

    // ✅ 1. Add this property to receive the type from the parent
    public string $procurementType = '';

    public int $perPageSelected = 5;
    protected array $queryString = ['search'];
    protected $listeners = ['open-mode-modal' => 'open'];


    public function updatingSearch()
    {
        $this->resetPage('modalPage');
    }

    public function open()
    {
        $this->search = '';
        $this->selectedProcurements = $this->existingSelection;
        $this->resetPage('modalPage');
        $this->resetPage('selectedPage');
        $this->showModal = true;
    }

    public function close()
    {
        $this->showModal = false;
    }

    public function removeSelection(int $procurementId): void
    {
        $this->selectedProcurements = array_diff($this->selectedProcurements, [$procurementId]);
        if (count($this->selectedProcurements) % $this->perPageSelected === 0) {
            $this->resetPage('selectedPage');
        }
    }

    public function selectProcurements()
    {
        if (empty($this->selectedProcurements)) {
            return;
        }
        $procurements = Procurement::whereIn('id', $this->selectedProcurements)->get();
        $selectedData = $procurements->map(fn($proc) => [
            'id' => $proc->id,
            'procID' => $proc->procID,
            'pr_number' => $proc->pr_number,
            'procurement_program_project' => $proc->procurement_program_project,
            'division_abbreviation' => $proc->division?->abbreviation,
            'abc' => $proc->abc,
        ])->toArray();
        session()->flash('selected_procurements', $selectedData);
        $this->close();

        // We need to pass the type back to the create route
        return redirect()->route('mode-of-procurement.create', ['type' => $this->procurementType]);
    }

    public function toggle(int $id)
    {
        // This remains for your expandable row functionality
    }

    public function render()
    {
        // Paginator for "Available Items"
        $procurementsQuery = Procurement::query()
            // ✅ 2. Add this "where" clause to filter by the selected type
            ->where('procurement_type', $this->procurementType)
            ->when(
                $this->search,
                fn($q) =>
                $q->where('pr_number', 'like', "%{$this->search}%")
                    ->orWhere('procurement_program_project', 'like', "%{$this->search}%")
            );

        // Manually create a paginator for "Selected Items"
        $totalSelected = count($this->selectedProcurements);
        $selectedPage = $this->getPage('selectedPage');
        $selectedIdsForCurrentPage = array_slice($this->selectedProcurements, ($selectedPage - 1) * $this->perPageSelected, $this->perPageSelected);

        $selectedItems = count($selectedIdsForCurrentPage) > 0
            ? Procurement::whereIn('id', $selectedIdsForCurrentPage)->get()->sortBy(fn($model) => array_search($model->id, $selectedIdsForCurrentPage))
            : collect();

        $selectedProcurementsForTable = new LengthAwarePaginator($selectedItems, $totalSelected, $this->perPageSelected, $selectedPage, ['pageName' => 'selectedPage']);

        return view('livewire.mode-of-procurement.mode-proc-select-modal', [
            'procurements' => $procurementsQuery->latest()->paginate($this->perPage, ['*'], 'modalPage'),
            'selectedProcurementsForTable' => $selectedProcurementsForTable,
            'totalSelectedCount' => $totalSelected,
        ]);
    }
}
