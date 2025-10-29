<?php

namespace App\Livewire\ModeOfProcurement;

use App\Models\MopGroup;
use App\Models\PrItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Component;
use App\Models\Procurement;
use App\Models\ModeOfProcurement;
use App\Models\MopLot;
use App\Models\BidSchedule;
use App\Models\NtfBidSchedule;
use App\Models\PrSvp;
use Livewire\WithPagination;

class ModeOfProcurementCreatePage extends Component
{
    use WithPagination;
    public $procurement = null;
    public ?string $ref_number = null;
    public string $procurementType = '';
    public int $activeTab = 1; // Default to Tab 1
    public array $selectedProcurements = [];
    public int $perPage = 10;
    public $selectedPRPage = 1;
    public ?int $mopGroupId = null;
    public $form = [
        'modes' => [],
    ];
    public array $selectedLots = [];
    public array $selectedItemGroups = [];
    protected $listeners = ['procurementsSelected'];
    public $isEditing = false;
    public bool $viewOnlyTab1 = false;
    public bool $viewOnlyTab2 = false;
    public bool $viewOnlyTab3 = false;

    public function mount($ref_number = null)
    {
        $this->activeTab = (int) request()->query('tab', 1);

        if (session('alert')) {
            $alert = session('alert');
            LivewireAlert::title($alert['title'])
                ->{$alert['type']}()
                    ->text($alert['message'])
                    ->toast()
                    ->position('top-end')
                    ->show();
        }

        if ($this->mopGroupId || $this->procurementType) {
            return;
        }

        $this->ref_number = $ref_number;

        if ($this->ref_number) {
            session()->forget(['selected_procurements', 'form_state']);
            $this->resetForm();
            // $this->activeTab = 2; // Removed: Default is now 1

            $mopGroup = MopGroup::with([
                'procurements.mops.modeDetails',
                'procurements.mops.bidSchedules',
                'procurements.mops.ntfBidSchedules',
                'procurements.mops.svpDetails',
                'prItems.procurement',
                'prItems.mops.modeDetails',
                'prItems.mops.bidSchedules',
                'prItems.mops.ntfBidSchedules',
                'prItems.mops.svpDetails',
            ])
                ->where('ref_number', $this->ref_number)
                ->firstOrFail();

            $this->mopGroupId = $mopGroup->id;
            $this->procurementType = $mopGroup->procurable_type;

            if ($this->procurementType === 'perLot') {
                $this->selectedProcurements = $mopGroup->procurements->map(function ($proc) {
                    return [
                        'id' => $proc->procID,
                        'pr_number' => $proc->pr_number,
                        'procurement_program_project' => $proc->procurement_program_project,
                        'items' => null,
                    ];
                })->toArray();
            } else {
                $this->selectedProcurements = $mopGroup->prItems
                    ->groupBy('procID')
                    ->map(function ($items) {
                        $proc = $items->first()->procurement;
                        if (!$proc)
                            return null;
                        return [
                            'id' => $proc->procID,
                            'pr_number' => $proc->pr_number,
                            'procurement_program_project' => $proc->procurement_program_project,
                            'items' => $items->map(function ($item) {
                                return [
                                    'id' => $item->prItemID,
                                    'description' => $item->description,
                                    'amount' => $item->amount,
                                ];
                            })->values()->all(),
                        ];
                    })
                    ->filter()
                    ->values()
                    ->toArray();
            }

            $firstItem = ($this->procurementType === 'perLot')
                ? $mopGroup->procurements->first()
                : $mopGroup->prItems->first();

            if ($firstItem && $firstItem->mops->isNotEmpty()) {
                $this->form['modes'] = $firstItem->mops->sortBy('mode_order')->map(function ($mop) {
                    $loadedSchedulesData = [];
                    $modeId = $mop->mode_of_procurement_id;

                    if ($modeId == 5) {
                        $svpDetail = $mop->svpDetails()->first();
                        if ($svpDetail) {
                            $loadedSchedulesData = [$svpDetail->toArray()];
                        }
                    } elseif ($modeId == 4) {
                        $ntfSchedulesCollection = $mop->ntfBidSchedules;
                        if ($ntfSchedulesCollection) {
                            $loadedSchedulesData = $ntfSchedulesCollection->sortBy('bidding_number')->toArray();
                        }
                    } else {
                        $bidSchedulesCollection = $mop->bidSchedules;
                        if ($bidSchedulesCollection) {
                            $loadedSchedulesData = $bidSchedulesCollection->sortBy('bidding_number')->toArray();
                        }
                    }

                    return [
                        'id' => $mop->id,
                        'uid' => $mop->uid,
                        'mode_of_procurement_id' => $mop->mode_of_procurement_id,
                        'mode_order' => $mop->mode_order,
                        'bid_schedules' => $loadedSchedulesData,
                    ];
                })->values()->all();
            } else {
                Log::warning("No initial MOP found for MopGroup ref: {$this->ref_number}, loading default.");
                $this->form['modes'] = []; // Ensure it's empty or load default if needed
            }

        } else {
            $this->activeTab = 1; // Explicitly set for create mode
            $this->procurementType = request()->query('type', 'perLot');

            if (session()->has('selected_procurements')) {
                $this->selectedProcurements = session('selected_procurements');
            }
            if (session()->has('form_state')) {
                $this->form = session('form_state', ['modes' => []]); // Ensure modes exists
                session()->forget('form_state');
            } else {
                $this->resetForm(); // Ensure clean state on create
            }
        }

        $this->ensureDefaultBidSchedules();
    }
    public function updatedFormModes($value, $key)
    {
        // Example: key = "0.mode_of_procurement_id"
        if (str_ends_with($key, 'mode_of_procurement_id')) {
            $index = explode('.', $key)[0] ?? null;

            if (is_numeric($index) && isset($this->form['modes'][$index])) {
                foreach ($this->form['modes'][$index]['bid_schedules'] ?? [] as &$schedule) {
                    // ✅ Regenerate unique temp UID
                    $schedule['uid'] = 'TEMP-' . uniqid();
                }
            }
        }
    }

