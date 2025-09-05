<?php

namespace App\Livewire\Procurement;

use App\Models\Category;
use App\Models\ClusterCommittee;
use App\Models\Division;
use App\Models\EndUser;
use App\Models\FundSource;
use App\Models\ProvinceHuc;
use App\Models\Supplier;
use App\Models\VenueSpecific;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Component;
use Illuminate\Support\Facades\Validator;
use App\Models\Procurement;

class CreatePage extends Component
{
    public string $procID = '';
    public $showEarlyPrompt = false;
    public $isAdvanceProcurement = false;
    public string $approved_ppmp = ''; // For radio buttons
    public string $otherPPMP = '';     // For the text input
    public string $app_updated = ''; // For radio buttons
    public string $otherAPP = '';

    public $venue_province_huc_id, $venue_specific_id, $category_venue;
    protected Category|null $categoryCache = null;
    public $form = [];
    public $showTable = true; // default visible

    public function mount()
    {
        $this->resetForm();
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

    public function updated($value)
    {
        if ($value === 'form.venue_province_huc_id' || $value === 'form.venue_specific_id') {
            $this->updateCategoryVenue();
        }

        if ($value === 'form.category_id') {
            $this->updatedFormCategoryId();
        }

        if ($value === 'form.abc') {
            $cleaned = preg_replace('/[^0-9.]/', '', $this->form['abc']);
            $numericValue = floatval($cleaned);
            $this->form['abc_50k'] = $numericValue >= 50000 ? 'above 50k' : '50k or less';
        }

        if ($value === 'form.procurement_type') {
            $isPerItem = $this->form['procurement_type'] === 'perItem';

            $this->form['items'] = $isPerItem ? ($this->form['items'] ?: [$this->addItem()]) : [];
            $this->form['perItems'] = $isPerItem ? [] : $this->form['perItems'];
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
        $this->form['items'][] = [
            'item_no' => '',
            'description' => '',
        ];
    }


public function save()
{
    // Normalize binary and numeric fields
    $this->form['approved_ppmp'] = (bool) $this->form['approved_ppmp'];
    $this->form['app_updated'] = (bool) $this->form['app_updated'];
    $this->form['abc'] = floatval(preg_replace('/[^0-9.]/', '', $this->form['abc']));

    // Normalize procurement_type
    if (!in_array($this->form['procurement_type'], ['perItem', 'perLot'])) {
        $this->form['procurement_type'] = 'perLot';
    }

    // Manual validation for full control
    $validator = Validator::make($this->form, [
        'pr_number' => ['regex:/^\d{4}-\d{4}$/', 'unique:procurements,pr_number'],
        'procurement_program_project' => 'required|string|max:255',
        'dtrack_no' => 'required|string|max:50',
        'divisions_id' => 'required|integer|exists:divisions,id',
        'cluster_committees_id' => 'required|integer|exists:cluster_committees,id',
        'category_id' => 'required|integer|exists:categories,id',
        'fund_source_id' => 'required|integer|exists:fund_sources,id',
        'abc' => 'required|numeric|min:1',
        'procurement_type' => 'required|in:perItem,perLot',
    ], [], [
        'pr_number' => 'PR Number',
        'procurement_type' => 'Procurement Type',
        'procurement_program_project' => 'Procurement Project',
        'dtrack_no' => 'DTrack No.',
        'divisions_id' => 'Division',
        'cluster_committees_id' => 'Cluster Committee',
        'category_id' => 'Category',
        'fund_source_id' => 'Fund Source',
        'abc' => 'ABC',
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

    // Generate PR number if missing
    if (empty($this->form['pr_number'])) {
        $this->form['pr_number'] = Procurement::generatePrNumber($this->form['early_procurement'] ?? false);
    }

    // Generate unique procID
    $this->procID = 'BAC' . $this->form['pr_number'] . now()->format('YmdHis');

    // Create procurement record
    $procurement = Procurement::create(array_merge($this->form, [
        'procID' => $this->procID,
        'early_procurement' => $this->form['early_procurement'] ?? null,
        'abc_50k' => $this->form['abc'] >= 50000 ? 'above 50k' : '50k or less',
    ]));

    LivewireAlert::title('Saved!')
        ->success()
        ->toast()
        ->position('top-end')
        ->show();
}


    public function render()
    {
        return view('livewire.procurement.create', [
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
