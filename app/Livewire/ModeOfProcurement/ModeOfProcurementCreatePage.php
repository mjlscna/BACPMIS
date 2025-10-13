<?php

namespace App\Livewire\ModeOfProcurement;

use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Component;
use App\Models\Procurement;
use App\Models\ModeOfProcurement;
use App\Models\MopLot;
use App\Models\BidSchedule;
use App\Models\NtfBidSchedule;
use App\Models\PrSvp;

class ModeOfProcurementCreatePage extends Component
{
    public $procurement = null;
    public string $procurementType = '';
    public $procID;
    public int $activeTab = 1;
    public array $selectedProcurements = [];

    public $form = [
        'modes' => [],
    ];

    protected $listeners = ['procurement-selected' => 'onProcurementSelected'];

    public function mount($procID = null)
    {
        $this->procurementType = request()->query('type', 'perLot');

        if (session()->has('selected_procurements')) {
            $this->selectedProcurements = session('selected_procurements');
            $this->form['procurement_ids'] = array_column($this->selectedProcurements, 'id');
        }

        // Combine procurement loading. No need for a separate `procurementIdToLoad` variable.
        $this->procID = $procID ?? $this->form['procurement_ids'][0] ?? null;

        // Note: This assumes if multiple are selected, they all belong to the same parent `procID`.
        // If you are only ever selecting one item, then this is fine.
        // If selecting multiple, you might need to adjust your business logic.

        if ($this->procID) {
            $this->procurement = Procurement::where('procID', $this->procID)->first();

            if ($this->procurement) {
                $this->form['pr_number'] = $this->procurement->pr_number;
                $this->form['procurement_program_project'] = $this->procurement->procurement_program_project;
                $this->hydrateForm();
            }
        }
    }
    public function removeLot(int $procIndex): void
    {
        if (isset($this->selectedProcurements[$procIndex])) {
            unset($this->selectedProcurements[$procIndex]);
            $this->selectedProcurements = array_values($this->selectedProcurements); // Re-index the array
            $this->form['procurement_ids'] = array_column($this->selectedProcurements, 'id');
        }
    }
    public function removeItem(int $procIndex, int $itemIndex): void
    {
        if (isset($this->selectedProcurements[$procIndex]['items'][$itemIndex])) {
            // Remove the specific item
            unset($this->selectedProcurements[$procIndex]['items'][$itemIndex]);

            // If no items are left in this group, remove the entire procurement entry
            if (empty($this->selectedProcurements[$procIndex]['items'])) {
                unset($this->selectedProcurements[$procIndex]);
            }

            // Re-index the main array to keep it clean
            $this->selectedProcurements = array_values($this->selectedProcurements);
        }
    }
    public function hydrateForm()
    {
        if (!$this->procurement) {
            return;
        }

        $modesFromDb = MopLot::with(['bidSchedules', 'ntfBidSchedules', 'prSvp'])
            ->where('procID', $this->procurement->procID)
            ->orderBy('mode_order', 'desc')
            ->get();

        if ($modesFromDb->isEmpty()) {
            $mopLot = MopLot::where('procID', $this->procurement->procID)->first();
            if ($mopLot) {
                $this->form['modes'] = [
                    [
                        'uid' => $mopLot->uid,
                        'mode_of_procurement_id' => $mopLot->mode_of_procurement_id,
                        'mode_order' => $mopLot->mode_order,
                        'bid_schedules' => [],
                    ]
                ];
            }
            return;
        }

        $this->form['modes'] = $modesFromDb->map(function ($mode) {
            $bidSchedules = $mode->bidSchedules->toArray();
            $ntfSchedules = $mode->ntfBidSchedules->toArray();
            $prSvp = $mode->prSvp ? [$mode->prSvp->toArray()] : [];

            $allSchedules = array_merge($bidSchedules, $ntfSchedules, $prSvp);
            usort($allSchedules, fn($a, $b) => ($a['bidding_number'] ?? 0) <=> ($b['bidding_number'] ?? 0));

            return [
                'uid' => $mode->uid,
                'mode_of_procurement_id' => $mode->mode_of_procurement_id,
                'mode_order' => $mode->mode_order,
                'bid_schedules' => $allSchedules,
            ];
        })->toArray();
    }

