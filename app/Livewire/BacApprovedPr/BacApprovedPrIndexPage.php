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

    public function sortBy(string $field): void
    {
        // If the same field is clicked, reverse the direction; otherwise, reset to ascending
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }

        $this->sortField = $field;
        $this->resetPage();
    }


    public function viewPdf(string $filepath): void
    {
        $url = asset('storage/' . $filepath);
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
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return view('livewire.bac-approved-pr.bac-approved-pr-index-page', [
            'approvedPrs' => $approvedPrs,
        ]);
    }
}
