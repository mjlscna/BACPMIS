<?php

namespace App\Livewire\Procurements;

use App\Models\Category;
use App\Models\ClusterCommittee;
use App\Models\Division;
use App\Models\EndUser;
use App\Models\FundSource;
use App\Models\ProvinceHuc;
use App\Models\VenueSpecific;
use Illuminate\Support\Facades\Validator;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Component;
use App\Models\Procurement;
use App\Models\MopLot;
use App\Models\MopItem;
use App\Models\PrLotPrstage;
use App\Models\PrItemPrstage;

class ProcurementCreatePage extends Component
{
    public string $procID = '';
    public $showEarlyPrompt = false;
    public bool $isPerItem = false;
    public $isAdvanceProcurement = false;
    public string $approved_ppmp = ''; // For radio buttons
    public string $otherPPMP = '';     // For the text input
    public string $app_updated = ''; // For radio buttons
    public string $otherAPP = '';

    public $venue_province_huc_id, $venue_specific_id, $category_venue;
    protected Category|null $categoryCache = null;
    public $form = [];
    public $showTable = true;
    public $page = 1;
    public $perPage = 10;

    public function mount()
    {
        $this->resetForm();

        $this->form['early_procurement'] = request()->query('early', false);

        $this->updatedFormProcurementType(
            $this->form['procurement_type']
        );
    }

    private function defaultForm()
    {
        return [
            'pr_number' => '',
            'procurement_program_project' => '',
            'procurement_type' => 'perLot',
            'items' => [],
            'date_receipt' => null,
            'dtrack_no' => '',
            'unicode' => '',
            'divisions_id' => null,
            'cluster_committees_id' => null,
            'category_id' => null,
            'category_type_id' => null,
            'bac_type_id' => null,
            'venue_specific_id' => null,
            'venue_province_huc_id' => null,
            'approved_ppmp' => false,
            'app_updated' => false,
            'immediate_date_needed' => '',
            'date_needed' => '',
            'end_users_id' => null,
            'early_procurement' => false,
            'fund_source_id' => null,
            'expense_class' => '',
            'abc' => 0,
            'abc_50k' => '50k or less',
        ];
    }

    private function resetForm()
    {
        $this->form = $this->defaultForm();

    }

    public function updated($propertyName, $value)
    {
        if ($propertyName === 'form.venue_province_huc_id' || $propertyName === 'form.venue_specific_id') {
            $this->updateCategoryVenue();
        }

        if ($propertyName === 'form.category_id') {
            $this->updatedFormCategoryId();
        }

        if ($propertyName === 'form.abc') {
            $cleaned = preg_replace('/[^0-9.]/', '', $value);
            $numericValue = floatval($cleaned);
            $this->form['abc_50k'] = $numericValue >= 50000 ? 'above 50k' : '50k or less';
        }
    }

    public function updatedFormProcurementType(string $value): void
    {
        // 1. Persist the new mode
        $this->form['procurement_type'] = $value;

        // 2. If switching to perLot, clear all items
        if ($value === 'perLot') {
            $this->form['items'] = [];
            return;
        }

        // 3. If switching to perItem and no items exist, seed one blank row
        if (empty($this->form['items'])) {
            $this->form['items'][] = [
                'uid' => uniqid(),
                'item_no' => 1,
                'description' => null,
                'amount' => 0,
            ];
        }


        // 4. Ensure item_no sequence is correct
        $this->reorderItemNumbers();
    }

    public function updatedFormCategoryId()
    {
        $this->categoryCache = Category::with(['categoryType', 'bacType'])
            ->find($this->form['category_id']);

        if ($this->categoryCache) {
            $this->form['category_type'] = $this->categoryCache->categoryType?->category_type ?? null;
            $this->form['rbac_sbac'] = $this->categoryCache->bacType?->abbreviation ?? null;
            $this->form['category_type_id'] = $this->categoryCache->category_type_id;
            $this->form['bac_type_id'] = $this->categoryCache->bac_type_id;
        } else {
            $this->form['category_type'] = null;
            $this->form['rbac_sbac'] = null;
            $this->form['category_type_id'] = null;
            $this->form['bac_type_id'] = null;
        }
    }

