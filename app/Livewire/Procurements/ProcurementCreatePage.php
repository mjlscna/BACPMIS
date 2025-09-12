<?php

namespace App\Livewire\Procurements;

use App\Models\Category;
use App\Models\ClusterCommittee;
use App\Models\Division;
use App\Models\EndUser;
use App\Models\FundSource;
use App\Models\ProvinceHuc;
use App\Models\VenueSpecific;
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
    public $perPage = 5;

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
            'abc' => '',
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
    public function addItem()
    {
        // Prepend new item at the beginning
        $this->form['items'] = array_merge([
            [
                'item_no' => '',
                'description' => '',
            ]
        ], $this->form['items'] ?? []);
    }

    public function save()
    {
        // --- existing normalization, validation, etc. ---
        $this->form['approved_ppmp'] = (bool) $this->form['approved_ppmp'];
        $this->form['app_updated'] = (bool) $this->form['app_updated'];
        $this->form['abc'] = floatval(preg_replace('/[^0-9.]/', '', $this->form['abc']));

        if (!in_array($this->form['procurement_type'], ['perItem', 'perLot'])) {
            $this->form['procurement_type'] = 'perLot';
        }

        // existing validation blocks ...

        // Nullify optional fields
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

    public function removeItem($index)
    {
        if (isset($this->form['items'][$index])) {
            unset($this->form['items'][$index]);
            $this->form['items'] = array_values($this->form['items']); // reindex
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
