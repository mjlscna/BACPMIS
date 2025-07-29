<?php
namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class UserPage extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $showCreateModal = false;
    public $showViewModal = false;
    public $editingId = null;
    public $viewingId = null;
    public $form = [
        'name' => '',
        'email' => '',
        'email_verified_at' => '',
        'password' => '',
    ];
    public $viewData = [];

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

    public function view($id)
    {
        $user = User::findOrFail($id);
        $this->viewData = [
            'name' => $user->name,
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at,
            'created_at' => $user->created_at,
        ];
        $this->viewingId = $id;
        $this->showViewModal = true;
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
            LivewireAlert::title('User updated!')->success()->toast()->position('top-end')->show();
        } else {
            User::create($data);
            LivewireAlert::title('User created!')->success()->toast()->position('top-end')->show();
        }
        $this->showCreateModal = false;
        $this->resetForm();
    }

    public function delete($id)
    {
        User::findOrFail($id)->delete();
        LivewireAlert::title('User deleted!')->success()->toast()->position('top-end')->show();
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
