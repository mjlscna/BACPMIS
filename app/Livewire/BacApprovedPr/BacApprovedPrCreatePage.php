<?php

namespace App\Livewire\BacApprovedPr;

use App\Models\BacApprovedPr;
use App\Models\Procurement;
use Illuminate\Support\Facades\Validator;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Component;
use Livewire\WithFileUploads;

class BacApprovedPrCreatePage extends Component
{
    use WithFileUploads;

    // The form state is managed by this public array
    public $form = [];

    // File uploads are handled by a separate public property for stability
    public $document_file;
    public $procID;

    public function mount()
    {
        $this->resetForm();
    }

    private function defaultForm(): array
    {
        return [
            'pr_number' => '',
            'procurement_program_project' => '',
            'remarks' => '',
        ];
    }

    private function resetForm(): void
    {
        $this->form = $this->defaultForm();
        $this->document_file = null; // Also reset the file input
        $this->resetErrorBag();
    }

    public function save()
    {
        // 1. Define validation rules and attributes (This part is perfect)
        $rules = [
            'form.pr_number' => 'required',
            'document_file' => 'required|file|mimes:pdf|max:10240',
            'form.remarks' => 'nullable|string',
        ];
        $attributes = [
            'form.pr_number' => 'PR Number',
            'document_file' => 'Approved PR Document',
        ];

        // 2. Validate the data (This part is also perfect)
        $validator = Validator::make(['form' => $this->form, 'document_file' => $this->document_file], $rules, [], $attributes);

        if ($validator->fails()) {
            LivewireAlert::title('ERROR!')
                ->error()
                ->text(collect($validator->errors()->all())->implode("\n"))
                ->toast()
                ->position('top-end')
                ->show();
            $this->validate($rules, [], $attributes);
            return;
        }

        $isAlreadySaved = BacApprovedPr::where('procID', $this->procID)->exists();
        if ($isAlreadySaved) {
            // Show a general error toast
            LivewireAlert::title('Error!')
                ->error()
                ->text('This PR has already been saved and cannot be added again.') // <-- Use a simple message
                ->toast()
                ->position('top-end')
                ->show();

            // Add a specific error message under the PR Number field
            $this->addError('form.pr_number', 'This PR has already been recorded.');

            return; // Stop the save process
        }
        // 4. Store the uploaded file
        $extension = $this->document_file->getClientOriginalExtension();
        $filename = $this->procID . '.' . $extension;
        $filePath = $this->document_file->storeAs('bac_approved_prs_docs', $filename, 'public');

        // 5. Create the record in the database
        BacApprovedPr::create([
            'procID' => $this->procID,
            'filepath' => $filePath,
            'remarks' => $this->form['remarks'],
        ]);

        // 6. Show success message and redirect
        LivewireAlert::title('Saved!')
            ->success()
            ->text('BAC Approved PR has been saved successfully.')
            ->toast()
            ->position('top-end')
            ->show();

        return $this->redirectRoute('bac-approved-pr.index', navigate: true);
    }
    public function updatedFormPrNumber($value)
    {
        // Find the selected procurement record
        $procurement = Procurement::find($value);

        // Update the textarea's value in the form array
        if ($procurement) {
            // If a record is found, fill the textarea with its project name
            $this->form['procurement_program_project'] = $procurement->procurement_program_project;
            $this->procID = $procurement->procID;
        } else {
            // If the user selects the empty option, clear the textarea
            $this->form['procurement_program_project'] = '';
            $this->procID = null;
        }
    }

    public function render()
    {
        return view('livewire.bac-approved-pr.bac-approved-pr-create-page', [
            // Pass the procurement data to the view for the dropdown
            'procurements' => Procurement::orderBy('pr_number', 'desc')->get(),
        ]);
    }
}