    public function getShowAddModeButtonProperty()
    {
        $modes = collect($this->form['modes'] ?? []);
        $hasDefaultMode = $modes->contains('mode_of_procurement_id', 1);
        $hasPendingOrEmptySchedule = $modes->contains(function ($mode) {
            $schedules = collect($mode['bid_schedules'] ?? []);
            return $schedules->isEmpty() ||
                $schedules->contains(fn($s) => empty($s['bidding_result']) && empty($s['ntf_bidding_result']));
        });

        return !$hasDefaultMode && !$hasPendingOrEmptySchedule;
    }

    public function onProcurementSelected($procurementData)
    {
        session()->flash('selected_procurement', $procurementData);
    }

    public function addMode()
    {
        $newMode = [
            'uid' => 'TEMP-' . uniqid(),
            'mode_of_procurement_id' => '',
            'mode_order' => count($this->form['modes'] ?? []) + 1,
            'bid_schedules' => [],
        ];
        array_unshift($this->form['modes'], $newMode);
    }

    public function addBidSchedule($modeIndex)
    {
        $existingSchedules = $this->form['modes'][$modeIndex]['bid_schedules'] ?? [];
        $newBiddingNumber = count($existingSchedules) + 1;

        $newBidSchedule = [
            'bidding_number' => $newBiddingNumber,
            'ib_number' => '',
            'pre_proc_conference' => null,
            'ads_post_ib' => null,
            'pre_bid_conf' => null,
            'eligibility_check' => null,
            'sub_open_bids' => null,
            'bidding_date' => null,
            'bidding_result' => '',
            'ntf_no' => '',
            'ntf_bidding_date' => null,
            'ntf_bidding_result' => '',
            'rfq_no' => '',
            'canvass_date' => null,
            'date_returned_of_canvass' => null,
            'abstract_of_canvass_date' => null,
            'resolution_number' => '',
        ];

        array_unshift($this->form['modes'][$modeIndex]['bid_schedules'], $newBidSchedule);
    }

    private function processMode(array $mode, int $modeIndex)
    {
        Log::info("Processing Mode {$modeIndex}:", $mode);
        $modeId = $mode['mode_of_procurement_id'];
        $modeOrder = $mode['mode_order'] ?? ($modeIndex + 1);

        $this->preventDuplicateMode($modeId, $mode);
        $existingMode = $this->updateOrCreateBidMode($mode, $modeId, $modeOrder);
        $this->syncModeUidToForm($mode, $existingMode->uid, $modeOrder);

        if (!empty($mode['bid_schedules'])) {
            if ($modeId == 5) {
                $this->processPrSvp($mode['bid_schedules'], $existingMode->uid);
            } else {
                $this->processSchedules($mode['bid_schedules'], $existingMode->uid, $modeId);
            }
        }
    }

    private function preventDuplicateMode($modeId, $mode)
    {
        if ($modeId == 1) {
            $exists = MopLot::where('procID', $this->procID)
                ->where('mode_of_procurement_id', 1)
                ->exists();

            if ($exists && (empty($mode['uid']) || str_starts_with($mode['uid'], 'TEMP-'))) {
                throw new \Exception('Mode of procurement ID 1 is already added.');
            }
        }
    }

    private function updateOrCreateBidMode($mode, $modeId, $modeOrder)
    {
        $existingMode = !empty($mode['uid']) && !str_starts_with($mode['uid'], 'TEMP-')
            ? MopLot::where('uid', $mode['uid'])->first()
            : null;

        $data = [
            'procID' => $this->procID,
            'mode_of_procurement_id' => $modeId,
            'mode_order' => $modeOrder,
        ];

        if ($existingMode && $existingMode->mode_of_procurement_id == 1 && $modeId != 1) {
            $newOrder = MopLot::where('procID', $this->procID)->max('mode_order') + 1;
            $data['mode_order'] = $newOrder;
            $data['uid'] = "MOP-{$modeId}-{$newOrder}";
            return MopLot::create($data);
        }

        if ($existingMode) {
            $existingMode->update($data);
        } else {
            $data['uid'] = "MOP{$modeId}-{$modeOrder}";
            $existingMode = MopLot::create($data);
        }

        return $existingMode;
    }

