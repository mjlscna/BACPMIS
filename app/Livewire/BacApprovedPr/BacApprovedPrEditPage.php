<?php

namespace App\Livewire\BacApprovedPr;

use App\Models\BACApprovedPR;
use App\Models\Procurement;
// REMOVED: use Illuminate\Support\Facades\Storage;
use Livewire\Component;
// REMOVED: use Livewire\WithFileUploads;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class BacApprovedPrEditPage extends Component
{
    // REMOVED: use WithFileUploads;

    public BACApprovedPR $bacapprovedpr;
    public $form = [];
    public $textareaRows = 1;
    // REMOVED: public $document_file;
    public $procurements = [];

    public function mount(BACApprovedPR $bacapprovedpr)
    {
        $this->bacapprovedpr = $bacapprovedpr;
        $procurement = Procurement::where('procID', $bacapprovedpr->procID)->first();

        if ($procurement) {
            $this->form['pr_number'] = $procurement->pr_number;
            $this->form['procurement_program_project'] = $procurement->procurement_program_project;
        }

        $this->form['remarks'] = $bacapprovedpr->remarks;
        $this->form['filepath'] = $bacapprovedpr->filepath; // ADDED: Load the URL into the form

        $this->procurements = Procurement::orderBy('pr_number', 'desc')->get();
    }

    public function updatedFormPrNumber($value)
    {
        $procurement = Procurement::find($value);
        if ($procurement) {
            $this->form['procurement_program_project'] = $procurement->procurement_program_project;
            // Removed procID update logic as it shouldn't change on edit
            $text = trim($procurement->procurement_program_project ?? '');
            $lineCount = substr_count($text, "\n") + 1;
            $approxExtraLines = ceil(strlen($text) / 150);
            $this->textareaRows = max($lineCount, $approxExtraLines, 1);
        } else {
            $this->form['procurement_program_project'] = '';
            $this->textareaRows = 1;
        }
    }

    public function save()
    {
        $rules = [
            'form.filepath' => 'required|url|max:255', // CHANGED: Validate a URL
            'form.remarks' => 'nullable|string',
        ];

        $attributes = [
            'form.filepath' => 'Approved PR Document URL', // CHANGED
        ];

        $this->validate($rules, [], $attributes);


        $this->bacapprovedpr->filepath = $this->form['filepath']; // CHANGED
        $this->bacapprovedpr->remarks = $this->form['remarks'];

        $this->bacapprovedpr->save();

        LivewireAlert::title('Updated!')
            ->success()
            ->text('BAC Approved PR has been updated successfully.')
            ->toast()
            ->position('top-end')
            ->show();
    }

    public function viewPdf()
    {
        $url = $this->form['filepath'] ?? null;

        if (!$url) {
            // Handle case where URL might be empty
            LivewireAlert::error('No document URL found.')->toast()->show();
            return;
        }

        // Check if the URL is an external link
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            // Use the proxy route for external URLs
            $proxiedUrl = route('pdf.proxy', ['url' => $url]);
            $this->dispatch('show-pdf-modal', url: $proxiedUrl);
        } else {
            // Use the asset helper for old, locally stored files
            $localUrl = asset('storage/' . $url);
            $this->dispatch('show-pdf-modal', url: $localUrl);
        }
    }

    public function render()
    {
        return view('livewire.bac-approved-pr.bac-approved-pr-edit-page');
    }
}
