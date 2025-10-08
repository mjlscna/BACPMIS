<?php

namespace App\Livewire\Procurements;

use App\Models\Category;
use App\Models\ClusterCommittee;
use App\Models\Division;
use App\Models\EndUser;
use App\Models\FundSource;
use App\Models\MopItem;
use App\Models\PrItemPrstage;
use App\Models\ProvinceHuc;
use App\Models\VenueSpecific;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Component;
use Illuminate\Validation\Rule;
use App\Models\Procurement;

class ProcurementEditPage extends Component
{
    public Procurement $procurement;
    public $form = [];
    protected ?Category $categoryCache = null;
    public $showTable = true;
    public $textareaRows = 1;
    public $page = 1;
    public $perPage = 10;
    public string $procID = '';

    public function mount(Procurement $procurement)
    {
        $procurement->load('pr_items');
        $this->procurement = $procurement;
        $this->procID = $procurement->procID ?? '';

        $this->form = $procurement->toArray();

        // Normalize procurement_type default
        if (!in_array($this->form['procurement_type'] ?? null, ['perItem', 'perLot'])) {
            $this->form['procurement_type'] = 'perLot';
        }

        // Load items (reverse/sort to match create visual order) and keep prItemID
        if ($this->form['procurement_type'] === 'perItem') {
            $this->form['items'] = $procurement->pr_items
                ->sortByDesc('id')
                ->map(fn($item) => [
                    'prItemID' => $item->prItemID,
                    'item_no' => $item->item_no,
                    'description' => $item->description,
                    'amount' => number_format((float) $item->amount, 2, '.', ''),
                ])
                ->values()
                ->toArray();

            if (empty($this->form['items'])) {
                $this->addItem();
            }
        } else {
            $this->form['items'] = $this->form['items'] ?? [];
        }
        $this->updatedFormCategoryId();
        $this->updateCategoryVenue();
        $this->updateAbcFromItems();
        if ($procurement) {
            $this->form['procurement_program_project'] = $procurement->procurement_program_project;
            $this->procID = $procurement->procID;

            // Dynamically calculate rows based on text length or line breaks
            $text = trim($procurement->procurement_program_project ?? '');

            // Count actual new lines
            $lineCount = substr_count($text, "\n") + 1;

            // Estimate wrapped lines more conservatively
            $approxExtraLines = ceil(strlen($text) / 150); // ← increased divisor from 100 → 150
            // That means: only very long text adds rows

            // Combine both counts, ensure at least 1 row
            $this->textareaRows = max($lineCount, $approxExtraLines, 1);
        } else {
            $this->form['procurement_program_project'] = '';
            $this->procID = null;
            $this->textareaRows = 1;
        }
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

        if ($propertyName === 'form.procurement_type') {
            // value will be 'perItem' or 'perLot'
            $this->updatedFormProcurementType($value);
        }
    }

    public function updatedFormCategoryId()
    {
        $this->categoryCache = Category::with(['categoryType', 'bacType'])
            ->find($this->form['category_id'] ?? null);

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

            $provinceName = '';
            if (!empty($this->form['venue_province_huc_id'])) {
                $venueProvinceHUC = ProvinceHuc::find($this->form['venue_province_huc_id']);
                $provinceName = $venueProvinceHUC?->province_huc;
            }

            if ($category && $venueSpecific) {
                $provinceText = $provinceName ? ', ' . $provinceName : '';
                // <-- use ->name to match your original create page
                $this->form['category_venue'] = $category->category . ' - ' . $venueSpecific->name . $provinceText;
            } else {
                $this->form['category_venue'] = null;
            }
        } else {
            $this->form['category_venue'] = null;
        }

