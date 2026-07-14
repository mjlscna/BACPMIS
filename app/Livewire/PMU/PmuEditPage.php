<?php

namespace App\Livewire\PMU;

use App\Models\Pmu;
use App\Models\PmuPo;
use App\Models\Procurement;
use App\Models\PrLotPrstage;
use App\Models\PrItemPrstage;
use App\Models\Supply;
use Livewire\Attributes\Title;
use Livewire\Component;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

#[Title('PMU | PMIS')]
class PmuEditPage extends Component
{
    private const DATE_FORMAT = 'Y-m-d';

    public string $noticeOfAwardNumber = '';
    public ?string $noticeOfAward = null;

    // Linked PRs pagination
    public int $editPage = 1;
    public int $editPerPage = 10;

    // Bulk edit selection
    public array $selectedItems = [];
    public bool $selectAll = false;
    public bool $showBulkEditModal = false;

    // Form fields
    public string $date_forwarded = '';
    public string $po_date = '';
    public ?string $po_date_deadline_display = null; // Earliest PO Date Deadline among selected items (read-only, for warning)
    public string $date_coa_stamped_received = '';
    public string $date_po_receipt_by_supplier = '';
    public string $contract_amount = '';
    public string $po_contract_number = '';
    public string $po_contract_number_link = '';
    public string $ntp_link = '';
    public string $contract_signing_date = '';
    public string $notice_to_proceed_date = '';
    public string $remarks = '';

    // Bulk forward to supply
    public bool $showForwardConfirm = false;
    public ?string $actualDateForwarded = null;

    // Manual status
    public string $manual_status = ''; // '' = no change, 'auto' = clear, 'return_to_bac', 'for_end_user_compliance'

    // Edit Remarks Modal (per pmu_po row)
    public bool $showEditRemarksModal = false;
    public ?int $editRemarksPoId = null;
    public string $editRemarksValue = '';

    // Edit NOA Remarks Modal (pmu.received_remarks)
    public ?string $noaRemarks = null;
    public bool $showEditNoaRemarksModal = false;
    public string $editNoaRemarksValue = '';

    private \Illuminate\Support\Collection|null $combinedRowsCache = null;

    public function mount(string $id): void
    {
        $this->noticeOfAwardNumber = $id;

        $record = Pmu::where('notice_of_award_number', $id)->firstOrFail();

        $this->noticeOfAward = DB::table('post_procurements')
            ->where('notice_of_award_number', $id)
            ->value('notice_of_award');

        $this->date_forwarded = $record->date_forwarded ? $record->date_forwarded->format(self::DATE_FORMAT) : '';
        $this->noaRemarks = $record->received_remarks;
    }

