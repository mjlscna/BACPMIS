<?php

namespace App\Livewire\Procurement;

use Livewire\Component;
use App\Models\Procurement;

class ViewPage extends Component
{
     public $showModal = false;
    public $form = [];

    protected $listeners = ['openProcurementView' => 'open'];

    public function open($id)
    {
        $procurement = Procurement::findOrFail($id);

        $this->form = $procurement->toArray();
        $this->showModal = true;
    }

    public function render()
    {
        return view('livewire.procurement.view');
    }
}
