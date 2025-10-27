<?php

namespace App\Livewire\ScheduleForPr;

use App\Models\BiddingStatus;
use App\Models\Procurement;
use App\Models\ScheduleForProcurement;
use App\Models\ScheduleForProcurementItems;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Component;
use Livewire\WithPagination;

class ScheduleForPrCreatePage extends Component
{
    use WithPagination;
    public $procurement = null;
    public int $perPage = 10;
    public $selectedPRPage = 1;

    public string $procurementType = '';

    public array $selectedProcurements = [];

    public $form = [
        'totalAbcFormatted' => '₱0.00',
        'twoPercent' => '₱0.00',
        'fivePercent' => '₱0.00',
        'thirtyPercent' => '₱0.00',
    ];

    // Computed values
    public float $totalAbc = 0;
    protected $listeners = ['procurementsSelected'];

    public function mount($procID = null)
    {
        session()->forget(['selected_procurements', 'form_state']);

        $this->resetForm();
        $this->procurementType = request()->query('type', 'perLot');

        if (session()->has('selected_procurements')) {
            $this->selectedProcurements = session('selected_procurements');
            $this->form['procurement_ids'] = array_column($this->selectedProcurements, 'id');
        }

        if (session()->has('form_state')) {
            $this->form = session('form_state', []); // Restore the form data
            session()->forget('form_state'); // Clean up the session
        }

        $this->procID = $procID ?? $this->form['procurement_ids'][0] ?? null;

        if ($this->procID) {
            $this->procurement = Procurement::where('procID', $this->procID)->first();

            if ($this->procurement) {
                $this->form['pr_number'] = $this->procurement->pr_number;
                $this->form['procurement_program_project'] = $this->procurement->procurement_program_project;
                $this->hydrateForm();
            }
        }

        // Initialize totals
        $this->calculateTotals();
    }
    private function persistFormState(): void
    {
        session([
            'form_state' => $this->form,
            'selected_procurements' => $this->selectedProcurements,
        ]);
    }
    public function openSelectionModal()
    {
        $this->persistFormState();
        session(['form_state' => $this->form]);

        $existingLotIds = collect($this->selectedProcurements)
            ->filter(fn($proc) => empty($proc['items']))
            ->pluck('id')
            ->toArray();

        $existingItemIds = collect($this->selectedProcurements)
            ->filter(fn($proc) => !empty($proc['items']))
            ->flatMap(fn($proc) => collect($proc['items'])->pluck('id'))
            ->toArray();

        // Pass the current selections to the modal
        $this->dispatch('open-mode-modal', existingLotIds: $existingLotIds, existingItemIds: $existingItemIds);
    }

    public function procurementsSelected(array $selectedData): void
    {
        // Replace the component's current selections with the new ones from the modal
        $this->selectedProcurements = $selectedData;

        // Recalculate the totals based on the new selection
        $this->calculateTotals();
    }


    public function onProcurementSelected(array $selections): void
    {
        $this->selectedProcurements = $selections;
        $this->calculateTotals();
    }

    public function hydrateForm()
    {
        if (!$this->procurement) {
            return;
        }
    }

    public function calculateTotals(): void
    {
        $this->totalAbc = collect($this->selectedProcurements)
            ->flatMap(fn($proc) => !empty($proc['items']) ? $proc['items'] : [$proc])
            ->sum(fn($entry) => floatval($entry['amount'] ?? $entry['abc'] ?? 0));

        // Update the values inside the $form array
        $this->form['totalAbcFormatted'] = '₱ ' . number_format($this->totalAbc, 2);
        $this->form['twoPercent'] = '₱ ' . number_format($this->totalAbc * 0.02, 2);
        $this->form['fivePercent'] = '₱ ' . number_format($this->totalAbc * 0.05, 2);
        $this->form['thirtyPercent'] = '₱ ' . number_format($this->totalAbc * 0.30, 2);
    }

    // --- NEW/UPDATED PAGINATION METHODS ---