    private function syncModeUidToForm($mode, $uid, $modeOrder)
    {
        foreach ($this->form['modes'] as &$formMode) {
            if (
                (!empty($formMode['uid']) && $formMode['uid'] === $mode['uid']) ||
                (empty($formMode['uid']) && $formMode['mode_of_procurement_id'] === $mode['mode_of_procurement_id'])
            ) {
                $formMode['uid'] = $uid;
                $formMode['mode_order'] = $modeOrder;
                break;
            }
        }
    }

    private function processSchedules(array $schedules, string $uid, int $modeId)
    {
        $reorderedSchedules = array_reverse($schedules);

        foreach ($reorderedSchedules as $i => $schedule) {
            $biddingNumber = $i + 1;
            $scheduleUid = "{$uid}-{$biddingNumber}";

            \Log::info("Processing Schedule for UID: {$scheduleUid}", $schedule);

            $baseData = [
                'procID' => $this->procID,
                'uid' => $scheduleUid,
                'ib_number' => $schedule['ib_number'] ?? null,
                'pre_proc_conference' => $schedule['pre_proc_conference'] ?? null,
                'ads_post_ib' => $schedule['ads_post_ib'] ?? null,
                'pre_bid_conf' => $schedule['pre_bid_conf'] ?? null,
                'eligibility_check' => $schedule['eligibility_check'] ?? null,
                'sub_open_bids' => $schedule['sub_open_bids'] ?? null,
                'bidding_number' => $biddingNumber,
            ];

            if ($modeId == 4) {
                $ntfData = array_merge($baseData, [
                    'ntf_no' => $schedule['ntf_no'] ?? null,
                    'ntf_bidding_date' => $schedule['ntf_bidding_date'] ?? null,
                    'ntf_bidding_result' => $schedule['ntf_bidding_result'] ?? null,
                    'rfq_no' => $schedule['rfq_no'] ?? null,
                    'canvass_date' => $schedule['canvass_date'] ?? null,
                    'date_returned_of_canvass' => $schedule['date_returned_of_canvass'] ?? null,
                    'abstract_of_canvass_date' => $schedule['abstract_of_canvass_date'] ?? null,
                ]);

                NtfBidSchedule::updateOrCreate(
                    ['procID' => $this->procID, 'uid' => $scheduleUid],
                    $ntfData
                );
            } else {
                $bidData = array_merge($baseData, [
                    'bidding_date' => $schedule['bidding_date'] ?? null,
                    'bidding_result' => $schedule['bidding_result'] ?? null,
                ]);

                BidSchedule::updateOrCreate(
                    ['procID' => $this->procID, 'uid' => $scheduleUid],
                    $bidData
                );
            }
        }
    }

