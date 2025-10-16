<?php

namespace App\Livewire\ScheduleForPr;

use App\Models\ScheduleForProcurement;
use Livewire\Component;
use Livewire\WithPagination;

class ScheduleForPrIndexPage extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;

    // Use the default tailwind theme for pagination
    protected $paginationTheme = 'tailwind';
    public function updatingSearch()
    {
        $this->resetPage();
    }
    public function render()
    {
        $schedules = ScheduleForProcurement::with('biddingStatus') // Eager load the status relationship
            ->when($this->search, function ($query) {
                $query->where('ib_number', 'like', "%{$this->search}%")
                    ->orWhere('project_name', 'like', "%{$this->search}%");
            })
            ->latest('opening_of_bids') // Order by the most recent opening date
            ->paginate(10);

        return view('livewire.schedule-for-pr.schedule-for-pr-index-page', [
            'schedules' => $schedules,
        ]);
    }
}
