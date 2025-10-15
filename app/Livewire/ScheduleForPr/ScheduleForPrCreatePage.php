<?php

namespace App\Livewire\ScheduleForPr;

use App\Models\BiddingStatus;
use App\Models\Procurement;
use Livewire\Component;

class ScheduleForPrCreatePage extends Component
{
    public $procurement = null;
    public string $procurementType = '';
    public int $activeTab = 1;

    public array $selectedProcurements = [];
    public array $selectedLots = [];
    public array $selectedItemGroups = [];

    public $form = [];

    // Computed values
    public float $totalAbc = 0;
    public string $totalAbcFormatted = '₱0.00';
    public string $twoPercent = '₱0.00';
    public string $fivePercent = '₱0.00';

    public function mount($procID = null)
    {
        $this->procurementType = request()->query('type', 'perLot');

        if (session()->has('selected_procurements')) {
            $this->selectedProcurements = session('selected_procurements');
            $this->form['procurement_ids'] = array_column($this->selectedProcurements, 'id');
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

    public function removeLot(int $procIndex): void
    {
        if (isset($this->selectedProcurements[$procIndex])) {
            unset($this->selectedProcurements[$procIndex]);
            $this->selectedProcurements = array_values($this->selectedProcurements);
            $this->form['procurement_ids'] = array_column($this->selectedProcurements, 'id');
        }

        $this->calculateTotals();
    }

    public function removeItem(int $procIndex, int $itemIndex): void
    {
        if (isset($this->selectedProcurements[$procIndex]['items'][$itemIndex])) {
            unset($this->selectedProcurements[$procIndex]['items'][$itemIndex]);

            if (empty($this->selectedProcurements[$procIndex]['items'])) {
                unset($this->selectedProcurements[$procIndex]);
            }

            $this->selectedProcurements = array_values($this->selectedProcurements);
        }

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

    /**
     * Calculate total ABC, 2%, and 5% based on selected procurements/items.
     */
    public function calculateTotals(): void
    {
        $this->totalAbc = 0;

        foreach ($this->selectedProcurements as $proc) {
            if (!empty($proc['items'])) {
                // per-item
                foreach ($proc['items'] as $item) {
                    $this->totalAbc += floatval($item['amount'] ?? 0);
                }
            } else {
                // per-lot
                $this->totalAbc += floatval($proc['abc'] ?? 0);
            }
        }

        // Format totals
        $this->totalAbcFormatted = '₱' . number_format($this->totalAbc, 2);
        $this->twoPercent = '₱' . number_format($this->totalAbc * 0.02, 2);
        $this->fivePercent = '₱' . number_format($this->totalAbc * 0.05, 2);
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
            ->filter(fn($proc) => empty($proc['items']))
            ->all();

        $this->selectedItemGroups = collect($this->selectedProcurements)
            ->filter(fn($proc) => !empty($proc['items']))
            ->all();

        $ActionTakenOptions = [
            ['id' => 'done', 'name' => 'Done'],
            ['id' => 'rebid', 'name' => 'Rebid'],
            ['id' => 'cancelled', 'name' => 'Cancelled'],
        ];

        return view('livewire.schedule-for-pr.schedule-for-pr-create-page', [
            'ActionTakenOptions' => $ActionTakenOptions,
            'biddingStatus' => BiddingStatus::all(),
            'existingLotIds' => $existingLotIds,
            'existingItemIds' => $existingItemIds,
        ]);
    }
}
