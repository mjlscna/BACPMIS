<?php

namespace App\Livewire\Partials;

use Livewire\Component;
use App\Services\ApiService;

class Navbar extends Component
{
    public $user;
    public $userPhoto;

    public function mount()
    {
        $this->user = session('user');

        // Fetch user photo from ApiService
        $apiService = new ApiService();
        $this->userPhoto = $apiService->fetchUserPhoto($this->user)
            ?? asset('storage/employees/default.png');

        // Optional: store in session if you want to reuse elsewhere
        session(['user_photo' => $this->userPhoto]);
    }

    public function render()
    {
        return view('livewire.partials.navbar');
    }
}
