<?php

namespace App\Livewire\BacApprovedPr;

use App\Models\BACApprovedPR;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Component;
use Livewire\WithPagination;

class BacApprovedPrIndexPage extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';
    public int $perPage = 10; // 🆕 Default per-page value

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
    ];

    public function mount()
    {
        if (session('alert')) {
            $alert = session('alert');

            LivewireAlert::title($alert['title'])
                ->{$alert['type']}()
                    ->text($alert['message'])
                    ->toast()
                    ->position('top-end')
                    ->show();
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function viewPdf(string $url): void
    {
        $this->dispatch('show-pdf-modal', url: $url);
    }

    public function render()
    {
        $approvedPrs = BACApprovedPR::with('procurement')
            ->whereHas('procurement', function ($query) {
                $query->where('pr_number', 'like', '%' . $this->search . '%')
                    ->orWhere('procurement_program_project', 'like', '%' . $this->search . '%');
            })
            ->latest($this->sortField)
            ->paginate($this->perPage);

        return view('livewire.bac-approved-pr.bac-approved-pr-index-page', [
            'approvedPrs' => $approvedPrs,
        ]);
    }
}