    public function update(): void
    {
        $this->validate([
            'po_date' => 'nullable|date',
            'date_coa_stamped_received' => 'nullable|date',
            'date_po_receipt_by_supplier' => 'nullable|date',
            'contract_amount' => 'nullable|numeric|min:0',
            'po_contract_number' => 'required|string|max:255',
            'po_contract_number_link' => 'nullable|url|max:2048',
            'ntp_link' => 'nullable|url|max:2048',
            'contract_signing_date' => 'nullable|date',
            'notice_to_proceed_date' => 'nullable|date',
            'remarks' => 'nullable|string',
        ], [
            'po_date.date' => 'PO Date must be a valid date.',
            'date_coa_stamped_received.date' => 'Stamped received of COA must be a valid date.',
            'date_po_receipt_by_supplier.date' => 'Date of PO Receipt by Supplier must be a valid date.',
            'contract_amount.numeric' => 'Contract amount must be a valid number.',
            'contract_amount.min' => 'Contract amount must be 0 or greater.',
            'po_contract_number.required' => 'PO / Contract number is required.',
            'contract_signing_date.date' => 'Contract signing date must be a valid date.',
            'notice_to_proceed_date.date' => 'Notice to proceed date must be a valid date.',
        ]);

        if (empty($this->selectedItems)) {
            LivewireAlert::title('No items selected')
                ->warning()
                ->text('Please select at least one item.')
                ->toast()
                ->position('top-end')
                ->show();
            return;
        }

        // Validate contract amount must equal each selected item's awarded amount
        if ($this->contract_amount !== '') {
            $contractAmount = (float) $this->contract_amount;

            $combined = $this->fetchCombinedRows();
            $selectedRows = $combined->whereIn('rowKey', $this->selectedItems);

            foreach ($selectedRows as $row) {
                if ($row->awarded_amount === null) {
                    continue; // skip rows with no awarded amount set
                }

                $awardedAmount = (float) $row->awarded_amount;

                if ($contractAmount !== $awardedAmount) {
                    LivewireAlert::title('Contract Amount Mismatch')
                        ->warning()
                        ->text("Contract amount (₱" . number_format($contractAmount, 2) . ") must equal the awarded amount (₱" . number_format($awardedAmount, 2) . ") for PR " . $row->pr_number . ".")
                        ->toast()
                        ->timer(5000)
                        ->position('top-end')
                        ->show();
                    return;
                }
            }
        }

        try {
            DB::beginTransaction();

            $pmu = Pmu::where('notice_of_award_number', $this->noticeOfAwardNumber)->firstOrFail();

            $data = [
                'po_date' => ($this->po_contract_number && $this->po_date) ? $this->po_date : null,
                'date_coa_stamped_received' => $this->date_coa_stamped_received ?: null,
                'date_po_receipt_by_supplier' => $this->date_po_receipt_by_supplier ?: null,
                'contract_amount' => $this->contract_amount !== '' ? $this->contract_amount : null,
                'po_contract_number' => $this->po_contract_number ?: null,
                'po_contract_number_link' => $this->po_contract_number_link ?: null,
                'ntp_link' => $this->ntp_link ?: null,
                'contract_signing_date' => $this->contract_signing_date ?: null,
                'notice_to_proceed_date' => $this->notice_to_proceed_date ?: null,
                'remarks' => $this->remarks ?: null,
            ];

            // Only touch manual_status if the user explicitly picked a value in the modal
            if ($this->manual_status !== '') {
                $data['manual_status'] = $this->manual_status === 'auto' ? null : $this->manual_status;
            }

            // Only users with update_pmu may modify rows already forwarded to supply
            $editableRowKeys = array_map('strval', $this->selectedItems);
            if (!auth()->user()->can('update_pmu')) {
                $forwardedRowKeys = PmuPo::where('pmu_id', $pmu->id)
                    ->where('manual_status', 'forwarded_to_supply')
                    ->whereIn('ref_id', $editableRowKeys)
                    ->pluck('ref_id')
                    ->map(fn($id) => (string) $id)
                    ->toArray();
                $editableRowKeys = array_values(array_diff($editableRowKeys, $forwardedRowKeys));
            }

            foreach ($editableRowKeys as $rowKey) {
                $pmuPo = PmuPo::where('ref_id', $rowKey)
                    ->where('pmu_id', $pmu->id)
                    ->first();

                if ($pmuPo) {
                    $rowData = $data;
                    // Protect forwarded_to_supply: never overwrite it via bulk edit unless explicitly set
                    if (
                        isset($rowData['manual_status']) &&
                        $rowData['manual_status'] === null &&
                        $pmuPo->manual_status === 'forwarded_to_supply'
                    ) {
                        unset($rowData['manual_status']);
                    }
                    // Update existing record to trigger audit events
                    $pmuPo->update($rowData);
                } else {
                    // Create new record to trigger audit events
                    PmuPo::create([
                        'ref_id' => $rowKey,
                        'pmu_id' => $pmu->id,
                        ...$data,
                    ]);
                }
            }

            DB::commit();

            $this->closeBulkEditModal();
            $this->clearSelections();

            LivewireAlert::title('Saved!')
                ->success()
                ->text('PO / Contract details saved for ' . count($editableRowKeys) . ' item(s).')
                ->toast()
                ->position('top-end')
                ->show();
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('PmuEditPage: Failed to update PO records', [
                'pmu_id' => $this->noticeOfAwardNumber,
                'error' => $e->getMessage(),
            ]);
            LivewireAlert::title('Error')
                ->error()
                ->text('Failed to save records. Please try again or contact support.')
                ->toast()
                ->position('top-end')
                ->show();
        }
    }

