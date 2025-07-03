<?php
namespace App\Livewire;

use App\Models\Venue;
use Livewire\Component;
use Livewire\WithPagination;

class VenuePage extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $showCreateModal = false;
    public $editingId = null;
    public $form = [
        'venue' => '',
        'slug' => '',
        'is_active' => true,
    ];

    protected $rules = [
        'form.venue' => 'required|string|max:255',
        'form.slug' => 'required|string|max:255|unique:venues,slug',
        'form.is_active' => 'boolean',
    ];

    public function render()
    {
        $query = Venue::query();
        if ($this->search) {
            $query->where('name', 'like', "%{$this->search}%")
                ->orWhere('location', 'like', "%{$this->search}%");
        }
        return view('livewire.venue-page', [
            'venues' => $query->paginate($this->perPage),
        ]);
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->editingId = null;
        $this->showCreateModal = true;
    }

    public function openEditModal($id)
    {
        $venue = Venue::findOrFail($id);
        $this->form = [
            'venue' => $venue->venue,
            'slug' => $venue->slug,
            'is_active' => (bool) $venue->is_active,
        ];
        $this->editingId = $id;
        $this->showCreateModal = true;
    }

    public function save()
    {
        $rules = $this->rules;
        if ($this->editingId) {
            // Allow slug to be the same for the current record
            $rules['form.slug'] .= ',' . $this->editingId;
        }
        $this->validate($rules);
        $data = $this->form;
        if ($this->editingId) {
            $venue = Venue::findOrFail($this->editingId);
            $venue->update($data);
        } else {
            Venue::create($data);
        }
        $this->showCreateModal = false;
        $this->resetForm();
    }

    public function delete($id)
    {
        Venue::findOrFail($id)->delete();
    }

    public function confirmVenueRemoval($id)
    {
        $this->delete($id);
    }

    public function resetForm()
    {
        $this->form = [
            'venue' => '',
            'slug' => '',
            'is_active' => true,
        ];
    }

    public function updatedFormVenue($value)
    {
        // Always update slug when venue changes
        $this->form['slug'] = \Illuminate\Support\Str::slug($value);
    }
}