    private function processPrSvp(array $schedules, string $uid)
    {
        $schedule = $schedules[0] ?? null;

        if (!$schedule)
            return;

        PrSvp::updateOrCreate(
            ['procID' => $this->procID, 'uid' => $uid],
            [
                'resolution_number' => $schedule['resolution_number'] ?? null,
                'rfq_no' => $schedule['rfq_no'] ?? null,
                'canvass_date' => $schedule['canvass_date'] ?? null,
                'date_returned_of_canvass' => $schedule['date_returned_of_canvass'] ?? null,
                'abstract_of_canvass_date' => $schedule['abstract_of_canvass_date'] ?? null,
            ]
        );
    }
    private function validateData()
    {
        // Base rules for all modes and shared fields
        $this->validate([
            'form.modes' => 'required|array',
            'form.modes.*.mode_of_procurement_id' => 'required|exists:mode_of_procurements,id',
            'form.modes.*.bid_schedules' => 'nullable|array',

            // Common optional fields
            'form.modes.*.bid_schedules.*.ib_number' => 'nullable|string|max:255',
            'form.modes.*.bid_schedules.*.pre_proc_conference' => 'nullable|date',
            'form.modes.*.bid_schedules.*.ads_post_ib' => 'nullable|date',
            'form.modes.*.bid_schedules.*.pre_bid_conf' => 'nullable|date',
            'form.modes.*.bid_schedules.*.eligibility_check' => 'nullable|date',
            'form.modes.*.bid_schedules.*.sub_open_bids' => 'nullable|date',
            'form.modes.*.bid_schedules.*.bidding_number' => 'nullable|integer|min:0|max:255',
            'form.modes.*.bid_schedules.*.bidding_date' => 'nullable|date',
            'form.modes.*.bid_schedules.*.bidding_result' => 'nullable|string|max:255',
            'form.modes.*.bid_schedules.*.ntf_no' => 'nullable|string|max:255',
            'form.modes.*.bid_schedules.*.ntf_bidding_date' => 'nullable|date',
            'form.modes.*.bid_schedules.*.ntf_bidding_result' => 'nullable|string|max:255',
        ]);

        // Mode 5 specific: require at least one schedule only if user actually filled something
        foreach ($this->form['modes'] as $modeIndex => $mode) {
            if ($mode['mode_of_procurement_id'] == 5) {

                // Ensure at least one bid schedule exists
                if (empty($mode['bid_schedules']) || !is_array($mode['bid_schedules'])) {
                    throw ValidationException::withMessages([
                        "form.modes.$modeIndex.bid_schedules" => 'Mode 5 requires at least one bid schedule.',
                    ]);
                }

                // Check if this is just the auto-generated placeholder
                $isPlaceholder = count($mode['bid_schedules']) === 1 &&
                    empty($mode['bid_schedules'][0]['resolution_number']) &&
                    empty($mode['bid_schedules'][0]['rfq_no']) &&
                    empty($mode['bid_schedules'][0]['canvass_date']) &&
                    empty($mode['bid_schedules'][0]['date_returned_of_canvass']) &&
                    empty($mode['bid_schedules'][0]['abstract_of_canvass_date']);

                if (!$isPlaceholder) {
                    // Validate every filled-in schedule entry
                    foreach ($mode['bid_schedules'] as $bidIndex => $schedule) {
                        $this->validate([
                            "form.modes.$modeIndex.bid_schedules.$bidIndex.resolution_number" => 'required|string|max:255',
                            "form.modes.$modeIndex.bid_schedules.$bidIndex.rfq_no" => 'required|string|max:255',
                            "form.modes.$modeIndex.bid_schedules.$bidIndex.canvass_date" => 'required|date',
                            "form.modes.$modeIndex.bid_schedules.$bidIndex.date_returned_of_canvass" => 'required|date',
                            "form.modes.$modeIndex.bid_schedules.$bidIndex.abstract_of_canvass_date" => 'required|date',
                        ]);
                    }
                }
            }
        }
    }
    private function prepareModes()
    {
        $modes = $this->form['modes'];
        usort($modes, fn($a, $b) => ($a['mode_order'] ?? 0) <=> ($b['mode_order'] ?? 0));
        return $modes;
    }
    public function checkSuccessfulBidOrNtf()
    {
        if (empty($this->procID)) {
            $this->hasSuccessfulBidOrNtf = false;
            return;
        }

        $this->hasSuccessfulBidOrNtf = BidSchedule::where('procID', $this->procID)
            ->where('bidding_result', 'SUCCESSFUL')
            ->exists() || NtfBidSchedule::where('procID', $this->procID)
                ->where('ntf_bidding_result', 'SUCCESSFUL')
                ->exists();
    }
    public function checkSuccessfulSvp()
    {
        if (empty($this->procID)) {
            $this->hasSuccessfulSvp = false;
            return;
        }

        // Adjust the field below if you have a different indicator for SVP success
        $this->hasSuccessfulSvp = PrSvp::where('procID', $this->procID)
            ->whereNotNull('resolution_number')
            ->whereNotNull('rfq_no')
            ->whereNotNull('canvass_date')
            ->whereNotNull('date_returned_of_canvass')
            ->whereNotNull('abstract_of_canvass_date')
            ->exists();
    }
    public function reindexBiddingNumbers()
    {
        foreach ($this->form['modes'] as $modeIndex => $mode) {
            if (isset($mode['bid_schedules']) && is_array($mode['bid_schedules'])) {
                foreach ($mode['bid_schedules'] as $bidIndex => $schedule) {
                    $this->form['modes'][$modeIndex]['bid_schedules'][$bidIndex]['bidding_number'] = $bidIndex + 1;
                }
            }
        }
    }