    public function save()
    {
        try {
            $this->validateOnly('date_forwarded', [
                'date_forwarded' => 'nullable|date',
            ]);
        } catch (ValidationException $e) {
            $firstError = collect($e->errors())->flatten()->first();
            LivewireAlert::title('Validation Error')
                ->warning()
                ->text($firstError)
                ->toast()
                ->position('top-end')
                ->show();
            return;
        }

        try {
            DB::beginTransaction();

            $record = Pmu::where('notice_of_award_number', $this->noticeOfAwardNumber)->firstOrFail();
            $record->update([
                'date_forwarded' => $this->date_forwarded ?: null,
            ]);

            DB::commit();

            session()->flash('alert', [
                'type' => 'success',
                'title' => 'Updated!',
                'message' => 'PMU record updated successfully.',
            ]);

            return redirect()->route('pmu.index');
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('PmuEditPage: Failed to update PMU record', [
                'pmu_id' => $this->noticeOfAwardNumber,
                'error' => $e->getMessage(),
            ]);
            LivewireAlert::title('Error')
                ->error()
                ->text('Failed to update record. Please try again or contact support.')
                ->toast()
                ->position('top-end')
                ->show();
        }
    }

    public function cancel()
    {
        return redirect()->route('pmu.index');
    }

    public function updatedSelectAll(bool $value): void
    {
        $pageIds = $this->buildEditPaginator()->items();
        $pageIds = collect($pageIds)
            ->pluck('rowKey')
            ->unique()->values()->map(fn($id) => (string) $id)->toArray();

        if ($value) {
            // Exclude already-forwarded rows from selection unless the user may edit them
            $forwardedRowKeys = [];
            if (!auth()->user()->can('update_pmu')) {
                $pmuRecord = Pmu::where('notice_of_award_number', $this->noticeOfAwardNumber)->first();
                $forwardedRowKeys = $pmuRecord
                    ? PmuPo::where('pmu_id', $pmuRecord->id)
                        ->where('manual_status', 'forwarded_to_supply')
                        ->whereIn('ref_id', $pageIds)
                        ->pluck('ref_id')
                        ->map(fn($id) => (string) $id)
                        ->toArray()
                    : [];
            }

            $selectableIds = array_values(array_diff($pageIds, $forwardedRowKeys));

            $this->selectedItems = array_values(array_unique(
                array_merge($this->selectedItems, $selectableIds)
            ));
        } else {
            $this->selectedItems = array_values(
                array_diff($this->selectedItems, $pageIds)
            );
        }
    }

    public function updatedSelectedItems(): void
    {
        $pageIds = $this->buildEditPaginator()->items();
        $pageIds = collect($pageIds)
            ->pluck('rowKey')
            ->unique()->values()->map(fn($id) => (string) $id)->toArray();
        $this->selectAll = !empty($pageIds) &&
            empty(array_diff($pageIds, array_unique($this->selectedItems)));
    }

    public function clearSelections(): void
    {
        $this->selectedItems = [];
        $this->selectAll = false;
    }