    private function ensureDefaultBidSchedules()
    {
        if (!isset($this->form['modes']) || !is_array($this->form['modes'])) {
            $this->form['modes'] = [];
        }

        foreach ($this->form['modes'] as &$mode) {
            if (empty($mode['bid_schedules']) || !is_array($mode['bid_schedules'])) {
                $mode['bid_schedules'] = [
                    [
                        'uid' => 'TEMP-' . uniqid(),
                        'ib_number' => '',
                        'bidding_date' => null,
                        'bidding_result' => '',
                        'ntf_bidding_result' => '',
                    ]
                ];
            }
        }
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

        $existingLotIds = collect($this->selectedProcurements)
            ->filter(fn($proc) => empty($proc['items']))
            ->pluck('id')
            ->toArray();

        $existingItemIds = collect($this->selectedProcurements)
            ->filter(fn($proc) => !empty($proc['items']))
            ->flatMap(fn($proc) => collect($proc['items'])->pluck('id'))
            ->toArray();

        $this->dispatch('open-mode-modal', existingLotIds: $existingLotIds, existingItemIds: $existingItemIds);
    }

    public function procurementsSelected(array $selectedData): void
    {
        $this->selectedProcurements = $selectedData;
    }

    public function onProcurementSelected(array $selections): void
    {
        $this->selectedProcurements = $selections;
    }

    public function hydrateForm()
    {
        if (!$this->procurement) {
            return;
        }
    }

    public function getSelectedPRProperty()
    {
        $items = collect($this->selectedProcurements)
            ->flatMap(function ($proc) {
                if (!empty($proc['items'])) {
                    return collect($proc['items'])->map(function ($item) use ($proc) {
                        $item['pr_number'] = $proc['pr_number'];
                        $item['is_item'] = true;
                        $item['unique_key'] = 'item_' . $item['id'];
                        return $item;
                    });
                } else {
                    $proc['is_item'] = false;
                    $proc['unique_key'] = 'lot_' . $proc['id'];
                    return [$proc];
                }
            });

        return $this->paginateCollection($items, $this->perPage, 'selectedPRPage');
    }