    public function hasProcurementMode(int $modeId): bool
    {
        return MopLot::where('procID', $this->procID)
            ->where('mode_of_procurement_id', $modeId)
            ->exists();
    }
    public function nextStep()
    {
        if ($this->activeTab < 3) {
            $this->activeTab++;
        }
    }

    public function previousStep()
    {
        if ($this->activeTab > 1) {
            $this->activeTab--;
        }
    }

    public function setStep($step)
    {
        $this->activeTab = $step;
    }
    public function save()
    {
        try {
            // Ensure Mode 5 always has at least one schedule before validating
            foreach ($this->form['modes'] as $modeIndex => $mode) {
                if (
                    isset($mode['mode_of_procurement_id']) &&
                    $mode['mode_of_procurement_id'] == 5 &&
                    (empty($mode['bid_schedules']) || !is_array($mode['bid_schedules']))
                ) {
                    $this->addBidSchedule($modeIndex);
                }
            }

            $this->validateData();

            $modesForProcessing = $this->prepareModes();

            foreach ($modesForProcessing as $modeIndex => $mode) {
                $this->processMode($mode, $modeIndex);
            }

            LivewireAlert::title('Saved Successfully!')
                ->success()->toast()->position('top-end')->show();

            $this->checkSuccessfulBidOrNtf();
            $this->checkSuccessfulSvp();

            if ($this->hasSuccessfulBidOrNtf || $this->hasSuccessfulSvp) {
                $this->activeTab = 3;
            }

        } catch (ValidationException $e) {
            LivewireAlert::title('Validation Failed!')
                ->error()->text($e->getMessage())->toast()->position('top-end')->show();
        } catch (\Exception $e) {
            \Log::error('Error Saving Data: ' . $e->getMessage());
            LivewireAlert::title('Error Saving Data!')
                ->error()->text($e->getMessage())->toast()->position('top-end')->show();
        }
    }
    public function render()
    {
        $modes = ModeOfProcurement::where('is_active', true)
            ->orderBy('modeofprocurements')
            ->get();

        // Prepare separate arrays of IDs to pass back to the modal
        $existingLotIds = [];
        $existingItemIds = [];

        foreach ($this->selectedProcurements as $proc) {
            if (empty($proc['items'])) {
                // This is a 'perLot' procurement, add its ID to the lots array
                $existingLotIds[] = $proc['id'];
            } else {
                // This is a 'perItem' procurement, add its item IDs to the items array
                foreach ($proc['items'] as $item) {
                    $existingItemIds[] = $item['id'];
                }
            }
        }

        $selectedLots = collect($this->selectedProcurements)
            ->filter(fn($proc) => empty($proc['items']))
            ->all();

        $selectedItemGroups = collect($this->selectedProcurements)
            ->filter(fn($proc) => !empty($proc['items']))
            ->all();

        return view('livewire.mode-of-procurement.mode-of-procurement-create-page', [
            'modesOfProcurement' => $modes,
            'selectedLots' => $selectedLots,
            'selectedItemGroups' => $selectedItemGroups,
            // Pass the new ID arrays to the view
            'existingLotIds' => $existingLotIds,
            'existingItemIds' => $existingItemIds,
        ]);
    }
}
