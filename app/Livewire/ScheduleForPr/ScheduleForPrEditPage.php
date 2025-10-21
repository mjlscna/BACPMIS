<?php

namespace App\Livewire\ScheduleForPr;

use App\Models\BiddingStatus;
use App\Models\Procurement;
use App\Models\PrItem;
use App\Models\ScheduleForProcurement;
use App\Models\ScheduleForProcurementItems;
use Illuminate\Pagination\LengthAwarePaginator; // <-- ADDED
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Component;
use Livewire\WithPagination; // <-- ADDED

class ScheduleForPrEditPage extends Component
{
    use WithPagination; // <-- ADDED

    public ?ScheduleForProcurement $schedule = null;
    public string $procurementType = '';
    public array $form = [];

    public array $selectedProcurements = [];

    // --- Pagination Properties ---
    public int $perPage = 10;
    public $selectedPRPage = 1;

    // --- Readonly Totals (as per view) ---
    public float $totalAbc = 0;
    public string $totalAbcFormatted = '₱0.00';
    public string $twoPercent = '₱0.00';
    public string $fivePercent = '₱0.00';

    protected $listeners = ['procurementsSelected'];

    public function mount(int $id)
    {
        $this->schedule = ScheduleForProcurement::findOrFail($id);

        // Ensure first item exists before trying to access it
        $firstItem = $this->schedule->items()->first();
        $this->procurementType = $firstItem ? $firstItem->itemable_type : 'perLot';


        // --- Prefill form data ---
        $this->form = [
            'ib_number' => $this->schedule->ib_number,
            'opening_of_bids' => optional($this->schedule->opening_of_bids)->format('Y-m-d'),
            'project_name' => $this->schedule->project_name,
            'is_framework' => (bool) $this->schedule->is_framework,
            'status_id' => $this->schedule->status_id,
            'action_taken' => $this->schedule->action_taken,
            'next_bidding_schedule' => optional($this->schedule->next_bidding_schedule)->format('Y-m-d'),
            'filepath' => $this->schedule->google_drive_link,
        ];

        // --- Load selected procurements/items ---
        $this->loadSelectedProcurements();

        // --- Compute totals ---
        $this->calculateTotals();
    }

    protected function loadSelectedProcurements(): void
    {
        $this->selectedProcurements = [];

        foreach ($this->schedule->items as $link) {
            if ($this->procurementType === 'perLot') {
                $proc = Procurement::where('procID', $link->itemable_UID)->first();
                if ($proc) {
                    $this->selectedProcurements[] = [
                        'id' => $proc->id, // procurement.id
                        'procID' => $proc->procID,
                        'pr_number' => $proc->pr_number,
                        'procurement_program_project' => $proc->procurement_program_project,
                        'abc' => $proc->abc,
                        'description' => $proc->procurement_program_project, // for unified table
                        'amount' => $proc->abc, // for unified table
                    ];
                }
            } else {
                $item = PrItem::where('prItemID', $link->itemable_UID)->first();
                if ($item) {
                    $proc = $item->procurement;
                    // Find if this item's parent PR is already in the array
                    $existingIndex = collect($this->selectedProcurements)->search(fn($p) => $p['id'] === $proc->id);

                    if ($existingIndex === false) {
                        // Not found, add the parent PR group
                        $this->selectedProcurements[] = [
                            'id' => $proc->id, // procurement.id
                            'pr_number' => $proc->pr_number,
                            'items' => [],
                        ];
                        $existingIndex = count($this->selectedProcurements) - 1; // Get index of the one just added
                    }

                    // Add the item to its parent PR's 'items' array
                    $this->selectedProcurements[$existingIndex]['items'][] = [
                        'id' => $item->id, // pr_item.id
                        'prItemID' => $item->prItemID,
                        'description' => $item->description,
                        'amount' => $item->amount,
                    ];
                }
            }
        }
    }

    /**
     * Updated calculateTotals to use cleaner flatMap logic.
     * This updates the public properties for the readonly inputs.
     */
    public function calculateTotals(): void
    {
        $this->totalAbc = collect($this->selectedProcurements)
            ->flatMap(fn($proc) => !empty($proc['items']) ? $proc['items'] : [$proc])
            ->sum(fn($entry) => floatval($entry['amount'] ?? $entry['abc'] ?? 0));

        // Update public properties, not the $form array, as per edit view
        $this->totalAbcFormatted = '₱' . number_format($this->totalAbc, 2);
        $this->twoPercent = '₱' . number_format($this->totalAbc * 0.02, 2);
        $this->fivePercent = '₱' . number_format($this->totalAbc * 0.05, 2);
    }