    public function removeSelectedPR(string $uniqueKey): void
    {
        [$type, $id] = explode('_', $uniqueKey);
        $id = (int) $id;

        if ($type === 'lot') {
            $this->selectedProcurements = collect($this->selectedProcurements)
                ->filter(fn($proc) => !empty($proc['items']) || (empty($proc['items']) && $proc['id'] !== $id))
                ->values()
                ->all();
        } else {
            foreach ($this->selectedProcurements as $procIndex => &$proc) {
                if (!empty($proc['items'])) {
                    $proc['items'] = collect($proc['items'])
                        ->filter(fn($item) => $item['id'] !== $id)
                        ->values()
                        ->all();
                    if (empty($proc['items'])) {
                        unset($this->selectedProcurements[$procIndex]);
                    }
                }
            }
            $this->selectedProcurements = array_values($this->selectedProcurements);
        }

        if ($this->SelectedPR->isEmpty() && $this->selectedPRPage > 1) {
            $this->selectedPRPage--;
        }
    }

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

    public function nextCustomPage(string $pageName)
    {
        if (property_exists($this, $pageName)) {
            $this->$pageName++;
        }
    }

    public function previousCustomPage(string $pageName)
    {
        if (property_exists($this, $pageName) && $this->$pageName > 1) {
            $this->$pageName--;
        }
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

    public function addMode()
    {
        // Define a template for an empty bid schedule
        $emptyBidSchedule = [
            'bidding_number' => 1, // Start with 1 for the first bid
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

        $newMode = [
            'uid' => 'TEMP-' . uniqid(),
            'mode_of_procurement_id' => '',
            'mode_order' => count($this->form['modes'] ?? []) + 1,
            'bid_schedules' => [$emptyBidSchedule],
        ];

        // Add the new mode to the beginning of the array
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

    private function validateData()
    {
        $this->validate([
            'form.modes' => 'required|array',
            'form.modes.*.mode_of_procurement_id' => 'required|exists:mode_of_procurements,id',
            'form.modes.*.bid_schedules' => 'nullable|array',
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

        foreach ($this->form['modes'] as $modeIndex => $mode) {
            if ($mode['mode_of_procurement_id'] == 5) {
                if (empty($mode['bid_schedules']) || !is_array($mode['bid_schedules'])) {
                    throw ValidationException::withMessages([
                        "form.modes.$modeIndex.bid_schedules" => 'Mode 5 requires at least one bid schedule.',
                    ]);
                }
                $isPlaceholder = count($mode['bid_schedules']) === 1 &&
                    empty($mode['bid_schedules'][0]['resolution_number']) &&
                    empty($mode['bid_schedules'][0]['rfq_no']) &&
                    empty($mode['bid_schedules'][0]['canvass_date']) &&
                    empty($mode['bid_schedules'][0]['date_returned_of_canvass']) &&
                    empty($mode['bid_schedules'][0]['abstract_of_canvass_date']);
                if (!$isPlaceholder) {
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

    public function setStep($step)
    {
        $this->activeTab = $step;
    }

    public function save()
    {
        if ($this->activeTab === 1) {
            $this->saveTab1();
        } elseif ($this->activeTab === 2) {
            $this->saveTab2();
        }
    }

    public function saveTab1()
    {
        if (empty($this->selectedProcurements)) {
            LivewireAlert::title('Selection Required')
                ->error()
                ->text('Please select at least one PR Lot or Item.')
                ->toast()->position('top-end')->show();
            return;
        }

        try {
            $group = DB::transaction(function () {
                $year = now()->format('Y');
                $lastNumber = MopGroup::whereYear('created_at', $year)
                    ->where('ref_number', 'like', "MOP-$year-%")
                    ->orderByDesc('id')
                    ->value('ref_number');
                $nextNumber = 1;
                if ($lastNumber) {
                    $lastNum = (int) Str::afterLast($lastNumber, '-');
                    $nextNumber = $lastNum + 1;
                }

                $refNumber = sprintf('MOP-%s-%04d', $year, $nextNumber);

                $mopGroup = MopGroup::create([
                    'ref_number' => $refNumber,
                    'status' => 'draft',
                    'procurable_type' => $this->procurementType,
                ]);

                $lotIDs = collect($this->selectedProcurements)
                    ->filter(fn($proc) => empty($proc['items']))
                    ->pluck('procID'); // Correct key

                $itemIDs = collect($this->selectedProcurements)
                    ->filter(fn($proc) => !empty($proc['items']))
                    ->pluck('items.*.prItemID') // Correct key
                    ->flatten();

                if ($lotIDs->isNotEmpty()) {
                    $mopGroup->procurements()->attach($lotIDs);
                    $attachedLots = Procurement::whereIn('procID', $lotIDs)->get();
                    foreach ($attachedLots as $lot) {
                        $lot->mops()->create([
                            'mode_of_procurement_id' => 1,
                            'mode_order' => 0,
                            'uid' => 'MOP-1-0'
                        ]);
                    }
                }

                if ($itemIDs->isNotEmpty()) {
                    $mopGroup->prItems()->attach($itemIDs);
                    $attachedItems = PrItem::whereIn('prItemID', $itemIDs)->get();
                    foreach ($attachedItems as $item) {
                        $item->mops()->create([
                            'mode_of_procurement_id' => 1,
                            'mode_order' => 0,
                            'uid' => 'MOP-1-0'
                        ]);
                    }
                }
                return $mopGroup;
            });

            session()->flash('alert', [
                'type' => 'success',
                'title' => 'Saved!',
                'message' => 'Your Procurement has been created successfully.',
            ]);

            return redirect()->route('mode-of-procurement.update', [
                'ref_number' => $group->ref_number,
                'tab' => 2
            ]);

        } catch (\Exception $e) {
            Log::error('Error saving MOP Details selections: ' . $e->getMessage());
            LivewireAlert::title('Error Saving Selections')
                ->error()
                ->text('An error occurred: ' . $e->getMessage())
                ->toast()->position('top-end')->show();
        }
    }

    private function syncMopFor(Model $model, array $modeData, $existingMops): void
    {
        $submittedModeId = $modeData['mode_of_procurement_id'];
        $submittedUid = $modeData['uid'] ?? null;
        $submittedOrder = $modeData['mode_order'] ?? null;

        $savedMop = $existingMops->firstWhere('uid', $submittedUid);

        $isNewModeViaButton = Str::startsWith($submittedUid ?? '', 'TEMP-');
        $isTransitionFromMode1 = false;
        $newModeOrder = $submittedOrder;

        if ($savedMop && $savedMop->mode_of_procurement_id == 1 && $submittedModeId != 1) {
            $isTransitionFromMode1 = true;
            // Determine the next mode order by finding the maximum existing order and adding 1.
            $newModeOrder = ($existingMops->max('mode_order') ?? -1) + 1;

            // **NEW LOGIC: If the existing order is 0, explicitly set the new order to 1**
            if ($savedMop->mode_of_procurement_id == 1 && $savedMop->mode_order == 0) {
                $newModeOrder = 1;
            }
        }

        if ($isNewModeViaButton || $isTransitionFromMode1) {
            // Generate the new Mode Order if it's a completely new entry
            if ($isNewModeViaButton && $newModeOrder === null) {
                $newModeOrder = ($existingMops->max('mode_order') ?? -1) + 1;
            }

            // 🎯 IMPLEMENTING THE NEW UID FORMAT: MOP-<mode_id>-<mode_order>
            $newUid = 'MOP-' . $submittedModeId . '-' . $newModeOrder;

            $mop = $model->mops()->create([
                'mode_of_procurement_id' => $submittedModeId,
                'mode_order' => $newModeOrder,
                'uid' => $newUid, // Storing the new formatted UID
            ]);

            // Update the form state with the permanent UID and Order
            foreach ($this->form['modes'] as $index => $formMode) {
                if (($formMode['uid'] ?? null) === $submittedUid) {
                    $this->form['modes'][$index]['uid'] = $newUid;
                    $this->form['modes'][$index]['mode_order'] = $newModeOrder;
                    break;
                }
            }
        } else {
            // Existing MOP update logic (no change to UID generation here, only updating fields)
            $mop = $model->mops()->updateOrCreate(
                ['uid' => $submittedUid],
                [
                    'mode_of_procurement_id' => $submittedModeId,
                    'mode_order' => $submittedOrder ?? $savedMop?->mode_order ?? 0,
                ]
            );
        }

        // --- (The rest of the Bid Schedule logic remains unchanged) ---

        $mop->bidSchedules()->delete();
        $mop->ntfBidSchedules()->delete();
        $mop->svpDetails()->delete();

        if (!empty($modeData['bid_schedules'])) {
            $modeId = $mop->mode_of_procurement_id;

            foreach ($modeData['bid_schedules'] as $scheduleIndex => $scheduleData) {
                $cleanScheduleData = collect($scheduleData)->filter(fn($value) => $value !== null)->all();
                try {
                    switch ($modeId) {
                        case 5:
                            $mop->svpDetails()->create($cleanScheduleData);
                            break;
                        case 4:
                            $mop->ntfBidSchedules()->create($cleanScheduleData);
                            break;
                        default:
                            $mop->bidSchedules()->create($cleanScheduleData);
                            break;
                    }
                } catch (\Exception $e) {
                    Log::error("Error saving schedule for Mop ID {$mop->id}: " . $e->getMessage(), $cleanScheduleData);
                    throw $e;
                }
            }
        }
    }

    public function saveTab2()
    {
        if (!$this->mopGroupId) {
            LivewireAlert::title('Error')->error()->text('Cannot save. Group ID not found. Please re-select procurements.')->toast()->position('top-end')->show();
            return;
        }

        try {
            // Basic validation for the modes array structure
            $this->validate([
                'form.modes' => 'required|array|min:1',
                'form.modes.*.mode_of_procurement_id' => 'required|exists:mode_of_procurements,id',
            ]);

            // Additional validation for Mode 5 (Small Value Procurement) requirements
            $this->validateData(); // This private method handles the specific validation for modes like SVP

        } catch (ValidationException $e) {
            // Log the validation error for debugging
            Log::error('Validation error in saveTab2: ' . $e->getMessage(), $e->errors());

            LivewireAlert::title('Validation Error')
                ->error()
                ->text('Please ensure all required fields in the Mode of Procurement section are correctly filled.')
                ->toast()->position('top-end')->show();

            throw $e;
        }

        try {
            DB::transaction(function () {
                $mopGroup = MopGroup::with(['procurements.mops', 'prItems.mops'])
                    ->findOrFail($this->mopGroupId);

                $itemsToUpdate = ($mopGroup->procurable_type === 'perLot')
                    ? $mopGroup->procurements
                    : $mopGroup->prItems;

                // Ensure modes are sorted by order before syncing
                $sortedModes = collect($this->form['modes'])->sortBy('mode_order')->values()->all();

                foreach ($itemsToUpdate as $item) {
                    $existingMops = $item->mops()->get();
                    $submittedUids = collect($sortedModes)->pluck('uid')->filter()->all();

                    // 1. Delete MOPs that are no longer in the form
                    $existingMops->whereNotIn('uid', $submittedUids)->each(function ($mopToDelete) {
                        $mopToDelete->bidSchedules()->delete();
                        $mopToDelete->ntfBidSchedules()->delete();
                        $mopToDelete->svpDetails()->delete();
                        $mopToDelete->delete();
                    });

                    // 2. Sync remaining/new MOPs
                    foreach ($sortedModes as $modeData) {
                        // This method contains the logic for the new UID and mode_order increment.
                        $this->syncMopFor($item, $modeData, $existingMops);
                    }
                }
            });

            LivewireAlert::title('Modes Saved!')
                ->success()
                ->text('The Modes of Procurement and their schedules have been saved.')
                ->toast()->position('top-end')->show();

            // Re-fetch the data to ensure the view reflects the saved state (including new UIDs/Orders)
            $this->mount($this->ref_number);
            $this->setStep(2);

        } catch (\Exception $e) {
            Log::error('Error saving/updating MOP Details: ' . $e->getMessage() . ' Stack: ' . $e->getTraceAsString());
            LivewireAlert::title('Error Saving MOP')
                ->error()
                ->text('An error occurred while saving: ' . $e->getMessage())
                ->toast()->position('top-end')->show();
        }
    }

    public function resetForm(): void
    {
        $this->selectedProcurements = [];
        $this->form = [
            'modes' => [],
        ];
        $this->resetValidation();
    }

    public function render()
    {
        $modes = ModeOfProcurement::where('is_active', true)
            ->orderBy('modeofprocurements')
            ->get();

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

        return view('livewire.mode-of-procurement.mode-of-procurement-create-page', [
            'modeOfProcurements' => $modes,
            'existingLotIds' => $existingLotIds,
            'existingItemIds' => $existingItemIds,
        ]);
    }
}
