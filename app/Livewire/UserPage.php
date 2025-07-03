<?php
namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class UserPage extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $showCreateModal = false;
    public $editingId = null;
    public $form = [
        'name' => '',
        'email' => '',
        'email_verified_at' => '',
        'password' => '',
    ];

    protected $rules = [
        'form.name' => 'required|string|max:255',
        'form.email' => 'required|email|max:255|unique:users,email',
        'form.email_verified_at' => 'nullable|date',
        'form.password' => 'required|string|min:6',
    ];

    public function render()
    {
        $query = User::query();
        if ($this->search) {
            $query->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%");
        }
        return view('livewire.user-page', [
            'users' => $query->paginate($this->perPage),
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
        $user = User::findOrFail($id);
        $this->form = [
            'name' => $user->name,
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at,
            'password' => '',
        ];
        $this->editingId = $id;
        $this->showCreateModal = true;
    }

    public function save()
    {
        $rules = $this->rules;
        if ($this->editingId) {
            $rules['form.password'] = 'nullable|string|min:6';
            $rules['form.email'] = [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->editingId),
            ];
        }
        $this->validate($rules);
        $data = $this->form;
        if ($data['password']) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        if ($this->editingId) {
            $user = User::findOrFail($this->editingId);
            $user->update($data);
        } else {
            User::create($data);
        }
        $this->showCreateModal = false;
        $this->resetForm();
    }

    public function delete($id)
    {
        User::findOrFail($id)->delete();
    }

    public function confirmUserRemoval($id)
    {
        $this->delete($id);
    }

    public function resetForm()
    {
        $this->form = [
            'name' => '',
            'email' => '',
            'email_verified_at' => '',
            'password' => '',
        ];
    }
}
