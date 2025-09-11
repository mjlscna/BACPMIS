<?php

namespace App\Livewire\ModeOfProcurement;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Procurement;

class ModeProcSelectModal extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    public $showModal = false;
    public $search = '';
    public $perPage = 5;
    public $selectedProcurement = null;

    protected $queryString = ['search'];
    protected $listeners = ['open-mode-modal' => 'open'];

    public function updatingSearch()
    {
        $this->resetPage('modalPage');
    }

    public function open()
    {
        $this->search = '';
        $this->resetPage('modalPage');
        $this->showModal = true;
    }

    public function close()
    {
        $this->search = '';
        $this->showModal = false;
    }

    public function selectProcurement()
    {
        if ($this->selectedProcurement) {
            $procurement = Procurement::findOrFail($this->selectedProcurement);
            session()->flash('selected_procurement', [
                'id' => $procurement->id,
                'procID' => $procurement->procID,
                'pr_number' => $procurement->pr_number,
                'procurement_program_project' => $procurement->procurement_program_project
            ]);
            $this->close();
            return redirect()->route('mode-of-procurement.create');
        }
    }

    public function render()
    {
        $procurements = Procurement::latest()
            ->when(
                $this->search,
                fn($q) =>
                $q->where('pr_number', 'like', "%{$this->search}%")
                    ->orWhere('procurement_program_project', 'like', "%{$this->search}%")
            )
            ->paginate($this->perPage, ['*'], 'modalPage');

        return view('livewire.mode-of-procurement.mode-proc-select-modal', [
            'procurements' => $procurements,
        ]);
    }
}
