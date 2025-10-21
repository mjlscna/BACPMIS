<?php

namespace App\Livewire\ScheduleForPr;

use App\Models\ScheduleForProcurement;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Component;
use Livewire\WithPagination;

class ScheduleForPrIndexPage extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;

    // Use the default tailwind theme for pagination
    protected $paginationTheme = 'tailwind';
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
            ->latest('created_at') // Order by the most recent opening date
            ->paginate(10);

        return view('livewire.schedule-for-pr.schedule-for-pr-index-page', [
            'schedules' => $schedules,
        ]);
    }
}
