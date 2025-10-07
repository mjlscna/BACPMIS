<?php

namespace App\Livewire\BacApprovedPr;

use App\Models\BACApprovedPR;
use App\Models\Procurement;
use Livewire\Component;
use Livewire\WithFileUploads;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class BacApprovedPrEditPage extends Component
{
    use WithFileUploads;

    public BACApprovedPR $bacapprovedpr; // The record being edited
    public $form = [];
    public $document_file; // For a new file upload
    public $procurements = []; // To populate the dropdown options

    public function mount(BACApprovedPR $bacapprovedpr)
    {
        $this->bacapprovedpr = $bacapprovedpr;

        // Find the related procurement record
        $procurement = Procurement::where('procID', $bacapprovedpr->procID)->first();

        // Populate the form with existing data
        if ($procurement) {
            $this->form['pr_number'] = $procurement->id; // The ID for the select dropdown
            $this->form['procurement_program_project'] = $procurement->procurement_program_project;
        }
        $this->form['remarks'] = $bacapprovedpr->remarks;

        // Load all procurements so the dropdown can display the correct one
        $this->procurements = Procurement::orderBy('pr_number', 'desc')->get();
    }

    public function update()
    {
        $rules = [
            'form.pr_number' => 'required|exists:procurements,id',
            'form.procurement_program_project' => 'required|string',
            'form.remarks' => 'nullable|string',
            'document_file' => 'nullable|file|mimes:pdf|max:10240', // File is now optional
        ];
        $this->validate($rules);

        // Check if a new file has been uploaded
        if ($this->document_file) {
            // If yes, store the new file and get its path
            $extension = $this->document_file->getClientOriginalExtension();
            $filename = $this->bacapprovedpr->procID . '.' . $extension;
            $this->bacapprovedpr->filepath = $this->document_file->storeAs('bac_approved_prs_docs', $filename, 'public');
        }

        // Update the remarks
        $this->bacapprovedpr->remarks = $this->form['remarks'];
        $this->bacapprovedpr->save();

        LivewireAlert::success('Updated!', 'The record has been updated successfully.')->show();
        return $this->redirectRoute('bac-approved-pr.index', navigate: true);
    }
    // In your BacApprovedPrEditPage.php

    public function viewPdf()
    {
        // Make sure the record and filepath exist
        if ($this->bacapprovedpr && $this->bacapprovedpr->filepath) {
            $url = asset('storage/' . $this->bacapprovedpr->filepath);
            $this->dispatch('show-pdf-modal', url: $url);
        }
    }
    public function render()
    {
        return view('livewire.bac-approved-pr.bac-approved-pr-edit-page', [
            'bacapprovedpr' => $this->bacapprovedpr,
        ]);
    }
}
