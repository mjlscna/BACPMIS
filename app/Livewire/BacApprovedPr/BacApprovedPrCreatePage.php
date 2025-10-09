<?php

namespace App\Livewire\BacApprovedPr;

use App\Models\BacApprovedPr;
use App\Models\Procurement;
use Illuminate\Support\Facades\Validator;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Component;
// use Livewire\WithFileUploads; // REMOVED: No longer needed

class BacApprovedPrCreatePage extends Component
{
    // use WithFileUploads; // REMOVED

    public $form = [];
    public $textareaRows = 1;
    // public $document_file; // REMOVED
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
            'filepath' => '', // ADDED: To hold the URL
        ];
    }

    private function resetForm(): void
    {
        $this->form = $this->defaultForm();
        // $this->document_file = null; // REMOVED
        $this->resetErrorBag();
    }

    public function save()
    {
        // 1. Define validation rules
        $rules = [
            'form.pr_number' => 'required',
            'form.filepath' => 'required|url|max:255', // CHANGED: Validate a URL string
            'form.remarks' => 'nullable|string',
        ];
        $attributes = [
            'form.pr_number' => 'PR Number',
            'form.filepath' => 'Approved PR Document URL', // CHANGED: Attribute name
        ];

        // 2. Validate the data
        $this->validate($rules, [], $attributes); // SIMPLIFIED: Livewire can handle this directly

        // 3. Check for duplicates
        $isAlreadySaved = BacApprovedPr::where('procID', $this->procID)->exists();
        if ($isAlreadySaved) {
            LivewireAlert::title('Error!')
                ->error()
                ->text('This PR has already been saved and cannot be added again.')
                ->toast()
                ->position('top-end')
                ->show();
            $this->addError('form.pr_number', 'This PR has already been recorded.');
            return;
        }

        // 4. Create the record in the database
        BacApprovedPr::create([
            'procID' => $this->procID,
            'filepath' => $this->form['filepath'], // CHANGED: Save the URL directly
            'remarks' => $this->form['remarks'],
        ]);

        // 5. Show success message
        LivewireAlert::title('Saved!')
            ->success()
            ->text('BAC Approved PR has been saved successfully.')
            ->toast()
            ->position('top-end')
            ->show();

        // 6. Optional: Redirect or reset form
        // return $this->redirect('/path-to-list', navigate: true);
        $this->resetForm();
    }

    public function updatedFormPrNumber($value)
    {
        $procurement = Procurement::find($value);

        if ($procurement) {
            $this->form['procurement_program_project'] = $procurement->procurement_program_project;
            $this->procID = $procurement->procID;

            $text = trim($procurement->procurement_program_project ?? '');
            $lineCount = substr_count($text, "\n") + 1;
            $approxExtraLines = ceil(strlen($text) / 150);
            $this->textareaRows = max($lineCount, $approxExtraLines, 1);
        } else {
            $this->form['procurement_program_project'] = '';
            $this->procID = null;
            $this->textareaRows = 1;
        }
    }

    public function render()
    {
        return view('livewire.bac-approved-pr.bac-approved-pr-create-page', [
            'procurements' => Procurement::orderBy('pr_number', 'desc')->get(),
        ]);
    }
}