        logger('Updated category_venue to: ' . $this->form['category_venue']);
    }

    public function updatedFormProcurementType(string $value): void
    {
        $this->form['procurement_type'] = $value;

        if ($value === 'perLot') {
            $this->form['items'] = [];
            return;
        }

        if (empty($this->form['items'])) {
            $this->form['items'][] = [
                'uid' => uniqid(),
                'item_no' => 1,
                'description' => null,
                'amount' => 0,
            ];
        }

        $this->reorderItemNumbers();
    }

    public function addItem(): void
    {
        $this->form['items'][] = [
            'uid' => Str::uuid()->toString(),
            'item_no' => 0, // Will be set by reorderItemNumbers
            'description' => '',
            'amount' => 0.00,
        ];

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
        $items = $this->form['items'] ?? [];

        foreach ($items as $i => &$item) {
            $item['item_no'] = $i + 1;
        }

        $this->form['items'] = $items;
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
        if (($this->form['procurement_type'] ?? '') === 'perItem') {
            $this->form['abc'] = collect($this->form['items'])
                ->sum(fn($item) => (float) ($item['amount'] ?? 0));

            $this->form['abc_50k'] = $this->form['abc'] >= 50000 ? 'above 50k' : '50k or less';
        }
    }

    public function save()
    {
        // Normalize binary and numeric fields
        $this->form['approved_ppmp'] = (bool) ($this->form['approved_ppmp'] ?? false);
        $this->form['app_updated'] = (bool) ($this->form['app_updated'] ?? false);
        $this->form['abc'] = floatval(preg_replace('/[^0-9.]/', '', $this->form['abc'] ?? 0));

        if (!in_array($this->form['procurement_type'] ?? '', ['perItem', 'perLot'])) {
            $this->form['procurement_type'] = 'perLot';
        }

        // --- 1. Main form validation ---
        $validator = Validator::make($this->form, [
            'pr_number' => [
                'regex:/^\d{4}-\d{4}$/',
                Rule::unique('procurements', 'pr_number')->ignore($this->procurement->id),
            ],
            'procurement_program_project' => 'required|string|max:1000',
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

        // --- 2. Item validation if perItem ---
        if (($this->form['procurement_type'] ?? '') === 'perItem' && !empty($this->form['items'])) {
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
                return;
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

        if (empty($this->procID)) {
            $this->procID = $this->procurement->procID ?? 'BAC' . $this->form['pr_number'] . now()->format('YmdHis');
        }

        // --- Update procurement record ---
        $this->procurement->update(array_merge($this->form, [
            'procID' => $this->procID,
            'early_procurement' => $this->form['early_procurement'] ?? null,
            'abc_50k' => $this->form['abc'] >= 50000 ? 'above 50k' : '50k or less',
        ]));

        // --- Save items (perItem) ---
        if (($this->form['procurement_type'] ?? '') === 'perItem') {
            $this->reorderItemNumbers();

            $existingItems = $this->procurement->pr_items()->pluck('id', 'prItemID')->toArray();
            $submittedPrItemIDs = [];

            foreach (array_reverse($this->form['items']) as $index => $item) {
                $prItemID = $item['prItemID'] ?? "{$this->procID}-" . ($index + 1);
                $submittedPrItemIDs[] = $prItemID;

                // Update or create the PR item
                $prItem = $this->procurement->pr_items()->updateOrCreate(
                    ['prItemID' => $prItemID],
                    [
                        'procID' => $this->procID,
                        'item_no' => $item['item_no'],
                        'description' => $item['description'],
                        'amount' => floatval(preg_replace('/[^0-9.]/', '', $item['amount'] ?? 0)),
                    ]
                );

                // Only if newly created
                if ($prItem->wasRecentlyCreated) {
                    MopItem::create([
                        'procID' => $this->procID,
                        'prItemID' => $prItem->prItemID,
                        'uid' => 'MOP-' . 1 . '-' . 1,
                        'mode_of_procurement_id' => 1,
                        'mode_order' => 1,
                    ]);

                    PrItemPrstage::create([
                        'procID' => $this->procID,
                        'prItemID' => $prItem->prItemID,
                        'pr_stage_id' => 1,
                        'stage_history' => "1",
                    ]);
                }
            }


            // Delete items that are no longer in the form
            $itemsToDelete = array_diff(array_keys($existingItems), $submittedPrItemIDs);
            if (!empty($itemsToDelete)) {
                $this->procurement->pr_items()->whereIn('prItemID', $itemsToDelete)->delete();
            }
        } else {
            // If switching to perLot, remove all items
            $this->procurement->pr_items()->delete();
        }

        LivewireAlert::title('Updated!')
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

    public function render()
    {
        return view('livewire.procurements.procurement-edit-page', [
            'divisions' => Division::all(),
            'categories' => Category::with(['categoryType', 'bacType'])->get(),
            'clusterCommittees' => ClusterCommittee::all(),
            'venueSpecifics' => VenueSpecific::all(),     // <-- restored original names
            'venueProvinces' => ProvinceHuc::all(),
            'endUsers' => EndUser::all(),
            'fundSources' => FundSource::all(),
            'form' => $this->form,
        ]);
    }
}