    public function openBulkEditModal(): void
    {
        if (empty($this->selectedItems)) {
            LivewireAlert::title('No items selected')
                ->warning()
                ->text('Please select at least one item.')
                ->toast()
                ->position('top-end')
                ->show();
            return;
        }

        $pmu = Pmu::where('notice_of_award_number', $this->noticeOfAwardNumber)->firstOrFail();

        // Fetch existing pmu_po rows for all selected rowKeys
        $existing = PmuPo::where('pmu_id', $pmu->id)
            ->whereIn('ref_id', $this->selectedItems)
            ->get()
            ->keyBy('ref_id');

        // Compute po_date_deadline_display: earliest PO Date Deadline among selected items (most restrictive constraint)
        $this->po_date_deadline_display = collect($this->selectedItems)
            ->map(fn($rowKey) => $existing->get($rowKey)?->po_date_deadline?->format(self::DATE_FORMAT))
            ->filter()
            ->sort()
            ->first();

        // Build a normalised snapshot for each selected item (null if no record)
        $snapshots = collect($this->selectedItems)->map(function ($rowKey) use ($existing) {
            $row = $existing->get($rowKey);
            return [
                'po_date' => $row && $row->po_date ? $row->po_date->format(self::DATE_FORMAT) : '',
                'date_coa_stamped_received' => $row && $row->date_coa_stamped_received ? $row->date_coa_stamped_received->format(self::DATE_FORMAT) : '',
                'date_po_receipt_by_supplier' => $row && $row->date_po_receipt_by_supplier ? $row->date_po_receipt_by_supplier->format(self::DATE_FORMAT) : '',
                'contract_amount' => $row ? (string) $row->contract_amount : '',
                'po_contract_number' => $row ? ($row->po_contract_number ?? '') : '',
                'po_contract_number_link' => $row ? ($row->po_contract_number_link ?? '') : '',
                'ntp_link' => $row ? ($row->ntp_link ?? '') : '',
                'contract_signing_date' => $row && $row->contract_signing_date ? $row->contract_signing_date->format(self::DATE_FORMAT) : '',
                'notice_to_proceed_date' => $row && $row->notice_to_proceed_date ? $row->notice_to_proceed_date->format(self::DATE_FORMAT) : '',
                'remarks' => $row ? ($row->remarks ?? '') : '',
            ];
        })->values();

        // If multiple items selected, ensure all share identical values
        if ($snapshots->unique()->count() > 1) {
            LivewireAlert::title('Conflicting Data')
                ->error()
                ->text('The selected items have different PO / Contract details. Please select items with identical data or edit them one at a time.')
                ->toast()
                ->timer(5000)
                ->position('top-end')
                ->show();
            return;
        }

        // Pre-fill form with common data; po_date falls back to PO Date Deadline if not yet set
        $data = $snapshots->first();
        $this->po_date = $data['po_date'] ?: ($this->po_date_deadline_display ?? '');
        $this->date_coa_stamped_received = $data['date_coa_stamped_received'];
        $this->date_po_receipt_by_supplier = $data['date_po_receipt_by_supplier'];
        $this->contract_amount = $data['contract_amount'];
        $this->po_contract_number = $data['po_contract_number'];
        $this->po_contract_number_link = $data['po_contract_number_link'];
        $this->ntp_link = $data['ntp_link'];
        $this->contract_signing_date = $data['contract_signing_date'];
        $this->notice_to_proceed_date = $data['notice_to_proceed_date'];
        $this->remarks = $data['remarks'];

        // Pre-fill manual_status: use common value if all selected items share the same status
        $statuses = collect($this->selectedItems)
            ->map(fn($rowKey) => $existing->get($rowKey)?->manual_status)
            ->values();
        $uniqueStatuses = $statuses->unique();
        $this->manual_status = $uniqueStatuses->count() === 1
            ? ($uniqueStatuses->first() === null ? 'auto' : $uniqueStatuses->first())
            : ''; // mixed → no change

        $this->showBulkEditModal = true;
    }

    public function closeBulkEditModal(): void
    {
        $this->showBulkEditModal = false;
        $this->po_date = '';
        $this->po_date_deadline_display = null;
        $this->date_coa_stamped_received = '';
        $this->date_po_receipt_by_supplier = '';
        $this->contract_amount = '';
        $this->po_contract_number = '';
        $this->po_contract_number_link = '';
        $this->ntp_link = '';
        $this->contract_signing_date = '';
        $this->notice_to_proceed_date = '';
        $this->remarks = '';
        $this->manual_status = '';
        $this->resetErrorBag();
    }

    public function setEditPage(int $page): void
    {
        $this->editPage = $page;
        $this->clearSelections();
    }

    public function updatingEditPerPage(): void
    {
        $this->editPage = 1;
        $this->clearSelections();
    }

