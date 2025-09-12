<?php

namespace App\Livewire;

use App\Models\Procurement;
use Livewire\Component;
use Livewire\WithPagination;

class HomePage extends Component
{
    use WithPagination;

    public $search = '';

    public function render()
    {
        $procurements = Procurement::with([
            'division',
            'prLotPrstages',
            'prItemPrstages',
            'procurementStage',
        ])
            ->when($this->search, function ($query) {
                $query->where('pr_number', 'like', "%{$this->search}%")
                    ->orWhere('procurement_program_project', 'like', "%{$this->search}%");
            })
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('livewire.home-page', [
            'procurements' => $procurements,
        ]);
    }
}