    public function openSelectionModal()
    {
        session(['form_state' => $this->form]);

        $existingLotIds = collect($this->selectedProcurements)
            ->filter(fn($proc) => empty($proc['items']))
            ->pluck('id') // procurement.id
            ->toArray();

        $existingItemIds = collect($this->selectedProcurements)
            ->filter(fn($proc) => !empty($proc['items']))
            ->flatMap(fn($proc) => collect($proc['items'])->pluck('id')) // pr_item.id
            ->toArray();

        // Pass the current selections to the modal
        $this->dispatch('open-mode-modal', existingLotIds: $existingLotIds, existingItemIds: $existingItemIds);
    }

    public function procurementsSelected(array $selectedData): void
    {
        $this->selectedProcurements = $selectedData;
        $this->calculateTotals();
        $this->selectedPRPage = 1; // Reset to first page
    }

    // --- NEW PAGINATION METHODS (from Create Page) ---

    /**
     * Computed property to create a paginated, FLAT collection for the view.
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

    // --- NEW UNIFIED REMOVAL METHOD ---

    /**
     * Removes an item or a lot from the $selectedProcurements array
     * based on its unique key ('lot_ID' or 'item_ID').
     */
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
            'ib_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('schedule_for_pr', 'ib_number')->ignore($this->schedule->id, 'id'),
            ],
            'opening_of_bids' => 'required|date',
            'project_name' => 'required|string|max:1000',
            'is_framework' => 'required|boolean',
            'status_id' => 'nullable|integer|exists:bidding_status,id',
            'action_taken' => 'nullable|string|max:50',
            'next_bidding_schedule' => 'nullable|date',
            'filepath' => 'required|url',
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

        // --- 2. Save transaction ---
        DB::transaction(function () {
            // Update main record
            $this->schedule->update([
                'ib_number' => $this->form['ib_number'],
                'opening_of_bids' => $this->form['opening_of_bids'],
                'project_name' => $this->form['project_name'],
                'is_framework' => $this->form['is_framework'],
                'status_id' => $this->form['status_id'] ?? null,
                'action_taken' => $this->form['action_taken'] ?? null,
                'next_bidding_schedule' => $this->form['next_bidding_schedule'] ?? null,
                'google_drive_link' => $this->form['filepath'],
                'ABC' => $this->totalAbc,
                'two_percent' => $this->totalAbc * 0.02,
                'five_percent' => $this->totalAbc * 0.05,
            ]);

            // Clear old links and recreate
            $this->schedule->items()->delete();

            // This logic is correct as it handles the grouped $selectedProcurements
            foreach ($this->selectedProcurements as $proc) {
                if (empty($proc['items'])) { // This is a 'perLot' procurement
                    ScheduleForProcurementItems::create([
                        'schedule_for_procurement_id' => $this->schedule->id,
                        'itemable_UID' => $proc['procID'],
                        'itemable_type' => $this->procurementType,
                    ]);
                } else { // This is a 'perItem' procurement
                    foreach ($proc['items'] as $item) {
                        ScheduleForProcurementItems::create([
                            'schedule_for_procurement_id' => $this->schedule->id,
                            'itemable_UID' => $item['prItemID'],
                            'itemable_type' => $this->procurementType,
                        ]);
                    }
                }
            }
        });

        // --- Clear persisted session state (same as create page) ---
        session()->forget(['selected_procurements', 'form_state']);

        // --- Flash success alert and redirect ---
        session()->flash('alert', [
            'type' => 'success',
            'title' => 'Updated!',
            'message' => 'The schedule has been updated successfully.',
        ]);

        return redirect()->route('schedule-for-procurement.index');
    }

    public function render()
    {
        // Get existing IDs for the modal
        $existingLotIds = collect($this->selectedProcurements)
            ->filter(fn($proc) => empty($proc['items']))
            ->pluck('id') // procurement.id
            ->toArray();

        $existingItemIds = collect($this->selectedProcurements)
            ->filter(fn($proc) => !empty($proc['items']))
            ->flatMap(fn($proc) => collect($proc['items'])->pluck('id')) // pr_item.id
            ->toArray();

        // --- REMOVED old property assignments ---
        // $this->selectedLots = ...
        // $this->selectedItemGroups = ...

        $ActionTakenOptions = [
            ['id' => 'Done', 'name' => 'Done'],
            ['id' => 'Rebid', 'name' => 'Rebid'],
            ['id' => 'Cancelled', 'name' => 'Cancelled'],
        ];

        return view('livewire.schedule-for-pr.schedule-for-pr-edit-page', [
            'ActionTakenOptions' => $ActionTakenOptions,
            'biddingStatus' => BiddingStatus::all(),
            'existingLotIds' => $existingLotIds,
            'existingItemIds' => $existingItemIds,
        ]);
    }
}
