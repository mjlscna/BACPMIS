<?php

namespace App\Livewire\ModeOfProcurement;

use Livewire\Component;
use App\Models\Procurement;

class ModeOfProcurementCreatePage extends Component
{
    public $procurement = null;
    public $expandedProcurementId = null;
    public $form = [];

    protected $listeners = ['procurement-selected' => 'onProcurementSelected'];

    public function mount()
    {
        if (session()->has('selected_procurement')) {
            $this->procurement = (object) session()->get('selected_procurement');
            $this->form['pr_number'] = $this->procurement->pr_number;
            $this->form['procurement_program_project'] = $this->procurement->procurement_program_project;
            session()->forget('selected_procurement');
        }
    }

    public function onProcurementSelected($procurementData)
    {
        session()->flash('selected_procurement', $procurementData);
    }

    public function render()
    {
        return view('livewire.mode-of-procurement.mode-of-procurement-create-page');
    }
}