    public function setManualStatus(int $pmuPoId, ?string $status): void
    {
        $allowed = [null, 'return_to_bac', 'for_end_user_compliance', 'forwarded_to_supply'];
        if (!in_array($status, $allowed, true)) {
            return;
        }
        $pmuPo = \App\Models\PmuPo::find($pmuPoId);
        if (!$pmuPo) {
            return;
        }
        $pmuPo->update(['manual_status' => $status]);
        $this->combinedRowsCache = null;
    }

    public function openEditRemarksModal(int $pmuPoId): void
    {
        $pmuPo = PmuPo::find($pmuPoId);
        if (!$pmuPo) {
            return;
        }
        $this->editRemarksPoId = $pmuPoId;
        $this->editRemarksValue = $pmuPo->remarks ?? '';
        $this->showEditRemarksModal = true;
    }

    public function confirmEditRemarks(): void
    {
        $this->validate([
            'editRemarksValue' => 'nullable|string|max:1000',
        ], [
            'editRemarksValue.max' => 'Remarks must not exceed 1000 characters.',
        ]);

        $pmuPo = PmuPo::find($this->editRemarksPoId);
        if (!$pmuPo) {
            LivewireAlert::title('Error')->error()->text('Record not found.')->toast()->position('top-end')->show();
            return;
        }

        $pmuPo->update(['remarks' => $this->editRemarksValue ?: null]);
        $this->combinedRowsCache = null;

        $this->closeEditRemarksModal();

        LivewireAlert::title('Remarks Saved!')
            ->success()
            ->text('Remarks have been updated.')
            ->toast()
            ->position('top-end')
            ->show();
    }

    public function closeEditRemarksModal(): void
    {
        $this->showEditRemarksModal = false;
        $this->editRemarksPoId = null;
        $this->editRemarksValue = '';
        $this->resetValidation(['editRemarksValue']);
    }

    // ─── NOA Remarks ───────────────────────────────────────────────────────────

    public function openEditNoaRemarksModal(): void
    {
        $this->editNoaRemarksValue = $this->noaRemarks ?? '';
        $this->showEditNoaRemarksModal = true;
    }

    public function confirmEditNoaRemarks(): void
    {
        $this->validate([
            'editNoaRemarksValue' => 'nullable|string|max:1000',
        ], [
            'editNoaRemarksValue.max' => 'Remarks must not exceed 1000 characters.',
        ]);

        $pmu = Pmu::where('notice_of_award_number', $this->noticeOfAwardNumber)->firstOrFail();
        $pmu->update(['received_remarks' => $this->editNoaRemarksValue ?: null]);
        $this->noaRemarks = $this->editNoaRemarksValue ?: null;

        $this->closeEditNoaRemarksModal();

        LivewireAlert::title('Remarks Saved!')
            ->success()
            ->text('NOA remarks have been updated.')
            ->toast()
            ->position('top-end')
            ->show();
    }

    public function closeEditNoaRemarksModal(): void
    {
        $this->showEditNoaRemarksModal = false;
        $this->editNoaRemarksValue = '';
        $this->resetValidation(['editNoaRemarksValue']);
    }

    private function fetchCombinedRows(): \Illuminate\Support\Collection
    {
        // Return cached results if already fetched in this request cycle
        if ($this->combinedRowsCache !== null) {
            return $this->combinedRowsCache;
        }

        $id = $this->noticeOfAwardNumber;

        $lots = Procurement::query()
            ->join('post_procurements', 'procurements.procID', '=', 'post_procurements.ref_id')
            ->join('pmus', 'post_procurements.notice_of_award_number', '=', 'pmus.notice_of_award_number')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'post_procurements.supplier_id')
            ->where('post_procurements.notice_of_award_number', $id)
            ->whereNull('pmus.deleted_at')
            ->whereHas('prLotPrstages', fn($q) => $q->where('pr_stage_id', 7))
            ->select(
                'procurements.procID',
                'procurements.pr_number',
                'procurements.procurement_program_project',
                'procurements.abc',
                'post_procurements.awarded_amount',
                'post_procurements.date_receipt_of_supplier_noa',
                'suppliers.name as supplier_name'
            )
            ->get()
            ->map(fn($p) => (object) [
                'rowKey' => $p->procID,
                'rowType' => 'lot',
                'procID' => $p->procID,
                'prItemID' => null,
                'pr_number' => $p->pr_number,
                'description' => $p->procurement_program_project,
                'abc' => $p->abc,
                'awarded_amount' => $p->awarded_amount,
                'supplier_name' => $p->supplier_name,
                'date_receipt_of_supplier_noa' => $p->date_receipt_of_supplier_noa,
            ])->toBase();

