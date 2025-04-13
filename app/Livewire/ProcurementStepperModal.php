<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Procurement;

class ProcurementStepperModal extends Component
{
    protected $listeners = ['openProcurementStepperModal' => 'openModal'];

    public $isOpen = false;
    public $step = 1;

    public $pr_number;
    public $procurement_program_project;
    public $date_receipt_advance;
    public $date_receipt_signed;
    public $rbac_sbac = 'RBAC';
    public $dtrack_no;
    public $unicode;
    public $divisions_id;
    public $cluster_committees_id;
    public $category_id;
    public $venue_specific_id;
    public $venue_province_huc_id;
    public $category_venue_id;
    public $approved_ppmp;
    public $app_updated;
    public $immediate_date_needed;
    public $date_needed;
    public $end_users_id;
    public $early_procurement = false;
    public $fund_source_id;
    public $expense_class;
    public $abc;
    public $abc_50k;
    public $mode_of_procurement_id = 1;
    public $ib_number;
    public $pre_proc_conference;
    public $ads_post_ib;
    public $pre_bid_conf;
    public $eligibility_check;
    public $sub_open_bids;
    public $bids = [];

    protected $rules = [
        'pr_number' => 'required|max:12',
        'procurement_program_project' => 'required|max:255',
        'rbac_sbac' => 'required',
        'abc' => 'required|numeric|min:0',
        'divisions_id' => 'required',
        'cluster_committees_id' => 'required',
        'category_id' => 'required',
    ];

    public function openModal()
    {
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
    }

    public function nextStep()
    {
        $this->validate();
        if ($this->step < 2) {
            $this->step++;
        }
    }

    public function prevStep()
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    public function save()
    {
        $this->validate();

        Procurement::create([
            'pr_number' => $this->pr_number,
            'procurement_program_project' => $this->procurement_program_project,
            'date_receipt_advance' => $this->date_receipt_advance,
            'date_receipt_signed' => $this->date_receipt_signed,
            'rbac_sbac' => $this->rbac_sbac,
            'dtrack_no' => $this->dtrack_no,
            'unicode' => $this->unicode,
            'divisions_id' => $this->divisions_id,
            'cluster_committees_id' => $this->cluster_committees_id,
            'category_id' => $this->category_id,
            'venue_specific_id' => $this->venue_specific_id,
            'venue_province_huc_id' => $this->venue_province_huc_id,
            'category_venue_id' => $this->category_venue_id,
            'approved_ppmp' => $this->approved_ppmp,
            'app_updated' => $this->app_updated,
            'immediate_date_needed' => $this->immediate_date_needed,
            'date_needed' => $this->date_needed,
            'end_users_id' => $this->end_users_id,
            'early_procurement' => $this->early_procurement,
            'fund_source_id' => $this->fund_source_id,
            'expense_class' => $this->expense_class,
            'abc' => $this->abc,
            'abc_50k' => $this->abc_50k,
            'mode_of_procurement_id' => $this->mode_of_procurement_id,
            'ib_number' => $this->ib_number,
            'pre_proc_conference' => $this->pre_proc_conference,
            'ads_post_ib' => $this->ads_post_ib,
            'pre_bid_conf' => $this->pre_bid_conf,
            'eligibility_check' => $this->eligibility_check,
            'sub_open_bids' => $this->sub_open_bids,
        ]);

        $this->closeModal();
        session()->flash('message', 'Procurement saved successfully!');
    }

    public function render()
    {
        return view('livewire.procurement-stepper-modal');
    }
}
