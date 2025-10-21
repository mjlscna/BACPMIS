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
    public function mount()
    {
        if (session('alert')) {
            $alert = session('alert');

            LivewireAlert::title($alert['title'])
                ->{$alert['type']}() // dynamic call: success(), error(), etc.
                    ->text($alert['message'])
                    ->toast()
                    ->position('top-end')
                    ->show();
        }
    }


    public function viewPdf(string $url): void
    {
        $this->dispatch('show-pdf-modal', url: $url);
    }
    public function render()
    {
        $approvedPrs = BACApprovedPR::with('procurement') // Eager load the relationship
            ->whereHas('procurement', function ($query) {
                // Search on the related procurement's PR number or project name
                $query->where('pr_number', 'like', '%' . $this->search . '%')
                    ->orWhere('procurement_program_project', 'like', '%' . $this->search . '%');
            })
            ->latest('created_at')
            ->paginate(10);

        return view('livewire.bac-approved-pr.bac-approved-pr-index-page', [
            'approvedPrs' => $approvedPrs,
        ]);
    }
}
