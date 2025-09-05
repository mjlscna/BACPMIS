<?php

namespace App\Livewire;

use App\Models\Procurement;
use Livewire\Component;
use Livewire\WithPagination;

class PostPage extends Component
{
    use WithPagination;

    public $perPage = 10;
    public function render()
    {
        $query = Procurement::query()->latest();

        return view('livewire.post-page', [
            'procurements' => $query->paginate($this->perPage),
        ]);
    }
}
