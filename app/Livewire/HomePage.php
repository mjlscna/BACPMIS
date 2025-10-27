<?php

namespace App\Livewire;

use App\Models\Procurement;
use Livewire\Component;
use Livewire\WithPagination;

class HomePage extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    public $search = '';
    public $selectedProcurement = null;
    public $expandedProcurementId = null;
    public $form = [
        'items' => [],
    ];
    public int $perPage = 10;
    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
    ];

    public function updatingSearch()
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

    public function render()
    {
        $procurements = Procurement::with([
            'division',
            'procurementStage',
            'prLotPrstages.procurementStage',   // perLot PR stages
            'pr_items.prstage.stage',           // perItem PR stage (singular)
        ])

            ->when($this->search, function ($query) {
                $query->where('pr_number', 'like', "%{$this->search}%")
                    ->orWhere('procurement_program_project', 'like', "%{$this->search}%");
            })
            ->orderByDesc('created_at')
            ->paginate($this->perPage);

        return view('livewire.home-page', [
            'procurements' => $procurements,
        ]);
    }
}
