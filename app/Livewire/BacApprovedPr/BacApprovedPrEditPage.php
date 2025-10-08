<?php

namespace App\Livewire\BacApprovedPr;

use App\Models\BACApprovedPR;
use App\Models\Procurement;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class BacApprovedPrEditPage extends Component
{
    use WithFileUploads;

    public BACApprovedPR $bacapprovedpr;
    public $form = [];
    public $document_file;
    public $procurements = [];

    public function mount(BACApprovedPR $bacapprovedpr)
    {
        $this->bacapprovedpr = $bacapprovedpr;

        // Load related procurement
        $procurement = Procurement::where('procID', $bacapprovedpr->procID)->first();

        if ($procurement) {
            $this->form['pr_number'] = $procurement->id; // the selected ID
            $this->form['pr_number_display'] = $procurement->pr_number; // for display only
            $this->form['procurement_program_project'] = $procurement->procurement_program_project;
        }

        $this->form['remarks'] = $bacapprovedpr->remarks;

        // Dropdown list
        $this->procurements = Procurement::orderBy('pr_number', 'desc')->get();
    }

    public function updatedFormPrNumber($value)
    {
        // Whenever the dropdown changes
        $procurement = Procurement::find($value);

        if ($procurement) {
            $this->form['pr_number_display'] = $procurement->pr_number;
            $this->form['procurement_program_project'] = $procurement->procurement_program_project;
        } else {
            $this->form['pr_number_display'] = null;
            $this->form['procurement_program_project'] = null;
        }
    }

    public function save()
    {
        $rules = [
            'form.pr_number' => 'required|exists:procurements,id',
            'form.procurement_program_project' => 'required|string',
            'form.remarks' => 'nullable|string',
            'document_file' => 'nullable|file|mimes:pdf|max:10240', // Nullable for edits
        ];

        $attributes = [
            'form.pr_number' => 'PR Number',
            'document_file' => 'Approved PR Document',
        ];

        $this->validate($rules, [], $attributes);

        // This block handles the file replacement logic
        if ($this->document_file) {
            // 1. Get the old file path directly from the model.
            // Your create method saved it as 'bac_approved_prs_docs/filename.pdf'
            $oldFilePath = $this->bacapprovedpr->filepath;


            // 2. If an old file path exists, delete that exact file.
            if ($oldFilePath && Storage::disk('public')->exists($oldFilePath)) {
                Storage::disk('public')->delete($oldFilePath);
            }

            // 3. Store the new file and get its new path.
            $extension = $this->document_file->getClientOriginalExtension();
            $filename = $this->bacapprovedpr->procID . '.' . $extension;
            $newFilePath = $this->document_file->storeAs('bac_approved_prs_docs', $filename, 'public');

            // 4. Update the filepath on the model for saving.
            $this->bacapprovedpr->filepath = $newFilePath;
        }

        // Update other fields
        $this->bacapprovedpr->remarks = $this->form['remarks'];

        // Save all changes to the database
        $this->bacapprovedpr->save();

        // Show success message
        LivewireAlert::title('Updated!')
            ->success()
            ->text('BAC Approved PR has been saved successfully.')
            ->toast()
            ->position('top-end')
            ->show();
    }
    public function viewPdf()
    {
        if ($this->bacapprovedpr && $this->bacapprovedpr->filepath) {
            $url = asset('storage/' . $this->bacapprovedpr->filepath);
            $this->dispatch('show-pdf-modal', url: $url);
        }
    }

    public function render()
    {
        return view('livewire.bac-approved-pr.bac-approved-pr-edit-page');
    }
}
