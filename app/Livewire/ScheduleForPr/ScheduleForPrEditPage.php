<?php

namespace App\Livewire\ScheduleForPr;

use App\Models\BiddingStatus;
use App\Models\Procurement;
use App\Models\PrItem;
use App\Models\ScheduleForProcurement;
use App\Models\ScheduleForProcurementItems;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Component;

class ScheduleForPrEditPage extends Component
{
    public ?ScheduleForProcurement $schedule = null;
    public string $procurementType = '';
    public array $form = [];

    public array $selectedProcurements = [];
    public array $selectedLots = [];
    public array $selectedItemGroups = [];

    public float $totalAbc = 0;
    public string $totalAbcFormatted = '₱0.00';
    public string $twoPercent = '₱0.00';
    public string $fivePercent = '₱0.00';

    protected $listeners = ['procurementsSelected'];

    public function mount(int $id)
    {
        $this->schedule = ScheduleForProcurement::findOrFail($id);
        $this->procurementType = $this->schedule->items()->first()->itemable_type ?? 'perLot';

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
                        'id' => $proc->id,
                        'procID' => $proc->procID,
                        'pr_number' => $proc->pr_number,
                        'procurement_program_project' => $proc->procurement_program_project,
                        'abc' => $proc->abc,
                    ];
                }
            } else {
                $item = PrItem::where('prItemID', $link->itemable_UID)->first();
                if ($item) {
                    $proc = $item->procurement;
                    $existingIndex = collect($this->selectedProcurements)->search(fn($p) => $p['id'] === $proc->id);
                    if ($existingIndex === false) {
                        $this->selectedProcurements[] = [
                            'id' => $proc->id,
                            'pr_number' => $proc->pr_number,
                            'items' => [],
                        ];
                        $existingIndex = count($this->selectedProcurements) - 1;
                    }

                    $this->selectedProcurements[$existingIndex]['items'][] = [
                        'id' => $item->id,
                        'prItemID' => $item->prItemID,
                        'description' => $item->description,
                        'amount' => $item->amount,
                    ];
                }
            }
        }
    }

    public function calculateTotals(): void
    {
        $this->totalAbc = 0;

        foreach ($this->selectedProcurements as $proc) {
            if (!empty($proc['items'])) {
                foreach ($proc['items'] as $item) {
                    $this->totalAbc += floatval($item['amount'] ?? 0);
                }
            } else {
                $this->totalAbc += floatval($proc['abc'] ?? 0);
            }
        }

        $this->totalAbcFormatted = '₱' . number_format($this->totalAbc, 2);
        $this->twoPercent = '₱' . number_format($this->totalAbc * 0.02, 2);
        $this->fivePercent = '₱' . number_format($this->totalAbc * 0.05, 2);
    }

    public function openSelectionModal()
    {
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
        $this->selectedProcurements = $selectedData;
        $this->calculateTotals();
    }

    public function removeLot(int $procIndex): void
    {
        unset($this->selectedProcurements[$procIndex]);
        $this->selectedProcurements = array_values($this->selectedProcurements);
        $this->calculateTotals();
    }

    public function removeItem(int $procIndex, int $itemIndex): void
    {
        unset($this->selectedProcurements[$procIndex]['items'][$itemIndex]);
        if (empty($this->selectedProcurements[$procIndex]['items'])) {
            unset($this->selectedProcurements[$procIndex]);
        }
        $this->selectedProcurements = array_values($this->selectedProcurements);
        $this->calculateTotals();
    }

    public function save()
    {
        // --- Validate ---
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

        // --- Save transaction ---
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

            foreach ($this->selectedProcurements as $proc) {
                if (empty($proc['items'])) {
                    ScheduleForProcurementItems::create([
                        'schedule_for_procurement_id' => $this->schedule->id,
                        'itemable_UID' => $proc['procID'],
                        'itemable_type' => $this->procurementType,
                    ]);
                } else {
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

        $this->selectedLots = collect($this->selectedProcurements)
            ->filter(fn($p) => empty($p['items']))
            ->all();

        $this->selectedItemGroups = collect($this->selectedProcurements)
            ->filter(fn($p) => !empty($p['items']))
            ->all();

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