    public function updateCategoryVenue()
    {
        if (!empty($this->form['category_id']) && !empty($this->form['venue_specific_id'])) {
            $category = $this->categoryCache ?? Category::find($this->form['category_id']);

            $venueSpecific = VenueSpecific::find($this->form['venue_specific_id']);

            $provinceName = ''; // Default to empty
            $venueProvinceHUC = null;

            if (!empty($this->form['venue_province_huc_id'])) {
                $venueProvinceHUC = ProvinceHuc::find($this->form['venue_province_huc_id']);
                $provinceName = $venueProvinceHUC?->province_huc;
            }

            if ($category && $venueSpecific) {
                $provinceText = $provinceName ? ', ' . $provinceName : ''; // 👈 conditionally prepend comma
                $this->form['category_venue'] = $category->category . ' - ' . $venueSpecific->name . $provinceText;
            } else {
                $this->form['category_venue'] = null;
            }
        } else {
            $this->form['category_venue'] = null;
        }


        logger('Updated category_venue to: ' . $this->form['category_venue']);
    }

    public function save()
    {
        // Normalize binary and numeric fields
        $this->form['approved_ppmp'] = (bool) $this->form['approved_ppmp'];
        $this->form['app_updated'] = (bool) $this->form['app_updated'];
        $this->form['abc'] = floatval(preg_replace('/[^0-9.]/', '', $this->form['abc']));

        if (!in_array($this->form['procurement_type'], ['perItem', 'perLot'])) {
            $this->form['procurement_type'] = 'perLot';
        }

        // --- 1. Main form validation ---

        $validator = Validator::make($this->form, [
            'pr_number' => ['regex:/^\d{4}-\d{4}$/', 'unique:procurements,pr_number'],
            'procurement_program_project' => 'required|string|max:255',
            'divisions_id' => 'required|integer|exists:divisions,id',
            'cluster_committees_id' => 'required|integer|exists:cluster_committees,id',
            'category_id' => 'required|integer|exists:categories,id',
            'fund_source_id' => 'required|integer|exists:fund_sources,id',
            'abc' => 'required|numeric|min:1',
            'procurement_type' => 'required|in:perItem,perLot',
            'date_receipt' => 'required|date',
            'unicode' => 'required|string|max:255',
            'immediate_date_needed' => 'required|string|max:255',
            'date_needed' => 'required|string|max:255',
            'end_users_id' => 'required|integer|exists:end_users,id',

        ], [], [
            'pr_number' => 'PR Number',
            'procurement_type' => 'Procurement Type',
            'procurement_program_project' => 'Procurement Project',
            'divisions_id' => 'Division',
            'cluster_committees_id' => 'Cluster Committee',
            'category_id' => 'Category',
            'fund_source_id' => 'Fund Source',
            'abc' => 'ABC',
            'date_receipt' => 'Date Receipt',
            'unicode' => 'UniCode',
            'immediate_date_needed' => 'Immediate Date Needed',
            'date_needed' => 'Date Needed',
            'end_users_id' => 'PMO/End-User',
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

        // --- 2. Extra validation for items if perItem ---
        if ($this->form['procurement_type'] === 'perItem' && !empty($this->form['items'])) {
            $itemValidator = Validator::make($this->form, [
                'items.*.item_no' => 'required',
                'items.*.description' => 'required',
                'items.*.amount' => 'required|numeric|min:0',
            ], [
                'items.*.item_no.required' => 'Item No. is Empty',
                'items.*.description.required' => 'Item Description is Empty',
                'items.*.amount.required' => 'Item Amount is required',
            ]);

            if ($itemValidator->fails()) {
                LivewireAlert::title('ERROR!')
                    ->error()
                    ->text(collect($itemValidator->errors()->all())->implode("\n"))
                    ->toast()
                    ->position('top-end')
                    ->show();
                return; // 🔥 stop before creating procurement
            }
        }

        // Nullify optional fields
        foreach ([
            'dtrack_no',
            'venue_specific_id',
            'venue_province_huc_id',
            'expense_class'
        ] as $field) {
            $this->form[$field] = empty($this->form[$field]) ? null : $this->form[$field];
        }

        // Hydrate category relationships
        $category = Category::with(['categoryType', 'bacType'])->find($this->form['category_id']);
        $this->form['category_type_id'] = $category?->category_type_id ?? null;
        $this->form['bac_type_id'] = $category?->bac_type_id ?? null;
        $this->form['category_type'] = $category?->categoryType?->category_type ?? null;
        $this->form['rbac_sbac'] = $category?->bacType?->abbreviation ?? null;

        $this->updateCategoryVenue();

        if (empty($this->form['pr_number'])) {
            $this->form['pr_number'] = Procurement::generatePrNumber($this->form['early_procurement'] ?? false);
        }

        $this->procID = 'BAC' . $this->form['pr_number'] . now()->format('YmdHis');

        // --- Create Procurement ---
        $procurement = Procurement::create(array_merge($this->form, [
            'procID' => $this->procID,
            'early_procurement' => $this->form['early_procurement'] ?? null,
            'abc_50k' => $this->form['abc'] >= 50000 ? 'above 50k' : '50k or less',
        ]));

        // --- If perItem, save items + related mop_item & pr_item_prstage ---
        if ($this->form['procurement_type'] === 'perItem' && !empty($this->form['items'])) {
            foreach (array_reverse($this->form['items']) as $index => $item) {
                $prItemID = "{$this->procID}-" . ($index + 1);

                $prItem = $procurement->pr_items()->create([
                    'procID' => $this->procID,
                    'prItemID' => $prItemID,
                    'item_no' => $item['item_no'],
                    'description' => $item['description'],
                ]);

                // default MopItem (mode_of_procurement_id = 1)
                MopItem::create([
                    'procID' => $this->procID,
                    'prItemID' => $prItem->prItemID,
                    'uid' => 'MOP-' . 1 . '-' . 1,
                    'mode_of_procurement_id' => 1,
                    'mode_order' => 1,
                ]);

                // default PrItemPrstage (stage_id = 1)
                PrItemPrstage::create([
                    'procID' => $this->procID,
                    'prItemID' => $prItem->prItemID,
                    'pr_stage_id' => 1,
                    'stage_history' => "1",
                ]);
            }
        }

        // --- If perLot, save defaults for the whole lot ---
        if ($this->form['procurement_type'] === 'perLot') {
            MopLot::create([
                'procID' => $this->procID,
                'uid' => 'MOP-' . 1 . '-' . 1,
                'mode_of_procurement_id' => 1,
                'mode_order' => 1,
            ]);

            PrLotPrstage::create([
                'procID' => $this->procID,
                'pr_stage_id' => 1,
                'stage_history' => "1",
            ]);
        }

        LivewireAlert::title('Saved!')
            ->success()
            ->toast()
            ->position('top-end')
            ->show();
    }

    public function getPaginatedItemsProperty()
    {
        $items = $this->form['items'] ?? [];
        $offset = ($this->page - 1) * $this->perPage;
        return array_slice($items, $offset, $this->perPage);
    }

    public function updatingPage()
    {
        // Reset if page goes out of range when items are reduced
        $items = $this->form['items'] ?? [];
        $totalPages = max(1, ceil(count($items) / $this->perPage));

        if ($this->page > $totalPages) {
            $this->page = $totalPages;
        }
    }
    public function addItem(): void
    {
        // Add new item at top
        array_unshift($this->form['items'], [
            'uid' => uniqid(),       // unique id for Livewire
            'item_no' => 0,          // placeholder, will be recalculated
            'description' => '',
            'amount' => 0.00,
        ]);

        $this->reorderItemNumbers();
        $this->updateAbcFromItems();
    }

    public function removeItem(int $index): void
    {
        array_splice($this->form['items'], $index, 1);
        $this->reorderItemNumbers();
        $this->updateAbcFromItems();
    }

    private function reorderItemNumbers(): void
    {
        $total = count($this->form['items']);

        foreach ($this->form['items'] as $i => &$item) {
            $item['item_no'] = $total - $i; // top = highest, bottom = 1
        }
    }

    public function updatedFormItems($value, $key)
    {
        // Only handle amount updates
        if (str_contains($key, '.amount')) {
            $cleaned = preg_replace('/[^0-9.]/', '', $value);
            $numericValue = floatval($cleaned);

            data_set($this->form, $key, number_format($numericValue, 2, '.', ''));

            $this->updateAbcFromItems();
        }
    }

    public function updateAbcFromItems(): void
    {
        if ($this->form['procurement_type'] === 'perItem') {
            $this->form['abc'] = collect($this->form['items'])
                ->sum(fn($item) => (float) ($item['amount'] ?? 0));

            $this->form['abc_50k'] = $this->form['abc'] >= 50000 ? 'above 50k' : '50k or less';
        }
    }


    public function render()
    {
        return view('livewire.procurements.procurement-create-page', [
            'divisions' => Division::all(),
            'categories' => Category::with(['categoryType', 'bacType'])->get(),
            'clusterCommittees' => ClusterCommittee::all(),
            'venueSpecifics' => VenueSpecific::all(),
            'venueProvinces' => ProvinceHuc::all(),
            'endUsers' => EndUser::all(),
            'fundSources' => FundSource::all(),
            'form' => $this->form,
        ]);
    }
}