        // Cache the combined result before returning
        $items = DB::table('pr_items')
            ->join('procurements', 'procurements.procID', '=', 'pr_items.procID')
            ->join('post_procurements', 'post_procurements.ref_id', '=', 'pr_items.prItemID')
            ->join('pmus', 'post_procurements.notice_of_award_number', '=', 'pmus.notice_of_award_number')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'post_procurements.supplier_id')
            ->whereExists(fn($q) => $q->select(DB::raw(1))
                ->from('pr_item_prstage')
                ->whereColumn('pr_item_prstage.prItemID', 'pr_items.prItemID')
                ->where('pr_item_prstage.pr_stage_id', 7))
            ->where('post_procurements.notice_of_award_number', $id)
            ->whereNull('pmus.deleted_at')
            ->select(
                'procurements.procID',
                'procurements.pr_number',
                'pr_items.prItemID',
                'pr_items.description',
                'pr_items.amount',
                'post_procurements.awarded_amount',
                'post_procurements.date_receipt_of_supplier_noa',
                'suppliers.name as supplier_name'
            )
            ->orderBy('procurements.pr_number')
            ->orderBy('pr_items.item_no')
            ->get()
            ->map(fn($r) => (object) [
                'rowKey' => $r->prItemID,
                'rowType' => 'item',
                'procID' => $r->procID,
                'prItemID' => $r->prItemID,
                'pr_number' => $r->pr_number,
                'description' => $r->description,
                'abc' => $r->amount,
                'awarded_amount' => $r->awarded_amount,
                'supplier_name' => $r->supplier_name,
                'date_receipt_of_supplier_noa' => $r->date_receipt_of_supplier_noa,
            ]);

        return $this->combinedRowsCache = $lots->merge($items);
    }

    private function buildEditPaginator(): \Illuminate\Pagination\LengthAwarePaginator
    {
        $combined = $this->fetchCombinedRows();
        $total = $combined->count();
        $perPage = max(1, (int) $this->editPerPage);
        $page = max(1, (int) $this->editPage);

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $combined->forPage($page, $perPage)->values(),
            $total,
            $perPage,
            $page,
            ['pageName' => 'edit_page']
        );
    }

    public function isRowComplete(?PmuPo $pmuPo): bool
    {
        if (!$pmuPo) {
            return false;
        }

        // Check if all required fields are filled
        return !empty($pmuPo->po_date)
            && !empty($pmuPo->po_contract_number)
            && !empty($pmuPo->contract_amount)
            && !empty($pmuPo->contract_signing_date)
            && !empty($pmuPo->po_contract_number_link)
            && !empty($pmuPo->date_po_receipt_by_supplier)
            && !empty($pmuPo->date_coa_stamped_received);
    }

    public function getForwardedToSupplySummaryProperty(): array
    {
        $forwarded = 0;
        $pending = 0;
        $pmu = Pmu::where('notice_of_award_number', $this->noticeOfAwardNumber)->first();

        if (!$pmu) {
            return ['forwarded' => 0, 'pending' => 0];
        }

        foreach ($this->selectedItems as $rowKey) {
            $pmuPo = PmuPo::where('ref_id', $rowKey)
                ->where('pmu_id', $pmu->id)
                ->first();

            if ($pmuPo && !empty($pmuPo->forwarded_to_supply_at)) {
                $forwarded++;
            } else {
                $pending++;
            }
        }

        return ['forwarded' => $forwarded, 'pending' => $pending];
    }

    public function openForwardConfirm(): void
    {
        if (empty($this->selectedItems)) {
            LivewireAlert::title('No items selected')
                ->warning()
                ->text('Please select at least one item.')
                ->toast()
                ->position('top-end')
                ->show();
            return;
        }

        // Validate all selected items are complete
        $pmu = Pmu::where('notice_of_award_number', $this->noticeOfAwardNumber)->firstOrFail();
        $incompleteItems = [];

        foreach ($this->selectedItems as $rowKey) {
            $pmuPo = PmuPo::where('ref_id', $rowKey)
                ->where('pmu_id', $pmu->id)
                ->first();

            if (!$this->isRowComplete($pmuPo)) {
                $incompleteItems[] = $rowKey;
            }
        }

        if (!empty($incompleteItems)) {
            LivewireAlert::title('Incomplete Data')
                ->warning()
                ->text('Some selected items have incomplete data. Please fill all required fields before forwarding to supply.')
                ->toast()
                ->position('top-end')
                ->show();
            return;
        }

        // Set the datetime to current time (Manila timezone)
        $this->actualDateForwarded = now('Asia/Manila')->format('Y-m-d\TH:i');

        $this->showForwardConfirm = true;
    }

    public function closeForwardConfirm(): void
    {
        $this->showForwardConfirm = false;
        $this->actualDateForwarded = null;
    }

    public function forwardToSupply(): void
    {
        // Validate the datetime input
        $this->validate([
            'actualDateForwarded' => 'required|date',
        ], [
            'actualDateForwarded.required' => 'Please enter the actual date and time forwarded.',
            'actualDateForwarded.date' => 'Please enter a valid date and time.'
        ]);

        if (empty($this->selectedItems)) {
            LivewireAlert::title('No items selected')
                ->warning()
                ->text('Please select at least one item.')
                ->toast()
                ->position('top-end')
                ->show();
            return;
        }

        try {
            DB::beginTransaction();

            // Convert the Manila timezone datetime to UTC for storage
            $utcDateForwarded = Carbon::createFromFormat('Y-m-d\TH:i', $this->actualDateForwarded, 'Asia/Manila')
                ->setTimezone('UTC');

            $pmu = Pmu::where('notice_of_award_number', $this->noticeOfAwardNumber)->firstOrFail();
            $forwardedCount = 0;

            // Build a rowKey → rowType map from the combined rows
            $combinedRowMap = $this->fetchCombinedRows()->keyBy(fn($r) => (string) $r->rowKey);

            foreach ($this->selectedItems as $rowKey) {
                $pmuPo = PmuPo::where('ref_id', $rowKey)
                    ->where('pmu_id', $pmu->id)
                    ->first();

                if ($pmuPo && $this->isRowComplete($pmuPo)) {
                    // Mark as forwarded to supply with the specified datetime and update status
                    $pmuPo->update([
                        'forwarded_to_supply_at' => $utcDateForwarded,
                        'manual_status' => 'forwarded_to_supply',
                    ]);

                    // Create / update stage-14 record (mirrors stage-7 creation in forwardToPmu)
                    $rowMeta = $combinedRowMap->get((string) $rowKey);
                    if ($rowMeta && $rowMeta->rowType === 'item') {
                        $prItem = DB::table('pr_items')->where('prItemID', $rowKey)->first();
                        $latestStage = PrItemPrstage::where('prItemID', $rowKey)
                            ->orderByDesc('created_at')->orderByDesc('id')->first();

                        if ($latestStage && $latestStage->pr_stage_id == 14) {
                            $previousStage = PrItemPrstage::where('prItemID', $rowKey)
                                ->where('id', '<', $latestStage->id)
                                ->orderByDesc('created_at')->orderByDesc('id')->first();
                            $latestStage->update([
                                'stage_history' => $previousStage ? (string) $previousStage->pr_stage_id : null,
                                'actual_date_forwarded' => $utcDateForwarded,
                            ]);
                        } else {
                            PrItemPrstage::create([
                                'procID' => $prItem?->procID,
                                'prItemID' => $rowKey,
                                'pr_stage_id' => 14,
                                'stage_history' => $latestStage ? (string) $latestStage->pr_stage_id : null,
                                'actual_date_forwarded' => $utcDateForwarded,
                            ]);
                        }
                    } else {
                        // lot — ref_id is procID
                        $latestStage = PrLotPrstage::where('procID', $rowKey)
                            ->orderByDesc('created_at')->orderByDesc('id')->first();

                        if ($latestStage && $latestStage->pr_stage_id == 14) {
                            $previousStage = PrLotPrstage::where('procID', $rowKey)
                                ->where('id', '<', $latestStage->id)
                                ->orderByDesc('created_at')->orderByDesc('id')->first();
                            $latestStage->update([
                                'stage_history' => $previousStage ? (string) $previousStage->pr_stage_id : null,
                                'actual_date_forwarded' => $utcDateForwarded,
                            ]);
                        } else {
                            PrLotPrstage::create([
                                'procID' => $rowKey,
                                'pr_stage_id' => 14,
                                'stage_history' => $latestStage ? (string) $latestStage->pr_stage_id : null,
                                'actual_date_forwarded' => $utcDateForwarded,
                            ]);
                        }
                    }

                    // Upsert Supply record for tracking on the supply side
                    if (!empty($pmuPo->po_contract_number)) {
                        Supply::updateOrCreate(
                            ['po_contract_number' => $pmuPo->po_contract_number],
                            ['date_forwarded' => $utcDateForwarded]
                        );
                    }

                    $forwardedCount++;
                }
            }

            DB::commit();

            $this->closeForwardConfirm();
            $this->clearSelections();

            LivewireAlert::title('Forwarded to Supply!')
                ->success()
                ->text("{$forwardedCount} item(s) forwarded to supply successfully.")
                ->toast()
                ->position('top-end')
                ->show();
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('PmuEditPage: Failed to forward records to supply', [
                'pmu_id' => $this->noticeOfAwardNumber,
                'error' => $e->getMessage(),
            ]);
            LivewireAlert::title('Error')
                ->error()
                ->text('Failed to forward records. Please try again or contact support.')
                ->toast()
                ->position('top-end')
                ->show();
        }
    }

    public function render()
    {
        $pmuRecord = Pmu::where('notice_of_award_number', $this->noticeOfAwardNumber)->first();

        // Key pmu_po records by ref_id (procID) for fast per-row lookup in the blade
        $pmuPoByProcId = $pmuRecord
            ? PmuPo::where('pmu_id', $pmuRecord->id)->get()->keyBy('ref_id')
            : collect();

        // Build a map of which rows are complete
        $completedRows = collect();
        foreach ($pmuPoByProcId as $refId => $pmuPo) {
            $completedRows[$refId] = $this->isRowComplete($pmuPo);
        }

        $allSelectedComplete = !empty($this->selectedItems) && collect($this->selectedItems)
            ->every(fn($rowKey) => $completedRows[$rowKey] ?? false);

        // True when at least one selected item has NOT yet been forwarded to supply
        $anySelectedNotYetForwarded = !empty($this->selectedItems) && collect($this->selectedItems)
            ->some(fn($rowKey) => ($pmuPoByProcId->get($rowKey)?->manual_status ?? null) !== 'forwarded_to_supply');

        return view('livewire.pmu.pmu-edit-page', [
            'noticeOfAwardNumber' => $this->noticeOfAwardNumber,
            'pmuRecord' => $pmuRecord,
            'pmuPoByProcId' => $pmuPoByProcId,
            'completedRows' => $completedRows,
            'noticeOfAward' => $this->noticeOfAward,
            'editPaginator' => $this->buildEditPaginator(),
            'forwardedToSupplySummary' => $this->forwardedToSupplySummary,
            'allSelectedComplete' => $allSelectedComplete,
            'anySelectedNotYetForwarded' => $anySelectedNotYetForwarded,
        ])->layout('components.layouts.app');
    }
}
