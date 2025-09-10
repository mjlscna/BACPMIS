<?php

namespace App\Livewire\Procurement;

use App\Models\Category;
use App\Models\ClusterCommittee;
use App\Models\Division;
use App\Models\EndUser;
use App\Models\FundSource;
use App\Models\ProvinceHuc;
use App\Models\VenueSpecific;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Component;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\Procurement;

class EditPage extends Component
{
    public Procurement $procurement;
    public $form = [];
    protected ?Category $categoryCache = null;
    public $showTable = true;
    public $page = 1;
    public $perPage = 5;
    public function mount(Procurement $procurement)
    {
        $procurement->load('pr_items');
        $this->procurement = $procurement;

        $this->form = $procurement->toArray();

        // Normalize procurement_type default
        if (!in_array($this->form['procurement_type'], ['perItem', 'perLot'])) {
            $this->form['procurement_type'] = 'perLot';
        }

        // 🔁 Reverse items to match create visual order
        if ($this->form['procurement_type'] === 'perItem') {
            $this->form['items'] = $procurement->pr_items
                ->sortByDesc('id') // or use prItemID if needed
                ->map(fn($item) => [
                    'item_no' => $item->item_no,
                    'description' => $item->description,
                ])
                ->values()
                ->toArray();

            // If no items, add one empty row
            if (empty($this->form['items'])) {
                $this->addItem();
            }
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
            $this->form['procurement_type'] = $value ? 'perItem' : 'perLot';

            if ($this->form['procurement_type'] === 'perLot') {
                $this->form['items'] = [];
            } elseif (empty($this->form['items'])) {
                $this->addItem();
            }
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
                'item_no' => null,
                'description' => null,
            ];
        }
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

            $provinceName = '';
            if (!empty($this->form['venue_province_huc_id'])) {
                $venueProvinceHUC = ProvinceHuc::find($this->form['venue_province_huc_id']);
                $provinceName = $venueProvinceHUC?->province_huc;
            }

            if ($category && $venueSpecific) {
                $provinceText = $provinceName ? ', ' . $provinceName : '';
                $this->form['category_venue'] = $category->category . ' - ' . $venueSpecific->name . $provinceText;
            } else {
                $this->form['category_venue'] = null;
            }
        } else {
            $this->form['category_venue'] = null;
        }
    }

    public function addItem()
    {
        $this->form['items'] = array_merge([
            [
                'item_no' => '',
                'description' => '',
            ]
        ], $this->form['items'] ?? []);
    }

    public function save()
    {
        // --- 1. Normalize binary and numeric fields ---
        $this->form['approved_ppmp'] = (bool) ($this->form['approved_ppmp'] ?? false);
        $this->form['app_updated'] = (bool) ($this->form['app_updated'] ?? false);
        $this->form['abc'] = floatval(preg_replace('/[^0-9.]/', '', $this->form['abc'] ?? 0));

        // Normalize procurement_type
        if (!in_array($this->form['procurement_type'] ?? '', ['perItem', 'perLot'])) {
            $this->form['procurement_type'] = 'perLot';
        }

        // --- 2. Main form validation ---
        $validator = Validator::make($this->form, [
            'pr_number' => [
                'regex:/^\d{4}-\d{4}$/',
                Rule::unique('procurements', 'pr_number')->ignore($this->procurement->id),
            ],
            'procurement_program_project' => 'required|string|max:255',
            'dtrack_no' => 'required|string|max:50',
            'divisions_id' => 'required|integer|exists:divisions,id',
            'cluster_committees_id' => 'required|integer|exists:cluster_committees,id',
            'category_id' => 'required|integer|exists:categories,id',
            'fund_source_id' => 'required|integer|exists:fund_sources,id',
            'abc' => 'required|numeric|min:1',
            'procurement_type' => 'required|in:perItem,perLot',
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

        // --- 3. Item validation if perItem ---
        if (($this->form['procurement_type'] ?? '') === 'perItem' && !empty($this->form['items'])) {
            $itemValidator = Validator::make($this->form, [
                'items.*.item_no' => 'required',
                'items.*.description' => 'required',
            ], [
                'items.*.item_no.required' => 'Item No. is Empty',
                'items.*.description.required' => 'Item Description is Empty',
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

        // --- 4. Nullify optional fields ---
        foreach ([
            'date_receipt',
            'unicode',
            'venue_specific_id',
            'venue_province_huc_id',
            'immediate_date_needed',
            'date_needed',
            'end_users_id',
            'expense_class'
        ] as $field) {
            $this->form[$field] = empty($this->form[$field]) ? null : $this->form[$field];
        }

        // --- 5. Hydrate category relationships ---
        $category = Category::with(['categoryType', 'bacType'])->find($this->form['category_id']);
        $this->form['category_type_id'] = $category?->category_type_id ?? null;
        $this->form['bac_type_id'] = $category?->bac_type_id ?? null;
        $this->form['category_type'] = $category?->categoryType?->category_type ?? null;
        $this->form['rbac_sbac'] = $category?->bacType?->abbreviation ?? null;

        $this->updateCategoryVenue();

        // --- 6. Generate procID if missing ---
        if (empty($this->procID)) {
            $this->procID = $this->procurement->procID ?? 'BAC' . $this->form['pr_number'] . now()->format('YmdHis');
        }

        // --- 7. Update procurement record ---
        $this->procurement->update(array_merge($this->form, [
            'procID' => $this->procID,
            'early_procurement' => $this->form['early_procurement'] ?? null,
            'abc_50k' => $this->form['abc'] >= 50000 ? 'above 50k' : '50k or less',
        ]));

        // --- 8. Save items (perItem) ---
        if (($this->form['procurement_type'] ?? '') === 'perItem') {
            $existingItems = $this->procurement->pr_items()->pluck('id', 'prItemID')->toArray();
            $submittedPrItemIDs = [];

            foreach (array_reverse($this->form['items']) as $index => $item) {
                $prItemID = $item['prItemID'] ?? "{$this->procID}-" . ($index + 1);
                $submittedPrItemIDs[] = $prItemID;

                $this->procurement->pr_items()->updateOrCreate(
                    ['prItemID' => $prItemID],
                    [
                        'procID' => $this->procID,
                        'item_no' => $item['item_no'],
                        'description' => $item['description'],
                    ]
                );
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

        // --- 9. Success alert ---
        LivewireAlert::title('Updated!')
            ->success()
            ->toast()
            ->position('top-end')
            ->show();
    }


    public function render()
    {
        return view('livewire.procurement.edit', [
            'divisions' => Division::all(),
            'categories' => Category::with(['categoryType', 'bacType'])->get(),
            'clusterCommittees' => ClusterCommittee::all(),
            'venueSpecifics' => VenueSpecific::all(),
            'venueProvinces' => ProvinceHuc::all(),
            'endUsers' => EndUser::all(),
            'fundSources' => FundSource::all(),
            'form' => $this->form, // 👈 added so Blade gets correct data
        ]);
    }
}