    /**
     * Use the new helper to create the paginated computed property.
     */
    public function getSelectedPRProperty()
    {
        $items = collect($this->selectedProcurements)
            ->flatMap(function ($proc) {
                if (!empty($proc['items'])) {
                    // 'perItem': return each item, adding parent pr_number and unique keys
                    return collect($proc['items'])->map(function ($item) use ($proc) {
                        $item['pr_number'] = $proc['pr_number'];
                        $item['is_item'] = true;
                        // Use item 'id' (pr_item.id)
                        $item['unique_key'] = 'item_' . $item['id'];
                        return $item;
                    });
                } else {
                    // 'perLot': return the proc itself, adding unique key
                    $proc['is_item'] = false;
                    // Use proc 'id' (procurement.id)
                    $proc['unique_key'] = 'lot_' . $proc['id'];
                    return [$proc]; // Must be wrapped in array for flatMap
                }
            });

        return $this->paginateCollection($items, $this->perPage, 'selectedPRPage');
    }

    public function removeSelectedPR(string $uniqueKey): void
    {
        [$type, $id] = explode('_', $uniqueKey);
        $id = (int) $id;

        if ($type === 'lot') {
            // Remove a 'perLot' procurement
            $this->selectedProcurements = collect($this->selectedProcurements)
                ->filter(function ($proc) use ($id) {
                    // Keep if it's a 'perItem' group OR if it's a 'perLot' and ID doesn't match
                    return !empty($proc['items']) || (empty($proc['items']) && $proc['id'] !== $id);
                })
                ->values()
                ->all();
        } else { // type === 'item'
            // Remove a 'perItem' item from its group
            foreach ($this->selectedProcurements as $procIndex => &$proc) {
                if (!empty($proc['items'])) {
                    // Filter out the item with the matching ID
                    $proc['items'] = collect($proc['items'])
                        ->filter(fn($item) => $item['id'] !== $id)
                        ->values()
                        ->all();

                    // If the 'items' array is now empty, remove the parent group
                    if (empty($proc['items'])) {
                        unset($this->selectedProcurements[$procIndex]);
                    }
                }
            }
            // Re-index the main array
            $this->selectedProcurements = array_values($this->selectedProcurements);
        }

        $this->calculateTotals();

        // After removal, check if the current page is now empty and go back
        if ($this->SelectedPR->isEmpty() && $this->selectedPRPage > 1) {
            $this->selectedPRPage--;
        }
    }
    /**
     * Reusable helper to paginate a collection.
     */
    private function paginateCollection($collection, $perPage, $pageName)
    {
        $page = $this->$pageName ?? 1;
        return new LengthAwarePaginator(
            $collection->forPage($page, $perPage),
            $collection->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'pageName' => $pageName]
        );
    }

    /**
     * Generic "next page" method.
     */
    public function nextCustomPage(string $pageName)
    {
        if (property_exists($this, $pageName)) {
            $this->$pageName++;
        }
    }

    /**
     * Generic "previous page" method.
     */
    public function previousCustomPage(string $pageName)
    {
        if (property_exists($this, $pageName) && $this->$pageName > 1) {
            $this->$pageName--;
        }
    }
    // --- END PAGINATION METHODS ---


    public function save()
    {
        // --- 1. Validation ---
        if (empty($this->selectedProcurements)) {
            LivewireAlert::title('ERROR!')
                ->error()
                ->text('Please select at least one PR Lot or Item.')
                ->toast()
                ->position('top-end')
                ->show();
            return;
        }

        $this->form['is_framework'] = (bool) ($this->form['is_framework'] ?? false);

        $validator = Validator::make($this->form, [
            'ib_number' => 'required|string|max:255|unique:schedule_for_pr,ib_number',
            'opening_of_bids' => 'required|date',
            'project_name' => 'required|string|max:1000',
            'is_framework' => 'required|boolean',
            'status_id' => 'nullable|integer|exists:bidding_status,id',
            'action_taken' => 'nullable|string|max:50',
            'next_bidding_schedule' => 'nullable|date',
            'filepath' => 'required|url', // Assuming form model is 'filepath' for the google drive link
        ], [], [
            'ib_number' => 'IB Number',
            'opening_of_bids' => 'Opening of Bids',
            'project_name' => 'Project Name',
            'status_id' => 'Bidding Status',
            'next_bidding_schedule' => 'Next Bidding Schedule',
            'filepath' => 'Google Drive Link',
        ]);

        if ($validator->fails()) {
            LivewireAlert::title('ERROR!')
                ->error()
                ->text(collect($validator->errors()->all())->implode("\n"))
                ->toast()
                ->position('top-end')
                ->show();
            return;
        }


        // --- 2. Data Preparation ---
        // Calculate number of items/lots
        $itemLotCount = 0;
        foreach ($this->selectedProcurements as $proc) {
            $itemLotCount += !empty($proc['items']) ? count($proc['items']) : 1;
        }

        // Calculate unique PR count
        $prCount = collect($this->selectedProcurements)->pluck('pr_number')->unique()->count();

        // Use a database transaction to ensure data integrity
        DB::transaction(function () use ($itemLotCount, $prCount) {
            // --- 3. Create Main Schedule Record ---
            $schedule = ScheduleForProcurement::create([
                'ib_number' => $this->form['ib_number'],
                'opening_of_bids' => $this->form['opening_of_bids'],
                'project_name' => $this->form['project_name'],
                'is_framework' => $this->form['is_framework'],
                'status_id' => $this->form['status_id'] ?? null,
                'action_taken' => $this->form['action_taken'] ?? null,
                'next_bidding_schedule' => $this->form['next_bidding_schedule'] ?? null,
                'google_drive_link' => $this->form['filepath'], // Map filepath to the correct db column
                'ABC' => $this->totalAbc,
                'two_percent' => $this->totalAbc * 0.02,
                'five_percent' => $this->totalAbc * 0.05,
            ]);

            // --- 4. Link Selected Lots and Items ---
            foreach ($this->selectedProcurements as $proc) {
                if (empty($proc['items'])) { // This is a 'perLot' procurement
                    ScheduleForProcurementItems::create([
                        'schedule_for_procurement_id' => $schedule->id,
                        'itemable_UID' => $proc['procID'],
                        'itemable_type' => $this->procurementType,
                    ]);
                } else { // This is a 'perItem' procurement
                    foreach ($proc['items'] as $item) {
                        ScheduleForProcurementItems::create([
                            'schedule_for_procurement_id' => $schedule->id,
                            'itemable_UID' => $item['prItemID'], // <-- Use the polymorphic ID field
                            'itemable_type' => $this->procurementType,      // <-- Specify the model type
                        ]);
                    }
                }
            }
        });

        session()->forget(['selected_procurements', 'form_state']);

        session()->flash('alert', [
            'type' => 'success',
            'title' => 'Saved!',
            'message' => 'Your schedule has been created successfully.',
        ]);

        return redirect()->route('schedule-for-procurement.index');
    } // app/Livewire/ScheduleForPr/ScheduleForPrCreatePage.php

    public function resetForm(): void
    {
        // Reset the main form array
        $this->form = [
            'totalAbcFormatted' => '₱0.00',
            'twoPercent' => '₱0.00',
            'fivePercent' => '₱0.00',
            'thirtyPercent' => '₱0.00',
        ];

        // Reset all selections
        $this->selectedProcurements = [];
        // $this->selectedLots = []; // These are derived, no need to reset
        // $this->selectedItemGroups = []; // These are derived, no need to reset

        // Recalculate totals, which will set them back to zero
        $this->calculateTotals();

        // If you use Livewire's built-in validation, you might want to reset its state too
        $this->resetValidation();
    }

    public function render()
    {
        $existingLotIds = [];
        $existingItemIds = [];

        foreach ($this->selectedProcurements as $proc) {
            if (empty($proc['items'])) {
                $existingLotIds[] = $proc['id'];
            } else {
                foreach ($proc['items'] as $item) {
                    $existingItemIds[] = $item['id'];
                }
            }
        }

        $ActionTakenOptions = [
            ['id' => 'Done', 'name' => 'Done'],
            ['id' => 'Rebid', 'name' => 'Rebid'],
            ['id' => 'Cancelled', 'name' => 'Cancelled'],
        ];

        return view('livewire.schedule-for-pr.schedule-for-pr-create-page', [
            'ActionTakenOptions' => $ActionTakenOptions,
            'biddingStatus' => BiddingStatus::all(),
            'existingLotIds' => $existingLotIds,
            'existingItemIds' => $existingItemIds,
        ]);
    }
}
